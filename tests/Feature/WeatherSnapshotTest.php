<?php

namespace Tests\Feature;

use App\Models\CuacaSnapshot;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class WeatherSnapshotTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Sleep::fake();
    }

    public function test_snapshot_menyimpan_cuaca_terkini_dengan_angin_silang(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->openMeteoBody(windDirection: 190))]);

        $this->artisan('weather:snapshot')->assertExitCode(Command::SUCCESS);

        $snapshot = CuacaSnapshot::where('waktu', '2019-03-10 06:15:00')->sole();
        $this->assertSame(63, $snapshot->kode_cuaca);
        $this->assertSame(2.4, $snapshot->hujan_mm);
        $this->assertSame(5.36, $snapshot->jarak_pandang_km);
        $this->assertSame(190, $snapshot->arah_angin_derajat);
        $this->assertSame(12.0, $snapshot->kecepatan_angin_kt);
        $this->assertSame(18.5, $snapshot->hembusan_kt);
        $this->assertSame(12.0, $snapshot->angin_silang_kt);
        $this->assertSame(27.8, $snapshot->suhu_c);
        $this->assertSame(87, $snapshot->kelembapan_persen);
        $this->assertSame(92, $snapshot->tutupan_awan_persen);
        $this->assertSame('Open-Meteo', $snapshot->sumber);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'wind_speed_unit=kn')
            && str_contains($request->url(), 'timezone=Asia%2FJakarta'));
    }

    public function test_snapshot_pada_waktu_yang_sama_diperbarui_bukan_diduplikasi(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::sequence()
            ->push($this->openMeteoBody(windDirection: 190))
            ->push($this->openMeteoBody(windDirection: 100))]);

        $this->artisan('weather:snapshot')->assertExitCode(Command::SUCCESS);
        $this->artisan('weather:snapshot')->assertExitCode(Command::SUCCESS);

        $snapshots = CuacaSnapshot::where('waktu', '2019-03-10 06:15:00')->get();
        $this->assertCount(1, $snapshots);
        $this->assertSame(0.0, $snapshots->first()->angin_silang_kt);
    }

    public function test_snapshot_gagal_tidak_menyimpan_data_perkiraan(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response(['error' => true], 503)]);
        $jumlahAwal = CuacaSnapshot::count();

        $this->artisan('weather:snapshot')->assertExitCode(Command::FAILURE);

        $this->assertSame($jumlahAwal, CuacaSnapshot::count());
    }

    /**
     * @return array<string, mixed>
     */
    private function openMeteoBody(int $windDirection): array
    {
        return [
            'timezone' => 'Asia/Jakarta',
            'current' => [
                'time' => '2019-03-10T06:15',
                'interval' => 900,
                'temperature_2m' => 27.8,
                'relative_humidity_2m' => 87,
                'precipitation' => 2.4,
                'weather_code' => 63,
                'cloud_cover' => 92,
                'wind_speed_10m' => 12.0,
                'wind_direction_10m' => $windDirection,
                'wind_gusts_10m' => 18.5,
                'visibility' => 5360.0,
            ],
        ];
    }
}
