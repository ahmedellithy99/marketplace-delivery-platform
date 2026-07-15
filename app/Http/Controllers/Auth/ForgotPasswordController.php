<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function showForgotForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendOtp(ForgotPasswordRequest $request): RedirectResponse
    {
        $phone = $request->input('phone');

        $executed = RateLimiter::attempt(
            'otp-send:' . $phone,
            $maxAttempts = 1,
            function () use ($phone) {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                OtpToken::create([
                    'phone' => $phone,
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(10),
                ]);

                $message = "🔐 رمز التحقق الخاص بك هو: {$otp}\nيستخدم هذا الرمز مرة واحدة فقط، صالح لمدة 10 دقائق.\nإذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذه الرسالة.";

                SendWhatsAppMessageJob::dispatch('2' . $phone, $message);
            },
            $decaySeconds = 60,
        );

        if (!$executed) {
            return back()->with('error', 'تم إرسال رمز التحقق مسبقاً. الرجاء الانتظار دقيقة قبل المحاولة مرة أخرى.');
        }

        session(['otp_phone' => $phone]);

        return redirect()->route('verify-otp')->with('success', 'تم إرسال رمز التحقق إلى رقم هاتفك عبر واتساب.');
    }

    public function showVerifyForm(): Response|RedirectResponse
    {
        if (!session('otp_phone')) {
            return redirect()->route('forgot-password');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'phone' => session('otp_phone'),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $phone = $request->input('phone');
        $otp = $request->input('otp');

        $otpToken = OtpToken::where('phone', $phone)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', 5)
            ->latest()
            ->first();

        if (!$otpToken || $otpToken->otp !== $otp) {
            OtpToken::where('phone', $phone)->where('expires_at', '>', now())->increment('attempts');
            return back()->with('error', 'رمز التحقق غير صحيح أو منتهي الصلاحية.');
        }

        $otpToken->delete();

        session(['otp_verified' => true, 'reset_phone' => $phone]);
        session()->forget('otp_phone');

        return redirect()->route('reset-password');
    }

    public function showResetForm(): Response|RedirectResponse
    {
        if (!session('otp_verified') || !session('reset_phone')) {
            return redirect()->route('forgot-password');
        }

        return Inertia::render('Auth/ResetPassword');
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $phone = session('reset_phone');

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'حدث خطأ. الرجاء المحاولة مرة أخرى.');
        }

        $user->update([
            'password' => $request->validated('password'),
        ]);

        OtpToken::where('phone', $phone)->delete();
        session()->forget(['otp_verified', 'reset_phone']);

        return redirect()->route('login')->with('success', 'تم تغيير كلمة المرور بنجاح. الرجاء تسجيل الدخول.');
    }
}
