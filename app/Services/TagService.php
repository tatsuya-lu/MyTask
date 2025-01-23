<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class TagService
{
    public function getAllUserTags()
    {
        return Tag::where('user_id', Auth::id())->get();
    }

    public function createTag(array $data)
    {
        return Tag::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'color' => $data['color']
        ]);
    }

    public function updateTag(Tag $tag, array $data)
    {
        $tag->update($data);
        return $tag;
    }

    public function deleteTag(Tag $tag)
    {
        $tag->delete();
    }
}
