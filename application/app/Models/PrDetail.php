<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class PrDetail extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function pr()
    {
    	return $this->belongsTo('App\Models\Pr', 'pr_id');
    }

    public function purchasing()
    {
    	return $this->belongsTo('App\User', 'purchasing_id');
    }

    public function po()
    {
    	return $this->hasMany('App\Models\Po', 'pr_detail_id');
    }

    public function setDatetimeRequestAttribute($value)
    {
    	$this->attributes['datetime_request'] = date('Y-m-d H:i:s', strtotime($value));
    }

    public function setDatetimeConfirmAttribute($value)
    {
    	$this->attributes['datetime_confirm'] = date('Y-m-d H:i:s', strtotime($value));
    }

    public function scopeWithStatisticPo($query)
    {

        $sql_po = '
            (
                SELECT
                    po.pr_detail_id,
                    COUNT(po.id) AS count_po
                FROM
                    po
                GROUP BY po.pr_detail_id
            ) statictic_po
        ';

        $sql_po_check_audit = '
            (
                SELECT
                    po.pr_detail_id,
                    COUNT(po.id) AS count_po_check_audit
                FROM
                    po
                WHERE
                    check_audit = 1
                GROUP BY po.pr_detail_id
            ) statictic_po_check_audit
        ';

        $sql_po_check_finance = '
            (
                SELECT
                    po.pr_detail_id,
                    COUNT(po.id) AS count_po_check_finance
                FROM
                    po
                WHERE
                    check_finance = 1
                GROUP BY po.pr_detail_id
            ) statictic_po_check_financ
        ';

        return $query->leftJoin(DB::raw($sql_po), 'statictic_po.pr_detail_id', '=', 'pr_details.id')
            ->leftJoin(DB::raw($sql_po_check_audit), 'statictic_po_check_audit.pr_detail_id', '=', 'pr_details.id')
            ->leftJoin(DB::raw($sql_po_check_finance), 'statictic_po_check_financ.pr_detail_id', '=', 'pr_details.id');
    }
}
