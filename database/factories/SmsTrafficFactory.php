<?php

namespace Database\Factories;

use App\Models\SmsTraffic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsTraffic>
 */
class SmsTrafficFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmsTraffic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $operators = ['Telkomsel', 'Indosat', 'XL', 'Smartfren'];
        $totalDikirim = fake()->numberBetween(10000, 50000);
        $totalGagal = fake()->numberBetween(100, 2000);
        $totalSukses = $totalDikirim - $totalGagal;

        return [
            'tanggal' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'operator' => fake()->randomElement($operators),
            'total_dikirim' => $totalDikirim,
            'total_sukses' => $totalSukses,
            'total_gagal' => $totalGagal,
        ];
    }
}
