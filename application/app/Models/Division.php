<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{	
	use SoftDeletes;

	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function users()
    {
        return $this->hasMany('App\User', 'division_id');
    }

    public function spk(){
        return $this->hasMany('App\Models\Spk', 'main_division_id');
    }
}
