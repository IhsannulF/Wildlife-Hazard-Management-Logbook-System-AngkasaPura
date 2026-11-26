/**
 * WHMS Photo Stamp — watermark bukti inspeksi pada foto satwa.
 *
 * Foto dari kamera maupun galeri digambar ulang di canvas dengan pita bawah berisi logo
 * InJourney Airports, "PT ANGKASA PURA INDONESIA · BUKTI INSPEKSI WHM", koordinat GPS
 * perangkat saat foto diambil/diunggah, waktu WIB, sumber foto, dan nama petugas.
 * Hasilnya JPEG baru; koordinat juga dikembalikan agar disimpan bersama laporan.
 */
(function () {
  'use strict';

  const MAX_SIDE = 1920;
  const TIMEZONE = 'Asia/Jakarta';

  /**
   * Lokasi perangkat saat ini; null bila izin ditolak, tidak didukung, atau melewati batas waktu.
   *
   * @returns {Promise<{lat: number, lng: number, accuracy: number}|null>}
   */
  function getLocation() {
    if (!('geolocation' in navigator)) {
      return Promise.resolve(null);
    }

    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
          accuracy: Math.round(pos.coords.accuracy || 0),
        }),
        () => resolve(null),
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
      );
    });
  }

  function loadImage(src) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = src;
    });
  }

  /** "26/11/2026 13:45:21 WIB" dan "2026-11-26 13:45:21" (untuk disimpan). */
  function wibTimestamps(date) {
    const parts = Object.fromEntries(
      new Intl.DateTimeFormat('en-GB', {
        timeZone: TIMEZONE, year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
      }).formatToParts(date).map((p) => [p.type, p.value])
    );
    const hour = parts.hour === '24' ? '00' : parts.hour;

    return {
      label: `${parts.day}/${parts.month}/${parts.year} ${hour}:${parts.minute}:${parts.second} WIB`,
      stored: `${parts.year}-${parts.month}-${parts.day} ${hour}:${parts.minute}:${parts.second}`,
      fileSuffix: `${parts.year}${parts.month}${parts.day}_${hour}${parts.minute}${parts.second}`,
    };
  }

  function roundedRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }

  function drawBanner(ctx, width, height, lines, logo) {
    const unit = Math.max(12, Math.round(width / 60));
    const padding = Math.round(unit * 0.9);
    const lineHeight = Math.round(unit * 1.35);
    const bannerHeight = padding * 2 + lineHeight * lines.length;
    const top = height - bannerHeight;

    const gradient = ctx.createLinearGradient(0, top - unit * 2, 0, height);
    gradient.addColorStop(0, 'rgba(15, 23, 42, 0)');
    gradient.addColorStop(0.25, 'rgba(15, 23, 42, 0.72)');
    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.9)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, top - unit * 2, width, bannerHeight + unit * 2);

    // Logo di atas kotak putih agar tetap terbaca di foto gelap maupun terang.
    let textLeft = padding;
    if (logo) {
      const logoHeight = bannerHeight - padding * 2;
      const logoWidth = Math.round(logoHeight * (logo.naturalWidth / logo.naturalHeight));
      ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
      roundedRect(ctx, padding, top + padding, logoWidth, logoHeight, Math.round(unit * 0.4));
      ctx.fill();
      ctx.drawImage(logo, padding, top + padding, logoWidth, logoHeight);
      textLeft = padding * 2 + logoWidth;
    }

    ctx.textBaseline = 'top';
    lines.forEach((line, index) => {
      ctx.font = `${index === 0 ? '700' : '500'} ${index === 0 ? Math.round(unit * 1.05) : unit}px "Segoe UI", Roboto, Arial, sans-serif`;
      ctx.fillStyle = index === 0 ? '#67E8F9' : '#F8FAFC';
      ctx.fillText(line, textLeft, top + padding + index * lineHeight, width - textLeft - padding);
    });
  }

  /**
   * Beri watermark bukti inspeksi pada foto.
   *
   * @param {File|Blob} file
   * @param {{source: 'kamera'|'galeri', officer?: string, logoUrl?: string}} options
   * @returns {Promise<{file: File, location: {lat:number,lng:number,accuracy:number}|null, takenAt: string, source: string}>}
   */
  async function stamp(file, options) {
    const source = options.source === 'galeri' ? 'galeri' : 'kamera';
    const [location, image, logo] = await Promise.all([
      getLocation(),
      loadImage(URL.createObjectURL(file)),
      options.logoUrl ? loadImage(options.logoUrl).catch(() => null) : Promise.resolve(null),
    ]);

    const scale = Math.min(1, MAX_SIDE / Math.max(image.naturalWidth, image.naturalHeight));
    const width = Math.round(image.naturalWidth * scale);
    const height = Math.round(image.naturalHeight * scale);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(image, 0, 0, width, height);
    URL.revokeObjectURL(image.src);

    const time = wibTimestamps(new Date());
    const locationLine = location
      ? `Lokasi ${source === 'galeri' ? 'saat unggah' : 'foto'}: ${location.lat.toFixed(6)}, ${location.lng.toFixed(6)} (±${location.accuracy} m)`
      : 'Lokasi tidak tersedia (izin GPS ditolak / sinyal lemah)';

    drawBanner(ctx, width, height, [
      'PT ANGKASA PURA INDONESIA · BUKTI INSPEKSI WHM',
      'Bandara Internasional Juanda (SUB/WARR)',
      locationLine,
      `${time.label} · ${source === 'galeri' ? 'Unggah galeri' : 'Kamera'}${options.officer ? ' · ' + options.officer : ''}`,
    ], logo);

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
    if (!blob) {
      throw new Error('Gagal membuat foto ber-watermark');
    }

    return {
      file: new File([blob], `bukti_inspeksi_${time.fileSuffix}.jpg`, { type: 'image/jpeg' }),
      location,
      takenAt: time.stored,
      source,
    };
  }

  window.WhmsPhotoStamp = { stamp, getLocation };
})();
