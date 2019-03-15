<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{

	use SoftDeletes;

	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'short_name',     
        'phone' ,  
        'fax',
        'created_at',
        'updated_at',  
    ];

    
    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function pic()
    {
    	return $this->hasMany('App\Models\Pic', 'company_id');
    }

    public function brands()
    {
    	return $this->hasMany('App\Models\Brand', 'company_id');
    }

    public function addresses()
    {
    	return $this->hasMany('App\Models\Address', 'company_id');
    }

    public function spk(){
        return $this->hasMany('App\Models\Spk', 'company_id');
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = phone_number_format($value);
    }

    public function setFaxAttribute($value)
    {
        $this->attributes['fax'] = phone_number_format($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setShortNameAttribute($value)
    {
        $this->attributes['short_name'] = strtoupper($value);
    }

    public function scopeWithStatisticSpk($query, $where = null, $alias = 'statistic_spk')
    {
        $sql_spk = '
        (
            SELECT 
                company_id,
                COUNT(statistic_production.spk_id) AS count_spk,
                SUM(statistic_production.total_hm) AS total_hm,
                SUM(statistic_production.total_he) AS total_he,
                SUM(statistic_production.total_hj) AS total_hj,
                SUM(statistic_production.profit) AS total_profit,
                SUM(statistic_production.total_loss) AS total_loss,
                SUM(statistic_production.total_ppn) AS total_ppn,
                SUM(statistic_production.count_production) AS count_production,
                SUM(statistic_production.count_production_finish) AS count_production_finish,
                SUM(statistic_production.sum_quantity_production) AS sum_quantity_production,
                SUM(statistic_production.sum_quantity_production_finish) AS sum_quantity_production_finish
            FROM spk
            LEFT JOIN
            (
                SELECT
                    statistic_production.spk_id,
                    (@totalHM := statistic_production.total_hm) as total_hm,
                    (@totalHE := statistic_production.total_he) as total_he,
                    (@totalHJ := statistic_production.total_hj) as total_hj,
                    @profit := (CASE WHEN @totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                    @percent := (@profit / (CASE WHEN @totalHE > 0 THEN @totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                    (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS total_real_omset,
                    (@totalHJ - @realOmset) as total_loss,
                    statistic_production.total_ppn,
                    statistic_production.count_production,
                    statistic_production.count_production_finish,
                    statistic_production.sum_quantity_production,
                    statistic_production.sum_quantity_production_finish,
                    statistic_production.datetime_finish
                FROM
                (
                    /* spk -> production */
                    SELECT
                        spk.id AS spk_id,
                        spk.name,
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
                        WHERE productions.datetime_finish IS NOT NULL AND productions.quantity > 0 AND productions.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
                        GROUP BY spk.id
                    ) finish ON finish.spk_id = spk.id
                    WHERE productions.quantity > 0 AND productions.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
                    GROUP BY spk.id
                ) statistic_production
            ) AS statistic_production ON statistic_production.spk_id = spk.id

            WHERE spk.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
            GROUP BY spk.company_id
        ) AS ' . $alias;


        return $query->leftJoin(DB::raw($sql_spk), $alias.'.company_id', 'companies.id');
    }

    public function scopeWithStatisticOffer($query, $where = null, $alias = 'statistic_offer')
    {
        $sql_offer = '
        (
            SELECT 
                `offers`.`company_id`,
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
            GROUP BY `offers`.`company_id`
        ) AS '. $alias;

        return $query->leftJoin(DB::raw($sql_offer), $alias.'.company_id', 'companies.id');
    }
}
