<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $appends = ['latest_price', 'latest_discount_price'];


    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(Image::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function getLatestPriceAttribute()
    {
        return $this->prices()->latest()->first()->price ?? null;
    }

    public function getLatestDiscountPriceAttribute()
    {
        return $this->prices()->latest()->first()->discount_price ?? null;
    }


}
