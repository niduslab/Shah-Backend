<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartEvent;
use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $filters = $request->only(['status', 'user_type', 'search', 'verified', 'sort_by', 'sort_order', 'per_page']);
        $customers = $this->userService->getCustomers($filters);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get a specific customer with paginated orders, wishlist, and abandoned cart.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user || !$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $user->load(['addresses']);

        $perPage = (int) $request->input('per_page', 10);

        // Paginated orders
        $orders = $user->orders()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'orders_page');

        // Paginated wishlist
        $wishlist = $user->wishlists()
            ->with(['product.images'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'wishlist_page');

        // Paginated abandoned cart: added products not subsequently removed
        $removedProductIds = CartEvent::where('user_id', $id)
            ->where('event_type', 'removed')
            ->pluck('product_id')
            ->unique();

        $abandonedCart = CartEvent::where('user_id', $id)
            ->where('event_type', 'added')
            ->whereNotIn('product_id', $removedProductIds)
            ->with(['product.images'])
            ->latest('event_at')
            ->get()
            ->unique('product_id')
            ->values();

        // Manual pagination for abandoned cart (already deduplicated in memory)
        $cartPage    = (int) $request->input('cart_page', 1);
        $cartTotal   = $abandonedCart->count();
        $cartSlice   = $abandonedCart->slice(($cartPage - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => array_merge($user->toArray(), [
                'orders'         => $orders,
                'wishlist'       => $wishlist,
                'abandoned_cart' => [
                    'data'         => $cartSlice,
                    'total'        => $cartTotal,
                    'per_page'     => $perPage,
                    'current_page' => $cartPage,
                    'last_page'    => (int) ceil($cartTotal / $perPage) ?: 1,
                    'from'         => $cartTotal ? ($cartPage - 1) * $perPage + 1 : 0,
                    'to'           => min($cartPage * $perPage, $cartTotal),
                ],
            ]),
        ]);
    }

    /**
     * Create a new user (admin-initiated).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8',
            'user_type'  => 'sometimes|in:customer,vendor,admin',
            'status'     => 'sometimes|boolean',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'password'   => Hash::make($validated['password']),
            'user_type'  => $validated['user_type'] ?? 'customer',
            'status'     => $validated['status'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $user,
        ], 201);
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update(['status' => !$user->status]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'User status updated.',
            'data'    => $user,
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
     * Permanently delete a user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 400);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
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
