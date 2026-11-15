<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['geonames_id', 'name', 'country', 'country_code', 'lat', 'lon', 'timezone'];

    public function aliases(): HasMany
    {
        return $this->hasMany(CityAlias::class);
    }
}
