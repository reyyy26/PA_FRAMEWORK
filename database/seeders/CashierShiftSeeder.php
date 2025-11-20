<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CashierShiftSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all()->keyBy('code');
        $users = User::all()->keyBy('email');

        $shifts = [
            [
                'branch' => 'CAB-01',
                'cashier' => 'kasir.bandung@demo.test',
                'opening_float' => 250000,
                'opened_at' => Carbon::today()->setTime(8, 0),
                'closing_amount' => null,
                'closed_at' => null,
            ],
            [
                'branch' => 'CAB-02',
                'cashier' => 'kasir.surabaya@demo.test',
                'opening_float' => 200000,
                'opened_at' => Carbon::today()->subDay()->setTime(7, 45),
                'closing_amount' => 365000,
                'closed_at' => Carbon::today()->subDay()->setTime(16, 30),
            ],
        ];

        foreach ($shifts as $data) {
            $branch = $branches->get($data['branch']);
            $cashier = $users->get($data['cashier']);

            if (!$branch || !$cashier) {
                continue;
            }

            CashierShift::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'cashier_id' => $cashier->id,
                    'opened_at' => $data['opened_at'],
                ],
                [
                    'opening_float' => $data['opening_float'],
                    'closing_amount' => $data['closing_amount'],
                    'closed_at' => $data['closed_at'],
                    'closing_notes' => ['source' => 'seed'],
                ]
            );
        }
    }
}
