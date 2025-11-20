<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
    }

    public function salesMix(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        return response()->json($this->analytics->salesMix($this->branchId($request), $from, $to));
    }

    public function salesMixExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $data = $this->analytics->salesMix($this->branchId($request), $from, $to);

        return $this->streamCsv($data, ['product_id', 'product_name', 'quantity', 'sales', 'mix_percentage'], 'sales_mix.csv');
    }

    public function stockAging(Request $request)
    {
        return response()->json($this->analytics->stockAging($this->branchId($request)));
    }

    public function stockAgingExport(Request $request): StreamedResponse
    {
        $data = $this->analytics->stockAging($this->branchId($request));

        return $this->streamCsv($data, ['product_id', 'product_name', 'branch_id', 'branch_name', 'batch_number', 'quantity', 'expiry_date', 'days_to_expiry', 'status'], 'stock_aging.csv');
    }

    public function demandForecast(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        return response()->json($this->analytics->demandForecast(
            $this->branchId($request),
            $request->integer('window_days', 30),
            $from,
            $to
        ));
    }

    public function demandForecastExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $data = $this->analytics->demandForecast(
            $this->branchId($request),
            $request->integer('window_days', 30),
            $from,
            $to
        );

        return $this->streamCsv($data, ['product_id', 'average_daily_sales', 'projected_weekly_demand'], 'demand_forecast.csv');
    }

    public function alerts(Request $request)
    {
        return response()->json($this->analytics->alerts($this->branchId($request)));
    }

    private function branchId(Request $request): ?int
    {
        return $request->attributes->get('branch_id') ?? $request->integer('branch_id');
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;

        return [$from, $to];
    }

    private function streamCsv($data, array $columns, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($data as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $line[] = data_get($row, $column);
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
