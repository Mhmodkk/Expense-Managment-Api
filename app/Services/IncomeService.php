<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IncomeService
{
    public function store(object $request): Income
    {
        $income = Income::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'date' => $request->date ?? Carbon::now(),
            'currency_id' => Auth::user()->currency_id,
        ]);

        $user = Auth::user();
        $user->balance = $user->balance + $income->amount;
        $user->save();

        return $income;

    }


    public function update(object $request, $id): Income
    {
        $income = Income::where('id',$id)->where('user_id',Auth::id())->first();

        $income->update([
            'amount' => $request->amount ?? $income->amount,
            'category_id' => $request->category_id ?? $income->category_id,
            'description' => $request->description ?? $income->description,
            'currency_id' => Auth::user()->currency_id,
        ]);

        $income->updated_at = Carbon::now();

        return $income;
    }


    public function delete( $id): bool
    {
        $income = Income::where('id',$id)->where('user_id',Auth::id())->first();

        $income->delete();
        return true;
    }


    public function getall()
    {
        return Income::where('user_id', Auth::id())->latest()->get();
    }

}
