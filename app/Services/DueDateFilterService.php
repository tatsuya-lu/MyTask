<?php

namespace App\Services;

use App\Models\DueDateFilter;
use Illuminate\Support\Facades\Auth;

class DueDateFilterService
{
    public function getAllUserFilters()
    {
        return DueDateFilter::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createFilter(array $data)
    {
        return DueDateFilter::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'duration_value' => $data['duration_value'],
            'duration_unit' => $data['duration_unit']
        ]);
    }

    public function deleteFilter(DueDateFilter $filter)
    {
        if ($filter->user_id !== Auth::id()) {
            throw new \Exception('権限がありません', 403);
        }

        $filter->delete();
    }
}