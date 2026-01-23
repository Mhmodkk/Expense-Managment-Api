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
            'name'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users|email',
            'password'=>'required|string|min:8',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $user = $this->authService->register($request);
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'message'=>__('app.registeration_success_verify'),
            'User'=>new UserResource($user),
            'Token' => $token

        ],201);

    }


    public function login(Request $request,CurrencyService $currencyService): Response
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
            ],401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        $currencyService->sendDollarRateNotification($user);

        return response([
            'message'=>$user->email_verified_at ? __('app.login_success') : __('app.login_success_verify'),
            'result' => [
                'Balance' => $user->balance,
                'User'=> new UserResource($user),
                'Token' => $token
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


        public function otp(Request $request)
    {
        //get user
        $user = Auth::user();

        //generate otp
        $otp = $this->authService->otp($user);


        return response([
            'message'=> __('app.otp_sent_success')

        ]);
    }


        public function verify(Request $request)
    {
        // validate the request
        $request->validate([
            'otp' => 'required|numeric'
        ]);
        //get user
        $user = Auth::user();

        //verify otp
        $user = $this->authService->verify($user,$request);


        return response([
            'message'=> __('app.verification_success'),
            'result' => [
                'user' => new UserResource($user)
            ]

        ]);
    }


    public function resetOtp(Request $request)
    {
        //validate request
        $request->validate([
            'email' => 'required|email|max:255|exists:users,email'
        ]);

        //get user
        $user = $this->authService->getUserByEmail($request->email);

        //generate otp
        $otp = $this->authService->otp($user,'password-reset');


        return response([
            'message'=> __('app.otp_sent_success')

        ]);
    }


    public function resetPassword(Request $request)
    {
        //validate request
        $request->validate([
            'otp' => 'required|numeric',
            'password' => 'required|min:6|max:255|confirmed',
            'password_confirmation' => 'required|min:6|max:255'
        ]);

        //get user
        $user = $this->authService->getUserByEmail($request->email);

        //reset password
        $user = $this->authService->otp($user,'password-reset');


        return response([
            'message'=> __('app.password_reset_success'),
        ]);
    }


    public function setInitialBalance(Request $request)
    {
        $request->validate([
            'initial_balance' => 'nullable|numeric|min:0'
        ]);

        $user = Auth::user();
        $user->initial_balance = $request->initial_balance;
        $user->balance = $request->initial_balance;
        $user->save();

        return response()->json([
            'message' => __('app.balance_set'),
            'balance' => $user->balance
        ]);
    }
}
