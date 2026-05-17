<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class PaginatedResource extends ResourceCollection
{
    protected $pagination;

    public function __construct(LengthAwarePaginator $resource)
    {
        parent::__construct($resource);

        $this->pagination = [
            'current_page' => $resource->currentPage(),
            'last_page' => $resource->lastPage(),
            'per_page' => $resource->perPage(),
            'total' => $resource->total(),
            'count' => $resource->currentPage() < $resource->lastPage()
                ? $resource->perPage()
                : $resource->total() - ($resource->currentPage() - 1) * $resource->perPage(),
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'rows' => $this->data($this->collection),
            'pagination' => $this->pagination,
        ];
    }

    abstract public function data($collection): AnonymousResourceCollection;
}
