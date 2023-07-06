<?php
namespace App\Http\Filters\Api\v1;

use Illuminate\Database\Eloquent\Builder;
class PriceRangeFilter implements \Spatie\QueryBuilder\Filters\Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        // Parse the value as an array of two numbers
        $value = explode(':', $value);

        // Validate the value
        if (count($value) != 2) {
            return $query;
        }

        // Cast the value to integers
        $min = (int) $value[0];
        $max = (int) $value[1];

        // Join the prices table and select the latest price for each product
        $query->join('prices as p2', function ($join) {
            $join->on('products.id', '=', 'p2.product_id')
                ->whereRaw('p2.created_at = (select max(created_at) from prices where product_id = products.id)');
        });

        // Filter by the price column in the prices table
        return $query->whereBetween('p2.price', [$min, $max]);
    }
}
