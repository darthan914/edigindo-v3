<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class Po extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'po';

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function pr_details()
    {
    	return $this->belongsTo('App\Models\PrDetail', 'pr_detail_id');
    }

    public function setDatetimePoAttribute($value)
    {
    	$this->attributes['datetime_po'] = date('Y-m-d H:i:s', strtotime($value));
    }

    public function setDatetimeReceivedAttribute($value)
    {
    	$this->attributes['datetime_received'] = date('Y-m-d H:i:s', strtotime($value));
    }
}
