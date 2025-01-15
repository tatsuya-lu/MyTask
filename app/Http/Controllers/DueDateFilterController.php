<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DueDateFilter;

class DueDateFilterController extends Controller
{
    public function index()
    {
        $filters = DueDateFilter::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($filters);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month'
        ]);

        $filter = DueDateFilter::create([
            'user_id' => auth()->id(),
            ...$validated
        ]);

        return response()->json($filter);
    }

    public function destroy(DueDateFilter $dueDateFilter)
    {
        if ($dueDateFilter->user_id !== auth()->id()) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        $dueDateFilter->delete();
        return response()->json(['message' => 'フィルターを削除しました']);
    }
}
