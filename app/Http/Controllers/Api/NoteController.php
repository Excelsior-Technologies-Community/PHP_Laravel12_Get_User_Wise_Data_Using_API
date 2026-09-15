<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Get customer-wise notes with search,
     * date filtering and pagination.
     */
    public function listByCustomer(Request $request)
    {
        // Get customer ID from query string
        $customerId = $request->query('customer_id');

        // Validate customer_id
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        // Validate customer_id format
        if (!is_numeric($customerId) || $customerId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id must be a valid positive number'
            ], 422);
        }

        // Get optional filters
        $search = $request->query('search');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        // Pagination
        $perPage = $request->query('per_page', 5);

        // Prevent very large page sizes
        if (!is_numeric($perPage) || $perPage < 1) {
            $perPage = 5;
        }

        $perPage = min((int) $perPage, 50);

        // Start customer-wise query
        $query = Note::where('created_by', $customerId);

        // Search title or description
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter notes from a specific date
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        // Filter notes until a specific date
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Latest notes first
        $notes = $query
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'customer_id' => (int) $customerId,

            'filters' => [
                'search' => $search,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],

            'pagination' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'from' => $notes->firstItem(),
                'to' => $notes->lastItem(),
            ],

            'data' => $notes->items()
        ]);
    }

    /**
     * Get summary of notes belonging to a customer.
     */
    public function customerSummary(Request $request)
    {
        // Get customer ID
        $customerId = $request->query('customer_id');

        // Validate customer_id
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        // Validate customer_id format
        if (!is_numeric($customerId) || $customerId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id must be a valid positive number'
            ], 422);
        }

        // Customer-wise query
        $query = Note::where('created_by', $customerId);

        // Total notes
        $totalNotes = $query->count();

        // Latest note
        $latestNote = (clone $query)
            ->latest('created_at')
            ->first();

        // Oldest note
        $oldestNote = (clone $query)
            ->oldest('created_at')
            ->first();

        return response()->json([
            'status' => true,
            'customer_id' => (int) $customerId,

            'summary' => [
                'total_notes' => $totalNotes,

                'latest_note' => $latestNote ? [
                    'id' => $latestNote->id,
                    'title' => $latestNote->title,
                    'created_at' => $latestNote->created_at,
                ] : null,

                'oldest_note' => $oldestNote ? [
                    'id' => $oldestNote->id,
                    'title' => $oldestNote->title,
                    'created_at' => $oldestNote->created_at,
                ] : null,
            ]
        ]);
    }

    /**
     * Get customer-wise note statistics.
     */
    public function customerStatistics(Request $request)
    {
        // Get customer ID from query string
        $customerId = $request->query('customer_id');

        // Validate customer_id
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        // Validate customer_id format
        if (!is_numeric($customerId) || $customerId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id must be a valid positive number'
            ], 422);
        }

        // Base customer-wise query
        $query = Note::where('created_by', $customerId);

        // Total number of notes
        $totalNotes = (clone $query)->count();

        // Notes created today
        $notesToday = (clone $query)
            ->whereDate('created_at', today())
            ->count();

        // Notes created this month
        $notesThisMonth = (clone $query)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Notes created this year
        $notesThisYear = (clone $query)
            ->whereYear('created_at', now()->year)
            ->count();

        // Latest note
        $latestNote = (clone $query)
            ->latest('created_at')
            ->first();

        return response()->json([
            'status' => true,
            'customer_id' => (int) $customerId,

            'statistics' => [
                'total_notes' => $totalNotes,
                'notes_today' => $notesToday,
                'notes_this_month' => $notesThisMonth,
                'notes_this_year' => $notesThisYear,
                'latest_note_date' => $latestNote
                    ? $latestNote->created_at->format('Y-m-d')
                    : null,
            ]
        ]);
    }
}