<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LaporanFormTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pegawai_can_access_laporan_create(): void
    {
        $user = User::where('role', 'pegawai')->first();

        $response = $this->actingAs($user)->get(route('laporan.create'));

        $response->assertStatus(200);
        $response->assertSee('Form Pengaduan Satwa Liar');
        $response->assertSee('mainSidebar');
        $response->assertSee('whms-page-header');
        $response->assertSee('cdn.tailwindcss.com');
        $response->assertSee('layerInteractiveHitBoxes');
        // Pastikan tidak ada hotspot data yang ditampilkan di setiap sel
        $response->assertDontSee('fill="#0F172A" fill-opacity="0.88"', false);
    }

    public function test_laporan_create_renders_weather_widget_and_autofills_kondisi_cuaca(): void
    {
        $user = User::where('role', 'pegawai')->first();

        $response = $this->actingAs($user)->get(route('laporan.create'));

        $response->assertOk();
        $response->assertSee('data-whms-weather', false);
        $response->assertSee('js/whms-weather.js', false);
        $response->assertSee('name="kondisi_cuaca" data-weather-autofill', false);
        $this->assertSame(1, substr_count($response->getContent(), 'js/whms-weather.js'));
    }

    public function test_user_dashboard_renders_weather_widget(): void
    {
        $user = User::where('role', 'pegawai')->first();

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertOk();
        $response->assertSee('data-whms-weather', false);
        $response->assertSee('Cuaca Saat Ini', false);
    }

    public function test_admin_cannot_access_laporan_create_and_is_redirected_to_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('laporan.create'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_pages_do_not_contain_buat_laporan_links(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Buat Laporan Baru', false);
        $response->assertDontSee('Form Input Satwa', false);
        $response->assertDontSee(route('laporan.create'), false);
    }

    public function test_admin_statistik_header_does_not_contain_export_excel_or_print_pdf(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.statistik'));

        $response->assertStatus(200);
        $response->assertSee('Peta Sebaran Grid');
        $response->assertDontSee('Cetak Peta PDF', false);
        $response->assertDontSee('admin/export/excel?tahun=', false);
    }
}
