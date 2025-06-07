<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Helpers\Helper;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\VolumeRegisterRequest;

class VolumeController extends Controller
{   
    use Helper, ApiResponse;
    
    public function store(VolumeRegisterRequest $request, Novel $novel): JsonResponse
    {
        try {

            DB::beginTransaction();

            $nov = [
                "title" => $request->title,
                "order" => $request->order ?? 1
            ];

            $novel->volumes()->create($nov);

            DB::commit();

            return $this->success(__("messages.SS001", ["attribute" => "Volume"]));

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
        
    }
}
