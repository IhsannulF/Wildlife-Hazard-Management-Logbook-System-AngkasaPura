<?php

namespace Tests\Feature;

use App\Models\PrediksiBirdstrikeAi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AiUiRenderTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * TEST-AI-17: UI rendering and integration checks
     */
    public function test_admin_ai_pages_and_components_render_successfully(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Create a dummy prediction
        $prediksi = PrediksiBirdstrikeAi::factory()->create([
            'status_eksekusi' => 'sukses',
            'status_advisory' => 'aktif',
            'level_bahaya' => 'tinggi',
            'skor_probabilitas' => 0.75,
        ]);

        // 2. Admin dashboard
        $dashResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('aiBriefingSection', false);

        // 3. Admin AI pages
        $this->actingAs($admin)->get(route('admin.ai.prediksi.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.ai.histori.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.ai.lalu-lintas.index'))->assertStatus(200);

        // 4. Latest JSON endpoint
        $latestResponse = $this->actingAs($admin)->getJson(route('admin.ai.prediksi.latest'));
        $latestResponse->assertStatus(200);
        $latestResponse->assertJson([
            'success' => true,
            'has_prediction' => true,
        ]);

        // 5. Acknowledge advisory endpoint
        $ackResponse = $this->actingAs($admin)->postJson(route('admin.ai.prediksi.akui', $prediksi->id));
        $ackResponse->assertStatus(200);
        $this->assertDatabaseHas('prediksi_birdstrike_ai', [
            'id' => $prediksi->id,
            'status_advisory' => 'diakui',
            'diakui_oleh' => $admin->id,
        ]);

        // 6. Pegawai form page includes AI button and panel
        $pegawai = User::factory()->create(['role' => 'pegawai']);
        $formResponse = $this->actingAs($pegawai)->get(route('laporan.create'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('btnAiIdentifySpecies', false);
        $formResponse->assertSee('aiSpeciesResultPanel', false);
    }
}
