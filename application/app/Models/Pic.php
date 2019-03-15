<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pic extends Model
{
    use SoftDeletes;

	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    protected $table = 'pic';

    protected $fillable = [
        'company_id',
        'first_name',     
        'last_name' ,  
        'gender',
        'position',
        'phone',
        'email',
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

    public function spk(){
        return $this->hasMany('App\Models\Spk', 'pic_id');
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }

    public function setPositionAttribute($value)
    {
        $this->attributes['position'] = ucfirst($value);
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = phone_number_format($value);
    }

    public function getLongGenderAttribute()
    {
        if($this->gender == 'M')
        { 
            return 'Male';
        }
        else if($this->gender == 'F')
        {
            return 'Female';
        }
        else
        {
            return 'Male';
        }
    }

    public function getFullnameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNicknameAttribute()
    {
        return "{$this->first_name}";
    }
}
