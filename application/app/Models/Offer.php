<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class Offer extends Model
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function sales()
    {
    	return $this->belongsTo('App\User', 'sales_id');
    }

    public function divisions()
    {
    	return $this->belongsTo('App\Models\Division', 'division_id');
    }

    public function companies()
    {
    	return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function brands()
    {
    	return $this->belongsTo('App\Models\Brand', 'brand_id');
    }

    public function pic()
    {
    	return $this->belongsTo('App\Models\Pic', 'pic_id');
    }

    public function offer_details()
    {
    	return $this->hasMany('App\Models\OfferDetail', 'offer_id');
    }

    public function getDateOfferReadableAttribute()
    {
    	return date('d-m-Y', strtotime($this->date_offer));
    }

    public function setDateOfferAttribute($value)
    {
    	$this->attributes['date_offer'] = date('Y-m-d', strtotime($value));
    }

    public function setPpnAttribute($value)
    {
        $this->attributes['ppn'] = isset($value) ? 10 : 0;
    }

    
}
