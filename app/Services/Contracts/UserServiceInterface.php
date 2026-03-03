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
    public function authenticate(string $email, string $password): array;

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
     * Send password reset link.
     */
    public function sendPasswordResetLink(string $email): array;

    /**
     * Reset password with token.
     */
    public function resetPassword(string $email, string $token, string $password): array;

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
