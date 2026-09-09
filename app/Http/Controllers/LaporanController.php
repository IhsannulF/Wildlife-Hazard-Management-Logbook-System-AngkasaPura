<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Models\DetailSatwa;
use App\Models\FotoLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        // Opsi dropdown dari DB
        $mapJabatanUnitKerja = [
            'AMC Divisi'      => 'Apron Movement Control',
            'ARFF Divisi'     => 'Airport Rescue & Fire Fighting',
            'Security Divisi' => 'Airport Security',
            'SMSOHS Divisi'   => 'SMS & OHS',
        ];
        $unitKerjaUser = $mapJabatanUnitKerja[$user->namalengkap] ?? '';

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
        $unit_kerja_opt    = $this->getDropdownOpsi('unit_kerja', ['Apron Movement Control', 'Airport Rescue & Fire Fighting', 'Airport Security', 'SMS & OHS']);
        $area_opt          = $this->getDropdownOpsi('area_inspeksi', ['Apron', 'Taxiway', 'Runway', 'Perimeter', 'Terminal', 'Gedung Operasional', 'Area Parkir']);
        $kondisi_apron_opt = $this->getDropdownOpsi('kondisi_apron', ['Hidup', 'Mati', 'Tidak ditemukan']);
        $tindak_lanjut_opt = $this->getDropdownOpsi('tindak_lanjut', ['Telah ditangani', 'Belum ditangani']);

        $satwa_list = JenisSatwa::orderBy('id', 'asc')->get();

        $gridField = FormField::where('tipe', 'grid')->first();
        $gridmapImage = ($gridField && $gridField->gridmap_path) ? $gridField->gridmap_path : 'gridmap_injourney.jpeg';

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
            'gridmapImage'
        ));
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
            } elseif (!in_array($rf->nama_field, ['grid_lokasi', 'satwa', 'foto_lainnya'])) {
                $rules[$rf->nama_field] = 'required';
            }
        }

        $validated = $request->validate($rules);

        // Validasi minimal 1 satwa atau 1 foto extra
        $satwaInput = $request->input('satwa', []);
        $adaSatwa = false;

        foreach ($satwaInput as $s) {
            foreach ($s['lokasi'] ?? [] as $lok) {
                if ((int)($lok['jumlah'] ?? 0) > 0) {
                    $adaSatwa = true;
                    break 2;
                }
            }
        }

        $adaFotoExtra = $request->hasFile('foto_lainnya');

        if (!$adaSatwa && !$adaFotoExtra) {
            return back()->withInput()->with('error', 'Pilih minimal satu satwa atau upload foto satwa tidak terdaftar.');
        }

        $statusLaporan = (stripos($request->input('tindak_lanjut', ''), 'telah ditangani') !== false) ? 'sudah' : 'belum';

        // Tentukan grid_lokasi utama
        $firstGrid = $request->input('grid_lokasi', '');
        if (empty($firstGrid)) {
            foreach ($satwaInput as $s) {
                foreach ($s['lokasi'] ?? [] as $lok) {
                    if (!empty($lok['grid'])) {
                        $firstGrid = $lok['grid'];
                        break 2;
                    }
                }
            }
        }

        $laporan = Laporan::create([
            'user_id'           => $user->id,
            'nama_petugas'      => $request->input('nama_petugas', $user->namalengkap ?: $user->username),
            'tanggal'           => $request->input('tanggal'),
            'kondisi_cuaca'     => $request->input('kondisi_cuaca', ''),
            'unit_kerja'        => $request->input('unit_kerja', ''),
            'area_inspeksi'     => $request->input('area_inspeksi', ''),
            'tanda_tangan'      => $request->input('tanda_tangan'),
            'grid_lokasi'       => strtoupper($firstGrid ?: '-'),
            'ciri_ukuran'       => $request->input('ciri_ukuran', '-'),
            'kondisi_apron'     => $request->input('kondisi_apron', '-'),
            'aktivitas_satwa'   => $request->input('aktivitas_satwa', '-'),
            'tindak_lanjut'     => $request->input('tindak_lanjut', 'Belum ditangani'),
            'detail_pengusiran' => $request->input('detail_pengusiran', '-'),
            'status'            => $statusLaporan,
        ]);

        // Simpan field dinamis ke extra_data (JSON)
        $standardFields = [
            'nama_petugas', 'tanggal', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
            'tanda_tangan', 'grid_lokasi', 'satwa', 'ciri_ukuran', 'kondisi_apron',
            'aktivitas_satwa', 'tindak_lanjut', 'detail_pengusiran', '_token', 'foto_lainnya'
        ];

        $extraData = [];
        $extraFieldsDef = FormField::where('aktif', true)->whereNotIn('nama_field', $standardFields)->get();
        foreach ($extraFieldsDef as $ef) {
            if ($request->has($ef->nama_field)) {
                $extraData[$ef->nama_field] = $request->input($ef->nama_field);
            }
        }
        if (!empty($extraData)) {
            $laporan->update(['extra_data' => $extraData]);
        }

        // Simpan Detail Satwa & Foto
        foreach ($satwaInput as $idx => $s) {
            $namaSatwa = $s['nama'] ?? '';
            $locations = $s['lokasi'] ?? [];

            foreach ($locations as $lokIdx => $lok) {
                $jumlah = (int)($lok['jumlah'] ?? 0);
                if ($jumlah > 0) {
                    $grid = strtoupper($lok['grid'] ?? '');

                    $detail = DetailSatwa::create([
                        'laporan_id' => $laporan->id,
                        'nama_satwa' => $namaSatwa,
                        'jumlah'     => $jumlah,
                        'grid'       => $grid,
                    ]);

                    // Cek upload foto per satwa
                    if ($request->hasFile("satwa.{$idx}.foto")) {
                        $file = $request->file("satwa.{$idx}.foto");
                        if ($file->isValid()) {
                            $filename = 'satwa_' . $laporan->id . '_' . $detail->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('uploads/satwa', $filename, 'public');

                            FotoLaporan::create([
                                'laporan_id'      => $laporan->id,
                                'detail_satwa_id' => $detail->id,
                                'nama_file'       => $filename,
                                'tipe'            => 'satwa',
                                'foto_path'       => 'uploads/satwa/' . $filename,
                            ]);
                        }
                    }
                }
            }
        }

        // Simpan Foto Extra (Satwa Tidak Terdaftar)
        if ($request->hasFile('foto_lainnya')) {
            $extraFiles = $request->file('foto_lainnya');
            if (!is_array($extraFiles)) {
                $extraFiles = [$extraFiles];
            }

            foreach ($extraFiles as $eIdx => $efile) {
                if ($efile && $efile->isValid()) {
                    $extFilename = 'extra_' . $laporan->id . '_' . $eIdx . '_' . time() . '.' . $efile->getClientOriginalExtension();
                    $efile->storeAs('uploads/extra', $extFilename, 'public');

                    FotoLaporan::create([
                        'laporan_id'      => $laporan->id,
                        'detail_satwa_id' => null,
                        'nama_file'       => $extFilename,
                        'tipe'            => 'extra',
                        'foto_path'       => 'uploads/extra/' . $extFilename,
                    ]);
                }
            }
        }

        return redirect()->route('user.dashboard')->with('sukses', 'Laporan berhasil dikirim dan tersimpan di sistem.');
    }
}
