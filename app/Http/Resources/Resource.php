<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class Resource extends JsonResource
{
    protected $showDetail;

    public function __construct($resource, $showDetail = false)
    {
        parent::__construct($resource);
        $this->showDetail = $showDetail;
    }

    public static function collection($resource)
    {
        return tap(static::newCollection($resource), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }
}
