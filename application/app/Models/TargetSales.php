<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetSales extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'target_sales';

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }


    public function targets()
    {
    	return $this->belongsTo('App\Models\Target', 'target_id');
    }

    public function sales()
    {
    	return $this->belongsTo('App\User', 'sales_id');
    }

}
