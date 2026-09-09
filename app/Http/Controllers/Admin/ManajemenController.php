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
        $tab = $request->query('tab', 'fields');

        $fields = FormField::with('options')->orderBy('urutan', 'asc')->get();
        $satwaList = JenisSatwa::orderBy('id', 'asc')->get();
        $users = User::orderBy('id', 'asc')->get();

        $gridField = FormField::where('tipe', 'grid')->first();
        $gridmapPath = $gridField?->gridmap_path ?: 'gridmap_injourney.jpeg';

        return view('admin.manajemen', compact(
            'tab',
            'fields',
            'satwaList',
            'users',
            'gridmapPath'
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

        return redirect()->route('admin.manajemen', ['tab' => 'fields'])->with('sukses', "Field \"$label\" berhasil ditambahkan.");
    }

    public function updateField(Request $request, int $id)
    {
        $field = FormField::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:100',
            'tipe'  => 'required|string',
        ]);

        $field->label       = $request->label;
        $field->tipe        = $request->tipe;
        $field->placeholder = $request->placeholder ?: '';
        $field->wajib       = $request->boolean('wajib');
        $field->keterangan  = $request->keterangan ?: '';

        if ($request->tipe === 'grid' && $request->hasFile('gridmap_file')) {
            $file = $request->file('gridmap_file');
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
            'nama' => 'required|string|max:100',
            'foto' => 'nullable|image|max:5120',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'satwa_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $fotoPath = $filename;
        }

        JenisSatwa::create([
            'nama'      => $request->nama,
            'foto_path' => $fotoPath,
        ]);

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Satwa \"{$request->nama}\" berhasil ditambahkan.");
    }

    public function updateSatwa(Request $request, int $id)
    {
        $satwa = JenisSatwa::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'foto' => 'nullable|image|max:5120',
        ]);

        $satwa->nama = $request->nama;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'satwa_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $satwa->foto_path = $filename;
        }

        $satwa->save();

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Data satwa berhasil diperbarui.");
    }

    public function destroySatwa(int $id)
    {
        $satwa = JenisSatwa::findOrFail($id);
        $satwa->delete();

        return redirect()->route('admin.manajemen', ['tab' => 'satwa'])->with('sukses', "Satwa berhasil dihapus.");
    }

    // ── User Management ──
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:4',
            'role'     => 'required|in:admin,pegawai',
        ]);

        User::create([
            'namalengkap' => $request->nama,
            'username'    => trim($request->username),
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'jabatan'     => ucfirst($request->role),
            'aktif'       => true,
        ]);

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User \"{$request->username}\" berhasil ditambahkan.");
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama'     => 'required|string|max:150',
            'username' => "required|string|max:100|unique:users,username,{$id}",
            'password' => 'nullable|string|min:4',
            'role'     => 'required|in:admin,pegawai',
        ]);

        $user->namalengkap = $request->nama;
        $user->username    = trim($request->username);
        $user->role        = $request->role;
        $user->jabatan     = ucfirst($request->role);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User berhasil diperbarui.");
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
        $user->delete();

        return redirect()->route('admin.manajemen', ['tab' => 'users'])->with('sukses', "User berhasil dihapus.");
    }
}
