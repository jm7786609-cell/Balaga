<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $limit;
    protected $page;

    public function __construct(Request $request)
    {
        $this->limit = (int) $request->get('limit', 10);
        $this->page = (int) $request->get('page', 1);
    }

    final public function success($data, string $message = 'OK', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'content' => $data,
        ], $code);
    }

    final public function denied(string $message = 'Forbidden', int $code = Response::HTTP_FORBIDDEN): array
    {
        return [
            'code' => $code,
            'message' => $message,
        ];
    }

    /**
     * Standardized error response for API
     */
    final public function error(string $message = 'Bad Request', int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'content' => null,
        ], $code);
    }
}
