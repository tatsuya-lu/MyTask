<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DueDateFilterRequest;
use App\Models\DueDateFilter;
use App\Services\DueDateFilterService;

class DueDateFilterController extends Controller
{
    protected $dueDateFilterService;

    public function __construct(DueDateFilterService $dueDateFilterService)
    {
        $this->dueDateFilterService = $dueDateFilterService;
    }

    public function index()
    {
        $filters = $this->dueDateFilterService->getAllUserFilters();
        return response()->json($filters);
    }

    public function store(DueDateFilterRequest $request)
    {
        $filter = $this->dueDateFilterService->createFilter($request->validated());
        return response()->json($filter);
    }

    public function destroy(DueDateFilter $dueDateFilter)
    {
        try {
            $this->dueDateFilterService->deleteFilter($dueDateFilter);
            return response()->json(['message' => 'フィルターを削除しました']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
