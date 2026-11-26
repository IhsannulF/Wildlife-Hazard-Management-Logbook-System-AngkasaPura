/**
 * WHMS Weather Widget — cuaca real-time berbasis lokasi pengguna.
 * Sumber: HTML5 Geolocation + Open-Meteo (tanpa API key).
 * Fallback: koordinat default Surabaya bila lokasi tidak tersedia.
 * Cache: localStorage selama 15 menit.
 */
const WHMS_WEATHER = (() => {
  const CACHE_KEY = 'whms_weather_cache_v1';
  const CACHE_TTL_MS = 15 * 60 * 1000;
  const DEFAULT_LOCATION = { latitude: -7.2575, longitude: 112.7521, label: 'Surabaya (lokasi default)' };
  const GEO_TIMEOUT_MS = 10000;

  /**
   * WMO Weather interpretation codes → deskripsi Bahasa Indonesia, ikon Material Symbols,
   * dan kandidat nilai dropdown "Kondisi Cuaca" pada form laporan (urut prioritas).
   */
  const WMO_CODES = {
    0: { label: 'Cerah', icon: 'sunny', tone: 'amber', form: ['Cerah'] },
    1: { label: 'Cerah Berawan', icon: 'partly_cloudy_day', tone: 'amber', form: ['Cerah', 'Berawan'] },
    2: { label: 'Berawan Sebagian', icon: 'partly_cloudy_day', tone: 'slate', form: ['Berawan'] },
    3: { label: 'Mendung', icon: 'cloud', tone: 'slate', form: ['Berawan', 'Mendung'] },
    45: { label: 'Berkabut', icon: 'foggy', tone: 'slate', form: ['Berkabut'] },
    48: { label: 'Kabut Beku', icon: 'foggy', tone: 'slate', form: ['Berkabut'] },
    51: { label: 'Gerimis Ringan', icon: 'rainy', tone: 'sky', form: ['Hujan Ringan', 'Gerimis'] },
    53: { label: 'Gerimis', icon: 'rainy', tone: 'sky', form: ['Hujan Ringan', 'Gerimis'] },
    55: { label: 'Gerimis Lebat', icon: 'rainy', tone: 'sky', form: ['Hujan Ringan', 'Gerimis'] },
    56: { label: 'Gerimis Beku', icon: 'weather_mix', tone: 'sky', form: ['Hujan Ringan'] },
    57: { label: 'Gerimis Beku Lebat', icon: 'weather_mix', tone: 'sky', form: ['Hujan Ringan'] },
    61: { label: 'Hujan Ringan', icon: 'rainy', tone: 'sky', form: ['Hujan Ringan'] },
    63: { label: 'Hujan Sedang', icon: 'rainy', tone: 'sky', form: ['Hujan Sedang', 'Hujan Ringan'] },
    65: { label: 'Hujan Lebat', icon: 'rainy', tone: 'indigo', form: ['Hujan Lebat'] },
    66: { label: 'Hujan Beku Ringan', icon: 'weather_mix', tone: 'sky', form: ['Hujan Ringan'] },
    67: { label: 'Hujan Beku Lebat', icon: 'weather_mix', tone: 'indigo', form: ['Hujan Lebat'] },
    71: { label: 'Salju Ringan', icon: 'weather_snowy', tone: 'sky', form: [] },
    73: { label: 'Salju', icon: 'weather_snowy', tone: 'sky', form: [] },
    75: { label: 'Salju Lebat', icon: 'weather_snowy', tone: 'sky', form: [] },
    77: { label: 'Butiran Salju', icon: 'weather_snowy', tone: 'sky', form: [] },
    80: { label: 'Hujan Lokal Ringan', icon: 'rainy', tone: 'sky', form: ['Hujan Ringan'] },
    81: { label: 'Hujan Lokal Sedang', icon: 'rainy', tone: 'sky', form: ['Hujan Sedang', 'Hujan Ringan'] },
    82: { label: 'Hujan Lokal Lebat', icon: 'rainy', tone: 'indigo', form: ['Hujan Lebat'] },
    85: { label: 'Hujan Salju Ringan', icon: 'weather_snowy', tone: 'sky', form: [] },
    86: { label: 'Hujan Salju Lebat', icon: 'weather_snowy', tone: 'sky', form: [] },
    95: { label: 'Hujan Petir', icon: 'thunderstorm', tone: 'violet', form: ['Hujan Petir', 'Hujan Lebat'] },
    96: { label: 'Hujan Petir disertai Es', icon: 'thunderstorm', tone: 'violet', form: ['Hujan Petir', 'Hujan Lebat'] },
    99: { label: 'Hujan Petir Lebat disertai Es', icon: 'thunderstorm', tone: 'violet', form: ['Hujan Petir', 'Hujan Lebat'] },
  };

  const TONE_CLASSES = {
    amber: 'bg-amber-50 text-amber-500 border-amber-200',
    slate: 'bg-slate-100 text-slate-500 border-slate-200',
    sky: 'bg-sky-50 text-sky-600 border-sky-200',
    indigo: 'bg-indigo-50 text-indigo-600 border-indigo-200',
    violet: 'bg-violet-50 text-violet-600 border-violet-200',
  };

  function describe(code) {
    return WMO_CODES[code] || { label: 'Tidak Diketahui', icon: 'help', tone: 'slate', form: [] };
  }

  // ── Cache (localStorage) ──
  function readCache() {
    try {
      const raw = window.localStorage.getItem(CACHE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  }

  function writeCache(entry) {
    try {
      window.localStorage.setItem(CACHE_KEY, JSON.stringify(entry));
    } catch (e) {
      // Storage penuh / diblokir: widget tetap berjalan tanpa cache.
    }
  }

  function isFresh(entry) {
    return !!entry && typeof entry.timestamp === 'number' && (Date.now() - entry.timestamp) < CACHE_TTL_MS;
  }

  // ── Geolocation ──
  function getPosition() {
    return new Promise((resolve, reject) => {
      if (!('geolocation' in navigator)) {
        reject(new Error('Browser tidak mendukung geolokasi'));
        return;
      }
      if (window.isSecureContext === false) {
        reject(new Error('Geolokasi memerlukan koneksi HTTPS'));
        return;
      }
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
        (err) => {
          const reasons = {
            1: 'Izin lokasi ditolak',
            2: 'Lokasi tidak dapat ditentukan',
            3: 'Waktu permintaan lokasi habis',
          };
          reject(new Error(reasons[err.code] || 'Lokasi tidak tersedia'));
        },
        { enableHighAccuracy: false, timeout: GEO_TIMEOUT_MS, maximumAge: CACHE_TTL_MS }
      );
    });
  }

  // ── Open-Meteo ──
  async function fetchWeather(latitude, longitude) {
    const url = 'https://api.open-meteo.com/v1/forecast'
      + `?latitude=${encodeURIComponent(latitude)}&longitude=${encodeURIComponent(longitude)}`
      + '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=auto';

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 8000);
    try {
      const res = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
      if (!res.ok) {
        throw new Error(`Open-Meteo HTTP ${res.status}`);
      }
      const json = await res.json();
      if (!json.current) {
        throw new Error('Respons Open-Meteo tidak lengkap');
      }
      return json;
    } finally {
      clearTimeout(timer);
    }
  }

  function buildEntry(json, coords, isFallback, note) {
    const c = json.current;
    return {
      timestamp: Date.now(),
      latitude: coords.latitude,
      longitude: coords.longitude,
      isFallback,
      note: note || null,
      data: {
        time: c.time,
        temperature: c.temperature_2m,
        humidity: c.relative_humidity_2m,
        weatherCode: c.weather_code,
        windSpeed: c.wind_speed_10m,
        units: json.current_units || {},
      },
    };
  }

  /**
   * Ambil cuaca: cache segar → lokasi pengguna → fallback Surabaya → cache kedaluwarsa.
   *
   * @param {boolean} force abaikan cache segar (tombol muat ulang)
   */
  async function load(force = false) {
    const cached = readCache();
    if (!force && isFresh(cached)) {
      return { ...cached, fromCache: true };
    }

    let coords = null;
    let fallbackReason = null;
    try {
      coords = await getPosition();
    } catch (e) {
      fallbackReason = e.message;
    }

    if (coords) {
      try {
        const entry = buildEntry(await fetchWeather(coords.latitude, coords.longitude), coords, false);
        writeCache(entry);
        return entry;
      } catch (e) {
        fallbackReason = 'Gagal memuat cuaca lokasi Anda';
      }
    }

    try {
      const entry = buildEntry(
        await fetchWeather(DEFAULT_LOCATION.latitude, DEFAULT_LOCATION.longitude),
        DEFAULT_LOCATION,
        true,
        fallbackReason
      );
      writeCache(entry);
      return entry;
    } catch (e) {
      if (cached && cached.data) {
        return { ...cached, fromCache: true, stale: true };
      }
      throw new Error(navigator.onLine === false ? 'Perangkat sedang offline' : 'Layanan cuaca tidak dapat dihubungi');
    }
  }

  // ── Rendering ──
  function setState(root, state) {
    root.querySelectorAll('[data-weather-state]').forEach((el) => {
      el.classList.toggle('hidden', el.getAttribute('data-weather-state') !== state);
    });
    root.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
  }

  function setText(root, role, value) {
    const el = root.querySelector(`[data-weather="${role}"]`);
    if (el) {
      el.textContent = value;
    }
  }

  function formatNumber(value, digits = 0) {
    return typeof value === 'number' && isFinite(value)
      ? value.toLocaleString('id-ID', { maximumFractionDigits: digits, minimumFractionDigits: digits })
      : '–';
  }

  function render(root, entry) {
    const info = describe(entry.data.weatherCode);

    setText(root, 'temperature', formatNumber(entry.data.temperature, 1));
    setText(root, 'condition', info.label);
    setText(root, 'humidity', formatNumber(entry.data.humidity));
    setText(root, 'wind', formatNumber(entry.data.windSpeed, 1));

    const iconWrap = root.querySelector('[data-weather="icon-wrap"]');
    if (iconWrap) {
      iconWrap.className = `w-12 h-12 rounded-xl border flex items-center justify-center shrink-0 ${TONE_CLASSES[info.tone] || TONE_CLASSES.slate}`;
    }
    setText(root, 'icon', info.icon);

    const location = entry.isFallback
      ? DEFAULT_LOCATION.label
      : `Lokasi Anda (${formatNumber(entry.latitude, 3)}, ${formatNumber(entry.longitude, 3)})`;
    setText(root, 'location', location);

    const updated = new Date(entry.timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    setText(root, 'updated', `Diperbarui ${updated}${entry.stale ? ' (data lama)' : ''}`);

    const notice = root.querySelector('[data-weather="notice"]');
    if (notice) {
      const message = entry.isFallback
        ? `${entry.note ? entry.note + '. ' : ''}Menampilkan cuaca lokasi default Surabaya.`
        : (entry.stale ? 'Koneksi bermasalah — menampilkan data cuaca terakhir.' : '');
      notice.textContent = message;
      notice.classList.toggle('hidden', message === '');
    }

    setState(root, 'ready');
  }

  /**
   * Isi otomatis <select data-weather-autofill> bila petugas belum memilih sendiri.
   */
  function autofillSelects(entry) {
    const info = describe(entry.data.weatherCode);
    document.querySelectorAll('select[data-weather-autofill]').forEach((select) => {
      if (select.value !== '' || select.dataset.weatherUserChanged === '1') {
        return;
      }
      const options = Array.from(select.options);
      const match = info.form
        .map((candidate) => options.find((opt) => opt.value.trim().toLowerCase() === candidate.toLowerCase()))
        .find(Boolean);
      if (!match) {
        return;
      }
      select.value = match.value;
      select.dataset.weatherAutofilled = '1';
      select.dispatchEvent(new Event('change', { bubbles: true }));

      const hint = document.querySelector(`[data-weather-autofill-hint="${select.name}"]`);
      if (hint) {
        hint.textContent = `Terisi otomatis: ${info.label}${entry.isFallback ? ' (lokasi default Surabaya)' : ''}. Ubah bila kondisi lapangan berbeda.`;
        hint.classList.remove('hidden');
      }
    });
  }

  async function refresh(root, force = false) {
    setState(root, 'loading');
    try {
      const entry = await load(force);
      render(root, entry);
      autofillSelects(entry);
      document.dispatchEvent(new CustomEvent('whms:weather', { detail: { ...entry, description: describe(entry.data.weatherCode) } }));
    } catch (e) {
      setText(root, 'error', e.message);
      setState(root, 'error');
    }
  }

  function init() {
    document.querySelectorAll('select[data-weather-autofill]').forEach((select) => {
      select.addEventListener('change', (event) => {
        if (event.isTrusted) {
          select.dataset.weatherUserChanged = '1';
        }
      });
    });

    document.querySelectorAll('[data-whms-weather]').forEach((root) => {
      root.querySelectorAll('[data-weather-action="refresh"]').forEach((btn) => {
        btn.addEventListener('click', () => refresh(root, true));
      });
      refresh(root);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return { load, describe, refresh };
})();
