<?php

namespace Tests\Feature;

use App\Models\CuacaSnapshot;
use App\Models\Laporan;
use App\Models\User;
use App\Services\DataQuality\DataQualityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PRD v2.1 FR-0.7: peringatan bila cuaca atau observasi kosong lebih dari 2 interval berturut-turut.
 */
class DataQualityWarningTest extends TestCase
{
    use DatabaseTransactions;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        config(['whms_risk.kualitas_data.jendela_jam' => 3]);
        $this->now = CarbonImmutable::parse('2019-03-10 10:00', 'Asia/Jakarta');
    }

    public function test_tiga_snapshot_cuaca_kosong_berturut_turut_memicu_peringatan(): void
    {
        $this->fillWeatherExcept(['08:00', '08:15', '08:30']);

        $gaps = $this->gapsFor(DataQualityService::SOURCE_WEATHER);

        $this->assertCount(1, $gaps);
        $this->assertSame(3, $gaps[0]['interval_kosong']);
        $this->assertSame('2019-03-10 08:00', $gaps[0]['mulai']->format('Y-m-d H:i'));
        $this->assertSame('2019-03-10 08:45', $gaps[0]['selesai']->format('Y-m-d H:i'));
    }

    public function test_dua_snapshot_cuaca_kosong_masih_ditoleransi(): void
    {
        $this->fillWeatherExcept(['08:00', '08:15']);

        $this->assertSame([], $this->gapsFor(DataQualityService::SOURCE_WEATHER));
    }

    public function test_observasi_kosong_di_jam_patroli_memicu_peringatan(): void
    {
        foreach (['07:10', '07:40', '09:40'] as $jam) {
            $this->createLaporan($jam);
        }

        $gaps = $this->gapsFor(DataQualityService::SOURCE_OBSERVATION);

        $this->assertCount(1, $gaps);
        $this->assertSame(3, $gaps[0]['interval_kosong']);
        $this->assertSame('08:00', $gaps[0]['mulai']->format('H:i'));
        $this->assertSame('09:30', $gaps[0]['selesai']->format('H:i'));
    }

    public function test_malam_di_luar_jam_patroli_tidak_dianggap_kosong(): void
    {
        config(['whms_risk.kualitas_data.jendela_jam' => 8]);
        $this->now = CarbonImmutable::parse('2019-03-10 06:00', 'Asia/Jakarta');
        $this->createLaporan('05:10');

        $this->assertSame([], $this->gapsFor(DataQualityService::SOURCE_OBSERVATION));
    }

    public function test_dashboard_admin_menampilkan_peringatan_kualitas_data(): void
    {
        $this->travelTo($this->now);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Peringatan kualitas data');
        $response->assertSee('Snapshot cuaca kosong 10/03 07:00–10:00 WIB (12 interval 15 menit berturut-turut).');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function gapsFor(string $source): array
    {
        $warnings = app(DataQualityService::class)->warnings($this->now);

        return array_values(array_filter($warnings, fn (array $warning): bool => $warning['sumber'] === $source));
    }

    /**
     * @param  list<string>  $emptySlots
     */
    private function fillWeatherExcept(array $emptySlots): void
    {
        for ($slot = $this->now->subHours(3); $slot->lt($this->now); $slot = $slot->addMinutes(15)) {
            if (! in_array($slot->format('H:i'), $emptySlots, true)) {
                CuacaSnapshot::create(['waktu' => $slot->format('Y-m-d H:i:s'), 'sumber' => 'test']);
            }
        }
    }

    private function createLaporan(string $jam): void
    {
        Laporan::create([
            'user_id' => User::factory()->create()->id,
            'tanggal' => $this->now->toDateString(),
            'jam' => $jam,
            'nama_petugas' => 'Petugas Uji',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway',
            'kondisi_cuaca' => 'Cerah',
            'status' => 'belum',
        ]);
    }
}
