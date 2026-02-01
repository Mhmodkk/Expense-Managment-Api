<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
       $request->validate([
            'FirstName'=>'required|string|max:255',
            'LastName'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users|email',
            'password'=>'required|string|min:8',

        ]);

        $user = $this->authService->register($request);


        return response()->json([
            'message'=>__('app.registeration_success_verify'),
            'User'=>new UserResource($user),

        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json([
            'User' => new UserResource($request->user())
        ], 200);
    }

    public function login(Request $request, CurrencyService $currencyService): Response
    {
        $request->validate([
            'email'=>'required|email|max:255',
            'password'=>'required|min:8|max:255',
        ]);

        $user = $this->authService->login($request);

        if(!$user)
        {
            return response([
                'message'=>__('auth.failed')
            ], 401);
        }
        $token = $user->createToken('auth')->plainTextToken;
        if(!$user->email_verified_at)
        {
            return response([
                'message'=>__('app.login_success_verify'),
                'must_verify' => true,
                'User'=> new UserResource($user),
                'Token' => $token
            ], 403);
        }

        $currencyService->sendDollarRateNotification($user);

        return response([
            'message'=>$user->email_verified_at ? __('app.login_success') : __('app.login_success_verify'),
            'result' => [
                'Balance' => $user->balance,
                'monthly_limit' => $user->monthly_limit,
                'User'=> new UserResource($user),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => __('app.logout_success'),
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        if ($user->otps()) {
            $user->otps()->delete();
        }

        $user->delete();
        return response()->json([
            'message' => __('app.account_deleted_success'),
        ]);
    }

    public function otp(Request $request)
    {
        $user = Auth::user();
        $otp = $this->authService->otp($user);

        return response([
            'message'=> __('app.otp_sent_success')
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $user = Auth::user();
        $user = $this->authService->verify($user, $request);
        $token = $user->createToken('auth')->plainTextToken;

        return response([
            'message'=> __('app.verification_success'),
            'result' => [
                'user' => new UserResource($user)
                ,'Token' => $token
            ]
        ]);
    }

    public function resetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|exists:users,email'
        ]);

        $user = $this->authService->getUserByEmail($request->email);
        $otp = $this->authService->otp($user, 'password-reset');

        return response([
            'message'=> __('app.otp_sent_success')
        ]);
    }

    public function resetPassword(Request $request): Response
    {
        $request->validate([
            'otp' => 'required|numeric',
            'email' => 'required|email|max:255|exists:users,email',
            'password' => 'required|min:6|max:255|confirmed',
            'password_confirmation' => 'required|min:6|max:255'
        ]);

        $user = $this->authService->getUserByEmail($request->email);
        $user = $this->authService->resetPassword($user, $request);

        return response([
            'message'=> __('app.password_reset_success'),
        ]);
    }


    public function setInitialBalance(Request $request)
    {
        $request->validate([
            'initial_balance' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $user = Auth::user();

        $user->currency_id = $request->currency_id;
        $user->initial_balance = $request->initial_balance;
        $user->balance = $request->initial_balance;
        $user->save();

        $user->load('currency');

        return response()->json([
            'message' => __('app.balance_set'),
            'result' => [
                'balance' => $user->balance,
                'currency_id' => $user->currency_id,
                'currency' => $user->currency // سيعيد كائن العملة (Name, Symbol, etc)
            ]
        ]);
    }
}
