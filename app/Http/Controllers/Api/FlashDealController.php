<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $flashDeals = FlashDeal::with(['products' => function ($query) {
                $query->where('status', 'active')
                    ->with(['images', 'brand', 'category']);
            }])
            ->active()
            ->orderBy('priority', 'desc')
            ->get();

        $flashDeals->each(function ($deal) {
            $deal->time_remaining = $deal->time_remaining;
            $deal->remaining_quantity = $deal->remaining_quantity;
        });

        return response()->json([
            'success' => true,
            'data' => $flashDeals,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $flashDeal = FlashDeal::with(['products' => function ($query) {
                $query->where('status', 'active')
                    ->with(['images', 'brand', 'category']);
            }])
            ->active()
            ->find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found or not active.',
            ], 404);
        }

        $flashDeal->time_remaining = $flashDeal->time_remaining;
        $flashDeal->remaining_quantity = $flashDeal->remaining_quantity;

        return response()->json([
            'success' => true,
            'data' => $flashDeal,
        ]);
    }

    public function upcoming(): JsonResponse
    {
        $flashDeals = FlashDeal::with(['products' => function ($query) {
                $query->where('status', 'active')
                    ->with(['images', 'brand', 'category']);
            }])
            ->upcoming()
            ->orderBy('starts_at', 'asc')
            ->get();

        $flashDeals->each(function ($deal) {
            $deal->time_remaining = $deal->time_remaining;
        });

        return response()->json([
            'success' => true,
            'data' => $flashDeals,
        ]);
    }
}
