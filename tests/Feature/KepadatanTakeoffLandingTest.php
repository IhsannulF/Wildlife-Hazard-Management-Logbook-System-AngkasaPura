<?php

namespace Tests\Feature;

use App\Models\KepadatanPenerbangan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * PRD v2.1 FR-0.6: traffic dipisah take-off / landing, total tetap konsisten.
 */
class KepadatanTakeoffLandingTest extends TestCase
{
    use DatabaseTransactions;

    private const TANGGAL = '2019-04-01';

    public function test_form_menyimpan_takeoff_landing_dan_total_dihitung_otomatis(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.store'), [
            'tanggal' => self::TANGGAL,
            'takeoff' => [6 => 8, 7 => null],
            'landing' => [6 => 7, 7 => null],
        ]);

        $response->assertRedirect(route('admin.ai.lalu-lintas.index', ['tanggal' => self::TANGGAL]));
        $this->assertDatabaseHas('kepadatan_penerbangan', [
            'tanggal' => self::TANGGAL,
            'jam' => 6,
            'jumlah_takeoff' => 8,
            'jumlah_landing' => 7,
            'jumlah_pergerakan' => 15,
        ]);
        $this->assertDatabaseMissing('kepadatan_penerbangan', ['tanggal' => self::TANGGAL, 'jam' => 7]);
    }

    public function test_kolom_kosong_tidak_menghapus_total_lama_yang_belum_dipisah(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createLegacyTotal(10, 25);

        $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.store'), [
            'tanggal' => self::TANGGAL,
            'takeoff' => [10 => null, 11 => 3],
            'landing' => [10 => null, 11 => 4],
        ]);

        $this->assertDatabaseHas('kepadatan_penerbangan', ['tanggal' => self::TANGGAL, 'jam' => 10, 'jumlah_pergerakan' => 25]);
    }

    public function test_nol_eksplisit_mengganti_total_lama(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createLegacyTotal(9, 30);

        $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.store'), [
            'tanggal' => self::TANGGAL,
            'takeoff' => [9 => 0],
            'landing' => [9 => 0],
        ]);

        $this->assertDatabaseHas('kepadatan_penerbangan', [
            'tanggal' => self::TANGGAL,
            'jam' => 9,
            'jumlah_takeoff' => 0,
            'jumlah_landing' => 0,
            'jumlah_pergerakan' => 0,
        ]);
    }

    public function test_form_menolak_pergerakan_di_atas_batas(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.store'), [
            'tanggal' => self::TANGGAL,
            'takeoff' => [6 => 500],
            'landing' => [6 => 1],
        ]);

        $response->assertSessionHasErrors('takeoff.6');
    }

    public function test_import_csv_format_baru_takeoff_landing(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = UploadedFile::fake()->createWithContent('traffic.csv', "tanggal,jam,jumlah_takeoff,jumlah_landing\n".self::TANGGAL.",7,9,11\n");

        $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.import'), ['csv_file' => $csv]);

        $this->assertDatabaseHas('kepadatan_penerbangan', [
            'tanggal' => self::TANGGAL,
            'jam' => 7,
            'jumlah_takeoff' => 9,
            'jumlah_landing' => 11,
            'jumlah_pergerakan' => 20,
        ]);
    }

    public function test_import_csv_format_lama_hanya_total_tetap_diterima(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = UploadedFile::fake()->createWithContent('traffic.csv', "tanggal,jam,jumlah_pergerakan\n".self::TANGGAL.",8,17\n");

        $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.import'), ['csv_file' => $csv]);

        $this->assertDatabaseHas('kepadatan_penerbangan', [
            'tanggal' => self::TANGGAL,
            'jam' => 8,
            'jumlah_takeoff' => 0,
            'jumlah_landing' => 0,
            'jumlah_pergerakan' => 17,
        ]);
    }

    public function test_halaman_menandai_jam_yang_belum_dipisah_dan_menampilkan_rincian(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createLegacyTotal(12, 21);
        KepadatanPenerbangan::create(['tanggal' => self::TANGGAL, 'jam' => 13, 'jumlah_takeoff' => 5, 'jumlah_landing' => 6]);

        $response = $this->actingAs($admin)->get(route('admin.ai.lalu-lintas.index', ['tanggal' => self::TANGGAL]));

        $response->assertOk();
        $response->assertViewHas('totalMovements', 32);
        $response->assertViewHas('totalTakeoff', 5);
        $response->assertViewHas('totalLanding', 6);
        $response->assertViewHas('unsplitHours', 1);
        $response->assertSee('Total lama 21 (belum dipisah)');
    }

    private function createLegacyTotal(int $jam, int $total): void
    {
        KepadatanPenerbangan::create(['tanggal' => self::TANGGAL, 'jam' => $jam, 'jumlah_pergerakan' => $total]);
    }
}
