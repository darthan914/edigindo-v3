<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class Pr extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'pr';

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function spk()
    {
    	return $this->belongsTo('App\Models\Spk', 'spk_id');
    }

    public function users()
    {
    	return $this->belongsTo('App\User', 'user_id');
    }

    public function divisions()
    {
        return $this->belongsTo('App\Models\Division', 'division_id');
    }

    public function pr_details()
    {
    	return $this->hasMany('App\Models\PrDetail', 'pr_id');
    }

    public function setDatetimeOrderAttribute($value)
    {
    	$this->attributes['datetime_order'] = date('Y-m-d H:i:s', strtotime($value));
    }

    public function setDeadlineAttribute($value)
    {
    	$this->attributes['deadline'] = date('Y-m-d H:i:s', strtotime($value));
    }
}
