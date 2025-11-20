<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchSetting;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'code' => 'MAIN-01',
                'name' => 'Gudang Utama Jakarta',
                'type' => 'main',
                'address' => 'Jl. Industri Raya No.18, Jakarta',
                'phone' => '021-555-0018',
                'is_active' => true,
                'settings' => [
                    'pos' => ['receipt_footer' => 'Terima kasih telah berbelanja di Gudang Utama'],
                    'operational_hours' => ['open' => '08:00', 'close' => '20:00'],
                ],
            ],
            [
                'code' => 'CAB-01',
                'name' => 'Cabang Bandung',
                'type' => 'branch',
                'address' => 'Jl. Asia Afrika No.12, Bandung',
                'phone' => '022-7654-3210',
                'is_active' => true,
                'settings' => [
                    'pos' => ['receipt_footer' => 'PT Nyxx Agrisupply - Cabang Bandung'],
                    'delivery' => ['courier' => 'Internal Fleet', 'eta_hours' => 24],
                ],
            ],
            [
                'code' => 'CAB-02',
                'name' => 'Cabang Surabaya',
                'type' => 'branch',
                'address' => 'Jl. Tunjungan No.88, Surabaya',
                'phone' => '031-9988-7766',
                'is_active' => true,
                'settings' => [
                    'pos' => ['receipt_footer' => 'Sampai jumpa di Cabang Surabaya'],
                    'delivery' => ['courier' => 'JNE Cargo', 'eta_hours' => 36],
                ],
            ],
        ];

        foreach ($branches as $data) {
            $settings = $data['settings'];
            unset($data['settings']);

            $branch = Branch::updateOrCreate(['code' => $data['code']], $data);

            foreach ($settings as $key => $value) {
                BranchSetting::updateOrCreate([
                    'branch_id' => $branch->id,
                    'key' => $key,
                ], [
                    'value' => $value,
                ]);
            }
        }
    }
}
