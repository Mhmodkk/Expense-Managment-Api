<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Income;
use Illuminate\Http\Request;
use App\Services\IncomeService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\IncomeResource;

class IncomeController extends Controller
{

    protected IncomeService $incomeService;

    public function __construct(IncomeService $incomeService) {
        $this->incomeService = $incomeService;
    }



    public function index()
    {
        $incomes = $this->incomeService->getall();

        return response()->json([
            'message' => __('app.incomes_listed'),
            'Income' => IncomeResource::collection($incomes->load('user')),
        ]);
    }



    public function store(Request $request)
    {

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',

        ]);

        $income = $this->incomeService->store($request);

        return response()->json([
            'message'=>__('app.income_created'),
            'Income'=>new IncomeResource($income),
            'balance' => Auth::user()->balance,
        ],201);
    }



    public function update(Request $request,$id)
    {

        $user_id=Auth::user()->id;
        $income = Income::findOrfail($id);

        if($income->user_id != $user_id)
        return response()->json(['message'=>__('app.invalid_act_update_income')],403);

        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'sometimes|string',

        ]);

        $income = $this->incomeService->update($request,$id);

        return response()->json([
            'message'=>__('app.income_updated'),
            'Income'=>new IncomeResource($income),
        ],200);
    }


    public function destroy($id)
    {
        try {
            $this->incomeService->delete($id);

            return response()->json([
            'message' => __('app.income_deleted')
        ]);
        }   catch (Exception $e) {
            return response()->json([
            'message' => __('app.invalid_act_delete_income'),

            ], 500);
        }
    }


    public function summary()
    {
        $user_id=Auth::user()->id;
        $total = Income::where('user_id', $user_id)->sum('amount');

        $sumByCategory = Income::where('user_id', $user_id)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($income) {
                return [
                    'category' => $income->category ? $income->category->name : 'Uncategorized',
                    'total' => (float) $income->total,
                ];
            });

            return response()->json([
                'message' => __('app.income_summary'),
                'total_incomes' => (float) $total,
                'incomes_by_category' => $sumByCategory,
            ]);
    }


    public function filterByPeriod(Request $request)
    {
        $user_id = Auth::id();
        $period = $request->query('period','day');

        $query = Income::where('user_id',$user_id);

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

        $incomes = $query->latest()->get();
        return response()->json([
            'totla_income' => $incomes->sum('amount'),
            'records' => $incomes
        ]);
    }

}

