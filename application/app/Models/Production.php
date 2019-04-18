<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

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

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('select', function (Builder $builder) {
            $builder->select('*')->addSelect(DB::raw('(hm * quantity) as total_hm'))
                ->addSelect(DB::raw('(he * quantity) as total_he'))
                ->addSelect(DB::raw('(hj * quantity) as total_hj'));
        });
    }

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
