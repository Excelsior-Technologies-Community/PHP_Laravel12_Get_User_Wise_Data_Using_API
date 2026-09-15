<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Get customer-wise notes with:
     * Search
     * Date filtering
     * Pagination
     * Sorting
     * Note ID filter
     * Title filter
     * Today filter
     * This month filter
     */
    public function listByCustomer(Request $request)
    {
        $customerId = $request->query('customer_id');

        // Validate customer_id
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        if (!is_numeric($customerId) || $customerId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id must be a valid positive number'
            ], 422);
        }

        $customerId = (int) $customerId;

        // Existing filters
        $search = $request->query('search');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        // New filters
        $noteId = $request->query('note_id');
        $title = $request->query('title');
        $today = $request->query('today');
        $thisMonth = $request->query('this_month');

        // Pagination
        $perPage = $request->query('per_page', 5);

        if (!is_numeric($perPage) || $perPage < 1) {
            $perPage = 5;
        }

        $perPage = min((int) $perPage, 50);

        // Sorting
        $allowedSorts = [
            'id',
            'title',
            'created_at',
            'updated_at'
        ];

        $sortBy = $request->query('sort_by', 'created_at');

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $sortDirection = strtolower(
            $request->query('sort_direction', 'desc')
        );

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Base query
        $query = Note::where('created_by', $customerId);

        // Search title or description
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Exact note ID filter
        if (!empty($noteId)) {
            if (is_numeric($noteId) && $noteId > 0) {
                $query->where('id', (int) $noteId);
            }
        }

        // Title filter
        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // From date
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        // To date
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Today filter
        if ($today === '1' || $today === 'true') {
            $query->whereDate('created_at', today());
        }

        // This month filter
        if ($thisMonth === '1' || $thisMonth === 'true') {
            $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        }

        // Sorting
        $notes = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'status' => true,

            'customer_id' => $customerId,

            'filters' => [
                'search' => $search,
                'note_id' => $noteId,
                'title' => $title,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'today' => $today,
                'this_month' => $thisMonth,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
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
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $query = Note::where('created_by', $customerId);

        $totalNotes = $query->count();

        $latestNote = (clone $query)
            ->latest('created_at')
            ->first();

        $oldestNote = (clone $query)
            ->oldest('created_at')
            ->first();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,

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
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $query = Note::where('created_by', $customerId);

        $totalNotes = (clone $query)->count();

        $notesToday = (clone $query)
            ->whereDate('created_at', today())
            ->count();

        $notesThisMonth = (clone $query)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $notesThisYear = (clone $query)
            ->whereYear('created_at', now()->year)
            ->count();

        $latestNote = (clone $query)
            ->latest('created_at')
            ->first();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,

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


    /**
     * NEW 1:
     * Get a single note belonging to a customer.
     */
    public function showCustomerNote(
        Request $request,
        $noteId
    ) {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        if (!is_numeric($noteId) || $noteId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid note ID'
            ], 422);
        }

        $note = Note::where('id', $noteId)
            ->where('created_by', $customerId)
            ->first();

        if (!$note) {
            return response()->json([
                'status' => false,
                'message' => 'Note not found for this customer'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'data' => $note
        ]);
    }


    /**
     * NEW 2:
     * Get customers with note counts.
     */
    public function customerNoteCounts()
    {
        $customers = Note::select(
                'created_by',
                DB::raw('COUNT(*) as total_notes')
            )
            ->groupBy('created_by')
            ->orderByDesc('total_notes')
            ->get();

        return response()->json([
            'status' => true,
            'total_customers' => $customers->count(),
            'data' => $customers
        ]);
    }


    /**
     * NEW 3:
     * Monthly customer activity.
     */
    public function monthlyActivity(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $activity = Note::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_notes')
            )
            ->where('created_by', $customerId)
            ->groupBy(
                DB::raw('YEAR(created_at)'),
                DB::raw('MONTH(created_at)')
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'data' => $activity
        ]);
    }


    /**
     * NEW 4:
     * Get notes created today.
     */
    public function todayNotes(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $notes = Note::where('created_by', $customerId)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'date' => today()->format('Y-m-d'),
            'total_notes' => $notes->count(),
            'data' => $notes
        ]);
    }


    /**
     * NEW 5:
     * Get notes created this month.
     */
    public function thisMonthNotes(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $notes = Note::where('created_by', $customerId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'month' => now()->format('Y-m'),
            'total_notes' => $notes->count(),
            'data' => $notes
        ]);
    }


    /**
     * NEW 6:
     * Latest N notes.
     */
    public function latestNotes(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $limit = $request->query('limit', 5);

        if (!is_numeric($limit) || $limit < 1) {
            $limit = 5;
        }

        $limit = min((int) $limit, 50);

        $notes = Note::where('created_by', $customerId)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'limit' => $limit,
            'data' => $notes
        ]);
    }


    /**
     * NEW 7:
     * Search only customer titles.
     */
    public function titleSearch(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $title = $request->query('title');

        if (!$title) {
            return response()->json([
                'status' => false,
                'message' => 'title is required'
            ], 400);
        }

        $notes = Note::where('created_by', $customerId)
            ->where('title', 'like', '%' . $title . '%')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'search' => $title,
            'total_results' => $notes->count(),
            'data' => $notes
        ]);
    }


    /**
     * NEW 8:
     * Get notes within a date range.
     */
    public function dateRange(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if (!$fromDate || !$toDate) {
            return response()->json([
                'status' => false,
                'message' => 'from_date and to_date are required'
            ], 400);
        }

        if ($fromDate > $toDate) {
            return response()->json([
                'status' => false,
                'message' => 'from_date cannot be greater than to_date'
            ], 422);
        }

        $notes = Note::where('created_by', $customerId)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,

            'date_range' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],

            'total_notes' => $notes->count(),

            'data' => $notes
        ]);
    }


    /**
     * NEW 9:
     * Customer note existence check.
     */
    public function hasNotes(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $hasNotes = Note::where('created_by', $customerId)->exists();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'has_notes' => $hasNotes
        ]);
    }


    /**
     * NEW 10:
     * Customer note count by year.
     */
    public function yearlyActivity(Request $request)
    {
        $customerId = $this->validateCustomerId($request);

        if ($customerId instanceof \Illuminate\Http\JsonResponse) {
            return $customerId;
        }

        $activity = Note::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total_notes')
            )
            ->where('created_by', $customerId)
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year')
            ->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'data' => $activity
        ]);
    }


    /**
     * Reusable customer ID validation.
     */
    private function validateCustomerId(Request $request)
    {
        $customerId = $request->query('customer_id');

        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        if (!is_numeric($customerId) || $customerId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id must be a valid positive number'
            ], 422);
        }

        return (int) $customerId;
    }
}