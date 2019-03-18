<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

class Spk extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_id',
        'name',     
        'no_spk' ,  
        'main_division_id',
        'date_spk',
        'company_id',
        'brand_id',
        'pic_id',
        'address',
        'latitude', 
        'longitude',
        'additional_phone',
        'ppn',
        'do_transaction',
        'note',
        'created_at',
        'updated_at',  
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    protected $table = 'spk';

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
        return $this->belongsTo('App\Models\Division', 'main_division_id');
    }

    public function companies()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function brands()
    {
        return $this->belongsTo('App\Models\Brand', 'company_id');
    }

    public function pic()
    {
        return $this->belongsTo('App\Models\Pic', 'company_id');
    }

    public function productions()
    {
        return $this->hasMany('App\Models\Production', 'spk_id');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice', 'spk_id');
    }

    public function getDatetimeConfirmReadableAttribute()
    {
        return ($this->datetime_confirm ? date('d-m-Y H:i:s', strtotime($this->datetime_confirm)) : '');
    }

    public function getFinishSpkAtReadableAttribute()
    {
        return ($this->finish_spk_at ? date('d-m-Y H:i:s', strtotime($this->finish_spk_at)) : '');
    }

    public function getDateSpkReadableAttribute()
    {
        return ($this->date_spk ? date('d-m-Y', strtotime($this->date_spk)) : '');
    }

    public function setDateSpkAttribute($value)
    {
        $this->attributes['date_spk'] = date('Y-m-d', strtotime($value));
    }

    public function setPpnAttribute($value)
    {
        $this->attributes['ppn'] = isset($value) ? 10 : 0;
    }

    public function setDoTransactionAttribute($value)
    {
        $this->attributes['do_transaction'] = isset($value) ? 1 : 0;
    }

    public function setDatetimeConfirmAttribute($value)
    {
        $this->attributes['datetime_confirm'] = isset($value) ? date('Y-m-d H:i:s') : null;
    }

    public function scopeWithStatisticProduction($query, $where = null, $alias = 'statistic_production')
    {

        $sql_production = '
            (
                SELECT
                    productions_with_statisic.spk_id,
                    (@totalHM := productions_with_statisic.total_hm) as total_hm,
                    (@totalHE := productions_with_statisic.total_he) as total_he,
                    (@totalHJ := productions_with_statisic.total_hj) as total_hj,
                    @profit := (CASE WHEN @totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                    @percent := (@profit / (CASE WHEN @totalHE > 0 THEN @totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                    (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS total_real_omset,
                    (@totalHJ - @realOmset) as total_loss,
                    productions_with_statisic.total_ppn,
                    productions_with_statisic.count_production,
                    productions_with_statisic.count_production_finish,
                    productions_with_statisic.sum_quantity_production,
                    productions_with_statisic.sum_quantity_production_finish,
                    productions_with_statisic.datetime_finish
                FROM
                (
                    SELECT
                        spk.id AS spk_id,
                        spk.sales_id, spk.name,
                        SUM(productions.hm * productions.quantity) AS total_hm,
                        SUM(productions.he * productions.quantity) AS total_he,
                        SUM(productions.hj * productions.quantity) AS total_hj,
                        (SUM(productions.hj * productions.quantity) * (1+(spk.ppn/100))) AS total_ppn,
                        COUNT(productions.id) AS count_production,
                        COALESCE(finish.count_production_finish, 0) AS count_production_finish,
                        SUM(productions.quantity) AS sum_quantity_production,
                        SUM(productions.count_finish) AS sum_quantity_production_finish,
                        MAX(productions.datetime_finish) AS datetime_finish

                    FROM spk join productions ON spk.id = productions.spk_id
                    LEFT JOIN (
                        SELECT
                            spk.id AS spk_id,
                            COUNT(productions.id) as count_production_finish

                        FROM spk join productions ON spk.id = productions.spk_id
                        WHERE productions.datetime_finish IS NOT NULL AND productions.quantity > 0 AND productions.deleted_at IS NULL
                        GROUP BY spk.id
                    ) finish ON finish.spk_id = spk.id
                    WHERE productions.quantity > 0 AND productions.deleted_at IS NULL
                    GROUP BY spk.id
                ) productions_with_statisic
            ) '.$alias.'
        ';

        return $query->leftJoin(DB::raw($sql_production), $alias.'.spk_id', '=', 'spk.id');
    }

    public function scopeWithStatisticInvoice($query, $where = null, $alias = 'statistic_invoice')
    {

        $sql_invoice = '
            (
                SELECT
                    invoices.spk_id,
                    COUNT(invoices.id) AS count_invoice,
                    SUM(invoices.value_invoice) AS sum_value_invoice
                FROM
                    invoices
                WHERE
                    invoices.deleted_at IS NULL
                GROUP BY
                    invoices.spk_id
            ) '.$alias.'
        ';

        $sql_invoice_complete = '
            (
                SELECT
                    invoices.spk_id,
                    COUNT(invoices.id) AS count_invoice_complete
                FROM
                    invoices
                WHERE
                    invoices.deleted_at IS NULL AND invoices.datetime_add_complete IS NOT NULL
                GROUP BY
                    invoices.spk_id
            ) '.$alias.'_complete
        ';

        return $query->leftJoin(DB::raw($sql_invoice), $alias.'.spk_id', '=', 'spk.id')->leftJoin(DB::raw($sql_invoice_complete), $alias.'_complete.spk_id', '=', 'spk.id');
    }

    public function scopeWithStatisticPr($query)
    {
        $sql_pr = '
            (
                SELECT
                    pr.spk_id,
                    COUNT(pr.id) AS count_pr
                FROM
                    pr
                JOIN
                    pr_details ON pr_details.pr_id = pr.id AND pr_details.deleted_at IS NULL
                WHERE
                    pr.deleted_at IS NULL AND pr.spk_id IS NOT NULL
                GROUP BY
                    pr.spk_id
            ) statistic_pr
        ';

        $sql_po = '
            (
                SELECT
                    pr.spk_id,
                    SUM(po.value) AS sum_value_pr
                FROM
                    pr
                JOIN
                    pr_details ON pr_details.pr_id = pr.id AND pr_details.deleted_at IS NULL
                JOIN
                    po ON po.pr_detail_id = pr_details.id AND po.deleted_at IS NULL
                WHERE
                    pr.deleted_at IS NULL AND pr.spk_id IS NOT NULL
                GROUP BY
                    pr.spk_id
            ) statistic_po
        ';

        return $query->leftJoin(DB::raw($sql_pr), 'statistic_pr.spk_id', '=', 'spk.id')->leftJoin(DB::raw($sql_po), 'statistic_po.spk_id', '=', 'spk.id');
    }

    public function scopeWithStatisticOffer($query, $where = null, $alias = 'statistic_offer')
    {
        $sql_offer = '
        (
            SELECT 
                `offers`.`sales_id`,
                SUM(`offers`.`total_price`) + SUM(`offer_all`.`sum_value`) AS `total_offer`,

                SUM(`offers`.`total_price`) AS `offer_expo_sum_value`,

                SUM(`offer_all`.`count_offer`) AS `offer_all_count`,
                SUM(`offer_all`.`sum_value`) AS `offer_all_sum_value`,

                SUM(`offer_waiting`.`count_offer`) AS `offer_waiting_count`,
                SUM(`offer_waiting`.`sum_value`) AS `offer_waiting_sum_value`,

                SUM(`offer_success`.`count_offer`) AS `offer_success_count`,
                SUM(`offer_success`.`sum_value`) AS `offer_success_sum_value`,

                SUM(`offer_cancel`.`count_offer`) AS `offer_cancel_count`,
                SUM(`offer_cancel`.`sum_value`) AS `offer_cancel_sum_value`,

                SUM(`offer_failed`.`count_offer`) AS `offer_failed_count`,
                SUM(`offer_failed`.`sum_value`) AS `offer_failed_sum_value`,

                SUM(`offer_failed_pricing`.`count_offer`) AS `offer_failed_pricing_count`,
                SUM(`offer_failed_pricing`.`sum_value`) AS `offer_failed_pricing_sum_value`,

                SUM(`offer_failed_timeline`.`count_offer`) AS `offer_failed_timeline_count`,
                SUM(`offer_failed_timeline`.`sum_value`) AS `offer_failed_timeline_sum_value`,

                SUM(`offer_failed_other`.`count_offer`) AS `offer_failed_other_count`,
                SUM(`offer_failed_other`.`sum_value`) AS `offer_failed_other_sum_value`
            FROM `offers`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL
                GROUP BY `offer_id`
            ) AS `offer_all` ON `offer_all`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "WAITING"
                GROUP BY `offer_id`
            ) AS `offer_waiting` ON `offer_waiting`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "SUCCESS"
                GROUP BY `offer_id`
            ) AS `offer_success` ON `offer_success`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "CANCEL"
                GROUP BY `offer_id`
            ) AS `offer_cancel` ON `offer_cancel`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "FAILED"
                GROUP BY `offer_id`
            ) AS `offer_failed` ON `offer_failed`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "FAILED" AND `reason` = "PRICING"
                GROUP BY `offer_id`
            ) AS `offer_failed_pricing` ON `offer_failed_pricing`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "FAILED" AND `reason` = "TIMELINE"
                GROUP BY `offer_id`
            ) AS `offer_failed_timeline` ON `offer_failed_timeline`.`offer_id` = `offers`.`id`
            LEFT JOIN
            (
                SELECT `offer_id`, COUNT(`offer_id`) AS `count_offer`, SUM(`value` * `quantity`) AS `sum_value`
                FROM `offer_details`
                WHERE `deleted_at` IS NULL AND `status` = "FAILED" AND `reason` = "OTHER"
                GROUP BY `offer_id`
            ) AS `offer_failed_other` ON `offer_failed_other`.`offer_id` = `offers`.`id`
            WHERE `offers`.`deleted_at` IS NULL AND '.(isset($where) ? $where : '1').'
            GROUP BY `offers`.`sales_id`
        ) AS '. $alias;

        return $query->leftJoin(DB::raw($sql_offer), $alias.'.sales_id', 'spk.sales_id');
    }

    public function scopeWithStatisticSpkYearly($query, $year, $alias = 'statistic_offer')
    {
        $query->withStatisticProduction()->withStatisticInvoice()->withStatisticPr()->whereYear('spk.spk_date', $year);
    }

}
