<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormFieldRequest;
use App\Http\Requests\UpdateFormInspeksiRequest;
use App\Models\FormField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin mengatur isian form inspeksi pegawai: label, petunjuk, wajib/opsional, aktif, urutan,
 * pilihan dropdown, dan isian tambahan. Field inti (tanggal, gridmap, tanda tangan) dikunci.
 */
class FormInspeksiController extends Controller
{
    public function index(): View
    {
        $fields = FormField::with('options')->orderBy('urutan')->orderBy('id')->get();

        return view('admin.form-inspeksi.index', [
            'fields' => $fields,
            'customTypes' => FormField::CUSTOM_TYPES,
            'totalAktif' => $fields->where('aktif', true)->count(),
            'totalWajib' => $fields->where('aktif', true)->where('wajib', true)->count(),
        ]);
    }

    public function update(UpdateFormInspeksiRequest $request): RedirectResponse
    {
        $input = $request->validated('fields');
        $fields = FormField::with('options')->whereIn('id', array_keys($input))->get();

        DB::transaction(function () use ($fields, $input): void {
            foreach ($fields as $field) {
                $values = $input[$field->id];

                $field->fill([
                    'label' => trim($values['label']),
                    'placeholder' => trim((string) ($values['placeholder'] ?? '')),
                    'urutan' => (int) $values['urutan'],
                    // Field inti selalu aktif & wajib, apa pun yang dikirim.
                    'aktif' => $field->isLocked() || filter_var($values['aktif'] ?? false, FILTER_VALIDATE_BOOL),
                    'wajib' => $field->isLocked() || filter_var($values['wajib'] ?? false, FILTER_VALIDATE_BOOL),
                ])->save();

                if ($field->tipe === 'dropdown' && array_key_exists('opsi', $values)) {
                    $field->syncOptionsFromText($values['opsi']);
                }
            }
        });

        return redirect()->route('admin.form-inspeksi.index')->with('success', 'Pengaturan form inspeksi disimpan. Form pegawai langsung memakai pengaturan baru.');
    }

    public function store(StoreFormFieldRequest $request): RedirectResponse
    {
        $label = trim($request->validated('label'));

        $field = FormField::create([
            'nama_field' => Str::limit(Str::slug($label, '_'), 40, '').'_'.now()->format('ymdHis'),
            'label' => $label,
            'tipe' => $request->validated('tipe'),
            'placeholder' => trim((string) $request->validated('placeholder')),
            'wajib' => $request->boolean('wajib'),
            'aktif' => true,
            'urutan' => (int) FormField::max('urutan') + 1,
            'keterangan' => '',
        ]);

        if ($field->tipe === 'dropdown') {
            $field->syncOptionsFromText($request->validated('opsi'));
        }

        return redirect()->route('admin.form-inspeksi.index')->with('success', "Isian \"{$label}\" ditambahkan ke bagian Isian Tambahan di form inspeksi.");
    }

    public function destroy(FormField $field): RedirectResponse
    {
        if ($field->isStandard()) {
            return redirect()->route('admin.form-inspeksi.index')->with('error', 'Isian bawaan tidak bisa dihapus; nonaktifkan saja bila tidak dipakai.');
        }

        $field->options()->delete();
        $field->delete();

        return redirect()->route('admin.form-inspeksi.index')->with('success', "Isian \"{$field->label}\" dihapus. Data yang sudah tersimpan di laporan lama tetap ada.");
    }
}
