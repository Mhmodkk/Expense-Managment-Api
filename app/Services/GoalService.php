<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GoalService
{
    public function store(object $request)
    {
        $goal = Goal::create([
        'user_id' => Auth::id(),
        'name' => $request->name,
        'target_amount' => $request->target_amount,
        'current_amount' => $request->current_amount ?? 0,
        'due_date' => $request->due_date ?? Carbon::now(),
        'note' => $request->note,
        ]);

        return $goal;

    }


    public function update(object $request, $id)
    {
        $goal = Goal::where('id',$id)->where('user_id',Auth::id())->first();

        $goal->update([
            'name' => $request->name ?? $goal->name,
            'target_amount' => $request->target_amount ?? $goal->target_amount,
            'current_amount' => $request->current_amount ?? $goal->current_amount,
            'due_date' => $request->due_date ?? $goal->due_date,
            'note' => $request->note ?? $goal->note,
        ]);

        $goal->updated_at = Carbon::now();

        return $goal;
    }


    public function delete( $id)
    {
        $goal = Goal::where('id',$id)->where('user_id',Auth::id())->first();

        $goal->delete();
        return true;
    }


    public function getall()
    {
        return Goal::where('user_id', Auth::id())->latest()->get();
    }
}
