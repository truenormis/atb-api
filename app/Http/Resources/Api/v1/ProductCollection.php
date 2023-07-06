<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($product) {
            return [
                'id' => $product->id,
                'title' => $product->title,
                'url' => route('products.store', ['product' => $product]),
                'description' => $product->description,
                'status' => $product->status,
                'code' => $product->code,
                'latest_prices' => $product->latest_price,
                'discount_prices' => $product->latest_discount_price,

                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'subcategory' => new SubcategoryResource($product->subcategory),

                'options' => OptionResource::collection($product->options),
                'image' => new ImageResource($product->image),


            ];
        })->all();
    }
}
