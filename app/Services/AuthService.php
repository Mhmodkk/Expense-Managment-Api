<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function register(object $request): User
    {
        $user = User::create([
            'uuid' => Str::uuid(),
            'FirstName' => $request->FirstName,
            'LastName' => $request->LastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'currency_id' => $request->currency_id,
        ]);

        //Send Verification Code
        $this->otp($user);

        return $user;
    }

    public function login(object $request): ?User
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function otp(User $user, string $type = 'verification'): Otp
    {
        //check for spam and throttle
        $tries = 3;
        $time = Carbon::now()->subMinutes(30);

        $count = Otp::where([
            'user_id' => $user->id,
            'type' => $type,
            'active' => 1,
        ])->where('created_at','>=',$time)->count();

        if($count >=$tries)
        {
            abort(422,'To many OTP Requests');
        }

        $code = random_int(100000, 999999);
        $otp = Otp::create([
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'active' => 1,
        ]);

        //Send Mail

        Mail::to($user)->send(new OtpMail($user, $otp));

        return $otp;
    }

    public function verify(User $user, object $request): User
    {
        $otp = Otp::where([
            'user_id' => $user->id,
            'code' => $request->otp,
            'active' => 1,
            'type' => 'verification',
        ])->first();

        if (!$otp) {
            abort(422, __('app.invalid_otp'));
        }

        //Update
        $user->email_verified_at = Carbon::now();
        $user->update();

        $otp->active = 0;
        $otp->updated_at = Carbon::now();
        $otp->update();

        return $user;
    }

    public function getUserByEmail(string $email): User
    {
        return User::where('email', $email)->first();
    }

    public function resetPassword(User $user, object $request): User
    {
        //validate otp

        $otp = Otp::where([
            'user_id' => $user->id,
            'code' => $request->otp,
            'active' => 1,
            'type' => 'password-reset',
        ])->first();

        if (!$otp) {
            abort(422, __('app.invalid_otp'));
        }

        //Update
        $user->password = Hash::make($request->password);
        $user->updated_at = Carbon::now();
        $user->save();

        $otp->active = 0;
        $otp->updated_at = Carbon::now();
        $otp->save();

        return $user;
    }
}
