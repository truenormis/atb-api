<?php

namespace App\Http\Filters\Api\v1;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class CountryFilter implements Filter
{

    public function __invoke(Builder $query, $value, string $property)
    {
        $query->whereHas('options', function (Builder $query) use ($value) {
            $query->where('name', 'Країна');
            $query->where('value', $value);
        });
    }
}
