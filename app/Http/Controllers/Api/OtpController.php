<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $otpCode = rand(100000, 999999);

        Otp::where('email', $validated['email'])
            ->where('is_used', false)
            ->update([
                'is_used' => true,
            ]);

        $otp = Otp::create([
            'email' => $validated['email'],
            'otp_code' => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(5),
            'is_used' => false,
        ]);

        Mail::to($validated['email'])->send(new SendOtpMail($otpCode));

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dibuat',
            'data' => [
                'email' => $otp->email,
                'expired_at' => $otp->expired_at,
            ],
        ], 200);
    }

    public function verifyOtp(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'otp_code' => 'required|string',
    ]);

    $otp = Otp::where('email', $validated['email'])
        ->where('otp_code', $validated['otp_code'])
        ->where('is_used', false)
        ->latest()
        ->first();

    if (!$otp) {
        return response()->json([
            'success' => false,
            'message' => 'OTP tidak valid'
        ], 400);
    }

    if (now()->greaterThan($otp->expired_at)) {
        return response()->json([
            'success' => false,
            'message' => 'OTP sudah expired'
        ], 400);
    }

    $otp->update([
        'is_used' => true
    ]);

    return response()->json([
        'success' => true,
        'message' => 'OTP berhasil diverifikasi'
    ], 200);
    }
}