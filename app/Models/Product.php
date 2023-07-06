<?php

namespace App\Models;

use App\Console\Commands\Update\OptionsCommand;
use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = false;
    protected $appends = ['latest_price','latest_discount_price'];


    public function subcategory(){
        return $this->belongsTo(Subcategory::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public  function image(){
        return $this->hasOne(Image::class);
    }

    public  function prices(){
        return $this->hasMany(Price::class);
    }

    public function options()
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
