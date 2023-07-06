<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Http\Filters\Api\v1\CountryFilter;
use App\Http\Filters\Api\v1\TrademarkFilter;
use App\Http\Resources\Api\v1\ProductCollection;
use App\Http\Resources\Api\v1\ProductResource;
use App\Models\Product;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index(){

        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::custom('trademark', new TrademarkFilter),
                AllowedFilter::custom('country', new CountryFilter()),
                ])
            ->get();
        return new ProductCollection($products);

    }

    public function store(Product $product){
        return new ProductResource($product);
    }
}
