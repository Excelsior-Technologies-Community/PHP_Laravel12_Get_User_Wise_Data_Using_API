<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function listByCustomer(Request $request)
    {
        $customerId = $request->query('customer_id');

        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        $notes = Note::where('created_by', $customerId)->get();

        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'data' => $notes
        ]);
    }
}
