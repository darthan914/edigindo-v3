<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class OfferDetail extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

	public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

	public function offers()
    {
    	return $this->belongsTo('App\Models\Offer', 'offer_id');
    }
}
