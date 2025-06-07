<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Helpers\ApiResponse;
use App\Http\Requests\CategoryAssignRequest;
use App\Interfaces\Category\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(private CategoryRepositoryInterface $categoryI){}

    public function index(): JsonResponse
    {
        try {

            $categories = $this->categoryI->getCategories();

            return $this->success(__("messages.SS008"), $categories);
        
        } catch (\Throwable $th) {

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function assignCategories(CategoryAssignRequest $request, Novel $novel): JsonResponse
    {
        try {

            $novel->categories()->sync($request->category_ids);

            return $this->success(__("messages.SS009", ["attribute" => "Category"]), $novel->categories);
        
        } catch (\Throwable $th) {
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }
}
