<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\User\UserRepositoryInterface;

class UserController extends Controller
{
    use ApiResponse, Helper;

    public function __construct(
        private UserRepositoryInterface $userI
    ){}

    public function getAuthorInfoAndNovels(): JsonResponse
    {
        try {

            $user = Auth::user();

            $novels = $this->userI->getNovelDetailsByAuthor($user->id);

            $data = null;

            if($novels->count() > 0) {

                $data = $novels->map(function ($novel){
                    $novel->cover_image = $this->getImageWithDBpath($novel->cover_image);
                    return $novel;
                });
            }

            $data->author_name = $user->name;
            $data->user_joined_date = $user->created_at;

            return $this->success(__("messages.SS008"), $data);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }
}
