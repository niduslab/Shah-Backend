<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {}

    /**
     * List all customers.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search', 'verified', 'sort_by', 'sort_order', 'per_page']);
        $customers = $this->userService->getCustomers($filters);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get a specific customer.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user || !$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load(['addresses', 'orders']),
        ]);
    }

    /**
     * Update a customer.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user || !$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string|max:20',
            'password' => 'sometimes|nullable|string|min:8',
            'user_type' => 'sometimes|in:customer,vendor,admin',
            'status' => 'sometimes|boolean',
        ]);

        // Handle user_type and status separately as they're not in updateProfile
        $adminFields = [];
        if (isset($validated['user_type'])) {
            $adminFields['user_type'] = $validated['user_type'];
        }
        if (isset($validated['status'])) {
            $adminFields['status'] = $validated['status'];
        }

        // Update profile fields (first_name, last_name, email, phone, password)
        $user = $this->userService->updateProfile($user, $validated);

        // Update admin-specific fields
        if (!empty($adminFields)) {
            $user->update($adminFields);
            $user->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Deactivate a customer.
     */
    public function deactivate(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user || !$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $this->userService->deactivate($user);

        return response()->json([
            'success' => true,
            'message' => 'Customer deactivated successfully.',
        ]);
    }

    /**
     * Reactivate a customer.
     */
    public function reactivate(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user || !$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $this->userService->reactivate($user);

        return response()->json([
            'success' => true,
            'message' => 'Customer reactivated successfully.',
        ]);
    }
}
