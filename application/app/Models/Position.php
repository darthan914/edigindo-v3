<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
	use NodeTrait, SoftDeletes;

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
        return $this->hasMany('App\User', 'position_id');
    }
}
