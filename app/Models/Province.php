<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name'];

    public $incrementing = false;

    protected $keyType = 'string';

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'province_id');
    }
}
