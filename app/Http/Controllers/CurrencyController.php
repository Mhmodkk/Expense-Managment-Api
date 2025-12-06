<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrencyController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    // يتم استدعاؤه عند فتح التطبيق
    public function checkRate(Request $request)
    {
        $user = Auth::user();

        $rate = $this->currencyService->sendDollarRateNotification($user);

        return response()->json([
            'message' => __('app.dollar_rate_sent'),
            'rate' => $rate,
            'currency' => $user->currency->code,
        ]);
    }

    public function changeCurrency(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $user = Auth::user();
        $user->currency_id = $request->currency_id;
        $user->save();

        return response()->json([
            'message' => __('app.currency_updated'),
            'user' => new UserResource($user),
        ]);
    }
}
