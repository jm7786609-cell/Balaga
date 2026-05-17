<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequisition\StorePurchaseRequisitionRequest;
use App\Http\Requests\PurchaseRequisition\UpdatePurchaseRequisitionRequest;
use App\Http\Resources\PurchaseRequisition\PurchaseRequisitionCollection;
use App\Http\Resources\PurchaseRequisition\PurchaseRequisitionResource;
use App\Models\PurchaseRequisition;
use App\Services\PurchaseRequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequisitionController extends Controller
{
    protected $requisitionService;

    public function __construct(PurchaseRequisitionService $requisitionService, Request $request)
    {
        parent::__construct($request);
        $this->requisitionService = $requisitionService;

        // $this->middleware('role:admin|pharmacist')->except(['index', 'show']);
    }

    public function index(Request $request): PurchaseRequisitionCollection
    {
        $validated = $request->validate([
            'search'      => 'nullable|string|max:255',
            'status'      => 'nullable|in:pending,approved,rejected,cancelled',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sort'        => 'nullable|in:created_at,updated_at',
            'order'       => 'nullable|in:asc,desc',
            'limit'       => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
        ]);

        $query = $this->requisitionService->getAllRequisitions($validated);
        $requisitions = $query->paginate($this->limit ?? 15);

        return new PurchaseRequisitionCollection($requisitions);
    }

    public function show(PurchaseRequisition $purchaseRequisition): JsonResponse
    {
        $purchaseRequisition->load(['supplier', 'requestedBy', 'approvedBy', 'items']);
        return $this->success(new PurchaseRequisitionResource($purchaseRequisition));
    }

    public function store(StorePurchaseRequisitionRequest $request): JsonResponse
    {
        $requisition = $this->requisitionService->create($request->validated());
        return $this->success(new PurchaseRequisitionResource($requisition), 'Purchase requisition created successfully', 201);
    }

    public function update(UpdatePurchaseRequisitionRequest $request, PurchaseRequisition $purchaseRequisition): JsonResponse
    {
        $requisition = $this->requisitionService->update($purchaseRequisition, $request->validated());
        return $this->success(new PurchaseRequisitionResource($requisition), 'Purchase requisition updated successfully');
    }

    public function destroy(PurchaseRequisition $purchaseRequisition): JsonResponse
    {
        try {
            $this->requisitionService->delete($purchaseRequisition);
            return $this->success(null, 'Purchase requisition deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
