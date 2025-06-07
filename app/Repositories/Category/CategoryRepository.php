<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Interfaces\Category\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
   public function getCategories()
   {
      return Category::select('id', 'name')->get();
   }
}
