<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryReport\StoreInventoryReportRequest;
use App\Http\Requests\InventoryReport\UpdateInventoryReportRequest;
use App\Http\Resources\InventoryReport\InventoryReportCollection;
use App\Http\Resources\InventoryReport\InventoryReportResource;
use App\Models\InventoryReport;
use App\Models\Product;
use App\Services\InventoryReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    protected $inventoryReportService;

    public function __construct(InventoryReportService $inventoryReportService, Request $request)
    {
        parent::__construct($request);
        $this->inventoryReportService = $inventoryReportService;

        // Restrict access except index/show
        // $this->middleware('role:admin|pharmacist')->except(['index', 'show']);
    }

    public function index(Request $request): InventoryReportCollection
    {
        $validated = $request->validate([
            'search'      => 'nullable|string|max:255',
            'report_date' => 'nullable|date',
            'sort'        => 'nullable|in:report_date,created_at',
            'order'       => 'nullable|in:asc,desc',
            'limit'       => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
        ]);

        $query = $this->inventoryReportService->getAllReports($validated);
        $reports = $query->paginate($this->limit ?? 15);

        return new InventoryReportCollection($reports);
    }

    public function create(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $reportData = $this->inventoryReportService->generateDailyReportData(Carbon::parse($date), auth()->id());

        // Load product details
        $items = collect($reportData)->map(function ($item) {
            $product = Product::select('id', 'product_code', 'product_name', 'product_brand', 'unit')->find($item['product_id']);
            return array_merge($item, ['product' => $product]);
        });

        return $this->success(['items' => $items]);
    }

    public function show(InventoryReport $inventoryReport): JsonResponse
    {
        $inventoryReport->load(['product', 'creator']);
        return $this->success(new InventoryReportResource($inventoryReport));
    }

    public function store(StoreInventoryReportRequest $request): JsonResponse
    {
        $reports = $this->inventoryReportService->createReport(
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            InventoryReportResource::collection($reports),
            'Inventory report created successfully',
            201
        );
    }

    public function update(UpdateInventoryReportRequest $request, InventoryReport $inventoryReport): JsonResponse
    {
        $updated = $this->inventoryReportService->updateReport($inventoryReport, $request->validated());

        return $this->success(new InventoryReportResource($updated), 'Inventory report updated successfully');
    }

    public function destroy(InventoryReport $inventoryReport): JsonResponse
    {
        $this->inventoryReportService->deleteReport($inventoryReport);

        return $this->success(null, 'Inventory report deleted successfully');
    }
}
