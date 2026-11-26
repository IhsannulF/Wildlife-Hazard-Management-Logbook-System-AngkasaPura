<?php

namespace App\Http\Controllers;

use App\Models\DetailSatwa;
use App\Models\FormField;
use App\Models\FotoLaporan;
use App\Models\GridCell;
use App\Models\IdentifikasiSatwaAi;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Models\MasterAlatDispersal;
use App\Models\TindakanPengusiran;
use App\Models\User;
use App\Services\IcaoRiskMatrixService;
use App\Services\Risk\GridRiskService;
use App\Support\GridCode;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    /**
     * Lokasi GPS perangkat & waktu saat foto bukti diambil/diunggah (diisi skrip watermark di form).
     *
     * @var array<string, list<string>>
     */
    private const PHOTO_LOCATION_RULES = [
        'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
        'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
        'gps_akurasi_m' => ['nullable', 'integer', 'min:0', 'max:100000'],
        'foto_diambil_at' => ['nullable', 'date'],
        'foto_sumber' => ['nullable', 'in:kamera,galeri'],
    ];

    /**
     * Metadata lokasi foto dari input form/payload sinkron; kosong bila perangkat tidak memberi lokasi.
     *
     * @param  array<string, mixed>  $input
     * @return array{gps_lat: float|null, gps_lng: float|null, gps_akurasi_m: int|null, diambil_at: string|null, sumber: string|null}
     */
    private function photoLocationAttributes(array $input): array
    {
        $number = fn (string $key): ?float => isset($input[$key]) && is_numeric($input[$key]) ? (float) $input[$key] : null;
        $takenAt = $input['foto_diambil_at'] ?? null;

        return [
            'gps_lat' => $number('gps_lat'),
            'gps_lng' => $number('gps_lng'),
            'gps_akurasi_m' => $number('gps_akurasi_m') !== null ? (int) round($number('gps_akurasi_m')) : null,
            'diambil_at' => $takenAt && strtotime((string) $takenAt) ? date('Y-m-d H:i:s', strtotime((string) $takenAt)) : null,
            'sumber' => in_array($input['foto_sumber'] ?? null, ['kamera', 'galeri'], true) ? $input['foto_sumber'] : null,
        ];
    }

    public function create()
    {
        $user = Auth::user();

        // Opsi dropdown dari DB berdasarkan akun departemen
        $mapJabatanUnitKerja = [
            'amc' => 'Apron Movement Control (AMC)',
            'pk' => 'Airport Rescue & Fire Fighting (PK / ARFF)',
            'security' => 'Airport Security (Avsec)',
            'safety' => 'Safety Management System (SMS)',
            'Apron Movement Control (AMC)' => 'Apron Movement Control (AMC)',
            'Airport Rescue & Fire Fighting (PK / ARFF)' => 'Airport Rescue & Fire Fighting (PK / ARFF)',
            'Airport Security (Avsec)' => 'Airport Security (Avsec)',
            'Safety Management System (SMS)' => 'Safety Management System (SMS)',
            'AMC Divisi' => 'Apron Movement Control (AMC)',
            'ARFF Divisi' => 'Airport Rescue & Fire Fighting (PK / ARFF)',
            'Security Divisi' => 'Airport Security (Avsec)',
            'SMSOHS Divisi' => 'Safety Management System (SMS)',
        ];
        $unitKerjaUser = $mapJabatanUnitKerja[$user->username] ?? $mapJabatanUnitKerja[$user->namalengkap] ?? '';

        $formFields = FormField::where('aktif', true)
            ->orderBy('urutan', 'asc')
            ->with('options')
            ->get();

        $fieldLabels = [];
        $aktifFields = [];
        foreach ($formFields as $f) {
            $fieldLabels[$f->nama_field] = $f->label;
            $aktifFields[$f->nama_field] = true;
        }

        // Pengaturan halaman admin "Form Inspeksi": tampil/wajib/label per isian bawaan + isian tambahan.
        $allFields = FormField::query()->with('options')->orderBy('urutan')->orderBy('id')->get();
        $fieldConfig = $allFields->mapWithKeys(fn (FormField $field): array => [$field->nama_field => [
            'label' => $field->label,
            'placeholder' => $field->placeholder,
            'aktif' => $field->aktif || $field->isLocked(),
            'wajib' => $field->wajib || $field->isLocked(),
        ]])->all();
        $customFields = $allFields->filter(fn (FormField $field): bool => $field->aktif && ! $field->isStandard())->values();

        $kondisi_cuaca_opt = $this->getDropdownOpsi('kondisi_cuaca', ['Cerah', 'Berawan', 'Hujan Ringan', 'Hujan Lebat', 'Berkabut']);
        $unit_kerja_opt = $this->getDropdownOpsi('unit_kerja', ['Apron Movement Control', 'Airport Rescue & Fire Fighting', 'Airport Security', 'SMS & OHS']);
        $area_opt = $this->getDropdownOpsi('area_inspeksi', ['Apron', 'Taxiway', 'Runway', 'Perimeter', 'Terminal', 'Gedung Operasional', 'Area Parkir']);
        $kondisi_apron_opt = $this->getDropdownOpsi('kondisi_apron', ['Hidup', 'Mati', 'Tidak ditemukan']);
        $tindak_lanjut_opt = $this->getDropdownOpsi('tindak_lanjut', ['Telah ditangani', 'Belum ditangani']);

        $satwa_list = JenisSatwa::orderByRaw('CASE WHEN id=1 THEN 1 WHEN id=2 THEN 2 WHEN id=6 THEN 3 WHEN id=9 THEN 4 WHEN id=10 THEN 5 WHEN id=8 THEN 6 ELSE 7 END, id ASC')->get();

        $gridField = FormField::where('tipe', 'grid')->first();
        $gridmapImage = ($gridField && $gridField->gridmap_path) ? $gridField->gridmap_path : 'gridmap_injourney.jpeg';
        $gridAgg = $this->getGridAggregation();
        $alat_dispersal_opt = MasterAlatDispersal::query()->aktif()->orderBy('id')->get(['id', 'nama_alat']);

        return view('user.form-pengaduan', compact(
            'alat_dispersal_opt',
            'user',
            'unitKerjaUser',
            'formFields',
            'fieldConfig',
            'customFields',
            'fieldLabels',
            'aktifFields',
            'kondisi_cuaca_opt',
            'unit_kerja_opt',
            'area_opt',
            'kondisi_apron_opt',
            'tindak_lanjut_opt',
            'satwa_list',
            'gridmapImage',
            'gridAgg'
        ));
    }

    /**
     * Ringkasan temuan per grid untuk peta form pegawai: data laporan asli dalam jendela zonasi
     * (default 180 hari) dan kelas risiko F1. Tidak ada data baseline hardcode.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getGridAggregation(): array
    {
        $gridRisk = app(GridRiskService::class);
        $from = CarbonImmutable::now(config('whms_risk.zona_waktu'))->subDays((int) config('whms_risk.zonasi.jendela_hari'));
        $zonasi = $gridRisk->scores($from);
        $cells = GridCell::query()->get()->keyBy('grid');

        $rows = DetailSatwa::query()
            ->join('laporan', 'laporan.id', '=', 'detail_satwa.laporan_id')
            ->whereDate('laporan.tanggal', '>=', $from->toDateString())
            ->whereNotNull('detail_satwa.grid')
            ->where('detail_satwa.grid', '!=', '')
            ->get(['detail_satwa.grid', 'detail_satwa.nama_satwa', 'detail_satwa.jumlah']);

        $gridAgg = [];
        foreach ($rows as $row) {
            $grid = (string) GridCode::normalize($row->grid);
            if (! GridCode::isValid($grid)) {
                continue;
            }

            $species = $row->nama_satwa ?: 'Satwa Liar';
            $gridAgg[$grid] ??= ['grid' => $grid, 'count' => 0, 'total_satwa' => 0, 'species_counts' => []];
            $gridAgg[$grid]['count']++;
            $gridAgg[$grid]['total_satwa'] += max(1, (int) $row->jumlah);
            $gridAgg[$grid]['species_counts'][$species] = ($gridAgg[$grid]['species_counts'][$species] ?? 0) + max(1, (int) $row->jumlah);
        }

        foreach ($gridAgg as $grid => &$item) {
            arsort($item['species_counts']);
            $zone = $zonasi[$grid] ?? null;
            $label = $zone ? GridRiskService::CLASS_LABELS[$zone['kelas']] : 'Tanpa Data Burung';

            $item['dominant_species'] = array_key_first($item['species_counts']);
            $item['zone_type'] = $cells->get($grid)?->zona ?? 'perimeter';
            $item['skor_zonasi'] = $zone['skor'] ?? null;
            $item['risk_level'] = $label;
            $item['risk_category'] = $zone ? sprintf('%s (S = %s)', $label, number_format($zone['skor'], 1, ',', '.')) : $label;
        }
        unset($item);

        return $gridAgg;
    }

    private function getDropdownOpsi(string $namaField, array $fallback): array
    {
        $field = FormField::where('nama_field', $namaField)->with('options')->first();
        if ($field && $field->options->isNotEmpty()) {
            return $field->options->pluck('nilai')->toArray();
        }

        return $fallback;
    }

    /**
     * Matriks risiko ICAO Doc 9137 dari total ekor per jenis; dipakai form web dan sinkron offline.
     *
     * @param  array<int, array{nama?: string, lokasi?: array<int, array<string, mixed>>}>  $satwaInput
     * @return array{score: int, level: string}
     */
    private function assessIcaoRisk(array $satwaInput, ?string $areaInspeksi, ?string $grid): array
    {
        $satwaCalculationData = [];
        foreach ($satwaInput as $satwa) {
            $nama = $satwa['nama'] ?? '';
            $jumlah = collect($satwa['lokasi'] ?? [])->sum(fn ($lokasi): int => (int) ($lokasi['jumlah'] ?? 0));

            if ($jumlah > 0 || $nama !== '') {
                $satwaCalculationData[] = ['nama' => $nama, 'jumlah' => $jumlah];
            }
        }

        $assessment = app(IcaoRiskMatrixService::class)->calculate($satwaCalculationData, $areaInspeksi, $grid);

        return ['score' => $assessment['score'], 'level' => $assessment['level']];
    }

    /**
     * Ambil atribut perilaku kawanan yang valid dari satu baris lokasi satwa; nilai di luar daftar diabaikan.
     *
     * @param  array<string, mixed>  $lokasi
     * @return array{perilaku: string|null, arah_terbang: string|null, ketinggian: string|null}
     */
    private function flockBehaviorAttributes(array $lokasi): array
    {
        $pick = fn (string $key, array $allowed): ?string => isset($lokasi[$key]) && array_key_exists((string) $lokasi[$key], $allowed)
            ? (string) $lokasi[$key]
            : null;

        return [
            'perilaku' => $pick('perilaku', DetailSatwa::PERILAKU),
            'arah_terbang' => $pick('arah_terbang', DetailSatwa::ARAH_TERBANG),
            'ketinggian' => $pick('ketinggian', DetailSatwa::KETINGGIAN),
        ];
    }

    /**
     * Catat tindakan pengusiran yang dilakukan saat inspeksi pada waktu dan grid laporan.
     */
    private function recordDispersalAction(Laporan $laporan, User $user, ?int $alatId, ?string $hasil): void
    {
        if (! $alatId || ! MasterAlatDispersal::query()->aktif()->whereKey($alatId)->exists()) {
            return;
        }

        TindakanPengusiran::create([
            'laporan_id' => $laporan->id,
            'alat_id' => $alatId,
            'user_id' => $user->id,
            'waktu' => Carbon::parse($laporan->tanggal->toDateString().' '.($laporan->jam ?: '00:00:00')),
            'grid' => $laporan->grid_lokasi ?: '-',
            'hasil' => array_key_exists((string) $hasil, TindakanPengusiran::HASIL) ? $hasil : null,
        ]);
    }

    /**
     * Draf PWA menyimpan isian form sebagai kunci datar (mis. "satwa[0][lokasi][0][jumlah]").
     * Ubah ke array bersarang agar dibaca sama seperti request form biasa.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function expandBracketKeys(array $payload): array
    {
        $hasFlatKeys = collect(array_keys($payload))->contains(fn ($key): bool => str_contains((string) $key, '['));
        if (! $hasFlatKeys) {
            return $payload;
        }

        parse_str(http_build_query($payload), $expanded);

        return $expanded;
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Ambil field aktif dan wajib
        $requiredFields = FormField::where('aktif', true)->where('wajib', true)->get();

        $rules = [
            'tanggal' => 'required|date',
            'jam' => ['required', 'date_format:H:i,H:i:s'],
            'satwa.*.lokasi.*.perilaku' => ['nullable', Rule::in(array_keys(DetailSatwa::PERILAKU))],
            'satwa.*.lokasi.*.arah_terbang' => ['nullable', Rule::in(array_keys(DetailSatwa::ARAH_TERBANG))],
            'satwa.*.lokasi.*.ketinggian' => ['nullable', Rule::in(array_keys(DetailSatwa::KETINGGIAN))],
            'pengusiran_alat_id' => ['nullable', 'integer', Rule::exists('master_alat_dispersal', 'id')->where('aktif', true)],
            'pengusiran_hasil' => ['nullable', Rule::in(array_keys(TindakanPengusiran::HASIL))],
            ...self::PHOTO_LOCATION_RULES,
        ];

        foreach ($requiredFields as $rf) {
            if ($rf->nama_field === 'tanda_tangan') {
                $rules['tanda_tangan'] = 'required|string';
            } elseif (! in_array($rf->nama_field, ['grid_lokasi', 'satwa', 'foto_lainnya'])) {
                $rules[$rf->nama_field] = 'required';
            }
        }

        // Isian tambahan buatan admin: wajib/opsional + jenis nilai (angka, tanggal, pilihan yang tersedia).
        $customFields = FormField::query()->with('options')->where('aktif', true)->whereNotIn('nama_field', FormField::STANDARD_FIELDS)->get();
        foreach ($customFields as $customField) {
            $rules[$customField->nama_field] = array_values(array_filter([
                $customField->wajib ? 'required' : 'nullable',
                match ($customField->tipe) {
                    'number' => 'numeric',
                    'date' => 'date',
                    'dropdown' => $customField->options->isNotEmpty() ? Rule::in($customField->options->pluck('nilai')->all()) : null,
                    default => 'string',
                },
                in_array($customField->tipe, ['text', 'textarea'], true) ? 'max:2000' : null,
            ]));
        }

        $validated = $request->validate($rules);

        // Validasi minimal 1 satwa atau 1 foto extra
        $satwaInput = $request->input('satwa', []);
        $adaSatwa = false;

        foreach ($satwaInput as $s) {
            foreach ($s['lokasi'] ?? [] as $lok) {
                if ((int) ($lok['jumlah'] ?? 0) > 0) {
                    $adaSatwa = true;
                    break 2;
                }
            }
        }

        $adaFotoExtra = $request->hasFile('foto_lainnya');

        if (! $adaSatwa && ! $adaFotoExtra) {
            return back()->withInput()->with('error', 'Pilih minimal satu satwa atau upload foto satwa tidak terdaftar.');
        }

        $statusLaporan = (stripos($request->input('tindak_lanjut', ''), 'telah ditangani') !== false) ? 'sudah' : 'belum';

        // Tentukan grid_lokasi utama
        $firstGrid = $request->input('grid_lokasi', '');
        if (empty($firstGrid)) {
            foreach ($satwaInput as $s) {
                foreach ($s['lokasi'] ?? [] as $lok) {
                    if (! empty($lok['grid'])) {
                        $firstGrid = $lok['grid'];
                        break 2;
                    }
                }
            }
        }

        $riskAssessment = $this->assessIcaoRisk($satwaInput, $request->input('area_inspeksi', ''), $firstGrid);

        $laporan = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => $request->input('nama_petugas', $user->namalengkap ?: $user->username),
            'tanggal' => $request->input('tanggal'),
            'jam' => $request->input('jam'),
            'kondisi_cuaca' => $request->input('kondisi_cuaca', ''),
            'unit_kerja' => $request->input('unit_kerja', ''),
            'area_inspeksi' => $request->input('area_inspeksi', ''),
            'tanda_tangan' => $request->input('tanda_tangan'),
            'grid_lokasi' => strtoupper($firstGrid ?: '-'),
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'ciri_ukuran' => $request->input('ciri_ukuran', '-'),
            'kondisi_apron' => $request->input('kondisi_apron', '-'),
            'aktivitas_satwa' => $request->input('aktivitas_satwa', '-'),
            'tindak_lanjut' => $request->input('tindak_lanjut', 'Belum ditangani'),
            'detail_pengusiran' => $request->input('detail_pengusiran', '-'),
            'risk_score' => $riskAssessment['score'],
            'risk_level' => $riskAssessment['level'],
            'status' => $statusLaporan,
        ]);

        // Simpan field dinamis ke extra_data (JSON)
        $standardFields = [
            'nama_petugas', 'tanggal', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
            'tanda_tangan', 'grid_lokasi', 'satwa', 'ciri_ukuran', 'kondisi_apron',
            'aktivitas_satwa', 'tindak_lanjut', 'detail_pengusiran', '_token', 'foto_lainnya',
        ];

        $extraData = [];
        $extraFieldsDef = FormField::where('aktif', true)->whereNotIn('nama_field', $standardFields)->get();
        foreach ($extraFieldsDef as $ef) {
            if ($request->has($ef->nama_field)) {
                $extraData[$ef->nama_field] = $request->input($ef->nama_field);
            }
        }
        if (! empty($extraData)) {
            $laporan->update(['extra_data' => $extraData]);
        }

        // Simpan Detail Satwa & Foto
        foreach ($satwaInput as $idx => $s) {
            $namaSatwa = $s['nama'] ?? '';
            $locations = $s['lokasi'] ?? [];

            foreach ($locations as $lokIdx => $lok) {
                $jumlah = (int) ($lok['jumlah'] ?? 0);
                if ($jumlah > 0) {
                    $grid = strtoupper($lok['grid'] ?? '');

                    $detail = DetailSatwa::create([
                        'laporan_id' => $laporan->id,
                        'nama_satwa' => $namaSatwa,
                        'nama_latin' => $s['nama_latin'] ?? $request->input('nama_latin'),
                        'jumlah' => $jumlah,
                        'grid' => $grid,
                        'sumber_identifikasi' => $s['sumber_identifikasi'] ?? ($request->filled('ai_identifikasi_id') ? 'ai' : 'manual'),
                        ...$this->flockBehaviorAttributes($lok),
                    ]);

                    // Cek upload foto per satwa
                    if ($request->hasFile("satwa.{$idx}.foto")) {
                        $file = $request->file("satwa.{$idx}.foto");
                        if ($file->isValid()) {
                            $filename = 'satwa_'.$laporan->id.'_'.$detail->id.'_'.time().'.'.$file->getClientOriginalExtension();
                            $file->storeAs('uploads/satwa', $filename, 'public');

                            FotoLaporan::create([
                                'laporan_id' => $laporan->id,
                                'detail_satwa_id' => $detail->id,
                                'nama_file' => $filename,
                                'tipe' => 'satwa',
                                'foto_path' => 'uploads/satwa/'.$filename,
                                ...$this->photoLocationAttributes($request->all()),
                            ]);
                        }
                    }
                }
            }
        }

        // Simpan Foto Extra (Satwa Tidak Terdaftar)
        if ($request->hasFile('foto_lainnya')) {
            $extraFiles = $request->file('foto_lainnya');
            if (! is_array($extraFiles)) {
                $extraFiles = [$extraFiles];
            }

            foreach ($extraFiles as $eIdx => $efile) {
                if ($efile && $efile->isValid()) {
                    $extFilename = 'extra_'.$laporan->id.'_'.$eIdx.'_'.time().'.'.$efile->getClientOriginalExtension();
                    $efile->storeAs('uploads/extra', $extFilename, 'public');

                    FotoLaporan::create([
                        'laporan_id' => $laporan->id,
                        'detail_satwa_id' => null,
                        'nama_file' => $extFilename,
                        'tipe' => 'extra',
                        'foto_path' => 'uploads/extra/'.$extFilename,
                    ]);
                }
            }
        }

        $this->recordDispersalAction(
            $laporan,
            $user,
            $request->integer('pengusiran_alat_id') ?: null,
            $request->input('pengusiran_hasil')
        );

        if ($request->filled('ai_identifikasi_id')) {
            IdentifikasiSatwaAi::query()
                ->where('id', $request->input('ai_identifikasi_id'))
                ->where('user_id', $user->id)
                ->update(['laporan_id' => $laporan->id]);
        }

        return redirect()->route('user.dashboard')->with('sukses', 'Laporan berhasil dikirim dan tersimpan di sistem.');
    }

    /**
     * Endpoint API sinkronisasi antrean draf PWA offline ke server.
     * Menerapkan mekanisme idempotensi berbasis offline_sync_id (TEST-SYNC-01).
     */
    public function syncOffline(Request $request): JsonResponse
    {
        $user = Auth::user();

        $offlineSyncId = (string) $request->input('offline_sync_id', '');
        if (empty($offlineSyncId)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter offline_sync_id wajib disertakan.',
            ], 422);
        }

        // 1. Idempotency check: jika offline_sync_id sudah ada, tolak duplikasi data (TEST-SYNC-01)
        $existing = Laporan::where('offline_sync_id', $offlineSyncId)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Draf offline sudah pernah disinkronkan sebelumnya (idempotent).',
                'offline_sync_id' => $offlineSyncId,
                'already_synced' => true,
                'laporan_id' => $existing->id,
                'no_laporan' => $existing->no_laporan,
            ], 200);
        }

        // Ekstrak data payload (mendukung format payload terbungkus maupun root request)
        $payload = $request->input('payload');
        if (! is_array($payload)) {
            $payload = $request->all();
        }
        $payload = $this->expandBracketKeys($payload);

        $waktuLokal = now(config('whms_risk.zona_waktu'));
        $tanggal = $payload['tanggal'] ?? $waktuLokal->format('Y-m-d');
        $jam = $payload['jam'] ?? $waktuLokal->format('H:i:s');
        $namaPetugas = $payload['nama_petugas'] ?? ($user->namalengkap ?: $user->username);
        $kondisiCuaca = $payload['kondisi_cuaca'] ?? '';
        $unitKerja = $payload['unit_kerja'] ?? '';
        $areaInspeksi = $payload['area_inspeksi'] ?? '';
        $ciriUkuran = $payload['ciri_ukuran'] ?? '-';
        $kondisiApron = $payload['kondisi_apron'] ?? '-';
        $aktivitasSatwa = $payload['aktivitas_satwa'] ?? '-';
        $tindakLanjut = $payload['tindak_lanjut'] ?? 'Belum ditangani';
        $detailPengusiran = $payload['detail_pengusiran'] ?? '-';
        $signature = $request->input('signature') ?: ($payload['tanda_tangan'] ?? null);

        $statusLaporan = (stripos($tindakLanjut, 'telah ditangani') !== false) ? 'sudah' : 'belum';

        $satwaInput = $payload['satwa'] ?? $request->input('satwa', []);
        if (is_string($satwaInput)) {
            $decoded = json_decode($satwaInput, true);
            if (is_array($decoded)) {
                $satwaInput = $decoded;
            }
        }

        // Tentukan grid_lokasi utama
        $firstGrid = $payload['grid_lokasi'] ?? ($payload['lokasi_grid'] ?? '');
        if (empty($firstGrid) && is_array($satwaInput)) {
            foreach ($satwaInput as $s) {
                foreach ($s['lokasi'] ?? [] as $lok) {
                    if (! empty($lok['grid'])) {
                        $firstGrid = $lok['grid'];
                        break 2;
                    }
                }
            }
        }

        $riskAssessment = $this->assessIcaoRisk(is_array($satwaInput) ? $satwaInput : [], $areaInspeksi, $firstGrid);

        $gpsLat = isset($payload['gps_lat']) && $payload['gps_lat'] !== '' ? (float) $payload['gps_lat'] : null;
        $gpsLng = isset($payload['gps_lng']) && $payload['gps_lng'] !== '' ? (float) $payload['gps_lng'] : null;

        $laporan = Laporan::create([
            'user_id' => $user->id,
            'offline_sync_id' => $offlineSyncId,
            'nama_petugas' => $namaPetugas,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'kondisi_cuaca' => $kondisiCuaca,
            'unit_kerja' => $unitKerja,
            'area_inspeksi' => $areaInspeksi,
            'tanda_tangan' => $signature,
            'grid_lokasi' => strtoupper($firstGrid ?: '-'),
            'gps_lat' => $gpsLat,
            'gps_lng' => $gpsLng,
            'risk_score' => $riskAssessment['score'] ?? 1,
            'risk_level' => $riskAssessment['level'] ?? 'rendah',
            'ciri_ukuran' => $ciriUkuran,
            'kondisi_apron' => $kondisiApron,
            'aktivitas_satwa' => $aktivitasSatwa,
            'tindak_lanjut' => $tindakLanjut,
            'detail_pengusiran' => $detailPengusiran,
            'status' => $statusLaporan,
        ]);

        // Simpan field dinamis ke extra_data (JSON)
        $standardFields = [
            'nama_petugas', 'tanggal', 'jam', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
            'tanda_tangan', 'grid_lokasi', 'satwa', 'ciri_ukuran', 'kondisi_apron',
            'aktivitas_satwa', 'tindak_lanjut', 'detail_pengusiran', '_token', 'foto_lainnya',
            'offline_sync_id', 'payload', 'photos', 'signature', 'created_at',
        ];

        $extraData = [];
        $extraFieldsDef = FormField::where('aktif', true)->whereNotIn('nama_field', $standardFields)->get();
        foreach ($extraFieldsDef as $ef) {
            if (isset($payload[$ef->nama_field])) {
                $extraData[$ef->nama_field] = $payload[$ef->nama_field];
            }
        }
        if (! empty($extraData)) {
            $laporan->update(['extra_data' => $extraData]);
        }

        // Simpan Detail Satwa
        $createdDetails = [];
        if (is_array($satwaInput)) {
            foreach ($satwaInput as $idx => $s) {
                $namaSatwa = $s['nama'] ?? '';
                $locations = $s['lokasi'] ?? [];

                foreach ($locations as $lokIdx => $lok) {
                    $jumlah = (int) ($lok['jumlah'] ?? 0);
                    if ($jumlah > 0) {
                        $grid = strtoupper($lok['grid'] ?? '');

                        $detail = DetailSatwa::create([
                            'laporan_id' => $laporan->id,
                            'nama_satwa' => $namaSatwa,
                            'jumlah' => $jumlah,
                            'grid' => $grid,
                            ...$this->flockBehaviorAttributes($lok),
                        ]);

                        $createdDetails[$idx] = $detail;
                    }
                }
            }
        }

        // Simpan Foto dari payload JSON (Base64)
        $photos = $request->input('photos', []);
        if (is_array($photos)) {
            foreach ($photos as $pIdx => $p) {
                $dataStr = $p['data'] ?? '';
                $fieldName = $p['field_name'] ?? '';
                $rawExt = pathinfo($p['name'] ?? 'photo.jpg', PATHINFO_EXTENSION) ?: 'jpg';

                if (empty($dataStr)) {
                    continue;
                }

                if (str_contains($dataStr, ';base64,')) {
                    [, $dataStr] = explode(';base64,', $dataStr);
                }
                $binary = base64_decode($dataStr);
                if ($binary === false) {
                    continue;
                }

                $detailId = null;
                $tipe = 'extra';
                $subFolder = 'extra';

                if (preg_match('/satwa\[(\d+)\]/', $fieldName, $matches)) {
                    $satwaIdx = (int) $matches[1];
                    if (isset($createdDetails[$satwaIdx])) {
                        $detailId = $createdDetails[$satwaIdx]->id;
                        $tipe = 'satwa';
                        $subFolder = 'satwa';
                    }
                }

                $filename = 'offline_'.$laporan->id.'_'.$pIdx.'_'.time().'.'.$rawExt;
                Storage::disk('public')->put('uploads/'.$subFolder.'/'.$filename, $binary);

                FotoLaporan::create([
                    'laporan_id' => $laporan->id,
                    'detail_satwa_id' => $detailId,
                    'nama_file' => $filename,
                    'tipe' => $tipe,
                    'foto_path' => 'uploads/'.$subFolder.'/'.$filename,
                    ...$this->photoLocationAttributes($payload),
                ]);
            }
        }

        // Dukungan simpan Foto multipart jika request dikirim sebagai multipart/form-data
        if ($request->hasFile('foto_lainnya')) {
            $extraFiles = $request->file('foto_lainnya');
            if (! is_array($extraFiles)) {
                $extraFiles = [$extraFiles];
            }

            foreach ($extraFiles as $eIdx => $efile) {
                if ($efile && $efile->isValid()) {
                    $extFilename = 'offline_extra_'.$laporan->id.'_'.$eIdx.'_'.time().'.'.$efile->getClientOriginalExtension();
                    $efile->storeAs('uploads/extra', $extFilename, 'public');

                    FotoLaporan::create([
                        'laporan_id' => $laporan->id,
                        'detail_satwa_id' => null,
                        'nama_file' => $extFilename,
                        'tipe' => 'extra',
                        'foto_path' => 'uploads/extra/'.$extFilename,
                    ]);
                }
            }
        }

        $this->recordDispersalAction(
            $laporan,
            $user,
            isset($payload['pengusiran_alat_id']) ? (int) $payload['pengusiran_alat_id'] : null,
            $payload['pengusiran_hasil'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan offline berhasil disinkronkan ke server.',
            'offline_sync_id' => $offlineSyncId,
            'laporan_id' => $laporan->id,
            'no_laporan' => $laporan->no_laporan,
        ], 200);
    }

    /**
     * Mengambil data detail laporan untuk modal pratinjau pegawai
     */
    public function detail(int $id)
    {
        $user = Auth::user();
        $query = Laporan::with([
            'user',
            'validator:id,namalengkap,username,jabatan,role',
            'detailSatwa.fotoLaporan',
            'fotoLaporan',
            'tanggapan' => function ($q) {
                $q->with('user')->orderBy('id', 'asc');
            },
        ]);

        // Isolasi data: jika bukan admin, hanya izinkan pemilik laporan
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $laporan = $query->findOrFail($id);

        $tanggapanList = [];
        foreach ($laporan->tanggapan as $t) {
            $author = $t->user ? ($t->user->namalengkap ?: $t->user->username) : 'Safety Officer';
            $authorRole = $t->user ? ($t->user->jabatan ?: ($t->user->role === 'admin' ? 'Safety Manager' : 'Petugas Lapangan')) : 'Validator';
            $tanggapanList[] = [
                'id' => $t->id,
                'laporan_id' => $t->laporan_id,
                'user_id' => $t->user_id,
                'kategori_tanggapan' => $t->kategori_tanggapan ?: 'instruksi',
                'isi' => $t->isi,
                'author_name' => $author,
                'author_role' => $authorRole,
                'is_admin' => $t->user ? $t->user->role === 'admin' : false,
                'created_at' => $t->created_at?->format('Y-m-d H:i:s'),
                'created_at_human' => $t->created_at ? $t->created_at->translatedFormat('d M Y, H:i').' WIB' : '-',
            ];
        }

        $data = $laporan->toArray();
        $data['tanggapan'] = $tanggapanList;
        $data['validator_name'] = $laporan->validator ? ($laporan->validator->namalengkap ?: $laporan->validator->username) : null;
        $data['validator_jabatan'] = $laporan->validator ? $laporan->validator->validator_display_title : null;
        $data['validated_at_human'] = $laporan->validated_at
            ? $laporan->validated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB'
            : null;

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
