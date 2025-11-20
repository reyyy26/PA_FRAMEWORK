<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-001',
                'name' => 'Nusantara Pangan',
                'contact_person' => 'Hendra Wijaya',
                'phone' => '021-7700-1100',
                'email' => 'po@nusantara-pangan.id',
                'address' => 'Jl. Raya Bogor KM.22, Jakarta',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Lautan Kimia Agro',
                'contact_person' => 'Dewi Anggraini',
                'phone' => '031-4411-8822',
                'email' => 'sales@lautanagro.id',
                'address' => 'Pergudangan Margomulyo Blok B12, Surabaya',
            ],
            [
                'code' => 'SUP-003',
                'name' => 'Sejahtera Benih',
                'contact_person' => 'Yohanes Pratama',
                'phone' => '022-6611-0099',
                'email' => 'support@sejahterabenih.co.id',
                'address' => 'Jl. Pasir Kaliki No.55, Bandung',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
