<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockIn\StoreStockInRequest;
use App\Http\Resources\StockIn\StockInCollection;
use App\Http\Resources\StockIn\StockInResource;
use App\Services\StockInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    protected $stockInService;

    public function __construct(StockInService $stockInService, Request $request)
    {
        parent::__construct($request);
        $this->stockInService = $stockInService;

        // $this->middleware('role:admin,pharmacist');
    }

    public function index(Request $request): StockInCollection
    {
        $validated = $request->validate([
            'product_id'  => 'nullable|exists:products,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
            'limit'       => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
        ]);

        $query = $this->stockInService->getStockInHistory($validated);
        $records = $query->paginate($this->limit ?? 20);

        return new StockInCollection($records);
    }

    public function store(StoreStockInRequest $request): JsonResponse
    {
        $stockIn = $this->stockInService->receiveStock($request->validated());

        return $this->success(
            new StockInResource($stockIn->load('product')),
            'Stock received and EOQ recalculated successfully',
            201
        );
    }
}
