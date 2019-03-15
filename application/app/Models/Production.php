<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'spk_id',
        'name',     
        'division_id',
        'source',
        'deadline',
        'quantity',
        'hm',
        'hj', 
        'free',
        'profitable',
        'detail',
        'count_finish',
        'created_at',
        'updated_at',  
    ];

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

    public function spk()
    {
        return $this->belongsTo('App\Models\Spk', 'spk_id');
    }

    public function divisions()
    {
        return $this->belongsTo('App\Models\Division', 'division_id');
    }

    public function getDeadlineReadableAttribute()
    {
        return ($this->deadline ? date('d-m-Y H:i:s', strtotime($this->deadline)) : '');
    }

    public function setDeadlineAttribute($value)
    {
        $this->attributes['deadline'] = date('Y-m-d', strtotime($value));
    }

    public function setSourceAttribute($value)
    {
        $this->attributes['source'] = strtoupper($value);
    }
}
