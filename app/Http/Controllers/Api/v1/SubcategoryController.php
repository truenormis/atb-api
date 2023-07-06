<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\v1\SubcategoryResource;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function store(Subcategory $subcategory){
        return new SubcategoryResource($subcategory);

    }
}
