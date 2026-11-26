<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAmbangZonasiRequest;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;

/**
 * Admin mengubah ambang kelas heatmap zonasi tanpa deploy (PRD v2.1 FR-1.4).
 */
class AmbangZonasiController extends Controller
{
    public function __invoke(UpdateAmbangZonasiRequest $request): RedirectResponse
    {
        Pengaturan::updateOrCreate(
            ['kunci' => Pengaturan::AMBANG_ZONASI],
            [
                'nilai' => array_map('floatval', $request->validated()),
                'diubah_oleh' => $request->user()->id,
            ]
        );

        return back()->with('success', 'Ambang kelas zonasi risiko diperbarui.');
    }
}
