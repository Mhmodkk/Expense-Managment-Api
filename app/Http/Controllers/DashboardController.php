<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function summary()
    {
        $user = Auth::user();
        $service = app(CurrencyService::class);

        $initial = $user->initial_balance ?? 0;

        $incomes = Income::where('user_id', $user->id)->get();
        $expenses = Expense::where('user_id', $user->id)->get();

        $totalIncome = $incomes->sum(fn($i) => $service->convert($i->amount, $i->currency_id, $user->currency_id));
        $totalExpense = $expenses->sum(fn($e) => $service->convert($e->amount, $e->currency_id, $user->currency_id));

        $balance = $initial + $totalIncome - $totalExpense;

        $transactions = collect()
            ->merge(
                $incomes->map(fn($i) => [
                    'type' => 'income',
                    'amount' => $service->convert($i->amount, $i->currency_id, $user->currency_id),
                    'category' => $i->category->name,
                    'date' => $i->date ?? $i->created_at->format('Y-m-d'),
                ])
            )
            ->merge(
                $expenses->map(fn($e) => [
                    'type' => 'expense',
                    'amount' => $service->convert($e->amount, $e->currency_id, $user->currency_id),
                    'category' => $e->category->name,
                    'date' => $e->date ?? $e->created_at->format('Y-m-d'),
                ])
            )
            ->sortByDesc('date')
            ->values();

        return response()->json([
            'currency' => $user->currency->code,
            'initial_balance' => round($initial, 2),
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'balance' => round($balance, 2),
            'transactions' => $transactions
        ]);
    }


    public function dashboard()
    {
        $user = Auth::user();
        $service = app(CurrencyService::class);

        $incomes = Income::where('user_id', $user->id)->get();
        $expenses = Expense::where('user_id', $user->id)->get();

        $totalIncome = $incomes->sum(fn($i) => $service->convert($i->amount, $i->currency_id, $user->currency_id));
        $totalExpense = $expenses->sum(fn($e) => $service->convert($e->amount, $e->currency_id, $user->currency_id));

        $balance = $totalIncome - $totalExpense;

        return response()->json([
            'currency' => $user->currency->code,
            'income_total' => round($totalIncome, 2),
            'expense_total' => round($totalExpense, 2),
            'balance' => round($balance, 2),
        ]);
    }


    public function expensePercent()
    {
        $user_id = Auth::id();

        $expenses = Expense::where('user_id', $user_id)
            ->with('category')
            ->get();

        $total = $expenses->sum('amount');

        if ($total == 0) {
            return response()->json([
                'message' => __('app.no_expenses_available'),
                'data' => []
            ]);
        }

        $result = $expenses
            ->groupBy(fn($e) => $e->category->name)
            ->map(fn($group, $categoryName) => [
            $categoryName . ' : ' . round(($group->sum('amount') / $total) * 100, 2) . '%'
            ])
            ->values();

        return response()->json([
            'data' => $result,
            'balance' => Auth::user()->balance,
        ]);
    }


    public function incomePercent()
    {
        $user_id = Auth::id();

        $incomes = Income::where('user_id', $user_id)
            ->with('category')
            ->get();

        $total = $incomes->sum('amount');

        if ($total == 0) {
            return response()->json([
                'message' => __('app.no_incomes_available'),
                'data' => []
            ]);
        }

        $result = $incomes
            ->groupBy(fn($e) => $e->category->name)
            ->map(fn($group, $categoryName) => [
            $categoryName . ' : ' . round(($group->sum('amount') / $total) * 100, 2) . '%'
            ])
            ->values();

        return response()->json([
            'data' => $result,
            'balance' => Auth::user()->balance,
        ]);
    }

        public function checkMonthlyLimit()
    {
        $user = Auth::user();

        if ($user->monthly_limit == 0) {
            return response()->json([
                'limit' => 0,
                'spent' => 0,
                'percentage' => 0,
                'status' => 'no-limit-set'
            ]);
        }

        $spentThisMonth = Expense::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $percentage = round(($spentThisMonth / $user->monthly_limit) * 100, 2);

        $status = 'normal';

        if ($percentage >= 100) {
            $status = 'limit-exceeded';
        } elseif ($percentage >= 90) {
            $status = 'almost-max';
        } elseif ($percentage >= 75) {
            $status = 'warning';
        }

        return response()->json([
            'limit' => $user->monthly_limit,
            'spent' => $spentThisMonth,
            'percentage' => $percentage,
            'status' => $status
        ]);
    }


    public function setMonthlyLimit(Request $request)
    {
        $request->validate([
            'monthly_limit' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $user->monthly_limit = $request->monthly_limit;
        $user->save();

        return response()->json([
            'message' => __('app.limit_set'),
            'monthly_limit' => $user->monthly_limit
        ]);
    }


}
