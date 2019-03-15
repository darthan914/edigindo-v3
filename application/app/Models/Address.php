<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'company_id',
        'address',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',  
    ];

    
    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function companies()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }
}
