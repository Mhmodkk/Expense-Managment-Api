<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Http\Resources\GoalResource;
use App\Services\GoalService;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{

    protected GoalService $goalService;

    public function __construct(GoalService $goalService) {
        $this->goalService = $goalService;
    }


    public function index()
    {
        $goals = $this->goalService->getall();

        return response()->json([
            'message' => __('app.goals_listed'),
            'Goal' => GoalResource::collection($goals->load('user')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'note' => 'nullable|string'
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'in_progress';

        $goal = Goal::create($validated);

        return response()->json([
            'message' => __('app.goal_created'),
            'data' => new GoalResource($goal)
        ], 201);
    }

    public function show($id)
    {
        $user_id = Auth::id();
        $goal = Goal::where('user_id', $user_id)->findOrFail($id);

        return new GoalResource($goal);
    }

    public function update(Request $request, $id)
    {
        $user_id = Auth::id();
        $goal = Goal::where('user_id', $user_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'target_amount' => 'sometimes|numeric|min:1',
            'current_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|in:in_progress,completed,cancelled',
            'due_date' => 'sometimes|date',
            'note' => 'nullable|string'
        ]);

        $goal->update($validated);

        return response()->json([
            'message' => __('app.goal_updated'),
            'data' => new GoalResource($goal)
        ], 200);
    }

    public function destroy($id)
    {
        $user_id = Auth::id();
        $goal = Goal::where('user_id', $user_id)->findOrFail($id);
        $goal->delete();

        return response()->json(['message' => __('app.goal_deleted')], 200);
    }
}
