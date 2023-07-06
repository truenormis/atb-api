<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\v1\CategoryCollection;
use App\Http\Resources\Api\v1\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::all();
        return new CategoryCollection($categories);
    }
    public function store(Category $category){
        return new CategoryResource($category);
    }
}

