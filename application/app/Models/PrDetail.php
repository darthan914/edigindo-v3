<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class PrDetail extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function pr()
    {
    	return $this->belongsTo('App\Models\Pr', 'pr_id');
    }

    public function purchasing()
    {
    	return $this->belongsTo('App\User', 'purchasing_id');
    }

    public function po()
    {
    	return $this->hasMany('App\Models\User', 'pr_detail_id');
    }

    public function setDatetimeRequestAttribute($value)
    {
    	$this->attributes['datetime_request'] = date('Y-m-d H:i:s', strtotime($value));
    }

    public function setDatetimeConfirmAttribute($value)
    {
    	$this->attributes['datetime_confirm'] = date('Y-m-d H:i:s', strtotime($value));
    }
}
