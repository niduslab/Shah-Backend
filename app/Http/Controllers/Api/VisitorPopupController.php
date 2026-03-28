<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorPopup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitorPopupController extends Controller
{
    /**
     * Store visitor popup submission.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $visitorPopup = VisitorPopup::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your submission!',
            'data' => $visitorPopup,
        ], 201);
    }
}
