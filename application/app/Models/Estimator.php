<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class Estimator extends Model
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

    public function estimator_details()
    {
    	return $this->hasMany('App\Models\EstimatorDetail', 'estimator_id');
    }

    public function sales()
    {
    	return $this->belongsTo('App\User', 'sales_id');
    }

    public function user_estimator()
    {
    	return $this->belongsTo('App\User', 'user_estimator_id');
    }

    public function getCreatedAtReadableAttribute()
    {
        return date('d-m-Y H:i:s', strtotime($this->created_at));
    }

    public function scopeWithStatisticDetail($query)
    {

        $sql_estimator = '(
        	SELECT estimator_id, SUM(estimator_details.value) AS sum_value, COUNT(estimator_details.id) AS count_created, MIN(estimator_details.created_at) AS datetime_estimator
        	FROM estimator_details
        	WHERE estimator_details.deleted_at IS NULL
        	GROUP BY estimator_id
        ) AS total_estimator';

        $sql_less_than = '(
            SELECT estimator_details.estimator_id, SUM(estimator_details.value) AS less_than_24_sum_value, COUNT(estimator_details.estimator_id) AS less_than_24_count_created
            FROM estimator_details JOIN estimators 
            	ON estimator_details.estimator_id = estimators.id
            WHERE (TIMESTAMPDIFF(HOUR, estimator_details.created_at, estimators.updated_at)) <= 24 AND estimator_details.deleted_at IS NULL
            GROUP BY estimator_id
        ) AS estimator_less_than_24';

        $sql_more_than = '(
            SELECT estimator_details.estimator_id, SUM(estimator_details.value) AS more_than_24_sum_value, COUNT(estimator_details.estimator_id) AS more_than_24_count_created
            FROM estimator_details JOIN estimators 
            	ON estimator_details.estimator_id = estimators.id
            WHERE (TIMESTAMPDIFF(HOUR, estimator_details.created_at, estimators.updated_at)) > 24 AND estimator_details.deleted_at IS NULL
            GROUP BY estimator_id
        ) AS estimator_more_than_24';

        return $query->leftJoin(DB::raw($sql_estimator), 'total_estimator.estimator_id', 'estimators.id')
            ->leftJoin(DB::raw($sql_less_than), 'estimator_less_than_24.estimator_id', 'estimators.id')
            ->leftJoin(DB::raw($sql_more_than), 'estimator_more_than_24.estimator_id', 'estimators.id');
    }
}
