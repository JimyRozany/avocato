<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Review::with(['reviewer:id,name,image', 'reviewed:id,name,image']);

        if ($request->filled('reviewed_id')) {
            $query->where('reviewed_id', $request->reviewed_id);
        }

        if ($request->filled('reviewer_id')) {
            $query->where('reviewer_id', $request->reviewer_id);
        }

        return $this->successResponse($query->latest()->get());
    }

    public function userReviews($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        $reviews = Review::with(['reviewer:id,name,image'])
            ->where('reviewed_id', $userId)
            ->latest()
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'created_at' => $review->created_at,
                    'reviewer'   => $review->reviewer,
                ];
            });



        return $this->successResponse([
            'user'     => $user->only(['id', 'name', 'image']),
            'reviews'  => $reviews

        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewed_id' => 'required|exists:users,id',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'nullable|string|max:1000',
            'case_id'     => 'nullable|exists:cases,id',
        ]);

        $user = auth()->user();

        if ($user->id == $validated['reviewed_id']) {
            return $this->errorResponse('You cannot review yourself', 422);
        }

        $hasExisting = Review::where('reviewer_id', $user->id)
            ->where('reviewed_id', $validated['reviewed_id'])
            ->exists();

        if ($hasExisting) {
            return $this->errorResponse('You have already reviewed this user', 422);
        }

        if (!$user->hasRole('admin')) {
            $reviewedUser = \App\Models\User::findOrFail($validated['reviewed_id']);

            $reviewerRole = $user->roles->first()->name;
            $reviewedRole = $reviewedUser->roles->first()->name;

            $allowedPairs = [
                ['client', 'avocato'],
                ['avocato', 'client'],
            ];

            $pair = [$reviewerRole, $reviewedRole];
            if (!in_array($pair, $allowedPairs)) {
                return $this->errorResponse('Clients can only review lawyers and lawyers can only review clients', 422);
            }

            $sharedCase = CaseModel::where(function ($q) use ($user, $validated) {
                $q->whereHas('parties', fn($pq) => $pq->where('user_id', $user->id))
                    ->whereHas('lawyers', fn($lq) => $lq->where('case_lawyers.lawyer_id', $validated['reviewed_id']));
            })->orWhere(function ($q) use ($user, $validated) {
                $q->whereHas('parties', fn($pq) => $pq->where('user_id', $validated['reviewed_id']))
                    ->whereHas('lawyers', fn($lq) => $lq->where('case_lawyers.lawyer_id', $user->id));
            })->first();

            if (!$sharedCase) {
                return $this->errorResponse('You must share a case with this user to review them', 422);
            }

            $validated['case_id'] = $sharedCase->id;
        }

        $validated['reviewer_id'] = $user->id;

        $review = Review::create($validated);

        return $this->successResponse(
            $review->load(['reviewer:id,name,image', 'reviewed:id,name,image']),
            'Review created successfully',
            201
        );
    }

    public function show($id)
    {
        $review = Review::with(['reviewer:id,name,image', 'reviewed:id,name,image'])
            ->findOrFail($id);

        return $this->successResponse($review->only([
            'id',
            'rating',
            'comment',
            'created_at',
            'reviewer',
            "reviewed"

        ]));
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $user = auth()->user();

        if ($user->id !== $review->reviewer_id && !$user->hasRole('admin')) {
            return $this->errorResponse('You can only update your own reviews', 403);
        }

        $validated = $request->validate([
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return $this->successResponse(
            $review->fresh()->load(['reviewer:id,name,image', 'reviewed:id,name,image']),
            'Review updated successfully'
        );
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        $user = auth()->user();

        if ($user->id !== $review->reviewer_id && !$user->hasRole('admin')) {
            return $this->errorResponse('You can only delete your own reviews', 403);
        }

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully');
    }
}
