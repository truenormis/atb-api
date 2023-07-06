<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => route('products.store', ['product' => $this]),
            'description' => $this->description,
            'status' => $this->status,
            'code' => $this->code,
            'prices' => PriceResource::collection($this->prices->sortByDesc('created_at')),

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ],
            'subcategory' => new SubcategoryResource($this->subcategory),

            'options' => OptionResource::collection($this->options),
            'image' => new ImageResource($this->image),


        ];
    }
}
