<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Constant\Auth\AuthConstant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AuthUserLoginRequest;
use App\Http\Requests\AuthUserRegisterRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct(
        private readonly AuthConstant $authConstant,
        private readonly ApiResponse $apiResponse
    ) {}

    /**
     * Register a new user.
     *
     * @param AuthUserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(AuthUserRegisterRequest $request): JsonResponse
    {   
        try {

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? $this->authConstant::ROLE_ADMIN
            ];
    
            User::create($userData);
    
            return $this->apiResponse->success(
                __('messages.SS001', ['attribute' => 'User'])
            );
        
        } catch(\Throwable $th) {

            $this->LogTheError($th);

            return $this->error(
                __('messages.SE010'), 
                []
            );
        }
    }

    /**
     * Authenticate user and generate token.
     *
     * @param AuthUserLoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(AuthUserLoginRequest $request): JsonResponse
    {
        try {

            $credentials = $request->only("email", "password");

            /** @var \App\Models\User $user */
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->badRequest(__('messages.SE004'));
            }

            if (Auth::attempt($credentials)) {

                # If user is active and credentials are true, create token and respone
                $user = Auth::user();

                $token = $user->createToken('auth_token')->plainTextToken;

                $data = [
                    "user" => Auth::user(),
                    "token" => $token,
                ];

                return $this->success(__("messages.SS004"), $data);

            } else { # wrong credentials

                return $this->badRequest(__("messages.SE008"));
            }

        } catch (\Throwable $th) {

            $this->LogTheError($th);

            return $this->error(__("messages.SE010"));
        }
        
    }

    /**
     * Logout the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {

        try {

            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $request->user()->currentAccessToken();

            $token->delete();

            return  $this->success(__("messages.SE004", ['attribute' => 'Login User']));

        } catch (\Throwable $th) {
            $this->LogTheError($th);

            return $this->error(__("messages.SE010"));
        }
        
    }

    /**
     * Get authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Log the error.
     *
     * @param \Throwable $th
     * @return void
     */     
    private function LogTheError(\Throwable $th)
    {
        Log::error($th->getMessage().'in file '. __FILE__.'at line '. __LINE__.'withing class '. get_class());
    }
}
