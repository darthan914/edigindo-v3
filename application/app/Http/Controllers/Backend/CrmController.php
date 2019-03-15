<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Config;
use App\Models\Crm;
use App\Models\CrmDetail;

use App\Company;
use App\Brand;
use App\Address;
use App\Pic;

use App\Notifications\Notif;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;
use Mail;

class CrmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['feedback', 'storeFeedback']]);
    }

    public function index(Request $request)
    {
        $year = Crm::select(DB::raw('YEAR(created_at) as year'))->orderBy('created_at', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $user = Crm::join('users', 'crm.sales_id', 'users.id')
            ->select('users.id', 'users.fullname')
            ->orderBy('users.fullname', 'ASC')
            ->distinct();

        if (!Auth::user()->can('allSales-crm')) {
            $user->whereIn('crm.sales_id', Auth::user()->staff());
        }

        $company = Crm::join('company', 'crm.company_id', 'company.id')
            ->join('brand', 'crm.brand_id', 'brand.id')
            ->select('company.id AS company_id', 'company.name AS company_name', 'brand.id AS brand_id', 'brand.brand AS brand_name')
            ->orderBy('company.name', 'ASC')
            ->distinct()->get();

        $company2 = Company::all();

        $time     = ['LAST_DAY' => 'Last Day', 'LAST_MONTH' => 'Last Month', 'LAST_YEAR' => 'Last Year'];
        $omset    = ['LESS' => 'Less than 500 Million', 'MORE' => 'More than 500 Million'];
        $activity = ['PRESENTATION' => 'Presentation', 'FOLLOWUP' => 'Follow Up', 'SAMPLE' => 'Sample / Teaser', 'QUOTATION' => 'Quotation', 'PO' => 'PO'];

        return view('backend.crm.index', compact('request', 'year', 'month', 'user', 'company', 'company2','time', 'omset', 'activity'));
    }

    public function datatables(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $f_year     = $this->filter($request->f_year, date('Y'));
        $f_month    = $this->filter($request->f_month);
        $f_sales    = $this->filter($request->f_sales, Auth::id());
        $f_company  = $this->filter($request->f_company);
        $f_time     = $this->filter($request->f_time);
        $f_omset    = $this->filter($request->f_omset);
        $f_activity = $this->filter($request->f_activity);

        $f_id     = $this->filter($request->f_id);
        $s_no_crm = $this->filter($request->s_no_crm);

        $f_type = $this->filter($request->f_type);

        $sql_crm_detail = "(
        	SELECT `crm_id`, MAX(`datetime_activity`) AS `datetime_activity`
        	FROM `crm_detail`
        	GROUP BY `crm_id`
        	ORDER BY `datetime_activity` DESC
        ) AS `least_crm_detail`";

        /* production */
        {
            $sql_production = '
				(
					/* sales -> spk */
					SELECT production.company_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset, SUM(production.totalLoss) AS totalLoss
					FROM
					(
						/* spk -> production with realOmset */
						SELECT
							production.spk_id,
							production.name,
							production.company_id,
							(@totalHM := production.totalHM) as totalHM,
							(@totalHJ := production.totalHJ) as totalHJ,
							@profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                            @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
							(@realOmset := CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset,
							(@totalHJ - @realOmset) as totalLoss
						FROM
						(
							/* spk -> production */
							SELECT
								spk.id AS spk_id,
								spk.company_id, spk.name,
								SUM(production.hm * production.quantity) AS totalHM,
				            	SUM(production.he * production.quantity) AS totalHE,
				            	SUM(production.hj * production.quantity) AS totalHJ
							FROM spk join production ON spk.id = production.spk_id
                            WHERE profitable = 1';

            if ($f_year) {
                $sql_production .= '
						AND YEAR(spk.date) = ' . $f_year . '
				';
            }

            if ($f_month) {
                $sql_production .= '
						AND MONTH(spk.date) = ' . $f_month . '
				';
            }

            $sql_production .= '
							GROUP BY spk.id
						) production
					) production
					GROUP BY production.company_id
				) production';
        }

        $index = Crm::leftJoin('users as sales', 'crm.sales_id', 'sales.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm.pic_id', 'pic.id')
            ->join(DB::raw($sql_crm_detail), 'least_crm_detail.crm_id', 'crm.id')
            ->join('crm_detail', function ($join) {
            	$join->on('crm_detail.crm_id', '=','crm.id')
            	->on('crm_detail.datetime_activity', '=','least_crm_detail.datetime_activity');
            })
            ->leftJoin(DB::raw($sql_production), 'crm.company_id', 'production.company_id')
            ->select(
                'crm.*',
                'sales.fullname as sales_fullname',
                DB::raw('COALESCE(pic.fullname, crm.pic_fullname_prospec) as pic_fullname'),
                DB::raw('COALESCE(company.name, crm.company_name_prospec) as company_name'),
                DB::raw('COALESCE(brand.brand, crm.brand_prospec) as brand_name'),
                'crm_detail.id as crm_detail_id',
                'crm_detail.activity',
                'crm_detail.datetime_activity'
            );


        if ($f_type != '') {
            $index->where('crm.type', $f_type);
        }

        if ($s_no_crm != '' || $f_id != '') {
            if ($s_no_crm != '') {
                $index->where('crm.no_crm', 'like', '%' . $s_no_crm . '%');
            }

            if ($f_id != '') {
                $index->where('crm.id', $f_id);
            }

        } else {
            if ($f_month != '') {
                $index->whereMonth('crm.created_at', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('crm.created_at', $f_year);
            }

            if ($f_sales == 'staff') {
                $index->whereIn('crm.sales_id', Auth::user()->staff());
            } else if ($f_sales != '') {
                $index->where('crm.sales_id', $f_sales);
            }

            if ($f_company != '') {
                $data = explode(', ', $f_company);

                $index->where('crm.company_id', $data[0]);

                if ($data[1] != '') {
                    $index->where('crm.brand_id', $data[1]);
                }
            }

            if ($f_time != '') {
                switch ($f_time) {
                    case 'LAST_DAY':
                        $index->whereDate('crm_detail.datetime_activity', '<', date('Y-m-d'));
                        break;

                    case 'LAST_MONTH':
                        $index->whereDate('crm_detail.datetime_activity', '<', date('Y-m-d', strtotime('-1 month')));
                        break;

                    case 'LAST_YEAR':
                        $index->whereDate('crm_detail.datetime_activity', '<', date('Y-m-d', strtotime('-1 year')));
                        break;

                    default:
                        break;
                }
            }

            if ($f_activity != '') {
                $index->where('crm_detail.activity', $f_activity);
            }

            if ($f_omset != '') {
                switch ($f_omset) {
                    case 'MORE':
                        $index->where('production.totalHJ', '>=', 500000000);
                        break;

                    case 'LESS':
                        $index->where('production.totalHJ', '<=', 500000000);
                        break;

                    default:
                        break;
                }
            }
        }

        $index = $index->orderBy('crm.id', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $html .= ' <a href="' . route('backend.crm.calendar', ['id' => $index->id]) . '" class="btn btn-xs btn-info"><i class="fa fa-calendar"></i></a>';

            if (Auth::user()->can('edit-crm') && ($this->usergrant($index->sales_id, 'allSales-crm') || $this->levelgrant($index->sales_id))) {

                if($index->type == 'CLIENT')
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-warning edit-crm" data-toggle="modal" data-target="#edit-crm"
                            data-id="' . $index->id . '"
                            data-sales_id="' . $index->sales_id . '"
                            data-company_id="' . $index->company_id . '"
                            data-pic_id="' . $index->pic_id . '"
                        ><i class="fa fa-edit" aria-hidden="true"></i></button>
                    ';
                }
                else
                {
                    $html .= ' <a href="' . route('backend.crm.editProspec', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>';
                }
                
            }

            if (Auth::user()->can('delete-crm') && ($this->usergrant($index->sales_id, 'allSales-crm') || $this->levelgrant($index->sales_id))) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-crm" data-toggle="modal" data-target="#delete-crm" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('activity', function ($index) {

            $html = $index->activity . "<br/>" . date('d M Y H:i', strtotime($index->datetime_activity));
            return $html;
        });

        $datatables->addColumn('client', function ($index) {
            $html = 'Company : ' . $index->company_name . '<br/>';
            $html .= 'Brand : ' . $index->brand_name . '<br/>';
            $html .= 'PIC : ' . $index->pic_fullname . '<br/>';
            $html .= 'Type : ' . $index->type;
            

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if ($this->usergrant($index->sales_id, 'allSales-crm') || $this->levelgrant($index->sales_id)) {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }



    public function store(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'company_id.required'        => 'This field required.',
            'pic_id.required'            => 'This field required.',
            'date_activity.required'     => 'This field required.',
            'date_activity.date'         => 'Date Format Only.',
            'time_activity.required'     => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'company_id'        => 'required',
            'pic_id'            => 'required',
            'date_activity'     => 'required|date',
            'time_activity'     => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-crm-error', 'Something Errors');
        }

        $index = new Crm;

        $index->no_crm     = $this->getCrm($request);
        $index->sales_id   = Auth::id();
        $index->company_id = $request->company_id;
        $index->brand_id   = $request->brand_id;
        $index->pic_id     = $request->pic_id;
        $index->address_id = $request->address_id;

        $index->save();

        $detail = new CrmDetail;

        $detail->crm_id            = $index->id;
        $detail->sales_id          = $index->sales_id;
        $detail->pic_id            = $index->pic_id;
        $detail->activity          = 'PRESENTATION';
        $detail->datetime_activity  = $request->date_activity . ' ' . $request->time_activity;

        $detail->save();

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function createProspec(Request $request)
    {
        $company = Company::all();
        $activity = ['PRESENTATION' => 'Presentation', 'FOLLOWUP' => 'Follow Up'];

        return view('backend.crm.createProspec', compact('request', 'company', 'activity'));
    }

    public function storeProspec(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'company_id.required_without' => 'This field required.',
            'pic_id.required_without'     => 'This field required.',
            'activity.required'           => 'This field required.',
            'date_activity.required'      => 'This field required.',
            'date_activity.date'          => 'Date Format Only.',
            'time_activity.required'      => 'This field required.',

            'company_name_prospec.required_without' => 'This field required.',
            'pic_fullname_prospec.required_without' => 'This field required.',
            'pic_gender_prospec.required_with'      => 'This field required.',

        ];

        $validator = Validator::make($request->all(), [
            'company_id'        => 'required_without:company_name_prospec',
            'pic_id'            => 'required_without:pic_fullname_prospec',
            'activity'          => 'required',
            'date_activity'     => 'required|date',
            'time_activity'     => 'required',

            'company_name_prospec' => 'required_without:company_id',
            'pic_fullname_prospec' => 'required_without:pic_id',
            'pic_gender_prospec'   => 'required_with:pic_fullname_prospec',
            'pic_phone_prospec'    => 'required_with:pic_fullname_prospec',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($request->company_id && $request->company_name_prospec) {
                $validator->errors()->add('company_id', 'Only One Can Fill');
                $validator->errors()->add('company_name_prospec', 'Only One Can Fill');
            }

            if ($request->pic_id && $request->pic_fullname_prospec) {
                $validator->errors()->add('pic_id', 'Only One Can Fill');
                $validator->errors()->add('pic_fullname_prospec', 'Only One Can Fill');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-crm-error', 'Something Errors');
        }

        DB::beginTransaction();

        $index = new Crm;

        $index->no_crm     = $this->getCrm($request);
        $index->type       = 'PROSPECT';
        $index->sales_id   = Auth::id();

        $index->company_id = $request->company_id;
        $index->brand_id   = $request->brand_id;
        $index->pic_id     = $request->pic_id;
        $index->address_id = $request->address_id;

        $index->company_name_prospec  = $request->company_name_prospec;
        $index->company_phone_prospec = $request->company_phone_prospec;
        $index->company_fax_prospec   = $request->company_fax_prospec;

        $index->pic_fullname_prospec = $request->pic_fullname_prospec;
        $index->pic_gender_prospec   = $request->pic_gender_prospec;
        $index->pic_position_prospec = $request->pic_position_prospec;
        $index->pic_phone_prospec    = $request->pic_phone_prospec;
        $index->pic_email_prospec    = $request->pic_email_prospec;

        $index->address_prospec = $request->address_prospec;
        $index->brand_prospec   = $request->brand_prospec;

        $index->save();

        $detail = new CrmDetail;

        $detail->crm_id               = $index->id;
        $detail->sales_id             = $index->sales_id;
        $detail->pic_id               = $index->pic_id;
        $detail->pic_fullname_prospec = $index->pic_fullname_prospec;
        $detail->activity             = $request->activity;
        $detail->datetime_activity    = $request->date_activity . ' ' . $request->time_activity;

        $detail->save();

        DB::commit();

        return redirect()->route('backend.crm')->with('success', 'Data Has Been Added');
    }

    public function update(Request $request)
    {
        $index = Crm::find($request->id);

        if (!$this->usergrant($index->sales_id, 'allSales-crm') || !$this->levelgrant($index->sales_id)) {
            return redirect()->route('backend.crm')->with('failed', 'Access Denied');
        }

    	$config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'sales_id.required' => 'This field required.',
            'pic_id.required'   => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'sales_id' => 'required',
            'pic_id'   => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-crm-error', 'Something Errors');
        }

        $this->saveArchive('App\Models\Crm', 'UDPATED', $index);

        $index->no_crm     = $this->getCrm($request);
        $index->sales_id   = $request->sales_id;
        $index->pic_id     = $request->pic_id;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function editProspec($id, Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }
        
        $index    = Crm::find($id);
        $sales    = User::whereIn('position', explode(', ', $sales_position->value))->orWhereIn('id', explode(', ', $sales_user->value))->where('active', 1)->get();

        return view('backend.crm.editProspec', compact('request', 'company', 'activity', 'index', 'sales'));
    }

    public function updateProspec($id, Request $request)
    {
        DB::beginTransaction();

        $index = Crm::find($id);

        if (!$this->usergrant($index->sales_id, 'allSales-crm') || !$this->levelgrant($index->sales_id)) {
            return redirect()->route('backend.crm')->with('failed', 'Access Denied');
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'sales_id.required'       => 'This field required.',
            'pic_id.required_without' => 'This field required.',

            'pic_fullname_prospec.required_without' => 'This field required.',
            'pic_gender_prospec.required_with'      => 'This field required.',
            'pic_phone_prospec.required_with'       => 'This field required.',

        ];

        $validator = Validator::make($request->all(), [
            'sales_id' => 'required',
            'pic_id'   => 'required_without:pic_fullname_prospec',

            'pic_fullname_prospec' => 'required_without:pic_id',
            'pic_gender_prospec'   => 'required_with:pic_fullname_prospec',
            'pic_phone_prospec'    => 'required_with:pic_phone_prospec',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($request->company_id && $request->company_name_prospec) {
                $validator->errors()->add('company_id', 'Only One Can Fill');
                $validator->errors()->add('company_name_prospec', 'Only One Can Fill');
            }

            if ($request->pic_id && $request->pic_fullname_prospec) {
                $validator->errors()->add('pic_id', 'Only One Can Fill');
                $validator->errors()->add('pic_fullname_prospec', 'Only One Can Fill');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-crm-error', 'Something Errors');
        }

        $index->sales_id   = $request->sales_id;

        $index->pic_id     = $request->pic_id;

        $index->pic_fullname_prospec = $request->pic_fullname_prospec;
        $index->pic_gender_prospec   = $request->pic_gender_prospec;
        $index->pic_position_prospec = $request->pic_position_prospec;
        $index->pic_phone_prospec    = $request->pic_phone_prospec;
        $index->pic_email_prospec    = $request->pic_email_prospec;
        $index->save();

        DB::commit();

        return redirect()->route('backend.crm')->with('success', 'Data Has Been Updated');
    }

    public function next(Request $request)
    {
        $crm = Crm::find($request->id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            return redirect()->route('backend.crm')->with('failed', 'Access Denied');
        }

    	$config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'activity.required' => 'This field required.',
            'date_activity.required'   => 'This field required.',
            'date_activity.date'       => 'Date Format Only.',
            'time_activity.required'   => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'activity'          => 'required',
            'date_activity' => 'required|date',
            'time_activity' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('next-crm-error', 'Something Errors');
        }

        if($request->activity == 'QUOTATION' && $crm->type == 'PROSPECT')
        {
            $this->saveArchive('App\Models\Crm', 'UDPATED', $crm);

            $crm->type == 'CLIENT';

            if($crm->company_id == null)
            {
                $company = new Company;

                $company->name  = $crm->company_name_prospec;
                $company->phone = $crm->company_phone_prospec;
                $company->fax   = $crm->company_fax_prospec;
                $company->save();

                $crm->company_name_prospec  = null;
                $crm->company_phone_prospec = null;
                $crm->company_fax_prospec   = null;
                $crm->company_id            = $company->id;

                if($crm->brand_id == null)
                {
                    if($crm->brand_prospec)
                    {
                        $brand = new Brand;

                        $brand->company_id = $company->id;
                        $brand->brand      = $crm->brand_prospec;
                        $brand->save();

                        $crm->brand_prospec = null;
                        $crm->brand_id      = $brand->id;
                    }
                    
                }

                if($crm->address_id == null)
                {
                    if($crm->address_prospec)
                    {
                        $address = new Address;

                        $address->company_id = $company->id;
                        $address->address    = $crm->address_prospec;
                        $address->save();

                        $crm->address_prospec = null;
                        $crm->address_id      = $address->id;
                    }
                        
                }

                if($crm->pic_id == null)
                {
                    $pic = new Pic;

                    $pic->company_id = $company->id;
                    $pic->fullname   = $crm->pic_fullname_prospec;
                    $pic->gender     = $crm->pic_gender_prospec;
                    $pic->position   = $crm->pic_position_prospec;
                    $pic->phone      = $crm->pic_phone_prospec;
                    $pic->email      = $crm->pic_email_prospec;
                    $pic->save();

                    $crm->pic_fullname_prospec = null;
                    $crm->pic_gender_prospec   = null;
                    $crm->pic_position_prospec = null;
                    $crm->pic_phone_prospec    = null;
                    $crm->pic_email_prospec    = null;
                    $crm->pic_id               = $pic->id;
                }
            }
            else
            {
                if($crm->brand_id == null)
                {
                    if($crm->brand_prospec)
                    {
                        $brand = new Brand;

                        $brand->company_id = $crm->company_id;
                        $brand->brand      = $crm->brand_prospec;
                        $brand->save();

                        $crm->brand_prospec = null;
                        $crm->brand_id      = $brand->id;
                    }
                    
                }

                if($crm->address_id == null)
                {
                    if($crm->address_prospec)
                    {
                        $address = new Address;

                        $address->company_id = $crm->company_id;
                        $address->address    = $crm->address_prospec;
                        $address->save();

                        $crm->address_prospec = null;
                        $crm->address_id      = $address->id;
                    }
                        
                }

                if($crm->pic_id == null)
                {
                    $pic = new Pic;

                    $pic->company_id = $crm->company_id;
                    $pic->fullname   = $crm->pic_fullname_prospec;
                    $pic->gender     = $crm->pic_gender_prospec;
                    $pic->position   = $crm->pic_position_prospec;
                    $pic->phone      = $crm->pic_phone_prospec;
                    $pic->email      = $crm->pic_email_prospec;
                    $pic->save();

                    $crm->pic_fullname_prospec = null;
                    $crm->pic_gender_prospec   = null;
                    $crm->pic_position_prospec = null;
                    $crm->pic_phone_prospec    = null;
                    $crm->pic_email_prospec    = null;
                    $crm->pic_id               = $pic->id;
                }
            }

            $crm->save();
        }

        $index = new CrmDetail;

        $index->crm_id            = $crm->id;
        $index->sales_id          = $crm->sales_id;
        $index->pic_id            = $crm->pic_id;
        $index->activity          = $request->activity;
        $index->datetime_activity = $request->date_activity . ' ' . $request->time_activity;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function reschedule(Request $request)
    {
        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            return redirect()->route('backend.crm')->with('failed', 'Access Denied');
        }

    	$config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'activity.required' => 'This field required.',
            'date_activity.required'   => 'This field required.',
            'date_activity.date'       => 'Date Format Only.',
            'time_activity.required'   => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'activity'          => 'required',
            'date_activity' => 'required|date',
            'time_activity' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('reschedule-crm-error', 'Something Errors');
        }

        if($request->activity == 'QUOTATION' && $crm->type == 'PROSPECT')
        {
            $this->saveArchive('App\Models\Crm', 'UDPATED', $crm);

            $crm->type == 'CLIENT';

            if($crm->company_id == null)
            {
                $company->name  = $crm->company_name_prospec;
                $company->phone = $crm->company_phone_prospec;
                $company->fax   = $crm->company_fax_prospec;
                $company->save();

                $crm->company_name_prospec  = null;
                $crm->company_phone_prospec = null;
                $crm->company_fax_prospec   = null;
                $crm->company_id            = $company->id;

                if($crm->brand_id == null)
                {
                    if($crm->brand_prospec)
                    {
                        $brand = new Brand;

                        $brand->company_id = $company->id;
                        $brand->brand      = $crm->brand_prospec;
                        $brand->save();

                        $crm->brand_prospec = null;
                        $crm->brand_id      = $brand->id;
                    }
                    
                }

                if($crm->address_id == null)
                {
                    if($crm->address_prospec)
                    {
                        $address = new Address;

                        $address->company_id = $company->id;
                        $address->address    = $crm->address_prospec;
                        $address->save();

                        $crm->address_prospec = null;
                        $crm->address_id      = $address->id;
                    }
                        
                }

                if($crm->pic_id == null)
                {
                    $pic = new Pic;

                    $pic->company_id = $company->id;
                    $pic->fullname   = $crm->pic_fullname_prospec;
                    $pic->gender     = $crm->pic_gender_prospec;
                    $pic->position   = $crm->pic_position_prospec;
                    $pic->phone      = $crm->pic_phone_prospec;
                    $pic->email      = $crm->pic_email_prospec;
                    $pic->save();

                    $crm->pic_fullname_prospec = null;
                    $crm->pic_gender_prospec   = null;
                    $crm->pic_position_prospec = null;
                    $crm->pic_phone_prospec    = null;
                    $crm->pic_email_prospec    = null;
                    $crm->pic_id               = $pic->id;
                }
            }
            else
            {
                if($crm->brand_id == null)
                {
                    if($crm->brand_prospec)
                    {
                        $brand = new Brand;

                        $brand->company_id = $crm->company_id;
                        $brand->brand      = $crm->brand_prospec;
                        $brand->save();

                        $crm->brand_prospec = null;
                        $crm->brand_id      = $brand->id;
                    }
                    
                }

                if($crm->address_id == null)
                {
                    if($crm->address_prospec)
                    {
                        $address = new Address;

                        $address->company_id = $crm->company_id;
                        $address->address    = $crm->address_prospec;
                        $address->save();

                        $crm->address_prospec = null;
                        $crm->address_id      = $address->id;
                    }
                        
                }

                if($crm->pic_id == null)
                {
                    $pic = new Pic;

                    $pic->company_id = $crm->company_id;
                    $pic->fullname   = $crm->pic_fullname_prospec;
                    $pic->gender     = $crm->pic_gender_prospec;
                    $pic->position   = $crm->pic_position_prospec;
                    $pic->phone      = $crm->pic_phone_prospec;
                    $pic->email      = $crm->pic_email_prospec;
                    $pic->save();

                    $crm->pic_fullname_prospec = null;
                    $crm->pic_gender_prospec   = null;
                    $crm->pic_position_prospec = null;
                    $crm->pic_phone_prospec    = null;
                    $crm->pic_email_prospec    = null;
                    $crm->pic_id               = $pic->id;
                }
            }

            $crm->save();
        }

        $this->saveArchive('App\Models\CrmDetail', 'UDPATED', $index);

        $index->sales_id          = $crm->sales_id;
        $index->pic_id            = $crm->pic_id;
        $index->activity          = $request->activity;
        $index->datetime_activity = $request->date_activity . ' ' . $request->time_activity;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Crm::find($request->id);

        if (!$this->usergrant($index->sales_id, 'allSales-crm') || !$this->levelgrant($index->sales_id)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\Crm', 'DELETED', $index);
        Crm::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if (is_array($request->id)) {
            foreach ($request->id as $list) {

                $index = Crm::find($list);

                if (($this->usergrant($index->sales_id, 'allSales-crm') || $this->levelgrant($index->sales_id))) {
                    $id[] = $list;
                }
            }

            if ($request->action == 'delete' && Auth::user()->can('delete-crm')) {
                $index = Crm::find($id);
                $this->saveMultipleArchive('App\Models\Crm', 'DELETED', $index);
                
                Crm::destroy($id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function calendar($id, Request $request)
    {
        $index = Crm::find($id);

        $crm_detail = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm_detail.pic_id', 'pic.id')
            ->leftJoin('users as sales', 'crm_detail.sales_id', 'sales.id')
            ->select(
                'crm_detail.*',
                'sales.fullname as sales_fullname',
                DB::raw('COALESCE(`crm_detail`.`feedback_email`, `pic`.`email`, `crm`.`pic_email_prospec`) AS feedback_email'),
                DB::raw('COALESCE(`crm_detail`.`feedback_phone`, `pic`.`phone`, `crm`.`pic_phone_prospec`) AS feedback_phone'),
                DB::raw('DATE_FORMAT(crm_detail.datetime_activity, "%Y-%m-%dT%H:%i:%s") as datetime_activity_iso'),
                DB::raw('COALESCE(pic.fullname, crm.pic_fullname_prospec) as pic_fullname'),
                DB::raw('COALESCE(company.name, crm.company_name_prospec) as company_name'),
                DB::raw('COALESCE(brand.brand, crm.brand_prospec) as brand_name')
            )
            ->where('crm.id', $id)
            ->orderBy('crm_detail.datetime_activity', 'DESC')
            ->get();

        $activity  = ['PRESENTATION' => 'Presentation', 'FOLLOWUP' => 'Follow Up', 'SAMPLE' => 'Sample', 'QUOTATION' => 'Quotation', 'PO' => 'PO'];

    	return view('backend.crm.calendar')->with(compact('index', 'crm_detail', 'activity', 'request'));
    }

    public function ajaxCalendar(Request $request)
    {
        $index = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm_detail.pic_id', 'pic.id')
            ->leftJoin('users as sales', 'crm_detail.sales_id', 'sales.id')
            ->select(
                'crm_detail.*',
                'sales.fullname as sales_fullname',
                'pic.fullname as pic_fullname',
                'company.name as company_name',
                'brand.brand as brand_name'
            )
            ->where('crm.id', $request->id);

        $index = $index->get();

        $event = '';
        $activity  = ['PRESENTATION' => 'Presentation', 'FOLLOWUP' => 'Follow Up', 'SAMPLE' => 'Sample', 'QUOTATION' => 'Quotation', 'PO' => 'PO'];
        $current_sales_id = 0;
        $color = ['#0080FF', '#7285A5'];
        $colorSwap = 0;

        // return $status;

        foreach ($index as $list) {
        	if($current_sales_id != $list->sales_id)
        	{
        		$colorSwap = ($colorSwap + 1) % 2;
        		$current_sales_id = $list->sales_id;
        	}

            $event [] = [
            	"title"             => $activity[$list->activity],
                "sales_fullname"    => $list->sales_fullname,
                "pic_fullname"      => $list->pic_fullname,
                "datetime_activity" => date('d F Y', strtotime($list->datetime_activity)),
                "activity"          => $activity[$list->activity],
                "start"             => date('Y-m-d', strtotime($list->datetime_activity)),
                "end"               => date('Y-m-d', strtotime($list->datetime_activity)),
                "color"             => $color[$colorSwap],
                "textColor"         => '#FFFFFF',
            ];
        }

        return $event;
    }

    public function getCrm(Request $request)
    {
        $date = date('Y-m-d');
        if (isset($request->date) && $request->date != '') {
            $date = $request->date;
        }

        $sales_id = Auth::id();
        if (isset($request->sales_id) && $request->sales_id != '') {
            $sales_id = $request->sales_id;
        }

        // return $sales_id;
        $user = User::where('id', $sales_id)->first();

        $crm = Crm::select('no_crm')
            ->where('no_crm', 'like', date('ym', strtotime($date)) . str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 2, '0', STR_PAD_LEFT) . "%")
            ->orderBy('no_crm', 'desc');

        $count = $crm->count();
        $year  = $crm->first();

        if ($count == 0) {
            $numberCrm = 0;
        } else {
            $numberCrm = intval(substr($year->no_crm, -4, 4));
        }

        return date('ym', strtotime($date)) . str_pad($user->no_ae == 0 ? $user->id : $user->no_ae, 2, '0', STR_PAD_LEFT) . str_pad($numberCrm + 1, 4, '0', STR_PAD_LEFT);
    }


    public function checkIn(Request $request)
    {
        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('App\Models\CrmDetail', 'CHECK_IN', $index);

        $index->datetime_check_in = $index->datetime_check_in ? null : date('Y-m-d H:i:s');
        $index->latitude_check_in = $request->latitude_check_in;
        $index->longitude_check_in = $request->longitude_check_in;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function checkOut(Request $request)
    {
        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('App\Models\CrmDetail', 'CHECK_OUT', $index);

        $index->datetime_check_out = date('Y-m-d H:i:s');
        $index->latitude_check_out = $request->latitude_check_out;
        $index->longitude_check_out = $request->longitude_check_out;
        

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function sendFeedbackByEmail(Request $request)
    {
        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        $pic = Pic::find($index->pic_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id) || $index->rating != null) {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $message = [
            'feedback_email.required' => 'This field required.',
            'feedback_email.email'   => 'Email format only.',
        ];

        $validator = Validator::make($request->all(), [
            'feedback_email'          => 'required|email',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('sendEmail-crm-error', 'Something Errors');
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('App\Models\CrmDetail', 'SEND_FEEDBACK', $index);

        $token = str_random(30);

        $index->feedback_email = $request->feedback_email;
        $index->feedback_phone = null;
        $index->feedback_token = $token;

        $index->save();

        Mail::send('email.crmFeedback', compact('index', 'pic', 'crm'), function ($message) use ($index) {
            $message->to($index->feedback_email)->subject('Feedback From Meeting');
        });

        return redirect()->back()->with('success', 'Feedback Has Been Sended');
    }

    public function sendFeedbackByWhatsapp(Request $request)
    {
        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        $pic = Pic::find($index->pic_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id) || $index->rating != null) {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $message = [
            'feedback_phone.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'feedback_phone'          => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('sendWhatsapp-crm-error', 'Something Errors');
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('App\Models\CrmDetail', 'SEND_FEEDBACK', $index);

        $token = str_random(30);

        $index->feedback_email = null;
        $index->feedback_phone = $this->whatsappPhone($request->feedback_phone);
        $index->feedback_token = $token;

        $index->save();


        $waText = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm_detail.pic_id', 'pic.id')
            ->leftJoin('users as sales', 'crm_detail.sales_id', 'sales.id')
            ->select(
                'crm_detail.*',
                'crm.pic_gender_prospec',
                'crm.pic_fullname_prospec',
                'sales.fullname as sales_fullname',
                'pic.fullname as pic_fullname',
                'company.name as company_name',
                'brand.brand as brand_name'
            )
            ->where('crm_detail.id', $request->id)
            ->first();


        $text = "
            Kepada Yth,";

        if(strtoupper($waText->gender ?? $waText->pic_gender_prospec) == 'M')
        {
            $text .= "
            Bapak " . $waText->pic_fullname ?? $waText->pic_fullname_prospec;
        }
        else if(strtoupper($waText->gender ?? $waText->pic_gender_prospec) == 'F')
        {
            $text .= "
                Ibu " . $waText->pic_fullname ?? $waText->pic_fullname_prospec;
        }
        else
        {
            $text .= "
                Bapak/Ibu" . $waText->pic_fullname ?? $waText->pic_fullname_prospec;
        }
        
        $text .= "
            Terima kasih atas kesediaan anda untuk meeting bersama kami, kami mohon untuk mengisi form kami, buka link ini ".  route('backend.crm.feedback', ["token" => $waText->feedback_token]) ."

            Dengan feedback anda, kami akan meningkatkan kualitas kami di pertemuan yang akan datang.

            Dengan demikan terima kasih atas waktu anda.
        ";


        return redirect()->away("https://wa.me/". $this->whatsappPhone($waText->feedback_phone) . "?text=" .urlencode($text));
    }

    public function feedback(Request $request)
    {

        $index = CrmDetail::where('feedback_token', $request->token)->whereNotNull('feedback_token')->first();

        $sales = '';
        $crm = '';

        if($index)
        {
            $sales = User::find($index->sales_id);
            $crm = Crm::find($index->crm_id);
        }

        return view('backend.crm.feedback', compact('crm', 'index', 'sales', 'request'));
    }

    public function storeFeedback(Request $request)
    {
        $index = CrmDetail::where('feedback_token', $request->token)->first();

        if($index)
        {
            $crm_detail = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm_detail.pic_id', 'pic.id')
            ->leftJoin('users as sales', 'crm_detail.sales_id', 'sales.id')
            ->select(
                'crm_detail.*',
                'sales.fullname as sales_fullname',
                'pic.fullname as pic_fullname',
                'company.name as company_name',
                'brand.brand as brand_name'
            )
            ->where('crm_detail.id', $index->id)
            ->first();

            $activity  = ['PRESENTATION' => 'Presentation', 'FOLLOWUP' => 'Follow Up', 'SAMPLE' => 'Sample', 'QUOTATION' => 'Quotation', 'PO' => 'PO'];

            $message = [
                'rating.required' => 'This field required.',
                'rating.not_in'   => 'This field required.',
            ];

            $validator = Validator::make($request->all(), [
                'rating'         => 'required|not_in:0',
            ], $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $index->feedback_token     = null;
            $index->rating             = $request->rating;
            $index->comment            = $request->comment;
            $index->option_performance = $request->option_performance ? implode('|', $request->option_performance) : '';
            $index->recommendation     = $request->recommendation;
            $index->recommendation_yes = $request->recommendation == 1 ? $request->recommendation_yes : null ;
            $index->recommendation_no  = $request->recommendation == 0 ? $request->recommendation_no : null ;

            $index->save();

            $user = User::find($index->sales_id);

            $html = '
                Your Meeting has been review : ' . $activity[$crm_detail->activity] . ' <br/>
                Datetime : ' . date('d M Y H:i' ,strtotime($crm_detail->datetime_activity)) . ' <br/>
            ';

            $user->notify(new Notif($crm_detail->company_name . ' - ' . $crm_detail->brand_name, $html, route('backend.crm.calendar', ['id' => $crm_detail->crm_id])));
        }
        

        return redirect()->back();
    }
}
