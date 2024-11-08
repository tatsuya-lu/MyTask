<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::where('user_id', auth()->id())->get();
        return response()->json($tags);
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'color' => $request->color
        ]);

        return response()->json($tag, 201);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);
        $tag->update($request->validated());
        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        $tag->delete();
        return response()->json(null, 204);
    }
}
