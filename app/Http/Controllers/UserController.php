<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\User\UserRepositoryInterface;
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
        try {

            $user_id = $request->query('user_id');

            User::where('id', $user_id)
                ->where('email_verified_at', NULL)
                ->update(['email_verified_at' => Carbon::now()]);

            return redirect()->away(config('default.front_end_url') . '/sign-in');

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }
}
