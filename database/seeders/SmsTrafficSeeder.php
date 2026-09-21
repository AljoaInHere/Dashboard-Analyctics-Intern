<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmsTrafficSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $operators = ['Telkomsel', 'Indosat', 'XL', 'Smartfren'];
    $data = [];

    // Mundur dari hari ini sampai 30 hari ke belakang
    for ($i = 30; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');

        foreach ($operators as $operator) {
            $totalKirim = rand(10000, 50000); // Simulasi 10rb - 50rb SMS per hari
            $totalGagal = rand(100, 2000); // Simulasi gagal
            $totalSukses = $totalKirim - $totalGagal;

            $data[] = [
                'tanggal' => $date,
                'operator' => $operator,
                'total_dikirim' => $totalKirim,
                'total_sukses' => $totalSukses,
                'total_gagal' => $totalGagal,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    // Masukkan semua array ke database sekaligus
    \Illuminate\Support\Facades\DB::table('sms_traffics')->insert($data);
}
}
