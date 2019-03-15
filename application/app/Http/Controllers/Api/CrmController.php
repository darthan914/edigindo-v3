<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Config;
use App\Models\Crm;
use App\Models\CrmDetail;

use App\Pic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Notifications\Notif;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Mail;

class CrmController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['waFeedback']]);
    }

    public function collection()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $crm = Crm::leftJoin('company', 'company.id', '=', 'crm.company_id')
	        ->leftJoin('brand', 'brand.id', '=', 'crm.brand_id')
	        ->leftJoin('pic', 'pic.id', '=', 'crm.pic_id')
            ->select(
            	'crm.id',
            	'crm.no_crm',
                DB::raw('COALESCE(company.name, crm.company_name_prospec) as company_name'),
                DB::raw('COALESCE(brand.brand, crm.brand_prospec) as brand_name'),
            	DB::raw('COALESCE(pic.fullname, crm.pic_fullname_prospec) as pic_fullname')
            )
            ->orderBy('company.name', 'ASC')
            ->where('crm.sales_id', Auth::id())->get();

        $company = Crm::join('company', 'company.id', '=', 'crm.company_id')
            ->select('company.name', 'company.id')
            ->orderBy('company.name', 'ASC')->distinct()
            ->where('crm.sales_id', Auth::id())->get();

        $sort_crm_detail = $this->arrayToJson([
            ['value' => 'crm_detail.id', 'name' => 'Created'],
            ['value' => 'crm_detail.activity', 'name' => 'Activity'],
        ]);

        $sort_crm = $this->arrayToJson([
            ['value' => 'crm.id', 'name' => 'Created'],
            ['value' => 'crm.company_name', 'name' => 'Company'],
        ]);

        $activity  = $this->arrayToJson([
        	['value' => 'PRESENTATION', 'name' => 'Presentation'], 
        	['value' => 'FOLLOWUP', 'name' => 'Follow Up'], 
        	['value' => 'SAMPLE', 'name' => 'Sample'], 
        	['value' => 'QUOTATION', 'name' => 'Quotation'], 
        	['value' => 'PO', 'name' => 'PO'],
        ]);

        $activity_new  = $this->arrayToJson([
            ['value' => 'PRESENTATION', 'name' => 'Presentation'], 
            ['value' => 'FOLLOWUP', 'name' => 'Follow Up'], 
        ]);

        $status2  = $this->arrayToJson([
            ['value' => 'NOT_FINISHED', 'name' => 'Not Finish'], 
        	['value' => 'CHECK_IN_NULL', 'name' => 'Planing'], 
        	['value' => 'CHECK_OUT_NULL', 'name' => 'Meeting'], 
        	['value' => 'FEEDBACK_NULL', 'name' => 'Waiting Feedback'], 
        	['value' => 'FINISHED', 'name' => 'Finished'], 
        ]);

        $type  = $this->arrayToJson([
            ['value' => 'CLIENT', 'name' => 'Client'], 
            ['value' => 'PROSPECT', 'name' => 'Prospect'], 
        ]);

        $data = compact('crm', 'company', 'status2', 'activity', 'activity_new', 'sort', 'sort_crm_detail','type');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function notifications()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $schedule = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm_detail.pic_id', 'pic.id')
            ->leftJoin('users as sales', 'crm_detail.sales_id', 'sales.id')
            ->select(
                'crm_detail.*',
                'sales.fullname as sales_fullname',
                DB::raw('DATE_FORMAT(crm_detail.datetime_activity, "%Y-%m-%dT%H:%i:%s") as datetime_activity_iso'),
                DB::raw('COALESCE(pic.fullname, crm.pic_fullname_prospec) as pic_fullname'),
                DB::raw('COALESCE(company.name, crm.company_name_prospec) as company_name'),
                DB::raw('COALESCE(brand.brand, crm.brand_prospec) as brand_name')
            )
            ->where('crm_detail.sales_id', Auth::id())->whereDate('crm_detail.datetime_activity', '>=', date('Y-m-d'))->latest()->take(100)->get();

        $data = compact('schedule');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function index(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $limit  = $this->filter($request->limit, 20);

        $f_company  = $this->filter($request->f_company);
        $f_type = $this->filter($request->f_type);

        $f_id     = $this->filter($request->f_id);
        $s_no_crm = $this->filter($request->s_no_crm);
        $s_company_prospec = $this->filter($request->s_company_prospec);


        $sort  = $this->filter($request->sort, 'id');
        $order = $this->filter($request->order, 'DESC');

        $index = Crm::leftJoin('users as sales', 'crm.sales_id', 'sales.id')
            ->leftJoin('company', 'crm.company_id', 'company.id')
            ->leftJoin('brand', 'crm.brand_id', 'brand.id')
            ->leftJoin('pic', 'crm.pic_id', 'pic.id')
            ->select(
                'crm.*',
                'sales.fullname as sales_fullname',
                DB::raw('COALESCE(pic.fullname, crm.pic_fullname_prospec) as pic_fullname'),
                DB::raw('COALESCE(company.name, crm.company_name_prospec) as company_name'),
                DB::raw('COALESCE(brand.brand, crm.brand_prospec) as brand_name')
            )
            ->where('crm.sales_id', Auth::id());

        
        if ($f_type != '') {
            $index->where('crm.type', $f_type);
        }

        if ($s_no_crm != '' || $f_id != '' || $s_company_prospec) {
            if ($s_no_crm != '') {
                $index->where('crm.no_crm', 'like', '%' . $s_no_crm . '%');
            }

            if ($s_company_prospec != '') {
                $index->where('crm.company_name_prospec', 'like', '%' . $s_company_prospec . '%');
            }

            if ($f_id != '') {
                $index->where('crm.id', $f_id);
            }

        } else {

            
            if ($f_company != '') {
                $index->where('crm.company_id', $f_company);
            }
        }

        $index = $index->orderBy($sort, $order)->paginate($limit);

        $data = compact('index');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function schedule(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $limit  = $this->filter($request->limit, 20);

        $f_id       = $this->filter($request->f_id);
        $f_company  = $this->filter($request->f_company);
        $f_activity = $this->filter($request->f_activity);
        $f_status   = $this->filter($request->f_status);

        $sort  = $this->filter($request->sort, 'id');
        $order = $this->filter($request->order, 'DESC');

        $index = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
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
            ->where('crm_detail.sales_id', Auth::id());

        
        if ($f_id != '') {
            $index->where('crm_detail.id', $f_id);
        } 
        else {
            if ($f_company != '') {
                $index->where('crm.company_id', $f_company);
            }

            if ($f_activity != '') {
                $index->where('crm_detail.activity', $f_activity);
            }


            if ($f_status != '')
            {
                switch ($f_status) {
                    case 'CHECK_IN_NULL':
                        $index->whereNull('crm_detail.datetime_check_in');
                        $index->whereNull('crm_detail.datetime_check_out');
                        $index->whereNull('crm_detail.rating');
                        break;
                    case 'CHECK_OUT_NULL':
                        $index->whereNotNull('crm_detail.datetime_check_in');
                        $index->whereNull('crm_detail.datetime_check_out');
                        $index->whereNull('crm_detail.rating');
                        break;
                    case 'FEEDBACK_NULL':
                        $index->whereNotNull('crm_detail.datetime_check_in');
                        $index->whereNotNull('crm_detail.datetime_check_out');
                        $index->whereNull('crm_detail.rating');
                        break;

                    case 'FINISHED':
                        $index->whereNotNull('crm_detail.datetime_check_in');
                        $index->whereNotNull('crm_detail.datetime_check_out');
                        $index->whereNotNull('crm_detail.rating');
                        break;
                    
                    case 'NOT_FINISHED':
                        $index->where(function($query) {
                            $query->whereNull('crm_detail.datetime_check_in');
                            $query->orWhereNull('crm_detail.datetime_check_out');
                            $query->orWhereNull('crm_detail.rating');
                        });
                        
                        break;

                    default:
                        break;
                }
            }
        }

        $index = $index->orderBy($sort, $order)->paginate($limit);

        $data = compact('index');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function create(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been Added';
        $data    = '';

        DB::beginTransaction();

        $index = new Crm;

        $index->no_crm     = $this->getCrm($request);
        $index->type       = $request->type;
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

        return response()->json(compact('status', 'message', 'data'));
    }

    public function next(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been Added';
        $data    = '';

        $message2 = [
            'crm_id.required'          => 'This field required.',
            'activity.required'        => 'This field required.',
            'date_activity.required'   => 'This field required.',
            'date_activity.date'       => 'Date Format Only.',
            'time_activity.required'   => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'crm_id'        => 'required',
            'activity'      => 'required',
            'date_activity' => 'required|date',
            'time_activity' => 'required',
        ], $message2);

        if ($validator->fails()) {
            $status  = 'ERROR';
            $message = 'Error Input';

            $error = $validator->errors();

            return response()->json(compact('status', 'message', 'data', 'error'));
        }

        $crm = Crm::find($request->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        if($request->activity == 'QUOTATION' && $crm->type == 'PROSPECT')
        {
            $this->saveArchive('CRM', 'UDPATED', $crm);

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

        return response()->json(compact('status', 'message', 'data'));
    }

    public function reschedule(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been Updated';
        $data    = '';

        $message2 = [
            'activity.required'        => 'This field required.',
            'date_activity.required'   => 'This field required.',
            'date_activity.date'       => 'Date Format Only.',
            'time_activity.required'   => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'activity'      => 'required',
            'date_activity' => 'required|date',
            'time_activity' => 'required',
        ], $message2);

        if ($validator->fails()) {
            $status  = 'ERROR';
            $message = 'Error Input';

            $error = $validator->errors();

            return response()->json(compact('status', 'message', 'data', 'error'));
        }


        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('CRM_DETAIL', 'UDPATED', $index);

        $index->sales_id          = $crm->sales_id;
        $index->pic_id            = $crm->pic_id;
        $index->activity          = $request->activity;
        $index->datetime_activity = $request->date_activity . ' ' . $request->time_activity;

        $index->save();


        return response()->json(compact('status', 'message', 'data'));
    }

    public function checkIn(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('CRM_DETAIL', 'CHECK_IN', $index);

        $index->datetime_check_in = $index->datetime_check_in ? null : date('Y-m-d H:i:s');
        $index->latitude_check_in = $request->latitude_check_in;
        $index->longitude_check_in = $request->longitude_check_in;

        $index->save();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function checkOut(Request $request)
    {

    	$status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id)) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('CRM_DETAIL', 'CHECK_OUT', $index);

        $token = str_random(30);

        $index->datetime_check_out = date('Y-m-d H:i:s');
        $index->latitude_check_out = $request->latitude_check_out;
        $index->longitude_check_out = $request->longitude_check_out;

        $index->save();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function sendFeedBack(Request $request)
    {
        $status  = 'OK';
        $message = 'Feedback Form Has Been Sended';
        $data    = '';

        $index = CrmDetail::find($request->id);

        $crm = Crm::find($index->crm_id);

        $pic = Pic::find($index->pic_id);

        if (!$this->usergrant($crm->sales_id, 'allSales-crm') || !$this->levelgrant($crm->sales_id) || $index->rating != null) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $message2 = [
            'feedback_email.required' => 'This field required.',
            'feedback_phone.required' => 'This field required.',
            'feedback_email.email' => 'Email format only.',
        ];

        $validator = Validator::make($request->all(), [
            'feedback_phone' => 'required',
            'feedback_email' => 'required|email',
        ], $message2);

        if ($validator->fails()) {
            $status  = 'ERROR';
            $message = 'Error Input';

            $error = $validator->errors();

            return response()->json(compact('status', 'message', 'data', 'error'));
        }

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $this->saveArchive('App\Models\CrmDetail', 'SEND_FEEDBACK', $index);

        $token = str_random(30);

        $index->feedback_email = $request->feedback_email;
        $index->feedback_phone = $this->whatsappPhone($request->feedback_phone);
        $index->feedback_token = $token;

        $index->save();

        Mail::send('email.crmFeedback', compact('index', 'pic', 'crm'), function ($message) use ($index) {
            $message->to($index->feedback_email)->subject('Feedback From Meeting');
        });

        return response()->json(compact('status', 'message', 'data'));
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

    public function waFeedback(Request $request)
    {
        $index = CrmDetail::join('crm', 'crm_detail.crm_id', 'crm.id')
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

        if($index->feedback_token && $index->feedback_phone)
        {
            $text = "
                Kepada Yth,";

            if(strtoupper($index->gender ?? $index->pic_gender_prospec) == 'M')
            {
                $text .= " Bapak " . $index->fullname ?? $index->pic_fullname_prospec;
            }
            else if(strtoupper($index->gender ?? $index->pic_gender_prospec) == 'F')
            {
                $text .= " Ibu " . $index->fullname ?? $index->pic_fullname_prospec;
            }
            else
            {
                $text .= " Bapak/Ibu" . $index->fullname ?? $index->pic_fullname_prospec;
            }
            
            $text .= "
                Terima kasih atas kesediaan anda untuk meeting bersama kami, kami mohon untuk mengisi form kami, buka link ini ".  route('backend.crm.feedback', ["token" => $index->feedback_token]) ."

                Dengan feedback anda, kami akan meningkatkan kualitas kami di pertemuan yang akan datang.

                Dengan demikan terima kasih atas waktu anda.
            ";



            $status  = 'OK';
            $message = '';
            $data = compact('index', 'text');

            return response()->json(compact('status', 'message', 'data'));
        }
        else
        {
            $status  = 'ERROR';
            $message = 'Token and phone number Not Available';
            $data = '';

            return response()->json(compact('status', 'message', 'data'));
        }
    }

    public function sendFeedbackByEmail(Request $request)
    {
        $status  = 'OK';
        $message = 'Feedback Form Has Been Sended';
        $data    = '';

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
            $status  = 'ERROR';
            $message = 'Error Input';

            $error = $validator->errors();

            return response()->json(compact('status', 'message', 'data', 'error'));
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

        return response()->json(compact('status', 'message', 'data'));
    }

    public function sendFeedbackByWhatsapp(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

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
            $status  = 'ERROR';
            $message = 'Error Input';

            $error = $validator->errors();

            return response()->json(compact('status', 'message', 'data', 'error'));
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


        $data = compact('index', 'text');

        return response()->json(compact('status', 'message', 'data'));
    }

}
