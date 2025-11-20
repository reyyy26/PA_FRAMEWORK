<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\AutomationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:auto-reorder', function () {
    /** @var AutomationService $automation */
    $automation = app(AutomationService::class);
    $suggestions = $automation->evaluateReorder();

    $this->info(sprintf('%d produk membutuhkan penambahan stok.', $suggestions->count()));
    $this->table([
        'Produk', 'Cabang', 'Stok', 'Reorder Point', 'Rekomendasi'
    ], $suggestions->map(function ($item) {
        return [
            'Produk' => $item['product_name'] ?? ('#' . $item['product_id']),
            'Cabang' => $item['branch_id'],
            'Stok' => $item['current_quantity'],
            'Reorder Point' => $item['reorder_point'],
            'Rekomendasi' => $item['recommended_quantity'],
        ];
    })->toArray());
})->purpose('Evaluasi otomatis kebutuhan restock cabang.');
