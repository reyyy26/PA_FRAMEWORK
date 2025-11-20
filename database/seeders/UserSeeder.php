<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Demo',
                'email' => 'nyxx@gmail.com',
                'phone' => '0800-0000-01',
                'is_super_admin' => true,
                'password' => 'nyxx123',
                'default_branch' => 'MAIN-01',
                'branch_roles' => [
                    'MAIN-01' => 'director',
                    'CAB-01' => 'director',
                    'CAB-02' => 'director',
                ],
            ],
            [
                'name' => 'Andi Procurement',
                'email' => 'procurement@demo.test',
                'phone' => '0800-0000-02',
                'is_super_admin' => false,
                'password' => 'password',
                'default_branch' => 'MAIN-01',
                'branch_roles' => [
                    'MAIN-01' => 'procurement',
                ],
            ],
            [
                'name' => 'Siti Kepala Bandung',
                'email' => 'manager.bandung@demo.test',
                'phone' => '0800-0000-03',
                'is_super_admin' => false,
                'password' => 'password',
                'default_branch' => 'CAB-01',
                'branch_roles' => [
                    'CAB-01' => 'branch_manager',
                ],
            ],
            [
                'name' => 'Budi Kepala Surabaya',
                'email' => 'manager.surabaya@demo.test',
                'phone' => '0800-0000-04',
                'is_super_admin' => false,
                'password' => 'password',
                'default_branch' => 'CAB-02',
                'branch_roles' => [
                    'CAB-02' => 'branch_manager',
                ],
            ],
            [
                'name' => 'Maya Kasir Bandung',
                'email' => 'kasir.bandung@demo.test',
                'phone' => '0800-0000-05',
                'is_super_admin' => false,
                'password' => 'password',
                'default_branch' => 'CAB-01',
                'branch_roles' => [
                    'CAB-01' => 'cashier',
                ],
            ],
            [
                'name' => 'Rudi Kasir Surabaya',
                'email' => 'kasir.surabaya@demo.test',
                'phone' => '0800-0000-06',
                'is_super_admin' => false,
                'password' => 'password',
                'default_branch' => 'CAB-02',
                'branch_roles' => [
                    'CAB-02' => 'cashier',
                ],
            ],
        ];

        $branches = Branch::all()->keyBy('code');

        foreach ($users as $data) {
            $defaultBranch = $branches->get($data['default_branch']);

            $attributes = Arr::only($data, ['name', 'phone', 'is_super_admin', 'password']);
            $plainPassword = $attributes['password'] ?? 'password';
            $attributes['password'] = Hash::make($plainPassword);
            $attributes['default_branch_id'] = $defaultBranch?->id;

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $attributes
            );

            $pivotData = collect($data['branch_roles'])
                ->mapWithKeys(fn ($role, $code) => $branches->has($code) ? [$branches->get($code)->id => ['role' => $role]] : [])
                ->filter();

            if ($pivotData->isNotEmpty()) {
                $user->branches()->sync($pivotData->all(), false);
            }
        }
    }
}
