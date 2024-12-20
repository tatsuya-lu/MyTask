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
        $user = auth()->user();

        // チーム数の制限チェック
        $teamsCount = $user->ownedTeams()->count();
        if (!$user->is_premium && $teamsCount >= 3) {  // isPremium()をis_premiumに修正
            return response()->json([
                'message' => 'チーム数の上限に達しました。プレミアムプランにアップグレードすると、より多くのチームを作成できます。'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',  // null対策
            'owner_id' => $user->id,
            'member_limit' => $user->is_premium ? 50 : 10,
            'is_premium_team' => $user->is_premium
        ]);

        // リーダーロールの取得（または作成）
        $leaderRole = Role::firstOrCreate(
            ['slug' => 'leader'],
            ['name' => 'リーダー']
        );

        // オーナーをリーダーとして追加
        $team->members()->attach($user->id, ['role_id' => $leaderRole->id]);

        return response()->json($team->load(['members', 'owner']), 201);
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

        // メンバー数の制限チェック
        if ($team->members()->count() >= $team->member_limit) {
            return response()->json([
                'message' => 'チームのメンバー数上限に達しました。'
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        // リーダーの重複チェック
        $leaderRole = Role::where('slug', 'leader')->first();
        $memberRole = Role::where('slug', 'member')->first();

        // リーダー追加時の重複チェック
        if (
            $validated['role_id'] == $leaderRole->id &&
            $team->members()->wherePivot('role_id', $leaderRole->id)->exists()
        ) {
            return response()->json([
                'message' => 'チームリーダーは既に存在します。'
            ], 422);
        }

        $team->members()->attach($validated['user_id'], [
            'role_id' => $validated['role_id']
        ]);

        return response()->json(['message' => 'メンバーを追加しました。']);
    }

    public function changeLeader(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $leaderRole = Role::where('slug', 'leader')->first();
        $memberRole = Role::where('slug', 'member')->first();

        // 現在のリーダーをメンバーに変更
        $currentLeader = $team->members()
            ->wherePivot('role_id', $leaderRole->id)
            ->first();

        if ($currentLeader) {
            $team->members()->updateExistingPivot($currentLeader->id, [
                'role_id' => $memberRole->id
            ]);
        }

        // 新しいリーダーを設定
        $team->members()->updateExistingPivot($validated['user_id'], [
            'role_id' => $leaderRole->id
        ]);

        return response()->json(['message' => 'チームリーダーを変更しました。']);
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
