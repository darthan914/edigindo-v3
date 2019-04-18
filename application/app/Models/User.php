<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Session;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Laravel\Passport\HasApiTokens;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, NodeTrait, SoftDeletes;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',     
        'password' ,  
        'position_id',
        'division_id',
        'no_ae',
        'first_name',
        'last_name',
        'phone',
        'active', 
        'parent_id',
        'created_at',
        'updated_at',  
    ];




    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getFullnameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNicknameAttribute()
    {
        return "{$this->first_name}";
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = phone_number_format($value);
    }


    public function AauthAcessToken(){
        return $this->hasMany('App\Models\OauthAccessToken');
    }

    public function spk(){
        return $this->hasMany('App\Models\Spk', 'sales_id');
    }

    public function positions()
    {
        return $this->belongsTo('App\Models\Position', 'position_id');
    }

    public function divisions()
    {
        return $this->belongsTo('App\Models\Division', 'division_id');
    }

    public function leader()
    {
        return $this->belongsTo('App\User', 'parent_id');
    }

    public function archives()
    {
        return $this->morphMany('App\Models\Archive', 'archivable');
    }

    public function hasAccess(string $permission) : bool
    {
        $permission_arr = explode(', ', $this->positions->permission);
        $grant_arr      = explode(', ', $this->grant);
        $denied_arr     = explode(', ', $this->denied);

        return (in_array($permission, $permission_arr) || in_array($permission, $grant_arr)) && !in_array($permission, $denied_arr);
    }

    public function setImpersonating($id)
    {
        Session::put('impersonate', $id);
        Session::put('original', Auth::id());

        Auth::logout();
        Auth::loginUsingId($id);
    }

    public function stopImpersonating()
    {
        Auth::logout();
        Auth::loginUsingId(Session::get('original'));

        Session::forget('impersonate');
        Session::forget('original');
    }

    public function isImpersonating()
    {
        return Session::has('impersonate');
    }

    public function staff()
    {
        return User::whereBetween('_lft', [$this->_lft, $this->_rgt])->get()->map(function ($user){
            return $user->id;
        });
    }

    public function count_spk()
    {
        return $this->spk()->count();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%');
        });
    }

    public function scopeWithStatisticSpk($query, $where = null, $alias = 'statistic_spk')
    {
        $sql_spk = '
        (
            SELECT 
                spk.sales_id,
                COUNT(statistic_production.spk_id) AS count_spk,
                SUM(statistic_production.total_hm) AS total_hm,
                SUM(statistic_production.total_he) AS total_he,
                SUM(statistic_production.total_hj) AS total_hj,
                SUM(statistic_production.profit) AS total_profit,
                SUM(statistic_production.total_real_omset) AS total_real_omset,
                SUM(statistic_production.total_loss) AS total_loss,
                SUM(statistic_production.total_ppn) AS total_ppn,
                SUM(statistic_production.count_production) AS count_production,
                SUM(statistic_production.count_production_finish) AS count_production_finish,
                SUM(statistic_production.sum_quantity_production) AS sum_quantity_production,
                SUM(statistic_production.sum_quantity_production_finish) AS sum_quantity_production_finish,
                SUM(statistic_invoice.count_invoice) AS count_invoice,
                SUM(statistic_invoice.sum_value_invoice) AS sum_value_invoice,
                SUM(statistic_invoice_complete.count_invoice_complete) AS count_invoice_complete,
                SUM(statistic_pr.count_pr) AS count_pr,
                SUM(statistic_po.sum_value_pr) AS sum_value_pr
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
            LEFT JOIN
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
            ) AS statistic_invoice ON statistic_invoice.spk_id = spk.id
            LEFT JOIN
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
            ) statistic_invoice_complete ON statistic_invoice_complete.spk_id = spk.id
            LEFT JOIN
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
            ) statistic_pr ON statistic_pr.spk_id = spk.id
            LEFT JOIN
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
            ) statistic_po ON statistic_po.spk_id = spk.id

            WHERE spk.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
            GROUP BY spk.sales_id
        ) AS ' . $alias;


        return $query->leftJoin(DB::raw($sql_spk), $alias.'.sales_id', 'users.id');
    }

    public function scopeWithStatisticEstimator($query, $where = null, $alias = 'statistic_estimator')
    {

        $sql_estimator = '
        (
            SELECT 
                estimators.user_estimator_id, 
                SUM(total_estimator.sum_value) AS sum_value, 
                SUM(total_estimator.count_created) AS count_created, 
                SUM(estimator_less_than_24.less_than_24_sum_value) AS less_than_24_sum_value, 
                SUM(estimator_less_than_24.less_than_24_count_created) AS less_than_24_count_created, 
                SUM(estimator_more_than_24.more_than_24_sum_value) AS more_than_24_sum_value,
                SUM(estimator_more_than_24.more_than_24_count_created) AS more_than_24_count_created
            FROM estimators
            LEFT JOIN 
            (
                SELECT estimator_id, SUM(estimator_details.value) AS sum_value, COUNT(estimator_details.id) AS count_created
                FROM estimator_details
                WHERE estimator_details.deleted_at IS NULL
                GROUP BY estimator_id
            ) AS total_estimator ON total_estimator.estimator_id = estimators.id
            LEFT JOIN 
            (
                SELECT estimator_details.estimator_id, SUM(estimator_details.value) AS less_than_24_sum_value, COUNT(estimator_details.estimator_id) AS less_than_24_count_created
                FROM estimator_details JOIN estimators 
                    ON estimator_details.estimator_id = estimators.id
                WHERE (TIMESTAMPDIFF(HOUR, estimator_details.created_at, estimators.updated_at)) <= 24 AND estimator_details.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
                GROUP BY estimator_id
            ) AS estimator_less_than_24 ON estimator_less_than_24.estimator_id = estimators.id
            LEFT JOIN
            (
                SELECT estimator_details.estimator_id, SUM(estimator_details.value) AS more_than_24_sum_value, COUNT(estimator_details.estimator_id) AS more_than_24_count_created
                FROM estimator_details JOIN estimators 
                    ON estimator_details.estimator_id = estimators.id
                WHERE (TIMESTAMPDIFF(HOUR, estimator_details.created_at, estimators.updated_at)) > 24 AND estimator_details.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
                GROUP BY estimator_id
            ) AS estimator_more_than_24 ON estimator_more_than_24.estimator_id = estimators.id
            WHERE estimators.deleted_at IS NULL AND '.(isset($where) ? $where : '1').'
            GROUP BY estimators.user_estimator_id
        ) as '. $alias;

        return $query->leftJoin(DB::raw($sql_estimator), $alias.'.user_estimator_id', 'users.id');
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

        return $query->leftJoin(DB::raw($sql_offer), $alias.'.sales_id', 'users.id');
    }

    public static function keypermission()
    {
        $array = 
        [
            // accessUser
            [
                'name' => 'User', 'id' => 'user',
                'data' => 
                [
                    ['name' => 'Full Access', 'value' => 'full-user'],
                    ['name' => 'List', 'value' => 'list-user'],
                    ['name' => 'Create', 'value' => 'create-user'],
                    ['name' => 'Update', 'value' => 'update-user'],
                    ['name' => 'Delete', 'value' => 'delete-user'],
                    ['name' => 'Access', 'value' => 'access-user'],
                    ['name' => 'Impersonate', 'value' => 'impersonate-user'],
                ]
            ],

            // accessPosition
            [
                'name' => 'Position', 'id' => 'position',
                'data' => 
                [
                    ['name' => 'Full Access', 'value' => 'full-position'],
                    ['name' => 'List', 'value' => 'list-position'],
                    ['name' => 'Create', 'value' => 'create-position'],
                    ['name' => 'Update', 'value' => 'update-position'],
                    ['name' => 'Delete', 'value' => 'delete-position'],
                ]
            ],

            // accessPosition
            [
                'name' => 'Division', 'id' => 'division',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-division'],
                    ['name' => 'Create', 'value' => 'create-division'],
                    ['name' => 'Update', 'value' => 'update-division'],
                    ['name' => 'Delete', 'value' => 'delete-division'],
                ]
            ],

            // accessFile
            [
                'name' => 'File', 'id' => 'file',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-file'],
                    ['name' => 'Create', 'value' => 'create-file'],
                    ['name' => 'Update', 'value' => 'update-file'],
                    ['name' => 'Delete', 'value' => 'delete-file'],
                ]
            ],

            // accessFile
            [
                'name' => 'Tool', 'id' => 'tool',
                'data' => 
                [
                    ['name' => 'Label Package', 'value' => 'labelPackage-tool'],
                ]
            ],

            // accessDashboard
            [
                'name' => 'Dashboard', 'id' => 'dashboard',
                'data' => 
                [
                    ['name' => 'Sales', 'value' => 'sales-dashboard'],
                    ['name' => 'Income/Outcome', 'value' => 'incomeOutcome-dashboard'],
                ]
            ],

            // accessTarget
            [
                'name' => 'Target', 'id' => 'target',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-target'],
                    ['name' => 'Create', 'value' => 'create-target'],
                    ['name' => 'Update', 'value' => 'update-target'],
                    ['name' => 'Delete', 'value' => 'delete-target'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-target'],
                ],
            ],

            // accessCampaign
            [
                'name' => 'Campaign', 'id' => 'campaign',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-campaign'],
                    ['name' => 'Create', 'value' => 'create-campaign'],
                    ['name' => 'Update', 'value' => 'update-campaign'],
                    ['name' => 'Delete', 'value' => 'delete-campaign'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-campaign'],
                ],
            ],

            // accessCompany
            [
                'name' => 'Company', 'id' => 'company',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-company'],
                    ['name' => 'Create', 'value' => 'create-company'],
                    ['name' => 'Update', 'value' => 'update-company'],
                    ['name' => 'Delete', 'value' => 'delete-company'],
                    ['name' => 'Send', 'value' => 'send-company'],
                    ['name' => 'Lock', 'value' => 'lock-company'],
                    ['name' => 'Confirm', 'value' => 'confirm-company'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-company'],
                ]
            ],

            // accessSpk
            [
                'name' => 'SPK', 'id' => 'spk',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-spk'],
                    ['name' => 'Create', 'value' => 'create-spk'],
                    ['name' => 'Update', 'value' => 'update-spk'],
                    ['name' => 'Delete', 'value' => 'delete-spk'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-spk'],

                    ['name' => 'Confirm', 'value' => 'confirm-spk'],
                    ['name' => 'Undo', 'value' => 'undo-spk'],

                    ['name' => 'PDF', 'value' => 'pdf-spk'],
                    ['name' => 'Excel', 'value' => 'excel-spk'],

                    ['name' => 'Edit Modal Price', 'value' => 'editHM-spk'],
                    ['name' => 'Edit Expo Price', 'value' => 'editHE-spk'],
                    ['name' => 'Edit Sell Price', 'value' => 'editHJ-spk'],
                ]
            ],

            // accessEstimator
            [
                'name' => 'Estimator', 'id' => 'estimator',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-estimator'],
                    ['name' => 'Create', 'value' => 'create-estimator'],
                    ['name' => 'Update', 'value' => 'update-estimator'],
                    ['name' => 'Delete', 'value' => 'delete-estimator'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-estimator'],

                    ['name' => 'List Price', 'value' => 'price-estimator'],
                    ['name' => 'Create Price', 'value' => 'createPrice-estimator'],
                    ['name' => 'Update Price', 'value' => 'updatePrice-estimator'],
                    ['name' => 'Delete Price', 'value' => 'deletePrice-estimator'],
                    
                ],
            ],

            // accessProduction
            [
                'name' => 'Production', 'id' => 'production',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-production'],
                    ['name' => 'Complete', 'value' => 'complete-production'],
                    ['name' => 'PDF', 'value' => 'pdf-production'],
                ]
            ],

            // accessOffer
            [
                'name' => 'Offer / Quotation', 'id' => 'offer',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-offer'],
                    ['name' => 'Create', 'value' => 'create-offer'],
                    ['name' => 'Update', 'value' => 'update-offer'],
                    ['name' => 'Delete', 'value' => 'delete-offer'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-offer'],

                    ['name' => 'Undo', 'value' => 'undo-offer'],
                    
                    ['name' => 'PDF', 'value' => 'pdf-offer'],
                ]
            ],

            // accessInvoice
            [
                'name' => 'Recap SPK / Invoice', 'id' => 'invoice',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-invoice'],
                    ['name' => 'Change Admin', 'value' => 'admin-invoice'],
                    ['name' => 'Create', 'value' => 'create-invoice'],
                    ['name' => 'Update', 'value' => 'update-invoice'],
                    ['name' => 'Delete', 'value' => 'delete-invoice'],
                    ['name' => 'Undo', 'value' => 'undo-invoice'],
                    ['name' => 'Check For Finance', 'value' => 'checkFinance-invoice'],
                    ['name' => 'Check For Master', 'value' => 'checkMaster-invoice'],

                    ['name' => 'Excel', 'value' => 'excel-invoice'],
                    ['name' => 'View Price', 'value' => 'viewPrice-invoice'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-invoice'],
                ]
            ],

            // accessPr
            [
                'name' => 'PR', 'id' => 'pr',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-pr'],
                    ['name' => 'Create', 'value' => 'create-pr'],
                    ['name' => 'Update', 'value' => 'update-pr'],
                    ['name' => 'Delete', 'value' => 'delete-pr'],
                    ['name' => 'Confirm', 'value' => 'confirm-pr'],
                    ['name' => 'Change Purchasing', 'value' => 'changePurchasing-pr'],
                    ['name' => 'Check Audit', 'value' => 'checkAudit-pr'],
                    ['name' => 'Check Finance', 'value' => 'checkFinance-pr'],

                    ['name' => 'PDF', 'value' => 'pdf-pr'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-pr'],
                    ['name' => 'Excel', 'value' => 'excel-pr'],
                ]
            ],


            // accessPr
            [
                'name' => 'PO', 'id' => 'po',
                'data' => 
                [
                    ['name' => 'List PO', 'value' => 'list-po'],
                    ['name' => 'Create PO', 'value' => 'create-po'],
                    ['name' => 'Update PO', 'value' => 'update-po'],
                    ['name' => 'Delete PO', 'value' => 'delete-po'],
                    ['name' => 'Undo PO', 'value' => 'undo-po'],
                ]
            ],

            // accessDelivery
            [
                'name' => 'Delivery', 'id' => 'delivery',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-delivery'],
                    ['name' => 'All Courier', 'value' => 'allCourier-delivery'],
                    ['name' => 'List', 'value' => 'list-delivery'],
                    ['name' => 'Create', 'value' => 'create-delivery'],
                    ['name' => 'Edit', 'value' => 'edit-delivery'],
                    ['name' => 'Delete', 'value' => 'delete-delivery'],
                    ['name' => 'Change Schedule', 'value' => 'change-delivery'],
                    ['name' => 'Send', 'value' => 'send-delivery'],
                    ['name' => 'Undo Send', 'value' => 'undoSend-delivery'],
                    ['name' => 'Confirmation', 'value' => 'confirm-delivery'],
                    ['name' => 'Undo Confirmation', 'value' => 'undoConfirm-delivery'],
                    ['name' => 'View Distance', 'value' => 'viewDist-delivery'],
                    ['name' => 'Courier', 'value' => 'courier-delivery'],
                    ['name' => 'Take', 'value' => 'take-delivery'],
                    ['name' => 'Undo Take', 'value' => 'undoTake-delivery'],
                ]
            ],

            // accessDesigner
            [
                'name' => 'Designer', 'id' => 'designer',
                'data' => 
                [
                    ['name' => 'All Designer', 'value' => 'allDesigner-designer'],
                    ['name' => 'All Sales', 'value' => 'allSales-designer'],
                    ['name' => 'List', 'value' => 'list-designer'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-designer'],
                    ['name' => 'Create', 'value' => 'create-designer'],
                    ['name' => 'Edit', 'value' => 'edit-designer'],
                    ['name' => 'Delete', 'value' => 'delete-designer'],
                    ['name' => 'Take Design', 'value' => 'take-designer'],
                    ['name' => 'Finish Design', 'value' => 'finish-designer'],
                    ['name' => 'Approved Design', 'value' => 'approved-designer'],
                    ['name' => 'Project Design', 'value' => 'project-designer'],
                    ['name' => 'Revision Design', 'value' => 'revision-designer'],
                    ['name' => 'Design Candidate', 'value' => 'designCandidate-designer'],
                    ['name' => 'Add Design Candidate', 'value' => 'createDesignCandidate-designer'],
                    ['name' => 'Edit Design Candidate', 'value' => 'editDesignCandidate-designer'],
                    ['name' => 'Delete Design Candidate', 'value' => 'deleteDesignCandidate-designer'],
                ]
            ],

            

            // accessSupplier
            [
                'name' => 'Supplier', 'id' => 'supplier',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-supplier'],
                    ['name' => 'Create', 'value' => 'create-supplier'],
                    ['name' => 'Edit', 'value' => 'edit-supplier'],
                    ['name' => 'Delete', 'value' => 'delete-supplier'],
                ]
            ],

            // accessTodo
            [
                'name' => 'Todo', 'id' => 'todo',
                'data' => 
                [
                    ['name' => 'All Sales', 'value' => 'allSales-todo'],
                    ['name' => 'List', 'value' => 'list-todo'],
                    ['name' => 'Create', 'value' => 'create-todo'],
                    ['name' => 'Edit', 'value' => 'edit-todo'],
                    ['name' => 'Delete', 'value' => 'delete-todo'],
                    ['name' => 'Update Status', 'value' => 'status-todo'],
                    ['name' => 'Undo Status', 'value' => 'undo-todo'],
                    ['name' => 'Dashboard', 'value' => 'dashboard-todo'],
                ]
            ],

            // accessCar
            [
                'name' => 'Car', 'id' => 'car',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-car'],
                    ['name' => 'Create', 'value' => 'create-car'],
                    ['name' => 'Edit', 'value' => 'edit-car'],
                    ['name' => 'Delete', 'value' => 'delete-car'],
                ]
            ],

            // accessAdvertisment
            [
                'name' => 'Advertisment', 'id' => 'advertisment',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-advertisment'],
                    ['name' => 'Create', 'value' => 'create-advertisment'],
                    ['name' => 'Edit', 'value' => 'edit-advertisment'],
                    ['name' => 'Delete', 'value' => 'delete-advertisment'],
                ]
            ],

            

            

            // accessDesignRequest
            [
                'name' => 'Design Request', 'id' => 'designRequest',
                'data' => 
                [
                    ['name' => 'All Client', 'value' => 'allClient-designRequest'],
                    ['name' => 'List', 'value' => 'list-designRequest'],
                    ['name' => 'Create', 'value' => 'create-designRequest'],
                    ['name' => 'Edit', 'value' => 'edit-designRequest'],
                    ['name' => 'Set Status', 'value' => 'setStatus-designRequest'],
                    ['name' => 'Delete', 'value' => 'delete-designRequest'],
                ],
            ],

            // accessContract
            [
                'name' => 'Contract', 'id' => 'contract',
                'data' => 
                [
                    ['name' => 'All Sales', 'value' => 'allSales-contract'],
                    ['name' => 'List', 'value' => 'list-contract'],
                    ['name' => 'Create', 'value' => 'create-contract'],
                    ['name' => 'Edit', 'value' => 'edit-contract'],
                    ['name' => 'Delete', 'value' => 'delete-contract'],
                    ['name' => 'PDF', 'value' => 'pdf-contract'],
                ],
            ],


            // accessRequest
            [
                'name' => 'List Request', 'id' => 'listRequest',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-listRequest'],
                    ['name' => 'Status', 'value' => 'status-listRequest'],
                    ['name' => 'List', 'value' => 'list-listRequest'],
                    ['name' => 'Create', 'value' => 'create-listRequest'],
                    ['name' => 'Edit', 'value' => 'edit-listRequest'],
                    ['name' => 'Delete', 'value' => 'delete-listRequest'],
                    ['name' => 'Feedback', 'value' => 'feedback-listRequest'],
                    ['name' => 'Undo Feedback', 'value' => 'undoFeedback-listRequest'],
                    ['name' => 'Confirm', 'value' => 'confirm-listRequest'],
                    ['name' => 'Undo Confirm', 'value' => 'undoConfirm-listRequest'],
                ],
            ],

            // accessCrm
            [
                'name' => 'CRM', 'id' => 'crm',
                'data' => 
                [
                    ['name' => 'All Sales', 'value' => 'allSales-crm'],
                    ['name' => 'List', 'value' => 'list-crm'],
                    ['name' => 'Create', 'value' => 'create-crm'],
                    ['name' => 'Edit', 'value' => 'edit-crm'],
                    ['name' => 'Delete', 'value' => 'delete-crm'],
                    ['name' => 'Next', 'value' => 'next-crm'],
                    ['name' => 'Reschedule', 'value' => 'reschedule-crm'],
                ],
            ],

             // accessAccount
            [
                'name' => 'Account', 'id' => 'account',
                'data' => 
                [

                    ['name' => 'Account Class', 'value' => 'accountClass-account'],
                    ['name' => 'Create Account Class', 'value' => 'createAccountClass-account'],
                    ['name' => 'Edit Account Class', 'value' => 'editAccountClass-account'],
                    ['name' => 'Delete Account Class', 'value' => 'deleteAccountClass-account'],

                    ['name' => 'Account Type', 'value' => 'accountType-account'],
                    ['name' => 'Create Account Type', 'value' => 'createAccountType-account'],
                    ['name' => 'Edit Account Type', 'value' => 'editAccountType-account'],
                    ['name' => 'Delete Account Type', 'value' => 'deleteAccountType-account'],

                    ['name' => 'Account List', 'value' => 'accountList-account'],
                    ['name' => 'Create Account List', 'value' => 'createAccountList-account'],
                    ['name' => 'Edit Account List', 'value' => 'editAccountList-account'],
                    ['name' => 'Delete Account List', 'value' => 'deleteAccountList-account'],
                    ['name' => 'Active Account List', 'value' => 'activeAccountList-account'],
                    ['name' => 'Relation Account List', 'value' => 'relationAccountList-account'],
                    ['name' => 'Merge Account List', 'value' => 'mergeAccountList-account'],

                    ['name' => 'Account Journal', 'value' => 'accountJournal-account'],
                    ['name' => 'Create Account General', 'value' => 'createAccountGeneral-account'],
                    ['name' => 'Edit Account General', 'value' => 'editAccountGeneral-account'],
                    ['name' => 'Delete Account General', 'value' => 'deleteAccountGeneral-account'],


                    ['name' => 'Account Sales', 'value' => 'accountSales-account'],
                    ['name' => 'Create Account Sales', 'value' => 'createAccountSales-account'],
                    ['name' => 'Edit Account Sales', 'value' => 'editAccountSales-account'],
                    ['name' => 'Delete Account Sales', 'value' => 'deleteAccountSales-account'],
                    ['name' => 'Status Account Sales', 'value' => 'statusAccountSales-account'],


                    ['name' => 'Account Banking', 'value' => 'accountBanking-account'],
                    ['name' => 'Create Account Banking', 'value' => 'createAccountBanking-account'],
                    ['name' => 'Edit Account Banking', 'value' => 'editAccountBanking-account'],
                    ['name' => 'Delete Account Banking', 'value' => 'deleteAccountBanking-account'],


                    ['name' => 'Account Purchasing', 'value' => 'accountPurchasing-account'],
                    ['name' => 'Create Account Purchasing', 'value' => 'createAccountPurchasing-account'],
                    ['name' => 'Edit Account Purchasing', 'value' => 'editAccountPurchasing-account'],
                    ['name' => 'Delete Account Purchasing', 'value' => 'deleteAccountPurchasing-account'],
                    ['name' => 'Status Account Purchasing', 'value' => 'statusAccountPurchasing-account'],

                ],
            ],

             // accessActivity
            [
                'name' => 'Activity', 'id' => 'activity',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-activity'],
                    ['name' => 'List', 'value' => 'list-activity'],
                    ['name' => 'Create', 'value' => 'create-activity'],
                    ['name' => 'Edit', 'value' => 'edit-activity'],
                    ['name' => 'Delete', 'value' => 'delete-activity'],
                    ['name' => 'Confirm', 'value' => 'confirm-activity'],
                    ['name' => 'Check HRD', 'value' => 'checkHRD-activity'],
                ],
            ],

            // accessDayoff
            [
                'name' => 'Dayoff', 'id' => 'dayoff',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-dayoff'],
                    ['name' => 'List', 'value' => 'list-dayoff'],
                    ['name' => 'Create', 'value' => 'create-dayoff'],
                    ['name' => 'Edit', 'value' => 'edit-dayoff'],
                    ['name' => 'Delete', 'value' => 'delete-dayoff'],
                    ['name' => 'Confirm', 'value' => 'confirm-dayoff'],
                    ['name' => 'Setting', 'value' => 'setting-dayoff'],
                    ['name' => 'Check HRD', 'value' => 'checkHRD-dayoff'],
                ],
            ],

            // accessAbsence
            [
                'name' => 'Absence', 'id' => 'absence',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-absence'],
                    ['name' => 'List', 'value' => 'list-absence'],
                    ['name' => 'Create', 'value' => 'create-absence'],
                    ['name' => 'Edit', 'value' => 'edit-absence'],
                    ['name' => 'Delete', 'value' => 'delete-absence'],
                    ['name' => 'Confirm', 'value' => 'confirm-absence'],
                    ['name' => 'Check HRD', 'value' => 'checkHRD-absence'],
                ],
            ],

            // accessArchive
            [
                'name' => 'Archive', 'id' => 'archive',
                'data' => 
                [

                    ['name' => 'List', 'value' => 'list-archive'],
                ],
            ],

            // accessTrash
            [
                'name' => 'Trash', 'id' => 'trash',
                'data' => 
                [

                    ['name' => 'List', 'value' => 'list-trash'],
                    ['name' => 'Delete', 'value' => 'delete-trash'],
                    ['name' => 'Restore', 'value' => 'restore-trash'],
                ],
            ],

            // accessJobApply
            [
                'name' => 'Job Apply', 'id' => 'jobApply',
                'data' => 
                [

                    ['name' => 'List', 'value' => 'list-jobApply'],
                    ['name' => 'Delete', 'value' => 'delete-jobApply'],
                ],
            ],

            // accessJobApply
            [
                'name' => 'Quote', 'id' => 'quote',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-quote'],
                    ['name' => 'Delete', 'value' => 'delete-quote'],
                ],
            ],

            // accessArModel
            [
                'name' => 'AR Model', 'id' => 'arModel',
                'data' => 
                [
                    ['name' => 'All User', 'value' => 'allUser-arModel'],
                    ['name' => 'List', 'value' => 'list-arModel'],
                    ['name' => 'Create', 'value' => 'create-arModel'],
                    ['name' => 'Edit', 'value' => 'edit-arModel'],
                    ['name' => 'Delete', 'value' => 'delete-arModel'],
                    ['name' => 'Active', 'value' => 'active-arModel'],
                ],
            ],

            // accessStock
            [
                'name' => 'Stock', 'id' => 'stock',
                'data' => 
                [
                    ['name' => 'List', 'value' => 'list-stock'],
                    ['name' => 'Create', 'value' => 'create-stock'],
                    ['name' => 'Edit', 'value' => 'edit-stock'],
                    ['name' => 'Delete', 'value' => 'delete-stock'],
                    ['name' => 'Create Stock Book', 'value' => 'createStockBook-stock'],
                    ['name' => 'Edit Stock Book', 'value' => 'editStockBook-stock'],
                    ['name' => 'Delete Stock Book', 'value' => 'deleteStockBook-stock'],
                    ['name' => 'statusStockBook', 'value' => 'statusStockBook-stock'],

                    ['name' => 'List Place', 'value' => 'listStockPlace-stock'],
                    ['name' => 'Create Place', 'value' => 'createStockPlace-stock'],
                    ['name' => 'Edit Place', 'value' => 'editStockPlace-stock'],
                    ['name' => 'Delete Place', 'value' => 'deleteStockPlace-stock'],
                ],
            ],

        ];

        return $array;
    }
   
}
