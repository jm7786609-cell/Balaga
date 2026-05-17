<?php

namespace App\Services;

use App\Models\InventoryReport;
use App\Models\Product;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{

    public function generateDailyReportData(Carbon $date, $userId)
    {
        $yesterday = $date->copy()->subDay();

        // Get all products
        $products = Product::withSum('inventories as current_stock', 'quantity')->get();

        $reportData = [];

        foreach ($products as $product) {
            // 1. Beginning = Ending from yesterday's report OR current stock if no report
            $yesterdayReport = InventoryReport::where('product_id', $product->id)
                ->whereDate('report_date', $yesterday)
                ->first();

            $beginning = $yesterdayReport?->physical_count ?? $product->current_stock;

            // 2. Today's completed sales
            $soldToday = TransactionItem::where('product_id', $product->id)
                ->whereHas('transaction', fn($q) => $q->where('status', 'completed'))
                ->whereDate('created_at', $date)
                ->sum('quantity');

            // 3. System ending
            $ending = max(0, $beginning - $soldToday);

            $reportData[] = [
                'product_id' => $product->id,
                'beginning_inventory' => $beginning,
                'ending_inventory' => $ending,
                'physical_count' => $ending, // default to system value, user can adjust
                'remarks' => null,
            ];
        }

        return $reportData;
    }

    public function createDailyReport(array $data, $userId)
    {
        $reportDate = Carbon::parse($data['report_date']);

        // Prevent duplicate report for same date
        if (InventoryReport::whereDate('report_date', $reportDate)->exists()) {
            throw new \Exception('Daily inventory report for this date already exists.');
        }

        $items = $data['items'] ?? [];

        return DB::transaction(function () use ($items, $reportDate, $userId) {
            $created = [];
            foreach ($items as $item) {
                $created[] = InventoryReport::create([
                    'report_date' => $reportDate,
                    'product_id' => $item['product_id'],
                    'beginning_inventory' => $item['beginning_inventory'],
                    'ending_inventory' => $item['ending_inventory'],
                    'physical_count' => $item['physical_count'],
                    'remarks' => $item['remarks'] ?? null,
                    'created_by' => $userId,
                ]);
            }
            return $created;
        });
    }

    public function getAllReports(array $filters): Builder
    {
        return InventoryReport::query()
            ->with(['product', 'creator'])
            ->when($filters['search'] ?? null, fn($q, $search) => $q->search($search))
            ->when($filters['report_date'] ?? null, fn($q, $date) => $q->forDate($date))
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'desc';
                $q->orderBy($sort, $order);
            });
    }

    public function createReport(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $reports = [];
            foreach ($data['items'] as $item) {
                $reports[] = InventoryReport::create([
                    'report_date'          => $data['report_date'],
                    'product_id'           => $item['product_id'],
                    'beginning_inventory'  => $item['beginning_inventory'],
                    'ending_inventory'     => $item['ending_inventory'],
                    'remarks'              => $item['remarks'] ?? null,
                    'created_by'           => $userId,
                ]);
            }
            return $reports;
        });
    }

    public function updateReport(InventoryReport $report, array $data): InventoryReport
    {
        $report->update($data);
        return $report->refresh();
    }

    public function deleteReport(InventoryReport $report): bool
    {
        return $report->delete();
    }
}
