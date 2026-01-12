<?php

namespace App\Services\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Register a new user.
     */
    public function register(array $data): User;

    /**
     * Authenticate user with credentials.
     */
    public function authenticate(string $email, string $password): ?array;

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Deactivate a user account.
     */
    public function deactivate(User $user): bool;

    /**
     * Send verification email to user.
     */
    public function sendVerificationEmail(User $user): void;

    /**
     * Initiate password reset process.
     */
    public function resetPassword(string $email): bool;

    /**
     * Complete password reset with token.
     */
    public function completePasswordReset(string $email, string $token, string $password): bool;

    /**
     * Get user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Get user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get all customers with optional filters.
     */
    public function getCustomers(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
