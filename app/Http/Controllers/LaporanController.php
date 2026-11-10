<?php

namespace App\Http\Controllers;

use App\Models\DetailSatwa;
use App\Models\FormField;
use App\Models\FotoLaporan;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Services\IcaoRiskMatrixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $gridAgg = [];

        return view('user.form-pengaduan', compact(
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

    public function store(Request $request)
    {
        $user = Auth::user();

        // Ambil field aktif dan wajib
        $requiredFields = FormField::where('aktif', true)->where('wajib', true)->get();

        $rules = [
            'tanggal' => 'required|date',
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

        // Kumpulkan data satwa untuk kalkulasi ICAO Risk Matrix
        $satwaCalculationData = [];
        foreach ($satwaInput as $s) {
            $namaSatwa = $s['nama'] ?? '';
            $totalJmlPerSatwa = 0;
            foreach ($s['lokasi'] ?? [] as $lok) {
                $totalJmlPerSatwa += (int) ($lok['jumlah'] ?? 0);
            }
            if ($totalJmlPerSatwa > 0 || ! empty($namaSatwa)) {
                $satwaCalculationData[] = [
                    'nama' => $namaSatwa,
                    'jumlah' => $totalJmlPerSatwa,
                ];
            }
        }

        // Hitung Matriks Risiko ICAO Doc 9137 Otomatis
        $riskService = app(IcaoRiskMatrixService::class);
        $riskAssessment = $riskService->calculate(
            $satwaCalculationData,
            $request->input('area_inspeksi', ''),
            $firstGrid
        );

        $laporan = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => $request->input('nama_petugas', $user->namalengkap ?: $user->username),
            'tanggal' => $request->input('tanggal'),
            'jam' => $request->input('jam', now()->format('H:i:s')),
            'kondisi_cuaca' => $request->input('kondisi_cuaca', ''),
            'unit_kerja' => $request->input('unit_kerja', ''),
            'area_inspeksi' => $request->input('area_inspeksi', ''),
            'tanda_tangan' => $request->input('tanda_tangan'),
            'grid_lokasi' => strtoupper($firstGrid ?: '-'),
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'risk_score' => $riskAssessment['score'],
            'risk_level' => $riskAssessment['level'],
            'ciri_ukuran' => $request->input('ciri_ukuran', '-'),
            'kondisi_apron' => $request->input('kondisi_apron', '-'),
            'aktivitas_satwa' => $request->input('aktivitas_satwa', '-'),
            'tindak_lanjut' => $request->input('tindak_lanjut', 'Belum ditangani'),
            'detail_pengusiran' => $request->input('detail_pengusiran', '-'),
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
                        'jumlah' => $jumlah,
                        'grid' => $grid,
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

        return redirect()->route('user.dashboard')->with('sukses', 'Laporan berhasil dikirim dan tersimpan di sistem.');
    }

    /**
     * Mengambil data detail laporan untuk modal pratinjau pegawai
     */
    public function detail(int $id)
    {
        $user = Auth::user();
        $query = Laporan::with([
            'user',
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

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
