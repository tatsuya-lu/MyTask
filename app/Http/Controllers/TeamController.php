<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamRequest;
use App\Http\Requests\TeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function index()
    {
        $teams = $this->teamService->getUserTeams();
        return response()->json($teams);
    }

    public function store(TeamRequest $request)
    {
        try {
            $team = $this->teamService->createTeam($request->validated());
            return response()->json($team, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);
        return response()->json($team->load(['members', 'owner']));
    }

    public function update(TeamRequest $request, Team $team)
    {
        $this->authorize('update', $team);
        $team->update($request->validated());
        return response()->json($team);
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(null, 204);
    }

    public function addMember(TeamMemberRequest $request, Team $team)
    {
        $this->authorize('addMember', $team);

        try {
            $this->teamService->addTeamMember(
                $team,
                $request->input('user_id'),
                $request->input('role_id')
            );

            return response()->json(['message' => 'メンバーを追加しました。']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function changeLeader(TeamMemberRequest $request, Team $team)
    {
        $this->authorize('update', $team);

        try {
            $this->teamService->changeTeamLeader(
                $team,
                $request->input('user_id')
            );

            return response()->json(['message' => 'チームリーダーを変更しました。']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('removeMember', $team);

        try {
            $this->teamService->removeTeamMember($team, $user);
            return response()->json(['message' => 'メンバーを削除しました。']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
