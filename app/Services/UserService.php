<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    /**
     * Register a new user.
     * 
     * @param array $data User registration data (first_name, last_name, email, password, phone)
     * @return User
     * @throws ValidationException
     */
    public function register(array $data): User
    {
        $this->validateRegistrationData($data);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'customer',
                'status' => true,
            ]);

            // Dispatch registered event to trigger verification email
            event(new Registered($user));

            return $user;
        });
    }

    /**
     * Authenticate user with credentials.
     * 
     * @param string $email
     * @param string $password
     * @return array|null Returns array with user and token on success, null on failure
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->status) {
            return null;
        }

        // Create Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Update user profile.
     * 
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $allowedFields = ['first_name', 'last_name', 'phone'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        // Handle email change separately (requires re-verification)
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $this->validateEmailUnique($data['email'], $user->id);
            $updateData['email'] = $data['email'];
            $updateData['email_verified_at'] = null;
        }

        // Handle password change
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // If email changed, send new verification
        if (isset($updateData['email_verified_at'])) {
            $this->sendVerificationEmail($user);
        }

        return $user->fresh();
    }

    /**
     * Deactivate a user account.
     * 
     * @param User $user
     * @return bool
     */
    public function deactivate(User $user): bool
    {
        // Revoke all tokens
        $user->tokens()->delete();

        return $user->update(['status' => false]);
    }

    /**
     * Reactivate a user account.
     * 
     * @param User $user
     * @return bool
     */
    public function reactivate(User $user): bool
    {
        return $user->update(['status' => true]);
    }

    /**
     * Send verification email to user.
     * 
     * @param User $user
     * @return void
     */
    public function sendVerificationEmail(User $user): void
    {
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    /**
     * Initiate password reset process.
     * 
     * @param string $email
     * @return bool
     */
    public function resetPassword(string $email): bool
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            // Return true anyway to prevent email enumeration
            return true;
        }

        $status = Password::sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Complete password reset with token.
     * 
     * @param string $email
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function completePasswordReset(string $email, string $token, string $password): bool
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing tokens
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET;
    }

    /**
     * Get user by ID.
     * 
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Get user by email.
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get all customers with optional filters.
     * 
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getCustomers(array $filters = []): LengthAwarePaginator
    {
        $query = User::customers();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['verified'])) {
            if ($filters['verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Validate registration data.
     * 
     * @param array $data
     * @throws ValidationException
     */
    protected function validateRegistrationData(array $data): void
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = ['First name is required.'];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = ['Last name is required.'];
        }

        if (empty($data['email'])) {
            $errors['email'] = ['Email is required.'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Email must be a valid email address.'];
        } elseif (User::where('email', $data['email'])->exists()) {
            $errors['email'] = ['Email has already been taken.'];
        }

        if (empty($data['password'])) {
            $errors['password'] = ['Password is required.'];
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = ['Password must be at least 8 characters.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate email uniqueness for update.
     * 
     * @param string $email
     * @param int $excludeUserId
     * @throws ValidationException
     */
    protected function validateEmailUnique(string $email, int $excludeUserId): void
    {
        if (User::where('email', $email)->where('id', '!=', $excludeUserId)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email has already been taken.'],
            ]);
        }
    }

    /**
     * Logout user by revoking current token.
     * 
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * Logout user from all devices.
     * 
     * @param User $user
     * @return bool
     */
    public function logoutAllDevices(User $user): bool
    {
        return $user->tokens()->delete() > 0;
    }
}
