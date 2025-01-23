<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function getUserTeams()
    {
        return Auth::user()->teams()->with('owner')->get();
    }

    public function createTeam(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

            // チーム数の制限チェック
            $teamsCount = $user->ownedTeams()->count();
            if (!$user->is_premium && $teamsCount >= 3) {
                throw new \Exception('チーム数の上限に達しました。プレミアムプランにアップグレードしてください。', 403);
            }

            // チーム作成
            $team = Team::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'owner_id' => $user->id,
                'member_limit' => $user->is_premium ? 50 : 10,
                'is_premium_team' => $user->is_premium
            ]);

            // リーダーロールの取得または作成
            $leaderRole = Role::firstOrCreate(
                ['slug' => 'leader'],
                ['name' => 'リーダー']
            );

            // オーナーをリーダーとして追加
            $team->members()->attach($user->id, ['role_id' => $leaderRole->id]);

            return $team->load(['members', 'owner']);
        });
    }

    public function addTeamMember(Team $team, int $userId, int $roleId)
    {
        return DB::transaction(function () use ($team, $userId, $roleId) {
            // メンバー数の制限チェック
            if ($team->members()->count() >= $team->member_limit) {
                throw new \Exception('チームのメンバー数上限に達しました。', 403);
            }

            $leaderRole = Role::where('slug', 'leader')->first();

            // リーダーの重複チェック
            if (
                $roleId == $leaderRole->id &&
                $team->members()->wherePivot('role_id', $leaderRole->id)->exists()
            ) {
                throw new \Exception('チームリーダーは既に存在します。', 422);
            }

            // メンバー追加
            $team->members()->attach($userId, ['role_id' => $roleId]);

            return true;
        });
    }

    public function changeTeamLeader(Team $team, int $newLeaderId)
    {
        return DB::transaction(function () use ($team, $newLeaderId) {
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
            $team->members()->updateExistingPivot($newLeaderId, [
                'role_id' => $leaderRole->id
            ]);

            return true;
        });
    }

    public function removeTeamMember(Team $team, User $user)
    {
        // オーナーは削除不可
        if ($user->id === $team->owner_id) {
            throw new \Exception('チームオーナーは削除できません。', 422);
        }

        $team->members()->detach($user->id);
        return true;
    }
}