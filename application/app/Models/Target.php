<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Target extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function target_sales()
    {
    	return $this->hasMany('App\Models\TargetSales', 'target_id');
    }

}
