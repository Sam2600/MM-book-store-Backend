<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Helper;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Constants\Auth\AuthConstant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\AuthUserLoginRequest;
use App\Http\Requests\AuthUserRegisterRequest;
use App\Mail\UserRegisterMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use Helper, ApiResponse;

    public function register(AuthUserRegisterRequest $request): JsonResponse
    {
        try {

            DB::beginTransaction();

            $rawToken = Str::random(64);

            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role_id" => $request->role_id ?? AuthConstant::ROLE_NORMAL_USER,
                "email_verification_token" => hash('sha256', $rawToken),
                "email_verification_token_expires_at" => now()->addHours(24),
            ]);

            $mailData = [
                'user_name' => $user->name,
                'verification_url' => config('app.url') . '/api/users/activate?token=' . $rawToken,
            ];

            Mail::to($request->email)->send(new UserRegisterMail($mailData));

            DB::commit();

            return $this->success(
                __("messages.SS001", ["attribute" => "User"])
            );

        } catch(\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(
                __("messages.SE010"),
                []
            );
        }
    }

    public function login(AuthUserLoginRequest $request): JsonResponse
    {
        try {

            /** @var \Illuminate\Http\Request $request */
            $credentials = $request->only("email", "password");

            /** @var \App\Models\User $user */
            $user = User::where("email", $request->email)->where("email_verified_at", "<>", null)->first();

            if (!$user) {
                return $this->badRequest(__("messages.SE004", ["attribute" => "Login User"]));
            }

            if (Auth::attempt($credentials)) {

                # If user is active and credentials are true, create token and respone
                $user = Auth::user();

                $token = $user->createToken("auth_token")->plainTextToken;

                $data = [
                    "user" => Auth::user(),
                    "token" => $token,
                ];

                return $this->success(__("messages.SS004"), $data);

            } else { # wrong credentials

                return $this->badRequest(__("messages.SE008"));
            }

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"));
        }
        
    }

    public function logout(Request $request): JsonResponse
    {
        try {

            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $request->user()->currentAccessToken();

            $token->delete();

            return  $this->success(__("messages.SS005"));

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"));
        }
        
    }

    public function profile(Request $request): JsonResponse
    {
        try {

            return $this->success(__("messages.SS008"), $request->user());

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"));
        }
    }
}
