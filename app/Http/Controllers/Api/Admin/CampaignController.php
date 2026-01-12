<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\Contracts\CampaignServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignServiceInterface $campaignService
    ) {}

    /**
     * List all campaigns.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::withCount('recipients');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('campaign_type')) {
            $query->where('campaign_type', $request->campaign_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $campaigns = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * Store a new campaign.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'campaign_type' => 'required|in:promotional,newsletter,announcement',
            'target_type' => 'required|in:all,segment,custom',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $campaign = $this->campaignService->createCampaign($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'data' => $campaign,
        ], 201);
    }

    /**
     * Get a specific campaign.
     */
    public function show(int $id): JsonResponse
    {
        $campaign = Campaign::withCount('recipients')
            ->with(['recipients' => fn($q) => $q->limit(100)])
            ->find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $campaign,
        ]);
    }

    /**
     * Update a campaign.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a sent campaign.',
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'campaign_type' => 'sometimes|in:promotional,newsletter,announcement',
            'target_type' => 'sometimes|in:all,segment,custom',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $campaign->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data' => $campaign,
        ]);
    }

    /**
     * Delete a campaign.
     */
    public function destroy(int $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        if ($campaign->status === 'sending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a campaign that is currently sending.',
            ], 400);
        }

        $campaign->recipients()->delete();
        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.',
        ]);
    }

    /**
     * Send a campaign.
     */
    public function send(int $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Campaign has already been sent or is scheduled.',
            ], 400);
        }

        $this->campaignService->sendCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Campaign is being sent.',
            'data' => $campaign->fresh(),
        ]);
    }

    /**
     * Get campaign statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $stats = $this->campaignService->getCampaignStats($campaign);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Preview target recipients.
     */
    public function previewRecipients(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_type' => 'required|in:all,segment,custom',
            'target_criteria' => 'nullable|array',
        ]);

        $recipients = $this->campaignService->getTargetCustomers(
            $validated['target_type'],
            $validated['target_criteria'] ?? []
        );

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $recipients->count(),
                'preview' => $recipients->take(20),
            ],
        ]);
    }
}
