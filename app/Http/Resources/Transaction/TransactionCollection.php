<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\PaginatedResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class TransactionCollection extends PaginatedResource
{
    public function data($collection): AnonymousResourceCollection
    {
        return TransactionResource::collection($collection);
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'rows'       => $this->data($this->collection),
            'pagination' => $this->pagination,
        ]);
    }
}
