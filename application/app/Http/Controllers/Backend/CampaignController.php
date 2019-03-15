<?php

namespace App\Http\Controllers\Backend;

use App\Models\Campaign;
use App\Models\CampaignSales;
use App\Models\CampaignDetail;
use App\Spk;
use App\Config;
use App\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;

class CampaignController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    private function pieChart(float $percent)
    {
        $html = "".number_format($percent, 2)." %";

        /*if($percent <= 50)
        {
            $html .= "<div class=\"pie\">
                        <div style=\"transform: rotate(".((min(100, $percent)/100) * 360)."deg);\"></div>
                      </div>";
        }
        else
        {
            $html .= "<div class=\"pie\">
                        <div class=\"over50\" style=\"transform: rotate(".(((min(100, $percent) - 50) /100) * 360)."deg);\"></div>
                      </div>";
        }*/
        
        return $html;
    }

    
    public function index(Request $request)
    {
        return view('backend.campaign.index');
    }

    public function datatables(Request $request)
    {
        $index = Campaign::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-campaign')) {
                $html .= '
                    <a href="' . route('backend.campaign.edit', ['index' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                ';
            }

            if (Auth::user()->can('delete-campaign')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-campaign" data-toggle="modal" data-target="#delete-campaign" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            $html .= '
                    <a href="' . route('backend.campaign.dashboard', ['index' => $index->id]) . '" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                ';

            return $html;
        });

        $datatables->editColumn('value', function ($index) {
            return number_format($index->value);
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function dataOldSales($f_year)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        /* production jan to apr */
        {
            $sql_real_omset_jan_to_apr = '
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
                            @profit := @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) AS profit,
                            @percent := (@profit / IF(@totalHM<>0,@totalHM,1)) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                (CASE WHEN production.he > 0 THEN SUM(production.he * production.quantity) ELSE SUM(production.hm * production.quantity) END) AS totalHM,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) BETWEEN 1 AND 4
		                        AND YEAR(spk.date) = '.($f_year ?? date('Y')).'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_jan_to_apr';
        }

        /* production may to aug */
        {
            $sql_real_omset_may_to_aug = '
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
                            @profit := @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) AS profit,
                            @percent := (@profit / IF(@totalHM<>0,@totalHM,1)) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                (CASE WHEN production.he > 0 THEN SUM(production.he * production.quantity) ELSE SUM(production.hm * production.quantity) END) AS totalHM,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) BETWEEN 5 AND 8
                            	AND YEAR(spk.date) = '.($f_year ?? date('Y')).'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_may_to_aug';
        }

        /* production sep to dec */
        {
            $sql_real_omset_sep_to_dec = '
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
                            @profit := @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) AS profit,
                            @percent := (@profit / IF(@totalHM<>0,@totalHM,1)) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                (CASE WHEN production.he > 0 THEN SUM(production.he * production.quantity) ELSE SUM(production.hm * production.quantity) END) AS totalHM,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) BETWEEN 9 AND 12
                            	AND YEAR(spk.date) = '.($f_year ?? date('Y')).'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_sep_to_dec';
        }

        /* campaign */
        {
            $sql_campaign = '
                (
                    SELECT *
                    FROM campaign
                    WHERE year = '.($f_year ?? date('Y')).'
                ) campaign
            ';
        }

        $index = Spk::select(
                'spk.sales_id',
                'users.fullname',
                DB::raw('real_omset_jan_to_apr.totalRealOmset AS realOmsetJanApr'),
                DB::raw('real_omset_may_to_aug.totalRealOmset AS realOmsetMayAug'),
                DB::raw('real_omset_sep_to_dec.totalRealOmset AS realOmsetSepDec'),
                DB::raw('real_omset_jan_to_apr.countSPK AS countJanApr'),
                DB::raw('real_omset_may_to_aug.countSPK AS countMayAug'),
                DB::raw('real_omset_sep_to_dec.countSPK AS countMayAug'),
                DB::raw('COALESCE(campaign.target_jan_to_apr, 0) AS targetJanApr'),
                DB::raw('COALESCE(campaign.target_may_to_aug, 0) AS targetMayAug'),
                DB::raw('COALESCE(campaign.target_sep_to_dec, 0) AS targetSepDec')
            )
            ->join('users', 'spk.sales_id', 'users.id')
            ->leftJoin(DB::raw($sql_real_omset_jan_to_apr), 'spk.sales_id', 'real_omset_jan_to_apr.sales_id')
            ->leftJoin(DB::raw($sql_real_omset_may_to_aug), 'spk.sales_id', 'real_omset_may_to_aug.sales_id')
            ->leftJoin(DB::raw($sql_real_omset_sep_to_dec), 'spk.sales_id', 'real_omset_sep_to_dec.sales_id')
            ->join(DB::raw($sql_campaign), DB::raw('YEAR(spk.date)'), 'campaign.year')
            ->leftJoin('campaign_sales', 'spk.sales_id', 'campaign_sales.sales_id')
            ->where('campaign_sales.old_sales', 1)
            ->where('campaign_sales.year', ($f_year ?? date('Y')))
            ->groupBy('sales_id');

        $index->whereYear('spk.date', $f_year ?? date('Y'));

        if(!Auth::user()->can('allSales-campaign'))
        {
            $index->whereIn('spk.sales_id', Auth::user()->staff());
        }

        if(!in_array(Auth::user()->position, explode(', ', $super_admin_position->value)))
        {
            $index->whereNotIn('users.position', explode(', ', $super_admin_position->value));
        }

        if(!in_array(Auth::id(), explode(', ', $super_admin_user->value)))
        {
            $index->whereNotIn('users.id', explode(', ', $super_admin_user->value));
        }

        $index = $index->get();

        $data = '';
        $realOmsetJanApr = $realOmsetMayAug = $realOmsetSepDec = 0;

        foreach ($index as $list) {

                $data[] = [
                    "id"              => $list->sales_id,
                    "fullname"        => $list->fullname,

                    "realOmsetJanApr" => number_format($list->realOmsetJanApr),
                    "countJanApr"     => number_format($list->countJanApr),
                    "remainJanApr"    => number_format($list->targetJanApr - $list->realOmsetJanApr),
                    "percentJanApr"   => $this->pieChart(min(($list->realOmsetJanApr / max($list->targetJanApr, 1)) * 100, 100)),

                    "realOmsetMayAug" => number_format($list->realOmsetMayAug),
                    "countMayAug"     => number_format($list->countMayAug),
                    "remainMayAug"    => number_format($list->targetMayAug - $list->realOmsetMayAug),
                    "percentMayAug"   => $this->pieChart(min(($list->realOmsetMayAug / max($list->targetMayAug, 1)) * 100, 100)),

                    "realOmsetSepDec" => number_format($list->realOmsetSepDec),
                    "countSepDec"     => number_format($list->countSepDec),
                    "remainSepDec"    => number_format($list->targetSepDec - $list->realOmsetSepDec),
                    "percentSepDec"   => $this->pieChart(min(($list->realOmsetSepDec / max($list->targetSepDec, 1)) * 100, 100)),
                ];

                $realOmsetJanApr += $list->realOmsetJanApr;
                $realOmsetMayAug += $list->realOmsetMayAug;
                $realOmsetSepDec += $list->realOmsetSepDec;
            }

        return compact('data', 'realOmsetJanApr', 'realOmsetMayAug', 'realOmsetSepDec');
    }

    public function ajaxOldSales(Request $request)
    {
        $f_year   = $this->filter($request->f_year, date('Y'));

        return $this->dataOldSales($f_year);
    }

    public function dataNewSales($f_year)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        /* production jan to apr */
        {
            $sql_real_omset_jan_to_aug = '
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
                            @profit := @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) AS profit,
                            @percent := (@profit / IF(@totalHM<>0,@totalHM,1)) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                (CASE WHEN production.he > 0 THEN SUM(production.he * production.quantity) ELSE SUM(production.hm * production.quantity) END) AS totalHM,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) BETWEEN 1 AND 4
		                        AND YEAR(spk.date) = '.($f_year ?? date('Y')).'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_jan_to_aug';
        }

        /* production may to aug */
        {
            $sql_real_omset_may_to_dec = '
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
                            @profit := @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) AS profit,
                            @percent := (@profit / IF(@totalHM<>0,@totalHM,1)) * 100 AS percent,
                            (@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
                            (@totalHJ - @realOmset) as totalLoss
                        FROM
                        (
                            /* spk -> production */
                            SELECT 
                                spk.id AS spk_id,
                                spk.sales_id, spk.name,
                                (CASE WHEN production.he > 0 THEN SUM(production.he * production.quantity) ELSE SUM(production.hm * production.quantity) END) AS totalHM,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) BETWEEN 5 AND 12
                            	AND YEAR(spk.date) = '.($f_year ?? date('Y')).'
                            GROUP BY spk.id
                        ) production
                    ) production
                    GROUP BY production.sales_id
                ) real_omset_may_to_dec';
        }

        /* campaign */
        {
            $sql_campaign = '
                (
                    SELECT *
                    FROM campaign
                    WHERE year = '.($f_year ?? date('Y')).'
                ) campaign
            ';
        }

        $index = Spk::select(
                'spk.sales_id',
                'users.fullname',
                DB::raw('real_omset_jan_to_aug.totalRealOmset AS realOmsetJanAug'),
                DB::raw('real_omset_may_to_dec.totalRealOmset AS realOmsetMayDec'),
                DB::raw('real_omset_jan_to_aug.countSPK AS countJanAug'),
                DB::raw('real_omset_may_to_dec.countSPK AS countMayDec'),
                DB::raw('COALESCE(campaign.target_jan_to_aug, 0) AS targetJanAug'),
                DB::raw('COALESCE(campaign.target_may_to_dec, 0) AS targetMayDec')
            )
            ->join('users', 'spk.sales_id', 'users.id')
            ->leftJoin(DB::raw($sql_real_omset_jan_to_aug), 'spk.sales_id', 'real_omset_jan_to_aug.sales_id')
            ->leftJoin(DB::raw($sql_real_omset_may_to_dec), 'spk.sales_id', 'real_omset_may_to_dec.sales_id')
            ->join(DB::raw($sql_campaign), DB::raw('YEAR(spk.date)'), 'campaign.year')
            ->leftJoin('campaign_sales', 'spk.sales_id', 'campaign_sales.sales_id')
            ->where('campaign_sales.old_sales', 0)
            ->where('campaign_sales.year', ($f_year ?? date('Y')))
            ->groupBy('sales_id');

        $index->whereYear('spk.date', $f_year ?? date('Y'));

        if(!Auth::user()->can('allSales-campaign'))
        {
            $index->whereIn('spk.sales_id', Auth::user()->staff());
        }

        if(!in_array(Auth::user()->position, explode(', ', $super_admin_position->value)))
        {
            $index->whereNotIn('users.position', explode(', ', $super_admin_position->value));
        }

        if(!in_array(Auth::id(), explode(', ', $super_admin_user->value)))
        {
            $index->whereNotIn('users.id', explode(', ', $super_admin_user->value));
        }

        $index = $index->get();

        $data = '';
        $realOmsetJanAug = $realOmsetMayDec = 0;

        foreach ($index as $list) {

        		if($list->targetJanAug - $list->realOmsetJanAug < 0)
        		{
        			$location_1         = 0;
        			$location_1_percent = 100;
        		}
        		else
        		{
        			$location_1         = $list->targetJanAug - $list->realOmsetJanAug;
        			$location_1_percent = ($list->realOmsetJanAug / max($list->targetJanAug, 1)) * 100;
        		}

        		if($list->targetJanAug - $list->realOmsetJanAug < 0)
        		{
        			$location_2         = $list->targetMayDec + ($list->targetJanAug - $list->realOmsetJanAug);
        			$location_2_percent = ($list->realOmsetMayDec / max($location_2, 1)) * 100;
        		}
        		else
        		{
        			$location_2         = $list->targetMayDec - $list->realOmsetMayDec;
        			$location_2_percent = ($list->realOmsetMayDec / max($list->targetMayDec, 1)) * 100;
        		}

                $data[] = [
                    "id"              => $list->sales_id,
                    "fullname"        => $list->fullname,
                    
                    "realOmsetJanAug" => number_format($list->realOmsetJanAug),
                    "countJanAug"     => number_format($list->countJanAug),
                    "remainJanAug"    => number_format($location_1),
                    "percentJanAug"   => $this->pieChart($location_1_percent),

                    "realOmsetMayDec" => number_format($list->realOmsetMayDec),
                    "countMayDec"     => number_format($list->countMayDec),
                    "remainMayDec"    => number_format($location_2),
                    "percentMayDec"   => $this->pieChart($location_2_percent),

                ];

                $realOmsetJanAug += $list->realOmsetJanAug;
                $realOmsetMayDec += $list->realOmsetMayDec;
            }

        return compact('data', 'realOmsetJanAug', 'realOmsetMayDec');
    }

    public function ajaxNewSales(Request $request)
    {
        $f_year   = $this->filter($request->f_year, date('Y'));

        return $this->dataNewSales($f_year);
    }

    public function campaignSetting(Request $request)
    {
        return view('backend.campaign.campaignSetting')->with(compact('request'));
    }

    public function datatablesCampaignSetting(Request $request)
    {
        
        $index = Campaign::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-campaign'))
            {
                $html .= '
                    <a href="' . route('backend.campaign.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if(Auth::user()->can('delete-campaign'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-campaign" data-toggle="modal" data-target="#delete-campaign" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables->editColumn('target_jan_to_apr', function ($index) {
            $html = number_format($index->target_jan_to_apr);
            return $html;
        });

        $datatables->editColumn('target_may_to_aug', function ($index) {
            $html = number_format($index->target_may_to_aug);
            return $html;
        });

        $datatables->editColumn('target_sep_to_dec', function ($index) {
            $html = number_format($index->target_sep_to_dec);
            return $html;
        });

        $datatables->editColumn('target_jan_to_aug', function ($index) {
            $html = number_format($index->target_jan_to_aug);
            return $html;
        });

        $datatables->editColumn('target_may_to_dec', function ($index) {
            $html = number_format($index->target_may_to_dec);
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        return view('backend.campaign.create');
    }

    public function store(Request $request)
    {

        $message = [
            'year.required' => 'This field required.',
            'year.integer' => 'This field integer only.',
            'year.unique' => 'This year already exits.',
            'name.required' => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric' => 'This field required.',

        ];

        $validator = Validator::make($request->all(), [
            'year'  => 'required|integer|unique:campaign,year',
			'name'  => 'required',
			'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Campaign;

        $index->year  = $request->year;
		$index->name  = $request->name;
		$index->value = $request->value;

        $index->save();

        return redirect()->route('backend.campaign.edit', ['index' => $index->id])->with('success', 'Data Has Been Added');
    }

    public function edit(Campaign $index)
    {
        return view('backend.campaign.edit')->with(compact('index'));
    }

    public function update(Campaign $index, Request $request)
    {
        $message = [
            'year.required'  => 'This field required.',
            'year.integer'   => 'This field integer only.',
            'year.unique'    => 'This year already exits.',
            'name.required'  => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric'  => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'year'  => 'required|integer|unique:campaign,year,'.$index->id,
            'name'  => 'required',
            'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index->year  = $request->year;
        $index->name  = $request->name;
        $index->value = $request->value;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        Campaign::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-campaign')) {
            Campaign::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }

    

    public function datatablesCampaignDetail(Campaign $index)
    {
        $datatables = Datatables::of($index->campaign_details);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-campaign')) {
                $html .= '
                    <a href="' . route('backend.campaign.editCampaignDetail', ['index' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                ';
            }

            if (Auth::user()->can('delete-campaign')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-campaignDetail" data-toggle="modal" data-target="#delete-campaignDetail" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('start_month', function ($index) {
            $month = ["January", "Febuary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            return $month[$index->start_month - 1];
        });

        $datatables->editColumn('end_month', function ($index) {
            $month = ["January", "Febuary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            return $month[$index->end_month - 1];
        });

        $datatables->editColumn('value', function ($index) {
            return number_format($index->value);
        });

        $datatables->editColumn('for_expo', function ($index) {
            return $index->for_expo ? "FOR EXPO" : 'NO';
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function createCampaignDetail(Campaign $index)
    {
        return view('backend.campaign.detail.create', compact('index'));
    }

    public function storeCampaignDetail(Request $request)
    {
        $message = [
            'name.required'  => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric'  => 'This numeric only.',
        ];

        $validator = Validator::make($request->all(), [
                'name'  => 'required',
                'value' => 'required|numeric',
            ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new CampaignDetail;

        $index->campaign_id = $request->campaign_id;
        $index->name        = $request->name;
        $index->start_month = $request->start_month;
        $index->end_month   = $request->end_month;
        $index->value       = $request->value;
        $index->for_expo    = $request->for_expo ? 1 : 0;

        $index->save();

        return redirect()->route('backend.campaign.edit', ['index' => $index->campaign_id])->with('success', 'Data Has Been Added');;
    }

    public function editCampaignDetail(CampaignDetail $index)
    {
        return view('backend.campaign.detail.edit', compact('index'));
    }

    public function updateCampaignDetail(CampaignDetail $index, Request $request)
    {

        $message = [
            'name.required'  => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric'  => 'This numeric only.',
        ];

        $validator = Validator::make($request->all(), [
                'name'  => 'required',
                'value' => 'required|numeric',
            ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index->name        = $request->name;
        $index->start_month = $request->start_month;
        $index->end_month   = $request->end_month;
        $index->value       = $request->value;
        $index->for_expo    = $request->for_expo ? 1 : 0;

        $index->save();

        return redirect()->route('backend.campaign.edit', ['index' => $index->campaign_id])->with('success', 'Data Has Been Updated');
    }

    public function deleteCampaignDetail(Request $request)
    {
        CampaignDetail::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    // public function dashboard(Request $request)
    // {
    //     $year = Spk::select(DB::raw('YEAR(date) as year'))->orderBy('date', 'ASC')->distinct()->get();

    //     $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
    //         ->select('sales.fullname', 'sales.id')
    //         ->orderBy('sales.fullname', 'ASC')->distinct();

    //     if(!Auth::user()->can('allSales-spk'))
    //     {
    //         $sales->whereIn('sales_id', Auth::user()->staff());
    //     }

    //     $sales = $sales->get();

    //     $f_year   = $this->filter($request->f_year, date('Y'));
        
    //     $campaign = Campaign::firstOrCreate(['year' => $f_year], [
    //         'location_1' => "-",
    //         'location_2' => "-", 
    //         'location_3' => "-", 
    //         'target_jan_to_apr' => 0, 
    //         'target_may_to_aug' => 0, 
    //         'target_sep_to_dec' => 0, 
    //         'target_jan_to_aug' => 0, 
    //         'target_may_to_dec' => 0,
    //     ]);

    //     $sales_target = Spk::select('sales_id')->distinct()->get();
    //     foreach ($sales_target as $list) {
    //         CampaignSales::firstOrCreate(['sales_id' => $list->sales_id, 'year' => $f_year], ['old_sales' => 0]);
    //     };

    //     return view('backend.campaign.index')->with(compact('year', 'sales', 'request', 'campaign'));
    // }

    public function dashboard(Campaign $index, Request $request)
    {
        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allSales-spk'))
        {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();
        $long_month = ['January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        return view('backend.campaign.dashboard', compact('index', 'request', 'sales', 'long_month'));
    }


    public function datatablesDashboard(CampaignDetail $campaign_detail, Request $request)
    {
        $f_sales = $this->filter($request->f_sales);

        $index = User::where(function($query){
                $query->whereIn('position', getConfigValue('sales_position'))
                ->orWhereIn('id', getConfigValue('sales_user'));
            })
            ->select('users.fullname')
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
                    DB::raw('COALESCE(SUM(real_omset_'.$i.'.totalRealOmset), 0) AS real_omset_'.$i.''),
                    DB::raw('real_omset_'.$i.'.countProduction as countProduction_'.$i)
                );
        }

        for ($i=0; $i < $delta_month; $i++) {

            $sql_real_omset = '
                (
                    /* sales -> spk */
                    SELECT production.sales_id, production.countProduction, SUM(production.totalHM) AS totalHM, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset, SUM(production.totalLoss) AS totalLoss, COUNT(production.sales_id) AS countSPK
                    FROM
                    (
                        /* spk -> production with realOmset */
                        SELECT
                            production.spk_id, 
                            production.name, 
                            production.sales_id,
                            production.countProduction,
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
                                COUNT(production.id) AS countProduction,
                                SUM(production.hm * production.quantity) AS totalHM,
                                SUM(production.he * production.quantity) AS totalHE,
                                SUM(production.hj * production.quantity) AS totalHJ
                            FROM spk join production ON spk.id = production.spk_id
                            WHERE MONTH(spk.date) = '.($i+1+($campaign_detail->start_month-1)).'
                                AND YEAR(spk.date) = '.$campaign_detail->campaign->year.' AND profitable = 1'.$where.'
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
        else
        {
            $index->whereIn('id', Auth::user()->staff());
        }


        $index = $index->groupBy('users.id')->get();

        $datatables = Datatables::of($index);

        for ($i=0; $i < $delta_month; $i++) {

            $datatables->editColumn('real_omset_'.$i, function ($index) use ($i) {
                // return number_format(eval('$index->real_omset_'.$i.';'));
                return number_format($index->{"real_omset_$i"});
            });

            $datatables->editColumn('countProduction_'.$i, function ($index) use ($i) {
                return number_format($index->{"countProduction_$i"});
            });

            $datatables->addColumn('remain_target_'.$i, function ($index) use ($i, $campaign_detail, $delta_month) {
                return number_format(($campaign_detail->value / $delta_month) - $index->{"real_omset_$i"});
            });

            $datatables->addColumn('percent_'.$i, function ($index) use ($i, $campaign_detail, $delta_month) {
                return number_format(($index->{"real_omset_$i"} / ($campaign_detail->value / $delta_month)) * 100, 2);

            });
        }

        $datatables = $datatables->make(true);
        return $datatables;

    }

    public function campaignSalesSetting(Request $request)
    {
        $year = Spk::select(DB::raw('YEAR(date) as year'))->orderBy('date', 'ASC')->distinct()->get();

        return view('backend.campaign.campaignSalesSetting')->with(compact('request', 'year'));
    }

    public function datatablesCampaignSalesSetting(Request $request)
    {
        $f_year  = $this->filter($request->f_year, date('Y'));

        $index = CampaignSales::where('year', $f_year)
            ->join('users', 'users.id', 'campaign_sales.sales_id')
            ->select('campaign_sales.*', 'users.fullname')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('status', function ($index) {
            $html = '';

            if($index->old_sales)
            {
                $html .= '
                    <span class="label label-default">Tim Tempur</span>
                ';
            }
            else
            {
                $html .= '
                    <span class="label label-info">Super Junior</span>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function actionCampaignSalesSetting(Request $request)
    {
        if(is_array($request->id))
        {
            if ($request->action == 'old' && Auth::user()->can('setting-campaign')) {

                CampaignSales::whereIn('id', $request->id)->update(['old_sales' => 1]);

                return redirect()->back()->with('success', 'Data Has Been Updated');
            }
            else if ($request->action == 'new' && Auth::user()->can('setting-campaign')) {

                CampaignSales::whereIn('id', $request->id)->update(['old_sales' => 0]);

                return redirect()->back()->with('success', 'Data Has Been Updated');
            }

            return redirect()->back()->with('failed', 'Access Denied');
        }

        return redirect()->back()->with('failed', 'Nothing Changed');
    }


}
