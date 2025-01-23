<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use App\Services\TagService;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function index()
    {
        $tags = $this->tagService->getAllUserTags();
        return response()->json($tags);
    }

    public function store(TagRequest $request)
    {
        $tag = $this->tagService->createTag($request->validated());
        return response()->json($tag, 201);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);
        $updatedTag = $this->tagService->updateTag($tag, $request->validated());
        return response()->json($updatedTag);
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        $this->tagService->deleteTag($tag);
        return response()->json(null, 204);
    }
}
