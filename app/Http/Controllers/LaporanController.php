<?php

namespace App\Http\Controllers;

use App\Models\DetailSatwa;
use App\Models\FormField;
use App\Models\FotoLaporan;
use App\Models\IdentifikasiSatwaAi;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Models\MasterAlatDispersal;
use App\Models\TindakanPengusiran;
use App\Models\User;
use App\Services\IcaoRiskMatrixService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
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

    private function getGridAggregation(): array
    {
        $baselineGrids = [
            'K-10' => ['count' => 38, 'satwa' => 'Biawak Air', 'total' => 38, 'area' => 'Kanal Drainase Perimeter Selatan Terminal 2', 'type' => 'perimeter', 'category' => 'reptil', 'desc' => 'Zona perimeter strip selatan Juanda berdekatan dengan kanal drainase induk Terminal 2.'],
            'D-9' => ['count' => 44, 'satwa' => 'Burung Blekok Sawah', 'total' => 44, 'area' => 'Rawa Buffer Barat & Runway 10 Approach', 'type' => 'perimeter', 'category' => 'burung', 'desc' => 'Zona buffer rawa terbuka sering didatangi koloni burung air saat musim penghujan.'],
            'E-12' => ['count' => 29, 'satwa' => 'Kuntul Kerbau', 'total' => 29, 'area' => 'Apron C & Taxiway NP-2', 'type' => 'apron', 'category' => 'burung', 'desc' => 'Area rumput terbuka sekitar Apron C Juanda menjadi tempat mencari serangga.'],
            'A-2' => ['count' => 22, 'satwa' => 'Burung Layang-layang Api', 'total' => 22, 'area' => 'Perimeter Barat Laut', 'type' => 'perimeter', 'category' => 'burung', 'desc' => 'Burung walet dan layang-layang melintas di area perimeter barat laut.'],
            'G-18' => ['count' => 28, 'satwa' => 'Burung Cangak Abu', 'total' => 28, 'area' => 'Bahu Runway 10/28 (Tengah Landasan)', 'type' => 'runway', 'category' => 'burung', 'desc' => 'Burung air berukuran sedang sering hinggap di bahu rumput runway 10/28 saat menjelang senja.'],
            'H-8' => ['count' => 19, 'satwa' => 'Burung Elang Tikus', 'total' => 19, 'area' => 'Runway Strip 10 (Barat)', 'type' => 'runway', 'category' => 'burung', 'desc' => 'Burung pemangsa bertengger di rambu navigasi runway strip barat.'],
            'F-5' => ['count' => 16, 'satwa' => 'Kera Ekor Panjang', 'total' => 16, 'area' => 'Regulating Pond 1 & Perimeter Barat', 'type' => 'perimeter', 'category' => 'mamalia', 'desc' => 'Kawanan kera dari vegetasi luar melompati kawat perimeter dekat kolam retensi.'],
            'B-4' => ['count' => 12, 'satwa' => 'Anjing Liar', 'total' => 12, 'area' => 'Perimeter Utara Buffer', 'type' => 'perimeter', 'category' => 'mamalia', 'desc' => 'Anjing liar terpantau melintas di area perimeter utara.'],
            'L-10' => ['count' => 11, 'satwa' => 'Biawak Air', 'total' => 11, 'area' => 'Kanal Drainase Perimeter Selatan', 'type' => 'perimeter', 'category' => 'reptil', 'desc' => 'Saluran drainase primer perimeter selatan menjadi jalur jelajah biawak.'],
            'C-6' => ['count' => 8,  'satwa' => 'Ular Sanca Kembang', 'total' => 8,  'area' => 'Perimeter Barat Hanggar', 'type' => 'perimeter', 'category' => 'reptil', 'desc' => 'Ditemukan di sudut saluran drainase sekitar fasilitas teknis perimeter barat.'],
            'J-11' => ['count' => 17, 'satwa' => 'Kucing Liar', 'total' => 17, 'area' => 'Apron A Terminal 2', 'type' => 'apron', 'category' => 'mamalia', 'desc' => 'Kucing liar berkeliaran di dekat area ground support equipment Apron A Terminal 2.'],
            'E-18' => ['count' => 14, 'satwa' => 'Burung Blekok Sawah', 'total' => 14, 'area' => 'Apron B Terminal 1', 'type' => 'apron', 'category' => 'burung', 'desc' => 'Kawanan burung melintas rendah di jalur pergerakan pesawat Apron B Terminal 1.'],
        ];

        $gridAgg = [];
        foreach ($baselineGrids as $bgKey => $bgVal) {
            $gridAgg[$bgKey] = [
                'grid' => $bgKey,
                'count' => $bgVal['count'],
                'total_satwa' => $bgVal['total'],
                'dominant_species' => $bgVal['satwa'],
                'dominant_count' => $bgVal['total'],
                'species_counts' => [$bgVal['satwa'] => $bgVal['total']],
                'status' => 'Telah Ditangani',
                'zone_name' => $bgVal['area'],
                'zone_type' => $bgVal['type'],
                'desc' => $bgVal['desc'],
                'last_mitigation' => 'Aktivasi Sirene Patroli & Repellent Trap',
                'peak_hours' => '06:30 - 08:30 WIB & 16:00 - 17:30 WIB',
            ];
        }

        try {
            $details = DetailSatwa::with('laporan')
                ->whereNotNull('grid')
                ->where('grid', '!=', '')
                ->get();

            foreach ($details as $d) {
                $rawG = strtoupper(trim(str_replace('=', '-', $d->grid)));
                if (empty($rawG)) {
                    continue;
                }

                $rowLetter = substr($rawG, 0, 1);
                $zoneType = in_array($rowLetter, ['G', 'H']) ? 'runway' : (in_array($rowLetter, ['E', 'F']) ? 'taxiway' : (in_array($rowLetter, ['D', 'J']) ? 'apron' : 'perimeter'));
                $zoneName = $zoneType === 'runway' ? 'Bahu Runway 10/28 Juanda' : ($zoneType === 'taxiway' ? 'Taxiway & Rawa Buffer' : ($zoneType === 'apron' ? 'Apron Area Pergerakan' : 'Perimeter Sisi Udara'));

                if (! isset($gridAgg[$rawG])) {
                    $gridAgg[$rawG] = [
                        'grid' => $rawG,
                        'count' => 0,
                        'total_satwa' => 0,
                        'dominant_species' => $d->nama_satwa ?: 'Satwa Liar',
                        'dominant_count' => 0,
                        'species_counts' => [],
                        'status' => 'Belum Ditangani',
                        'zone_name' => $zoneName,
                        'zone_type' => $zoneType,
                        'desc' => "Lokasi temuan satwa di zona {$zoneName}.",
                        'last_mitigation' => 'Patroli Regu AMC',
                        'peak_hours' => '07:00 - 17:00 WIB',
                    ];
                }

                $sp = $d->nama_satwa ?: 'Satwa Liar';
                $gridAgg[$rawG]['count'] += 1;
                $gridAgg[$rawG]['total_satwa'] += ($d->jumlah ?: 1);
                $gridAgg[$rawG]['species_counts'][$sp] = ($gridAgg[$rawG]['species_counts'][$sp] ?? 0) + ($d->jumlah ?: 1);
            }
        } catch (\Throwable $e) {
            // Silently fallback to baseline grids
        }

        foreach ($gridAgg as $gCode => &$gItem) {
            $c = $gItem['count'];
            if ($c >= 30) {
                $gItem['risk_level'] = 'Kritis';
                $gItem['risk_category'] = 'Kategori 4 - Kritis';
                $gItem['risk_badge'] = 'bg-rose-500 text-white';
                $gItem['risk_border'] = 'border-rose-500';
                $gItem['risk_bg'] = 'bg-rose-500/20';
                $gItem['risk_text'] = 'text-rose-400';
            } elseif ($c >= 15) {
                $gItem['risk_level'] = 'Sedang';
                $gItem['risk_category'] = 'Kategori 3 - Flocking Sedang';
                $gItem['risk_badge'] = 'bg-amber-500 text-slate-900';
                $gItem['risk_border'] = 'border-amber-500';
                $gItem['risk_bg'] = 'bg-amber-500/20';
                $gItem['risk_text'] = 'text-amber-400';
            } else {
                $gItem['risk_level'] = 'Rendah';
                $gItem['risk_category'] = 'Kategori 2 - Terpantau';
                $gItem['risk_badge'] = 'bg-sky-500 text-white';
                $gItem['risk_border'] = 'border-sky-500';
                $gItem['risk_bg'] = 'bg-sky-500/20';
                $gItem['risk_text'] = 'text-sky-400';
            }
        }
        unset($gItem);

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
        ];

        foreach ($requiredFields as $rf) {
            if ($rf->nama_field === 'tanda_tangan') {
                $rules['tanda_tangan'] = 'required|string';
            } elseif (! in_array($rf->nama_field, ['grid_lokasi', 'satwa', 'foto_lainnya'])) {
                $rules[$rf->nama_field] = 'required';
            }
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
