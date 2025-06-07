<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Helper;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Constant\Auth\AuthConstant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AuthUserLoginRequest;
use App\Http\Requests\AuthUserRegisterRequest;

class AuthController extends Controller
{
    use Helper, ApiResponse;
    
    public function __construct(
        private readonly AuthConstant $authConstant,
    ) {}

    public function register(AuthUserRegisterRequest $request): JsonResponse
    {   
        try {

            $userData = [
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role" => $request->role ?? $this->authConstant::ROLE_ADMIN
            ];
    
            User::create($userData);
    
            return $this->success(
                __("messages.SS001", ["attribute" => "User"])
            );
        
        } catch(\Throwable $th) {

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
            $user = User::where("email", $request->email)->first();

            if (!$user) {
                return $this->badRequest(__("messages.SE004"));
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

            return  $this->success(__("messages.SE004", ["attribute" => "Login User"]));

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
