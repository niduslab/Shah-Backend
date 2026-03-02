<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Get all user addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store a new address.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_line_1' => 'required|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'contact_no' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'address_type' => 'required|in:user_address,shipping_address,billing_address',
            'is_default' => 'nullable|boolean',
        ]);

        // If this is set as default, unset other defaults of same type
        if (!empty($validated['is_default'])) {
            $request->user()->addresses()
                ->where('address_type', $validated['address_type'])
                ->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data' => $address,
        ], 201);
    }

    /**
     * Get a specific address.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    /**
     * Update an address.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        $validated = $request->validate([
            'address_line_1' => 'sometimes|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'contact_no' => 'sometimes|string|max:20',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'zip_code' => 'sometimes|string|max:20',
            'address_type' => 'sometimes|in:user_address,shipping_address,billing_address',
            'is_default' => 'nullable|boolean',
        ]);

        // If this is set as default, unset other defaults of same type
        if (isset($validated['is_default']) && $validated['is_default']) {
            $addressType = $validated['address_type'] ?? $address->address_type;
            $request->user()->addresses()
                ->where('address_type', $addressType)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => $address->fresh(),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        // Check if address is being used in any orders
        $usedInOrders = $address->orders()->exists();

        if ($usedInOrders) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete address that is associated with orders.',
            ], 400);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        // Unset other defaults of same type
        $request->user()->addresses()
            ->where('address_type', $address->address_type)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully.',
            'data' => $address->fresh(),
        ]);
    }
}
