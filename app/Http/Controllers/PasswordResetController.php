<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\Helper;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    use Helper, ApiResponse;

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->whereNotNull('email_verified_at')
            ->first();

        // Always return the same message — do not reveal whether email exists
        if (!$user) {
            return $this->success(__('messages.SS011'));
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $rawToken = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => hash('sha256', $rawToken),
            'created_at' => now(),
        ]);

        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000')
            . '/reset-password?token=' . $rawToken
            . '&email=' . urlencode($request->email);

        Mail::to($request->email)->send(new PasswordResetMail([
            'user_name' => $user->name,
            'reset_url' => $resetUrl,
        ]));

        return $this->success(__('messages.SS011'));
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:5|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $request->token))) {
            return $this->badRequest(__('messages.SE021'));
        }

        if (Carbon::parse($record->created_at)->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->badRequest(__('messages.SE022'));
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->badRequest(__('messages.SE004', ['attribute' => 'User']));
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $user->tokens()->delete();

        return $this->success(__('messages.SS012'));
    }
}
