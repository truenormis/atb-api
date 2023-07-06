<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'url' => route('categories.store', ['category' => $category->id]),
            ];
        })->all();
    }
}
