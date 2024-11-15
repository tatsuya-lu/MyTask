<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = auth()->user()->teams()->with('owner')->get();
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'owner_id' => auth()->id()
        ]);

        $leaderRole = Role::where('slug', 'leader')->first();
        $team->members()->attach(auth()->id(), ['role_id' => $leaderRole->id]);

        return response()->json($team, 201);
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);
        return response()->json($team->load(['members', 'owner']));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $team->update($validated);
        return response()->json($team);
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(null, 204);
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('addMember', $team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($team->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'すでにチームのメンバーです。'], 422);
        }

        $team->members()->attach($validated['user_id'], [
            'role_id' => $validated['role_id']
        ]);

        return response()->json(['message' => 'メンバーを追加しました。']);
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('removeMember', $team);

        if ($user->id === $team->owner_id) {
            return response()->json(['message' => 'チームオーナーは削除できません。'], 422);
        }

        $team->members()->detach($user->id);
        return response()->json(['message' => 'メンバーを削除しました。']);
    }
}