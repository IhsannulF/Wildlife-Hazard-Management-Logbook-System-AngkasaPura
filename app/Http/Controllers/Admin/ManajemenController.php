<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\JenisSatwa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManajemenController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'satwa');

        // Metrics for Katalog Satwa
        $totalSpesies = JenisSatwa::count();
        $avianCount = JenisSatwa::where('kategori', 'burung')->orWhere('nama', 'like', '%Burung%')->count();
        $reptilCount = JenisSatwa::where('kategori', 'reptil')->orWhere('nama', 'like', '%Biawak%')->orWhere('nama', 'like', '%Ular%')->count();
        $mamaliaCount = max(0, $totalSpesies - ($avianCount + $reptilCount));

        $kritisCount = JenisSatwa::where('tingkat_risiko', 'kritis')->count();
        $sedangCount = JenisSatwa::where('tingkat_risiko', 'sedang')->count();
        $rendahCount = JenisSatwa::where('tingkat_risiko', 'rendah')->count();

        $satwaList = JenisSatwa::orderBy('id', 'asc')->get();
        $fields = FormField::with('options')->orderBy('urutan', 'asc')->get();
        $users = User::orderBy('id', 'asc')->get();

        $gridField = FormField::where('tipe', 'grid')->first();
        $gridmapPath = $gridField?->gridmap_path ?: 'gridmap_injourney.jpeg';

        return view('admin.manajemen', compact(
            'tab',
            'fields',
            'satwaList',
            'users',
            'gridmapPath',
            'totalSpesies',
            'avianCount',
            'reptilCount',
            'mamaliaCount',
            'kritisCount',
            'sedangCount',
            'rendahCount'
        ));
    }

    // ── Field Management ──
    public function storeField(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'tipe'  => 'required|string',
        ]);

        $label = trim($request->label);
        $namaField = Str::slug($label, '_') . '_' . time();
        $maxUrutan = (int)FormField::max('urutan') + 1;

        $field = FormField::create([
            'nama_field'  => $namaField,
            'label'       => $label,
            'tipe'        => $request->tipe,
            'placeholder' => $request->placeholder ?: '',
            'wajib'       => $request->boolean('wajib'),
            'aktif'       => true,
            'urutan'      => $maxUrutan,
            'keterangan'  => $request->keterangan ?: '',
        ]);

        if ($request->tipe === 'dropdown' && !empty($request->opsi)) {
            $opsiArray = array_filter(array_map('trim', explode("\n", $request->opsi)));
            foreach ($opsiArray as $i => $opt) {
                FormFieldOption::create([
                    'field_id' => $field->id,
                    'nilai'    => $opt,
                    'urutan'   => $i + 1,
                ]);
            }
        }

        return redirect()->route('admin.manajemen', ['tab' => 'fields'])->with('sukses', "Field \"{$label}\" berhasil ditambahkan.");
    }

    public function updateField(Request $request, int $id)
    {
        $field = FormField::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:100',
            'tipe'  => 'required|string',
        ]);

        $field->label       = trim($request->label);
        $field->tipe        = $request->tipe;
        $field->placeholder = $request->placeholder ?: '';
        $field->wajib       = $request->boolean('wajib');
        $field->keterangan  = $request->keterangan ?: '';

        if ($request->hasFile('gridmap')) {
            $file = $request->file('gridmap');
            $ext = $file->getClientOriginalExtension();
            $filename = 'gridmap_injourney.' . $ext;
            $file->move(public_path('images'), $filename);
            $field->gridmap_path = $filename;
        }

        $field->save();

        if ($request->tipe === 'dropdown') {
            $field->options()->delete();
            if (!empty($request->opsi)) {
                $opsiArray = array_filter(array_map('trim', explode("\n", $request->opsi)));
                foreach ($opsiArray as $i => $opt) {
                    FormFieldOption::create([
                        'field_id' => $field->id,
                        'nilai'    => $opt,
                        'urutan'   => $i + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.manajemen', ['tab' => 'fields'])->with('sukses', "Field \"{$field->label}\" berhasil diperbarui.");
    }

    public function toggleField(int $id)
    {
        $field = FormField::findOrFail($id);
        $field->aktif = !$field->aktif;
        $field->save();

        return redirect()->route('admin.manajemen', ['tab' => 'fields'])->with('sukses', "Status field berhasil diubah.");
    }

    public function destroyField(int $id)
    {
        $field = FormField::findOrFail($id);
        $field->delete();

        return redirect()->route('admin.manajemen', ['tab' => 'fields'])->with('sukses', "Field berhasil dihapus.");
    }

    // ── Satwa Management ──
    public function storeSatwa(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:100',
            'kategori'       => 'nullable|string',
            'tingkat_risiko' => 'nullable|string',
            'grid_hotspot'   => 'nullable|string|max:50',
            'deskripsi'      => 'nullable|string',
            'sop_pengusiran' => 'nullable|string',
            'jam_puncak'     => 'nullable|string',
            'bobot_rata_rata'=> 'nullable|string',
            'foto'           => 'nullable|image|max:5120',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'satwa_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $fotoPath = $filename;
        }

        JenisSatwa::create([
            'nama'            => $request->nama,
            'kategori'        => $request->kategori ?: 'burung',
            'tingkat_risiko'  => $request->tingkat_risiko ?: 'sedang',
            'grid_hotspot'    => $request->grid_hotspot,
            'deskripsi'       => $request->deskripsi,
            'sop_pengusiran'  => $request->sop_pengusiran,
            'jam_puncak'      => $request->jam_puncak,
            'bobot_rata_rata' => $request->bobot_rata_rata,
            'foto_path'       => $fotoPath,
        ]);

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Spesies satwa \"{$request->nama}\" berhasil ditambahkan.");
    }

    public function updateSatwa(Request $request, int $id)
    {
        $satwa = JenisSatwa::findOrFail($id);

        $request->validate([
            'nama'           => 'required|string|max:100',
            'kategori'       => 'nullable|string',
            'tingkat_risiko' => 'nullable|string',
            'grid_hotspot'   => 'nullable|string|max:50',
            'deskripsi'      => 'nullable|string',
            'sop_pengusiran' => 'nullable|string',
            'jam_puncak'     => 'nullable|string',
            'bobot_rata_rata'=> 'nullable|string',
            'foto'           => 'nullable|image|max:5120',
        ]);

        $satwa->nama            = $request->nama;
        $satwa->kategori        = $request->kategori ?: $satwa->kategori;
        $satwa->tingkat_risiko  = $request->tingkat_risiko ?: $satwa->tingkat_risiko;
        $satwa->grid_hotspot    = $request->grid_hotspot;
        $satwa->deskripsi       = $request->deskripsi;
        $satwa->sop_pengusiran  = $request->sop_pengusiran;
        if ($request->filled('jam_puncak')) $satwa->jam_puncak = $request->jam_puncak;
        if ($request->filled('bobot_rata_rata')) $satwa->bobot_rata_rata = $request->bobot_rata_rata;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'satwa_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $satwa->foto_path = $filename;
        }

        $satwa->save();

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Data spesies satwa \"{$satwa->nama}\" berhasil diperbarui.");
    }

    public function destroySatwa(int $id)
    {
        $satwa = JenisSatwa::findOrFail($id);
        $nama = $satwa->nama;
        $satwa->delete();

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Spesies satwa \"{$nama}\" berhasil dihapus.");
    }

    // ── User Management ──
    public function storeUser(Request $request)
    {
        $request->validate([
            'namalengkap' => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username',
            'password'    => 'required|string|min:6',
            'role'        => 'required|in:admin,pegawai',
            'jabatan'     => 'nullable|string|max:50',
        ]);

        User::create([
            'namalengkap' => trim($request->namalengkap),
            'username'    => trim($request->username),
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'jabatan'     => $request->jabatan ?: ($request->role === 'admin' ? 'Administrator' : 'Pegawai Lapangan'),
            'aktif'       => true,
        ]);

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User \"{$request->username}\" berhasil ditambahkan.");
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'namalengkap' => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username,' . $id,
            'password'    => 'nullable|string|min:6',
            'role'        => 'required|in:admin,pegawai',
            'jabatan'     => 'nullable|string|max:50',
        ]);

        $user->namalengkap = trim($request->namalengkap);
        $user->username    = trim($request->username);
        $user->role        = $request->role;
        $user->jabatan     = $request->jabatan ?: ($request->role === 'admin' ? 'Administrator' : 'Pegawai Lapangan');

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User \"{$user->username}\" berhasil diperbarui.");
    }

    public function toggleUser(int $id)
    {
        $user = User::findOrFail($id);
        $user->aktif = !$user->aktif;
        $user->save();

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "Status user berhasil diubah.");
    }

    public function destroyUser(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('error', "Anda tidak dapat menghapus akun Anda sendiri.");
        }
        $username = $user->username;
        $user->delete();

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User \"{$username}\" berhasil dihapus.");
    }
}
