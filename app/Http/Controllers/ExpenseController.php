<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ExpenseResource;

class ExpenseController extends Controller
{

    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService) {
        $this->expenseService = $expenseService;
    }



    public function index()
    {
        $expenses = $this->expenseService->getall();

        return response()->json([
            'message' => __('app.incomes_listed'),
            'expense' => ExpenseResource::collection($expenses->load('user')),
        ]);
    }



        public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
        ]);

        $expense = $this->expenseService->store($request);

        return response()->json([
            'message' => __('app.expense_created'),
            'warning' => $expense->warning,
            'Expense' => new ExpenseResource($expense),
            'balance' => Auth::user()->balance,
        ], 201);
    }


    public function update(Request $request,$id)
    {

        $user_id=Auth::user()->id;
        $expense = Expense::findOrfail($id);

        if($expense->user_id != $user_id)
        return response()->json(['message'=>__('app.invalid_act_update_expense')],403);

        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'sometimes|string',

        ]);

        $expense = $this->expenseService->update($request,$id);

        return response()->json([
            'message'=>__('app.expense_updated'),
            'expense'=>new ExpenseResource($expense),
        ],200);
    }


    public function destroy($id)
    {
        try {
            $this->expenseService->delete($id);

            return response()->json([
            'message' => __('app.expense_deleted')
        ]);
        }   catch (Exception $e) {
            return response()->json([
            'message' => __('app.invalid_act_delete_expense'),

            ], 500);
        }
    }


        public function summary()
    {
        $user = Auth::user();
        $service = app(\App\Services\CurrencyService::class); // خدمة التحويل

        // جميع مصاريف المستخدم
        $expenses = Expense::where('user_id', $user->id)->with('category', 'currency')->get();

        if ($expenses->count() == 0) {
            return response()->json([
                'message' => __('app.expense_summary'),
                'total_expenses' => 0,
                'expenses_by_category' => []
            ]);
        }

        // نحول كل قيمة ل عملة المستخدم الحالية
        $total = $expenses->sum(function ($exp) use ($service, $user) {
            return $service->convert($exp->amount, $exp->currency_id, $user->currency_id);
        });

        // حساب مجموع كل فئة و النسبة
        $sumByCategory = $expenses
            ->groupBy('category_id')
            ->map(function ($group) use ($service, $user, $total) {

                $categoryTotal = $group->sum(function ($exp) use ($service, $user) {
                    return $service->convert($exp->amount, $exp->currency_id, $user->currency_id);
                });

                return [
                    'category' => $group->first()->category->name,
                    'total' => round($categoryTotal, 2),
                    'percentage' => round(($categoryTotal / $total) * 100, 2)
                ];
            })
            ->values();

        return response()->json([
            'message' => __('app.expense_summary'),
            'currency' => $user->currency->code,
            'total_expenses' => round($total, 2),
            'expenses_by_category' => $sumByCategory
        ]);
    }


    public function filterByPeriod(Request $request)
    {
        $user_id = Auth::id();
        $period = $request->query('period','day');

        $query = Expense::where('user_id',$user_id);

        switch ($period)
        {
            case 'day':
                $query->whereDate('created_at',now());
                break;

            case 'week':
                $query->whereBetween('created_at',[now()->startOfWeek(),now()->endOfWeek()]);
                break;

            case 'month':
                $query->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year);
                break;

            case 'year':
                $query->whereYear('created_at',now()->year);
                break;

            default:
                return response()->json(['message' => 'Invalid Period'],400);
        }

        $expenses = $query->latest()->get();
        return response()->json([
            'total_expense' => $expenses->sum('amount'),
            'records' => $expenses
        ]);
    }
}
