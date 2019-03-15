<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $fillable = [
        'archivable_id',
        'archivable_type',
        'user_id',
        'action_data',
        'insert_data',
        'old_data',
        'created_at',
    ];
    
    public function archivable()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function setActionDataAttribute($value)
    {
        $this->attributes['action_data'] = strtoupper($value);
    }

    public function setInsertDataAttribute($value)
    {
        $this->attributes['insert_data'] = json_encode($value);
    }

    public function setOldDataAttribute($value)
    {
        $this->attributes['old_data'] = json_encode($value);
    }

    public function getInsertDataAttribute($value)
    {
        return json_decode($value);
    }

    public function getOldDataAttribute($value)
    {
        return json_decode($value);
    }
}
