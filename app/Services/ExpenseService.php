<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function store(object $request): Expense
    {
        $expense = Expense::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'date' => $request->date ?? Carbon::now(),
            'currency_id' => Auth::user()->currency_id,
        ]);

        $user = Auth::user();
        $user->balance -= $expense->amount;
        $user->save();

        $limit = $user->monthly_limit;
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $spentThisMonth = Expense::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $percentage = ($spentThisMonth / $limit) * 100;

        $warning = null;

        if ($percentage >= 100) {
        $warning = "You have exceeded your monthly spending limit!";
            } elseif ($percentage >= 80) {
        $warning = "You have reached " . round($percentage) . "% of your monthly limit.";
            }


        $expense->warning = $warning;

        return $expense;
    }


    public function update(object $request, $id): Expense
    {
        $expense = Expense::where('id',$id)->where('user_id',Auth::id())->first();

        $expense->update([
            'amount' => $request->amount ?? $expense->amount,
            'category_id' => $request->category_id ?? $expense->category_id,
            'description' => $request->description ?? $expense->description,
            'date' => $request->date ?? $expense->date,
            'currency_id' => Auth::user()->currency_id,
        ]);

        $expense->updated_at = Carbon::now();

        return $expense;
    }


    public function delete( $id): bool
    {
        $expense = Expense::where('id',$id)->where('user_id',Auth::id())->first();

        $expense->delete();
        return true;
    }


    public function getall()
    {
        return Expense::where('user_id', Auth::id())->latest()->get();
    }

}
