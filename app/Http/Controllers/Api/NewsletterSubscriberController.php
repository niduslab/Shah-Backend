<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterSubscriberController extends Controller
{
    /**
     * Store a new newsletter subscription.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->status === 'subscribed') {
                return response()->json([
                    'success' => true,
                    'message' => "You're already subscribed. Thanks for staying with us!",
                    'data' => $existing,
                ], 200);
            }

            // Re-subscribe a previously unsubscribed email.
            $existing->update([
                'status' => 'subscribed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! Your 20% off code is on its way.',
                'data' => $existing,
            ], 200);
        }

        $subscriber = NewsletterSubscriber::create([
            'email' => $email,
            'status' => 'subscribed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks for subscribing! Your 20% off code is on its way.',
            'data' => $subscriber,
        ], 201);
    }
}
