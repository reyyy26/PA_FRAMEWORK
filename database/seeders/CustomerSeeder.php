<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Rahmat Santoso', 'phone' => '0812-9900-1122', 'email' => 'rahmat@example.com', 'is_opt_in' => true],
            ['name' => 'Sari Dewi', 'phone' => '0813-4455-6677', 'email' => 'sari@example.com', 'is_opt_in' => false],
            ['name' => 'PT Lumbung Sejahtera', 'phone' => '021-5566-7788', 'email' => 'pengadaan@lumbung.id', 'is_opt_in' => true],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
