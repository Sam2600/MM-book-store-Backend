<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateProfileRequest;
use App\Interfaces\User\UserRepositoryInterface;
use App\Mail\UserRegisterMail;
use App\Models\User;

class UserController extends Controller
{
    use ApiResponse, Helper;

    public function __construct(
        private UserRepositoryInterface $userI
    ){}

    public function getMyInfo(): JsonResponse
    {
        try {

            $user = Auth::user();

            $novels = $this->userI->getNovelDetailsByAuthor($user->id);

            $novelList = $novels->map(function ($novel) {
                $novel['cover_image'] = $this->getImageWithDBpath($novel['cover_image'] ?? "");
                return $novel;
            })->values();

            $data = [
                "user_id" => $user->id,
                "author_name" => $user->name,
                "user_joined_date" => $user->created_at,
                "novel_list" => $novelList,
            ];
            
            return $this->success(__("messages.SS008"), $data);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getAuthorInfoAndNovels(int|String $id): JsonResponse
    {
        try {

            $userInfo = $this->userI->getAuthorInfoAndNovels($id);

            if (empty($userInfo)) {
                return $this->error(__("messages.SE004"), ["attribute" => "User"]);
            }

            if($userInfo->novels->count() > 0) {

                foreach ($userInfo->novels as $novel) {
                    $novel->cover_image = $this->getImageWithDBpath($novel->cover_image ?? "");
                }

            }

            return $this->success(__("messages.SS008"), $userInfo);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function activate(Request $request)
    {
        $frontendUrl = config('default.front_end_url');
        $verifiedPage = $frontendUrl . '/email-verified';

        try {

            $rawToken = $request->query('token');

            if (!$rawToken) {
                return redirect()->away($verifiedPage . '?verified=invalid');
            }

            $hashedToken = hash('sha256', $rawToken);

            $user = User::where('email_verification_token', $hashedToken)
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                return redirect()->away($verifiedPage . '?verified=invalid');
            }

            if (now()->isAfter($user->email_verification_token_expires_at)) {
                return redirect()->away($verifiedPage . '?verified=expired');
            }

            $user->update([
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
            ]);

            return redirect()->away($verifiedPage . '?verified=1');

        } catch (\Throwable $th) {

            $this->logException($th);

            return redirect()->away($verifiedPage . '?verified=error');
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {

            $request->user()->update([
                'name'              => $request->name,
                'email'             => $request->email,
                'payment_method_id' => $request->payment_method_id,
                'payment_account'   => $request->payment_account,
            ]);

            return $this->success(__("messages.SS007", ["attribute" => "Profile"]), $request->user()->fresh()->load('paymentMethod'));

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function updatePaymentInfo(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'payment_method_id' => 'required|exists:payment_methods,id',
                'payment_account'   => 'required|string|max:100',
            ]);

            $request->user()->update([
                'payment_method_id' => $request->payment_method_id,
                'payment_account'   => $request->payment_account,
            ]);

            return $this->success(__("messages.SS007", ["attribute" => "Payment info"]), [
                'payment_method_id' => $request->payment_method_id,
                'payment_account'   => $request->payment_account,
            ]);

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function resendVerification(Request $request): JsonResponse
    {
        try {

            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                // Return success regardless to prevent email enumeration
                return $this->success(__("messages.SS006", ["attribute" => "Verification mail"]));
            }

            $rawToken = Str::random(64);

            $user->update([
                'email_verification_token' => hash('sha256', $rawToken),
                'email_verification_token_expires_at' => now()->addHours(24),
            ]);

            $mailData = [
                'user_name' => $user->name,
                'verification_url' => config('app.url') . '/api/users/activate?token=' . $rawToken,
            ];

            Mail::to($user->email)->send(new UserRegisterMail($mailData));

            return $this->success(__("messages.SS006", ["attribute" => "Verification mail"]));

        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }
}
