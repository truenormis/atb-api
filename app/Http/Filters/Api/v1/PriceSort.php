<?php
namespace App\Http\Filters\Api\v1;

use Illuminate\Database\Eloquent\Builder;
class PriceSort implements \Spatie\QueryBuilder\Sorts\Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        // Join the prices table and select the latest price for each product
        $query->join('prices as p1', function ($join) {
            $join->on('products.id', '=', 'p1.product_id')
                ->whereRaw('p1.created_at = (select max(created_at) from prices where product_id = products.id)');
        });

        // Sort by the price column in the prices table
        return $query->orderBy('p1.price', $descending ? 'desc' : 'asc');
    }
}
