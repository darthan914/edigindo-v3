<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Session;
use App\User;
use App\Config;
use App\TargetSales;
use App\Spk;
use App\Models\Archive;

use App\Models\Campaign;
use App\Models\CampaignDetail;
use App\Models\CampaignSales;

use App\Invoice;
use App\Models\PrDetail;

use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Cache;

use App\Notifications\Notif;


class DashboardController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $f_year  = $this->filter($request->f_year, date('Y'));

    	$sales = User::where(function($query){
            $query->whereIn('position_id', getConfigValue('sales_position', true))
            ->orWhereIn('id', getConfigValue('sales_user', true));
        })
        ->orderBy('active', 'DESC');

    	if(!Auth::user()->can('allSales-spk'))
        {
            $sales->whereIn('id', Auth::user()->staff());
        }

        if(!in_array(Auth::user()->positions->id, getConfigValue('super_admin_position', true)))
        {
            $sales->whereNotIn('users.position', getConfigValue('super_admin_position', true));
        }

        if(!in_array(Auth::id(), getConfigValue('super_admin_user', true)))
        {
            $sales->whereNotIn('users.id', getConfigValue('super_admin_user', true));
        }

        $sales = $sales->get();

        $campaign = Campaign::where('year', $f_year)->first();

    	return view('backend.dashboard.index', compact('request', 'f_year', 'sales', 'campaign'));
    }

    public function ajaxChartMonthly(Request $request)
    {
        $f_year  = $this->filter($request->f_year, date('Y'));
    	$f_user  = $this->filter($request->f_user);

    	$month = ['january', 'febuary', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    	$index = User::orderBy('active', 'DESC');

    	for ($i=0; $i < 12 ; $i++) {
    		$index->addSelect(
    			DB::raw('COALESCE(SUM('.$month[$i].'.totalHJ), 0) AS '.$month[$i].'_totalHJ'),
    			DB::raw('COALESCE(SUM('.$month[$i].'.totalRealOmset), 0) AS '.$month[$i].'_totalRealOmset')
    		);
    	}
    		
    	for ($i=0; $i < 12 ; $i++) {

    		$sql = '
				( 
				    SELECT 
				    	production.sales_id, 
				    	SUM(production.totalHM) AS totalHM, 
				    	SUM(production.totalHJ) As totalHJ, 
				    	SUM(production.totalRealOmset) AS totalRealOmset 
				    FROM 
				    ( 
				        SELECT 
				        	production.spk_id, 
				        	production.name, 
				        	production.sales_id, 
				        	(@totalHM := production.totalHM) as totalHM, 
				        	(@totalHJ := production.totalHJ) as totalHJ, 
				        	@profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                            @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
				        	(CASE WHEN @percent < 35 THEN (@profit / 0.35) + @profit ELSE @totalHJ END) AS totalRealOmset 
				        FROM 
				        ( 
				            SELECT 
				            	spk.id AS spk_id, 
				            	spk.sales_id, spk.name, 
                                SUM(production.hm * production.quantity) AS totalHM, 
				            	SUM(production.he * production.quantity) AS totalHE, 
				            	SUM(production.hj * production.quantity) AS totalHJ 
				            FROM spk join production ON spk.id = production.spk_id
				            WHERE MONTH(spk.date) = '.($i+1).' AND profitable = 1';

			if($f_year)
			{
				$sql .= '
					AND YEAR(spk.date) = '.$f_year.'
				';
			}

			$sql .= '
					        GROUP BY spk.id
					    ) production
					) production
					GROUP BY production.sales_id
				) '.$month[$i].'
    		';


    		$index->leftJoin(DB::raw($sql), $month[$i].'.sales_id', 'users.id');
    	}
    	

        if(!in_array(Auth::user()->position, getConfigValue('super_admin_position'))
           && !in_array(Auth::id(), getConfigValue('super_admin_user')))
        {
            $index->where('id', Auth::id());
        }
        else if($f_user != '')
        {
            $index->where('id', $f_user);
        }

        $index = $index->first();


        $array_chartHJ = $array_chartRO = '';

        for ($i=0; $i < 12 ; $i++) {
            eval('$'.$month[$i].'_hj   = round($index->'.$month[$i].'_totalHJ / $this->divine(), 2);');
            eval('$'.$month[$i].'_ro   = round($index->'.$month[$i].'_totalRealOmset / $this->divine(), 2);');

            $array_chartHJ .= '$'.$month[$i].'_hj,';
            $array_chartRO .= '$'.$month[$i].'_ro,';
        }

        eval('$chartHJ = ['.$array_chartHJ.'];');
        eval('$chartRO = ['.$array_chartRO.'];');
    	

    	return compact('chartHJ', 'chartRO');
    }

    public function ajaxChartTarget(Request $request)
    {
    	$f_year  = $this->filter($request->f_year, date('Y'));

    	$sql = '
			( 
			    SELECT 
			    	production.sales_id, 
			    	SUM(production.totalHM) AS totalHM, 
			    	SUM(production.totalHJ) As totalHJ, 
			    	SUM(production.totalRealOmset) AS totalRealOmset 
			    FROM 
			    ( 
			        SELECT 
			        	production.spk_id, 
			        	production.name, 
			        	production.sales_id, 
			        	(@totalHM := production.totalHM) as totalHM, 
			        	(@totalHJ := production.totalHJ) as totalHJ, 
			        	@profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                        @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
			        	(CASE WHEN @percent < 35 THEN (@profit / 0.35) + @profit ELSE @totalHJ END) AS totalRealOmset 
			        FROM 
			        ( 
			            SELECT 
			            	spk.id AS spk_id, 
			            	spk.sales_id, spk.name, 
                            SUM(production.hm * production.quantity) AS totalHM, 
			            	SUM(production.he * production.quantity) AS totalHE, 
			            	SUM(production.hj * production.quantity) AS totalHJ 
			            FROM spk join production ON spk.id = production.spk_id
                        WHERE profitable = 1';
		if($f_year)
		{
			$sql .= '
				AND YEAR(spk.date) = '.$f_year.'
			';
		}

		$sql .= '
				        GROUP BY spk.id
				    ) production
				) production
				GROUP BY production.sales_id
			) production
		';
    	
    	$index = User::orderBy('active', 'DESC')
    		->select('users.id', DB::raw('SUM(production.totalRealOmset) AS totalRealOmset'))
	    	->join(DB::raw($sql), 'users.id', 'production.sales_id');

        $note_target = '';

    	if(!in_array(Auth::user()->position, getConfigValue('super_admin_position')) && !in_array(Auth::id(), getConfigValue('super_admin_user')))
        {
            $index->where('id', Auth::id());
            $target_sales = TargetSales::where('user_id', Auth::id())->orderBy('year', 'DESC');

            if($f_year != '')
	        {
	            $target_sales->where('year', $f_year);
	        }

	        $target_sales = $target_sales->first();
            $target = $target_sales->target ?? getConfigValue('default_target_sales');
            $note_target = $target_sales->note_target ?? '';
        }
        else
        {
        	$target_sales = TargetSales::where('user_id', 0)->orderBy('year', 'DESC');

            if($f_year != '')
	        {
	            $target_sales->where('year', $f_year);
	        }

	        $target_sales = $target_sales->first();
            $target = $target_sales->target ?? getConfigValue('default_target_master');
            $note_target = $target_sales->note_target;
        }

        $index = $index->first();

        $init_totalRealOmset = $index->totalRealOmset;

        $target = $target - $init_totalRealOmset;

    	$chartTarget = [round($init_totalRealOmset / $this->divine(), 2), round(max($target / $this->divine(), 0), 2)];


    	return compact('chartTarget', 'note_target');
    }

    public function ajaxChartInOut(Request $request)
    {
    	$f_year  = $this->filter($request->f_year, date('Y'));
        $f_user  = $this->filter($request->f_user);

    	$month = ['january', 'febuary', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    	$index = User::where(function($query){
                $query->whereIn('position', getConfigValue('sales_position'))
                ->orWhereIn('id', getConfigValue('sales_user'));
            })
            ->orderBy('active', 'DESC');

        for ($j=0; $j < 3 ; $j++) {
            for ($i=0; $i < 12 ; $i++) {
                $index->addSelect(
                    DB::raw('COALESCE(SUM('.$month[$i].'_minus_'.$j.'.totalHM), 0) AS '.$month[$i].'_minus_'.$j.'_totalHM'),
                    DB::raw('COALESCE(SUM('.$month[$i].'_minus_'.$j.'.totalRealOmset), 0) AS '.$month[$i].'_minus_'.$j.'_totalRealOmset')
                );

            }
        }

        for ($j=0; $j < 3 ; $j++) {
            for ($i=0; $i < 12 ; $i++) {
            

                $sql = '
                    ( 
                        SELECT 
                            production.sales_id, 
                            SUM(production.totalHM) AS totalHM, 
                            SUM(production.totalHJ) As totalHJ, 
                            SUM(production.totalRealOmset) AS totalRealOmset 
                        FROM 
                        ( 
                            SELECT 
                                production.spk_id, 
                                production.name, 
                                production.sales_id, 
                                (@totalHM := production.totalHM) as totalHM, 
                                (@totalHJ := production.totalHJ) as totalHJ, 
                                @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                                @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                                (CASE WHEN @percent < 35 THEN (@profit / 0.35) + @profit ELSE @totalHJ END) AS totalRealOmset 
                            FROM 
                            ( 
                                SELECT 
                                    spk.id AS spk_id, 
                                    spk.sales_id, spk.name, 
                                    SUM(production.hm * production.quantity) AS totalHM, 
                                    SUM(production.he * production.quantity) AS totalHE, 
                                    SUM(production.hj * production.quantity) AS totalHJ 
                                FROM spk join production ON spk.id = production.spk_id
                                WHERE MONTH(spk.date) = '.($i+1).' AND profitable = 1';


                $sql .= '
                    AND YEAR(spk.date) = '.($f_year - $j).'
                ';

                $sql .= '
                                GROUP BY spk.id
                            ) production
                        ) production
                        GROUP BY production.sales_id
                    ) '.$month[$i].'_minus_'.$j.'
                ';


                $index->leftJoin(DB::raw($sql), $month[$i].'_minus_'.$j.'.sales_id', 'users.id');
            }
        }
        
        if(!in_array(Auth::user()->position, getConfigValue('super_admin_position')) 
           && !in_array(Auth::id(), getConfigValue('super_admin_user')))
        {
            $index->where('id', Auth::id());
        }
        else if($f_user != '')
        {
            $index->where('id', $f_user);
        }


        $index = $index->first();

        $array_in_total = $array_out_total = $array_income = $array_outcome = '';

        for ($i=0; $i < 3; $i++) {

            $array_in = '';
            $array_out = '';

            for ($j=0; $j < 12 ; $j++) {
                eval('$in_'.$month[$j].'_minus_'.$i.'   = round($index->'.$month[$j].'_minus_'.$i.'_totalRealOmset / $this->divine(), 2);');
                eval('$out_'.$month[$j].'_minus_'.$i.'   = round($index->'.$month[$j].'_minus_'.$i.'_totalHM / $this->divine(), 2);');

                $array_in .= '$in_'.$month[$j].'_minus_'.$i.',';
                $array_out .= '$out_'.$month[$j].'_minus_'.$i.',';

            }


            eval('$in_total_minus_'.$i.' = collect(['.$array_in.'])->sum();');
            eval('$out_total_minus_'.$i.' = collect(['.$array_out.'])->sum();');

            eval('$income_minus_'.$i.' = ['.$array_in.'];');
            eval('$outcome_minus_'.$i.' = ['.$array_out.'];');

        }

	    return compact('income_minus_0', 'outcome_minus_0', 'in_total_minus_0', 'out_total_minus_0', 'income_minus_1', 'outcome_minus_1', 'in_total_minus_1', 'out_total_minus_1', 'income_minus_2', 'outcome_minus_2', 'in_total_minus_2', 'out_total_minus_2');
    }

    public function ajaxChartCampaign(Request $request)
    {
        $year  = $this->filter($request->f_year, date('Y'));
        $f_user  = $this->filter($request->f_user);

        $short_month = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DES'];

        $campaign = Campaign::where('year', $year)->first();

        $index = User::where(function($query){
                $query->whereIn('position', getConfigValue('sales_position'))
                ->orWhereIn('id', getConfigValue('sales_user'));
            })
            ->orderBy('active', 'DESC');

        $where = '';
        if($request->for_expo)
        {
            $where = ' AND spk.main_division in ("'. implode('","', getConfigValue('division_expo')) .'")';
        }
        else
        {
            $where = ' AND spk.main_division not in ("'. implode('","', getConfigValue('division_expo')) .'")';
        }

        for ($i=1; $i <= 12; $i++) {
            $index->addSelect(
                    DB::raw('COALESCE(SUM(real_omset_'.$i.'.totalRealOmset), 0) AS real_omset_'.$i.'')
                );
        }

        for ($i=1; $i <= 12; $i++)  {

            $sql_real_omset = '
                (
                    /* sales -> spk */
                    SELECT production.sales_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset, SUM(production.totalLoss) AS totalLoss, COUNT(production.sales_id) AS countSPK
                    FROM
                    (
                        /* spk -> production with realOmset */
                        SELECT
                            production.spk_id, 
                            production.name, 
                            production.sales_id,
                            (@totalHM := production.totalHM) as totalHM,
                            (@totalHJ := production.totalHJ) as totalHJ,
                            @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                            @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 35 THEN (@profit / 0.35) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                SUM(production.hm * production.quantity) AS totalHM,
                                SUM(production.he * production.quantity) AS totalHE,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) = '.$i.'
                                AND YEAR(spk.date) = '.$year.' AND profitable = 1'.$where.'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_'.$i;

            $index->leftJoin(DB::raw($sql_real_omset), 'real_omset_'.$i.'.sales_id', 'users.id');
        }
        

        if(!in_array(Auth::user()->position, getConfigValue('super_admin_position'))
           && !in_array(Auth::id(), getConfigValue('super_admin_user')))
        {
            $index->where('id', Auth::id());
        }
        else if($f_user != '')
        {
            $index->where('id', $f_user);
        }


        $index = $index->first();

        $array_chart_real_omset = '';
        $array_chart_sub_real_omset = '';
        $array_label_month = '';

        for ($i=1; $i <= 12; $i++)  {

            eval('$real_omset_'.$i.' = round($index->real_omset_'.$i.' / $this->divine(), 2);');
            eval('$sub_real_omset_'.$i.' = round(max($campaign->value - $index->real_omset_'.$i.', 0) / $this->divine(), 2);');

            $array_chart_real_omset .= '$real_omset_'.$i.',';
            $array_chart_sub_real_omset .= '$sub_real_omset_'.$i.',';
            $array_label_month .=  '"'.$short_month[$i-1].'",';


        }

        eval('$chart_real_omset = ['.$array_chart_real_omset.'];');
        eval('$chart_sub_real_omset = ['.$array_chart_sub_real_omset.'];');
        $label_month = $short_month;
        

        return compact('chart_real_omset', 'chart_sub_real_omset', 'label_month', 'campaign');
    }

    public function ajaxChartCampaignDetail(CampaignDetail $campaign_detail, Request $request)
    {
        $year  = $this->filter($request->f_year, date('Y'));
        $f_user  = $this->filter($request->f_user);
        
        $short_month = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DES'];

        $index = User::where(function($query){
                $query->whereIn('position', getConfigValue('sales_position'))
                ->orWhereIn('id', getConfigValue('sales_user'));
            })
            ->orderBy('active', 'DESC');

        $delta_month = $campaign_detail->end_month - $campaign_detail->start_month + 1;

        $where = '';
        if($campaign_detail->for_expo)
        {
            $where = ' AND spk.main_division in ("'. implode('","', getConfigValue('division_expo')) .'")';
        }
        else
        {
            $where = ' AND spk.main_division not in ("'. implode('","', getConfigValue('division_expo')) .'")';
        }

        for ($i=0; $i < $delta_month; $i++) {
            $index->addSelect(
                    DB::raw('COALESCE(SUM(real_omset_'.$i.'.totalRealOmset), 0) AS real_omset_'.$i.'')
                );
        }

        for ($i=0; $i < $delta_month; $i++) {

            $sql_real_omset = '
                (
                    /* sales -> spk */
                    SELECT production.sales_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset, SUM(production.totalLoss) AS totalLoss, COUNT(production.sales_id) AS countSPK
                    FROM
                    (
                        /* spk -> production with realOmset */
                        SELECT
                            production.spk_id, 
                            production.name, 
                            production.sales_id,
                            (@totalHM := production.totalHM) as totalHM,
                            (@totalHJ := production.totalHJ) as totalHJ,
                            @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                            @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 35 THEN (@profit / 0.35) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                SUM(production.hm * production.quantity) AS totalHM,
                                SUM(production.he * production.quantity) AS totalHE,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) = '.($i+1+($campaign_detail->start_month-1)).'
                                AND YEAR(spk.date) = '.$year.' AND profitable = 1'.$where.'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_'.$i;

            $index->leftJoin(DB::raw($sql_real_omset), 'real_omset_'.$i.'.sales_id', 'users.id');
        }
        

        if(!in_array(Auth::user()->position, getConfigValue('super_admin_position')) 
           && !in_array(Auth::id(), getConfigValue('super_admin_user')))
        {
            $index->where('id', Auth::id());
        }
        else if($f_user != '')
        {
            $index->where('id', $f_user);
        }


        $index = $index->first();

        $array_chart_real_omset = '';
        $array_chart_sub_real_omset = '';
        $array_label_month = '';

        for ($i=0; $i < $delta_month; $i++) {

            eval('$real_omset_'.$i.' = round($index->real_omset_'.$i.' / $this->divine(), 2);');
            eval('$sub_real_omset_'.$i.' = max(round((($campaign_detail->value / '.($campaign_detail->end_month - $campaign_detail->start_month + 1).') - $index->real_omset_'.$i.') / $this->divine(), 2), 0);');

            $array_chart_real_omset .= '$real_omset_'.$i.',';
            $array_chart_sub_real_omset .= '$sub_real_omset_'.$i.',';
            $array_label_month .=  '"'.$short_month[$i+($campaign_detail->start_month-1)].'",';


        }

        eval('$chart_real_omset = ['.$array_chart_real_omset.'];');
        eval('$chart_sub_real_omset = ['.$array_chart_sub_real_omset.'];');
        eval('$label_month = ['.$array_label_month.'];');
        

        return compact('chart_real_omset', 'chart_sub_real_omset', 'label_month', 'campaign_detail');
    }

}
