<?php

namespace App\Http\Controllers\Backend;

use App\Pr;
use App\Models\PrDetail;
use App\Models\Po;
use App\User;
use App\Division;
use App\Spk;
use App\Config;
use App\Supplier;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;

use Session;
use File;
use Hash;
use Validator;
use PDF;
use Excel;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class PrController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(date_order) as year'))->orderBy('date_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $user = Pr::join('users', 'users.id', '=', 'pr.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-pr'))
        {
            $user->whereIn('pr.user_id', Auth::user()->staff());
        }

        $user = $user->get();

        $division = Division::where('active', 1)->get();
        $spk     = Spk::all();

    	return view('backend.pr.index')->with(compact('request', 'year', 'month', 'user', 'spk', 'division'));
    }

    public function datatables(Request $request)
    {
        $f_user  = $this->filter($request->f_user, Auth::id());
        $f_month = $this->filter($request->f_month, date('n'));
        $f_year  = $this->filter($request->f_year, date('Y'));

        $s_no_pr = $this->filter($request->s_no_pr);

        $index = Pr::leftJoin('spk', 'pr.spk_id', 'spk.id')
            ->leftJoin('users', 'users.id', '=', 'pr.user_id')
            ->select('pr.*')
            ->addSelect('spk.spk', 'spk.name as spk_name', 'users.fullname')
            ->orderBy('pr.id', 'DESC');

        if($s_no_pr != '')
        {
            $index->where('pr.no_pr', 'LIKE', '%'.$s_no_pr.'%');
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr.date_order', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr.date_order', $f_year);
            }

            if($f_user == 'staff')
            {
                $index->whereIn('pr.user_id', Auth::user()->staff());
            }
            else if($f_user != '')
            {
                $index->where('pr.user_id', $f_user);
            }
        }

        

    	$index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('spk', function ($index){
            return $index->spk ?? $index->type;
        });

        $datatables->editColumn('fullname', function ($index){
            return $index->fullname ?? 'not set';
        });

        $datatables->editColumn('date_order', function ($index){
            return date('d/m/Y', strtotime($index->date_order));
        });

        $datatables->editColumn('created_at', function ($index){
            return date('d/m/Y H:i', strtotime($index->created_at));
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d/m/Y', strtotime($index->deadline));
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if( $this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id) )
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('view-pr') )
            {
                $html .= '
                    <a href="'.route('backend.pr.edit', ['id' => $index->id]).'" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
                ';
            }
            
            if( Auth::user()->can('delete-pr') && ($this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id)) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-pr" data-toggle="modal" data-target="#delete-pr" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('pdf-pr') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-pr" data-toggle="modal" data-target="#pdf-pr" data-id="'.$index->id.'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getSpkItem(Request $request)
    {
        $index = PrDetail::select(DB::raw('
                pr_detail.*,
                spk.spk
            '))
            ->leftJoin('pr', 'pr_detail.pr_id', '=', 'pr.id')
            ->select('pr.no_pr', 'pr_detail.item', 'pr.name', 'pr_detail.quantity', 'pr_detail.unit')
            ->where('pr.spk_id', $request->id)
            ->where('pr_detail.confirm', '1')
            ->get();

        return $index;
    }

    public function unconfirm(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(date_order) as year'))->orderBy('date_order', 'ASC')->distinct()->get();
        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    	return view('backend.pr.unconfirm')->with(compact('request', 'year', 'month'));
    }

    public function datatablesUnconfirm(Request $request)
    {
        $f_month = $this->filter($request->f_month);
        $f_year  = $this->filter($request->f_year);

    	$index = PrDetail::where('pr_detail.confirm', 0)
            ->join('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin('users', 'users.id', 'pr.user_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->select('pr_detail.*', 'spk.spk', 'spk.name as spk_name', 'users.fullname as name', 'pr.deadline', 'pr.no_pr', 'pr.type')
            ->orderBy('pr_detail.id', 'DESC');

        if($f_month != '')
        {
            $index->whereMonth('pr.date_order', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('pr.date_order', $f_year);
        }

    	$index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('spk', function ($index){
            return $index->type == 'PROJECT' ? $index->spk : $index->type;
        });

        $datatables->editColumn('spk_name', function ($index){
            return $index->type == 'PROJECT' ? $index->spk_name : $index->type;
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d/m/Y', strtotime($index->deadline));
        });

        $datatables->editColumn('quantity', function ($index) {
            return $index->quantity . ' ' . $index->unit;
        });

        $datatables->addColumn('confirm', function ($index) {
            $html = '';

            $html .= '
                <input type="checkbox" class="check-confirm" value="'.$index->id.'" name="confirm[]" form="action">
            ';
                
            return $html;
        });

        $datatables->addColumn('reject', function ($index) {
            $html = '';

            $html .= '
                <input type="checkbox" class="check-reject" value="'.$index->id.'" name="reject[]" form="action">
            ';
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function confirm(Request $request)
    {
    	$year       = Pr::select(DB::raw('YEAR(date_order) as year'))->orderBy('date_order', 'ASC')->distinct()->get();
        $month      = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
            $query->whereIn('position', explode(', ' , $purchasing_position->value))
            ->orWhereIn('id', explode(', ' , $purchasing_user->value));
        })->where('active', 1);

        $finance = User::where(function ($query) use ($financial_position, $financial_user) {
            $query->whereIn('position', explode(', ' , $financial_position->value))
            ->orWhereIn('id', explode(', ' , $financial_user->value));
        })->where('active', 1);

        $purchasing = $purchasing->get();
        $finance = $finance->get();

        $supplier   = Supplier::all();

        return view('backend.pr.confirm')->with(compact('request', 'year', 'month', 'purchasing', 'finance', 'supplier'));
    }

    public function getStatusConfirmProject(Request $request)
    {
        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));

        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                WHERE `po`.`status_received` <> \'COMPLAIN\'';
        // if($f_month != '')
        // {
        //     $sql .= ' AND MONTH(pr_detail.datetime_confirm) = ' . $f_month;
        // }

        $sql .= '
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
                $query->whereIn('position', explode(', ' , $purchasing_position->value))
                ->orWhereIn('id', explode(', ' , $purchasing_user->value));
            })->where('active', 1)
            ->get();

        

        $pr_detail = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr_detail.purchasing_id')
            ->select(
                'pr_detail.*',
                'spk.spk',
                'spk.name as spk_name',
                'pr.name',
                'pr.deadline',
                'pr.no_pr',
                'users.fullname as purchasing',
                'pr_detail.purchasing_id',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->where('pr_detail.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'))
            ->whereIn('pr.type', ['PROJECT', 'OFFICE'])
            ->where(function($query){
                $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckAudit, 0)'))
                    ->orWhere(DB::raw('COALESCE(countPO, 0)'), 0);
            })
            ->distinct();

        if($f_month != '')
        {
            $pr_detail->whereMonth('pr_detail.datetime_confirm', $f_month);
        }

        if($f_year != '')
        {
            $pr_detail->whereYear('pr_detail.datetime_confirm', $f_year);
        }

        $pr_detail = $pr_detail->get();

        $status = '';

        foreach($purchasing as $list)
        {
            $total_today   = 0;
            $total_past1   = 0;
            $total_past2   = 0;
            $total_past3   = 0;
            $total_past4   = 0;
            $total_pending = 0;
            $total_stock   = 0;
            $total_cancel  = 0;

            foreach($pr_detail as $count)
            {
                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d') && $count->status == '' )
                {
                    $total_today += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-1 day')) && $count->status == '' )
                {
                    $total_past1 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-2 days')) && $count->status == '' )
                {
                    $total_past2 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-3 days')) && $count->status == '')
                {
                    $total_past3 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) <= date('Y-m-d', strtotime('-4 days')) && $count->status == '')
                {
                    $total_past4 += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'PENDING' )
                {
                    $total_pending += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'STOCK' )
                {
                    $total_stock += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'CANCEL' )
                {
                    $total_cancel += 1;
                }
            }

            $status[] = [
                'id'         => $list->id,
                'name'       => $list->fullname,
                'today'      => $total_today,
                'past_1_day' => $total_past1,
                'past_2_day' => $total_past2,
                'past_3_day' => $total_past3,
                'past_4_day' => $total_past4,
                'pending'    => $total_pending,
                'stock'      => $total_stock,
                'cancel'     => $total_cancel,
            ];
        }

        return compact('status');
    }

    public function datatablesConfirmProject(Request $request)
    {
        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));
        $f_purchasing = $this->filter($request->f_purchasing, (
            in_array(Auth::user()->position, explode(', ', $purchasing_position))
            || in_array(Auth::id(), explode(', ', $purchasing_user)) ? Auth::id() : ''));
        $f_status     = $this->filter($request->f_status);
        $f_day        = $this->filter($request->f_day);
        $f_value      = $this->filter($request->f_value);
        $f_audit      = $this->filter($request->f_audit);
        $f_finance    = $this->filter($request->f_finance);
        $f_id         = $this->filter($request->f_id);
        $s_no_pr      = $this->filter($request->s_no_pr);
        $s_no_po      = $this->filter($request->s_no_po);
        $s_item       = $this->filter($request->s_item);

        $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
            $query->whereIn('position', explode(', ' , $purchasing_position->value))
            ->orWhereIn('id', explode(', ' , $purchasing_user->value));
        })
        ->get();

        $supplier   = Supplier::select('*');

        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance,
                    GROUP_CONCAT(DISTINCT `po`.`no_po`) AS list_no_po
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                WHERE `po`.`status_received` <> \'COMPLAIN\'';

        // if($f_month != '')
        // {
        //     $sql .= ' AND MONTH(pr_detail.datetime_confirm) = ' . $f_month;
        // }

        $sql .= '
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $index = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users as purchasing', 'purchasing.id', 'pr_detail.purchasing_id')
            ->leftJoin('users', 'users.id', 'pr.user_id')
            ->select(
                'pr_detail.*',
                'spk.spk',
                'spk.name as spk_name',
                'users.fullname as name',
                'pr.deadline',
                'pr.no_pr',
                'pr.type',
                'pr.division',
                'purchasing.fullname as purchasing',
                'pr_detail.purchasing_id',
                'po.countPO',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->whereIn('pr.type', ['PROJECT', 'OFFICE'])
            ->distinct()
            ->orderBy('pr_detail.id', 'DESC');

        if($f_id != '' || $s_no_pr != '' || $s_item != '' || $s_no_po != '')
        {
            if($s_no_pr != '')
            {
                $index->where('pr.no_pr', 'LIKE', '%'.$s_no_pr.'%');
            }

            else if ($s_item != '')
            {
                $index->where('pr_detail.item', 'LIKE', '%'.$s_item.'%');
            }

            else if ($s_no_po != '')
            {
                $index->where('po.list_no_po', 'LIKE', '%'.$s_no_po.'%');
            }

            else if($f_id != '')

            {
                $index->whereIn('pr_detail.id', explode(',', $f_id));
            }
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr_detail.datetime_confirm', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr_detail.datetime_confirm', $f_year);
            }

            if($f_purchasing == 'staff')
            {
                $index->whereIn('pr_detail.purchasing_id', Auth::user()->staff());
            }
            else if($f_purchasing != '')
            {
                $index->where('pr_detail.purchasing_id', $f_purchasing);
            }

            if($f_status != '')
            {
                if($f_status == "none")
                {
                    $index->whereNull('pr_detail.status');
                }
                else
                {
                    $index->where('pr_detail.status', $f_status);
                }
            }

            switch ($f_day) {
                case '4':
                    $index->whereDate('pr_detail.datetime_confirm', '<=', date('Y-m-d', strtotime('-4 days')));
                    break;
                case '3':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-3 days')));
                    break;
                case '2':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-2 days')));
                    break;
                case '1':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-1 day')));
                    break;
                case '0':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d'));
                    break;
                default:
                    //
                    break;
            }

            if ($f_value != '' && $f_value == 0) 
            {
                $index->whereColumn('pr_detail.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            } 
            else if ($f_value == 1) 
            {
                $index->whereColumn('pr_detail.quantity', '<=', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            }


            if ($f_audit != '' && $f_audit == 0) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckAudit, 0)'))
                        ->orWhere(DB::raw('COALESCE(countPO, 0)'), 0);
                });
            }
            else if ($f_audit == 1) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '<=', DB::raw('COALESCE(countCheckAudit, 0)'))
                        ->where(DB::raw('COALESCE(countPO, 0)'), '<>',0);
                });
            }

            if ($f_finance != '' && $f_finance == 0) 
            {
                $index->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckFinance, 0)'));
            }
            else if ($f_finance == 1) 
            {
                $index->whereColumn(DB::raw('COALESCE(countPO, 0)'), '<=', DB::raw('COALESCE(countCheckFinance, 0)'));
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('spk', function ($index){
            return $index->type == 'PROJECT' ? $index->spk : $index->type;
        });

        $datatables->editColumn('spk_name', function ($index){
            return $index->type == 'PROJECT' ? $index->spk_name : $index->type;
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d/m/Y', strtotime($index->deadline));
        });

        $datatables->editColumn('quantity', function ($index) {
            return $index->quantity . ' ' . $index->unit . ' (' . $index->totalPoQty . ')';
        });

        $datatables->editColumn('purchasing', function ($index) use ($purchasing) {
            $html = '';

            // Class changePurchasing
            if(Auth::user()->can('changePurchasing-pr'))
            {
                $html .= '<select class="form-control change-purchasing" name="purchasing_id" data-id='.$index->id.'>';

                foreach($purchasing as $list)
                {
                    $html .= '<option value="'.$list->id.'" '. ($list->id == $index->purchasing_id ? 'selected' : '') .'>'.$list->fullname.'</option>';
                }

                $html .= '</select>';
            }
            else
            {
                $html .= $index->purchasing;
            }

            $html .= '<br/><select class="form-control change-status" name="status" data-id='.$index->id.'>';


            $html .= '<option value="" '. ($index->status == "" ? 'selected' : '') .'>Set Status</option>';
            $html .= '<option value="PENDING" '. ($index->status == "PENDING" ? 'selected' : '') .'>Pending</option>';
            $html .= '<option value="STOCK" '. ($index->status == "STOCK" ? 'selected' : '') .'>Stock</option>';
            $html .= '<option value="CANCEL" '. ($index->status == "CANCEL" ? 'selected' : '') .'>Cancel</option>';

            $html .= '</select>';

            if(Auth::user()->can('unconfirmItem-pr'))
            {
                $html .= '<br/>
                        <button type="button" class="btn btn-xs btn-warning unconfirm-detail" data-toggle="modal" data-target="#unconfirm-detail" data-id="'.$index->id.'">Unconfirm</button>
                    ';
            }

            return $html;
        });

        // with table po
        $datatables->addColumn('po', function ($index) use ($supplier) {
            return view('backend.pr.datatables.poProject', compact('index', 'supplier'));
        });

        $datatables->editColumn('date_po', function ($index) {

            $html = '';

            if($index->date_po)
            {
                $html .= date('d/m/Y', strtotime($index->date_po));
            }

            return $html;
        });

        $datatables->editColumn('date_request', function ($index) {

            $html = '';

            if($index->date_request)
            {
                $html .= date('d/m/Y H:i', strtotime($index->date_request));
            }

            return $html;
        }); // mark as deadline

        $datatables->editColumn('created_at', function ($index) {

            $html = '';

            if($index->created_at)
            {
                $html .= date('d/m/Y H:i', strtotime($index->created_at));
            }

            return $html;
        });

        $datatables->editColumn('datetime_confirm', function ($index) {

            $html = '';

            if($index->datetime_confirm)
            {
                $html .= date('d/m/Y H:i', strtotime($index->datetime_confirm));
            }

            return $html;
        });

        $datatables->editColumn('check_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkAudit-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_audit" '.($index->check_audit ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_audit ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('check_finance', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkFinance-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_finance" '.($index->check_finance ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_finance ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('note_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('noteAudit-pr'))
            {
                $html .= '<textarea class="note_audit form-control" data-id="' . $index->id . '" name="note_audit">'.$index->note_audit.'</textarea>';
            }
            else
            {
                $html .= $index->note_audit;
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';
           
            if( Auth::user()->can('delete-pr') && ( $index->user_id == Auth::id() || Auth::user()->can('allUser-pr') ) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->setRowClass(function ($index) {
            if($index->date_request >= '2010-01-01' && $index->date_request < date('Y-m-d') && $index->status_received == 'WAITING')
            {
                return 'alert-danger';
            }
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getStatusConfirmPayment(Request $request)
    {
        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));

        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                WHERE `po`.`status_received` <> \'COMPLAIN\'';
        // if($f_month != '')
        // {
        //     $sql .= ' AND MONTH(pr_detail.datetime_confirm) = ' . $f_month;
        // }

        $sql .= '
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $purchasing = User::where(function ($query) use ($financial_position, $financial_user) {
                $query->whereIn('position', explode(', ' , $financial_position->value))
                ->orWhereIn('id', explode(', ' , $financial_user->value));
            })->where('active', 1)
            ->get();

        

        $pr_detail = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr_detail.purchasing_id')
            ->select(
                'pr_detail.*',
                'spk.spk',
                'spk.name as spk_name',
                'pr.name',
                'pr.deadline',
                'pr.no_pr',
                'users.fullname as purchasing',
                'pr_detail.purchasing_id',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->where('pr_detail.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'))
            ->where('pr.type', 'PAYMENT')
            ->where(function($query){
                $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckAudit, 0)'))
                    ->orWhere(DB::raw('COALESCE(countPO, 0)'), 0);
            })
            ->distinct();

        if($f_month != '')
        {
            $pr_detail->whereMonth('pr_detail.datetime_confirm', $f_month);
        }

        if($f_year != '')
        {
            $pr_detail->whereYear('pr_detail.datetime_confirm', $f_year);
        }

        $pr_detail = $pr_detail->get();

        $status = '';

        foreach($purchasing as $list)
        {
            $total_today   = 0;
            $total_past1   = 0;
            $total_past2   = 0;
            $total_past3   = 0;
            $total_past4   = 0;
            $total_pending = 0;
            $total_stock   = 0;
            $total_cancel  = 0;

            foreach($pr_detail as $count)
            {
                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d') )
                {
                    $total_today += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-1 day')) )
                {
                    $total_past1 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-2 days')) )
                {
                    $total_past2 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) == date('Y-m-d', strtotime('-3 days')) )
                {
                    $total_past3 += 1;
                }

                if($list->id == $count->purchasing_id && 
                    date('Y-m-d', strtotime($count->datetime_confirm)) <= date('Y-m-d', strtotime('-4 days')) )
                {
                    $total_past4 += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'PENDING' )
                {
                    $total_pending += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'STOCK' )
                {
                    $total_stock += 1;
                }

                if($list->id == $count->purchasing_id && $count->status == 'CANCEL' )
                {
                    $total_cancel += 1;
                }
            }

            $status[] = [
                'id'         => $list->id,
                'name'       => $list->fullname,
                'today'      => $total_today,
                'past_1_day' => $total_past1,
                'past_2_day' => $total_past2,
                'past_3_day' => $total_past3,
                'past_4_day' => $total_past4,
                'pending'    => $total_pending,
                'stock'      => $total_stock,
                'cancel'     => $total_cancel,
            ];
        }

        return compact('status');
    }

    public function datatablesConfirmPayment(Request $request)
    {
        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));
        $f_purchasing = $this->filter($request->f_purchasing, (
            in_array(Auth::user()->position, explode(', ', $purchasing_position))
            || in_array(Auth::id(), explode(', ', $purchasing_user)) ? Auth::id() : ''));
        $f_status     = $this->filter($request->f_status);
        $f_day        = $this->filter($request->f_day);
        $f_value      = $this->filter($request->f_value);
        $f_audit      = $this->filter($request->f_audit);
        $f_finance    = $this->filter($request->f_finance);
        $f_id         = $this->filter($request->f_id);
        $s_no_pr      = $this->filter($request->s_no_pr);
        $s_no_po      = $this->filter($request->s_no_po);
        $s_item       = $this->filter($request->s_item);

        $purchasing = User::where(function ($query) use ($financial_position, $financial_user) {
            $query->whereIn('position', explode(', ' , $financial_position->value))
            ->orWhereIn('id', explode(', ' , $financial_user->value));
        })
        ->get();

        $supplier   = Supplier::select('*');

        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance,
                    GROUP_CONCAT(DISTINCT `po`.`no_po`) AS list_no_po
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                WHERE `po`.`status_received` <> \'COMPLAIN\'';

        // if($f_month != '')
        // {
        //     $sql .= ' AND MONTH(pr_detail.datetime_confirm) = ' . $f_month;
        // }

        $sql .= '
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $index = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr_detail.purchasing_id')
            ->select(
                'pr_detail.*',
                'spk.spk',
                'spk.name as spk_name',
                'pr.name',
                'pr.deadline',
                'pr.no_pr',
                'users.fullname as purchasing',
                'pr_detail.purchasing_id',
                'pr.division',
                'po.countPO',
                'po.totalValue as value_po',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->where('pr.type', 'PAYMENT')
            ->distinct()
            ->orderBy('pr_detail.id', 'DESC');

        if($f_id != '' || $s_no_pr != '' || $s_item != '' || $s_no_po != '')
        {
            if($s_no_pr != '')
            {
                $index->where('pr.no_pr', 'LIKE', '%'.$s_no_pr.'%');
            }

            else if ($s_item != '')
            {
                $index->where('pr_detail.item', 'LIKE', '%'.$s_item.'%');
            }

            else if ($s_no_po != '')
            {
                $index->where('po.list_no_po', 'LIKE', '%'.$s_no_po.'%');
            }

            else if($f_id != '')

            {
                $index->whereIn('pr_detail.id', explode(',', $f_id));
            }
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr_detail.datetime_confirm', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr_detail.datetime_confirm', $f_year);
            }

            if($f_purchasing == 'staff')
            {
                $index->whereIn('pr_detail.purchasing_id', Auth::user()->staff());
            }
            else if($f_purchasing != '')
            {
                $index->where('pr_detail.purchasing_id', $f_purchasing);
            }

            if($f_status != '')
            {
                if($f_status == "none")
                {
                    $index->whereNull('pr_detail.status');
                }
                else
                {
                    $index->where('pr_detail.status', $f_status);
                }
            }

            switch ($f_day) {
                case '4':
                    $index->whereDate('pr_detail.datetime_confirm', '<=', date('Y-m-d', strtotime('-4 days')));
                    break;
                case '3':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-3 days')));
                    break;
                case '2':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-2 days')));
                    break;
                case '1':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d', strtotime('-1 day')));
                    break;
                case '0':
                    $index->whereDate('pr_detail.datetime_confirm', date('Y-m-d'));
                    break;
                default:
                    //
                    break;
            }

            if ($f_value != '' && $f_value == 0) 
            {
                $index->whereColumn('pr_detail.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            } 
            else if ($f_value == 1) 
            {
                $index->whereColumn('pr_detail.quantity', '<=', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            }


            if ($f_audit != '' && $f_audit == 0) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckAudit, 0)'))
                        ->orWhere(DB::raw('COALESCE(countPO, 0)'), 0);
                });
            }
            else if ($f_audit == 1) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(countPO, 0)'), '<=', DB::raw('COALESCE(countCheckAudit, 0)'))
                        ->where(DB::raw('COALESCE(countPO, 0)'), '<>',0);
                });
            }

            if ($f_finance != '' && $f_finance == 0) 
            {
                $index->whereColumn(DB::raw('COALESCE(countPO, 0)'), '>', DB::raw('COALESCE(countCheckFinance, 0)'));
            }
            else if ($f_finance == 1) 
            {
                $index->whereColumn(DB::raw('COALESCE(countPO, 0)'), '<=', DB::raw('COALESCE(countCheckFinance, 0)'));
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('value', function ($index){
            $html = number_format($index->value_po > 0 ? $index->value_po : $index->value);

            if($index->no_rekening)
            {
                $html .= '<br/>to: ' . $index->no_rekening;
            }

            return $html;
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d/m/Y', strtotime($index->deadline));
        });

        $datatables->editColumn('quantity', function ($index) {
            return $index->quantity . ' ' . $index->unit . ' (' . $index->totalPoQty . ')';
        });

        $datatables->editColumn('purchasing', function ($index) use ($purchasing) {
            $html = '';

            // Class changePurchasing
            if(Auth::user()->can('changePurchasing-pr'))
            {
                $html .= '<select class="form-control change-purchasing" name="purchasing_id" data-id='.$index->id.'>';

                foreach($purchasing as $list)
                {
                    $html .= '<option value="'.$list->id.'" '. ($list->id == $index->purchasing_id ? 'selected' : '') .'>'.$list->fullname.'</option>';
                }

                $html .= '</select>';
            }
            else
            {
                $html .= $index->purchasing;
            }

            $html .= '<br/><select class="form-control change-status" name="status" data-id='.$index->id.'>';


            $html .= '<option value="" '. ($index->status == "" ? 'selected' : '') .'>Set Status</option>';
            $html .= '<option value="PENDING" '. ($index->status == "PENDING" ? 'selected' : '') .'>Pending</option>';
            $html .= '<option value="STOCK" '. ($index->status == "STOCK" ? 'selected' : '') .'>Stock</option>';
            $html .= '<option value="CANCEL" '. ($index->status == "CANCEL" ? 'selected' : '') .'>Cancel</option>';

            $html .= '</select>';

            if(Auth::user()->can('unconfirmItem-pr'))
            {
                $html .= '<br/>
                        <button type="button" class="btn btn-xs btn-warning unconfirm-detail" data-toggle="modal" data-target="#unconfirm-detail" data-id="'.$index->id.'">Unconfirm</button>
                    ';
            }

            return $html;
        });

        // with table po
        $datatables->addColumn('po', function ($index) {
            return view('backend.pr.datatables.poPayment', compact('index'));
        });

        $datatables->editColumn('date_po', function ($index) {

            $html = '';

            if($index->date_po)
            {
                $html .= date('d/m/Y', strtotime($index->date_po));
            }

            return $html;
        });

        $datatables->editColumn('date_request', function ($index) {

            $html = '';

            if($index->date_request)
            {
                $html .= date('d/m/Y H:i', strtotime($index->date_request));
            }

            return $html;
        }); // mark as deadline

        $datatables->editColumn('created_at', function ($index) {

            $html = '';

            if($index->created_at)
            {
                $html .= date('d/m/Y H:i', strtotime($index->created_at));
            }

            return $html;
        });

        $datatables->editColumn('datetime_confirm', function ($index) {

            $html = '';

            if($index->datetime_confirm)
            {
                $html .= date('d/m/Y H:i', strtotime($index->datetime_confirm));
            }

            return $html;
        });

        $datatables->editColumn('check_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkAudit-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_audit" '.($index->check_audit ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_audit ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('check_finance', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkFinance-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_finance" '.($index->check_finance ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_finance ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('note_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('noteAudit-pr'))
            {
                $html .= '<textarea class="note_audit form-control" data-id="' . $index->id . '" name="note_audit">'.$index->note_audit.'</textarea>';
            }
            else
            {
                $html .= $index->note_audit;
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';
           
            if( Auth::user()->can('delete-pr') && ( $index->user_id == Auth::id() || Auth::user()->can('allUser-pr') ) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->setRowClass(function ($index) {
            if($index->date_request >= '2010-01-01' && $index->date_request < date('Y-m-d') && $index->status_received == 'WAITING')
            {
                return 'alert-danger';
            }
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $division = Division::where('active', 1)->get();
    	$spk     = Spk::all();

    	return view('backend.pr.create')->with(compact('division', 'spk'));
    }

    public function store(Request $request)
    {
    	$message = [
            'spk_id.required' => 'Select One',
            'no_pr.required' => 'This field required.',
            'no_pr.min' => 'This field minimum 9 character.',
            'no_pr.max' => 'This field maximum 9 character.',
            'no_pr.unique' => 'Data already taken.',
            'name.required' => 'This field required.',
            'division.required' => 'Select One',
        ];

        $validator = Validator::make($request->all(), [
            'spk_id' => 'required',
            'no_pr' => 'required|unique:pr,no_pr|min:9|max:9',
            'name' => 'required',
            'division' => 'required',
        ], $message);

        if($request->spk_id)
        {
            $validator->after(function ($validator) use ($request) {
                $check = Spk::find($request->spk_id);
                if ($check->no_admin != 0 && $check->no_admin != -2) {
                    $validator->errors()->add('spk_id', 'This SPK already sended');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $code = Config::firstOrCreate(['for' => 'pr_code'], ['value' => '00']);
        $spk  = Spk::find($request->spk_id);

        $index = new Pr;

        $index->spk_id     = $request->spk_id;
        $index->no_pr      = $request->no_pr;
        $index->user_id    = Auth::id();
        $index->name       = $request->name;
        $index->date_order = date('Y-m-d H:i:s');
        $index->deadline   = date('Y-m-d H:i:s');
        $index->division   = $request->division;
        $index->barcode    = substr($spk->spk, -3) . substr($request->no_pr, -4) . date('dm', strtotime($request->date_order)) . $code->value;

        $index->save();

        return redirect()->route('backend.pr.edit', ['id' => $index->id])->with('success', 'Data Has Been Added');
    }

    public function storeProjectPr(Request $request)
    {
        $spk     = Spk::find($request->spk_id);

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $message = [
            'spk_id.required' => 'Select One',
            'division.required' => 'Select One',
        ];

        $validator = Validator::make($request->all(), [
            'spk_id' => 'required',
            'division' => 'required',
        ], $message);

        if($request->spk_id)
        {
            $validator->after(function ($validator) use ($request) {
                $check = Spk::find($request->spk_id);
                if ($check->no_admin != 0 && $check->no_admin != -2) {
                    $validator->errors()->add('spk_id', 'This SPK already sended');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('createProjectPr-pr-error', 'Something Errors');;
        }

        $index = new Pr;

        $index->spk_id     = $request->spk_id;
        $index->no_pr      = $this->getPr($request);
        $index->user_id    = Auth::id();
        $index->name       = Auth::user()->fullname;
        $index->date_order = date('Y-m-d H:i:s');
        $index->deadline   = date('Y-m-d H:i:s');
        $index->division   = $request->division;
        $index->type       = 'PROJECT';
        $index->barcode    = substr($spk->spk, -3) . substr($index->no_pr, -4) . date('dm', strtotime($index->date_order)) . $pr_code->value;

        $index->save();

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function storeOfficePr(Request $request)
    {
        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $index = new Pr;

        $index->spk_id     = 0;
        $index->no_pr      = $this->getPr($request);
        $index->user_id    = Auth::id();
        $index->name       = Auth::user()->fullname;
        $index->date_order = date('Y-m-d H:i:s');
        $index->deadline   = date('Y-m-d H:i:s');
        $index->division   = 'NONE';
        $index->type       = 'OFFICE';
        $index->barcode    = '000' . substr($index->no_pr, -4) . date('dm', strtotime($index->date_order)) . $pr_code->value;

        $index->save();

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function storePaymentPr(Request $request)
    {
        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $index = new Pr;

        $index->spk_id     = 0;
        $index->no_pr      = $this->getPr($request);
        $index->user_id    = Auth::id();
        $index->name       = Auth::user()->fullname;
        $index->date_order = date('Y-m-d H:i:s');
        $index->deadline   = date('Y-m-d H:i:s');
        $index->division   = $request->division;
        $index->type       = 'PAYMENT';
        $index->barcode    = '001' . substr($index->no_pr, -4) . date('dm', strtotime($index->date_order)) . $pr_code->value;

        $index->save();

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function edit($id)
    {
    	$index = Pr::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-pr') || !$this->levelgrant($index->user_id))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        if($index->type == 'PAYMENT')
        {
            $purchasing = User::where(function ($query) use ($financial_position, $financial_user) {
                $query->whereIn('position', explode(', ' , $financial_position->value))
                ->orWhereIn('id', explode(', ' , $financial_user->value));
            })->where('active', 1)->get();
        }
        else
        {
            $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
                $query->whereIn('position', explode(', ' , $purchasing_position->value))
                ->orWhereIn('id', explode(', ' , $purchasing_user->value));
            })->where('active', 1)->get();
        }
    	

        $division   = Division::all();
        $spk        = Spk::all();
    	
    	return view('backend.pr.edit')->with(compact('index', 'purchasing', 'division', 'spk'));
    }

    public function update(Request $request, $id)
    {
        $index = Pr::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-pr') || !$this->levelgrant($index->user_id))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

    	$message = [
            'spk_id.required' => 'Select One',
            'name.required' => 'This field required.',
            'division.required' => 'Select One',
        ];

        $validator = Validator::make($request->all(), [
            'spk_id' => 'required',
            'name' => 'required',
            'division' => 'required',
        ], $message);

        if($request->spk_id)
        {
            $validator->after(function ($validator) use ($request, $index) {
                $check = Spk::find($request->spk_id);
                if (($check->no_admin != 0 && $check->no_admin != -2) && $index->spk_id != $request->spk_id) {
                    $validator->errors()->add('spk_id', 'This SPK already sended');
                }
            });
        }


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->saveArchive('App\Pr', 'UPDATED', $index);

        $index->spk_id     = $request->spk_id;
        $index->name       = $request->name;
        $index->division   = $request->division;

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function delete(Request $request)
    {
        $index = Pr::find($request->id);

        if(!$this->usergrant($index->user_id, 'allUser-pr') || !$this->levelgrant($index->user_id))
        {
            $this->saveArchive('App\Pr', 'DELETED', $index);
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

    	Pr::destroy($request->id);

    	return redirect()->back()->with('success', 'Data has been deleted');
    }

    public function action(Request $request)
    {
        if(is_array($request->id))
        {
            foreach ($request->id as $list) {

                $index = Pr::find($list);

                if($this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id))
                {
                    $id[] = $list; 
                }
            }

            if ($request->action == 'delete' && Auth::user()->can('delete-pr')) {
                $index = Pr::find($id);
                $this->saveMultipleArchive('App\Pr', 'DELETED', $index);

                Pr::destroy($id);
                return redirect()->back()->with('success', 'Data Has Been Deleted');
            }
        }

        return redirect()->back()->with('Info', 'No data change');
    }

    public function datatablesPrDetail(Request $request)
    {
        $query = '(SELECT pr_detail_id, COUNT(`id`) AS count_po FROM `po` GROUP BY pr_detail_id) as `po`';

    	$index = PrDetail::leftJoin('users', 'pr_detail.purchasing_id', 'users.id')
            ->join('pr', 'pr_detail.pr_id', 'pr.id')
            ->select(
                'pr_detail.id',
                'pr_detail.pr_id',
                'pr_detail.item',
                'pr_detail.quantity',
                'pr_detail.unit',
                'pr_detail.confirm',
                'pr_detail.purchasing_id',
                'pr_detail.date_request',
                'pr_detail.value',
                'pr.type',
                'po.count_po',
                'users.fullname'
            )
            ->leftJoin(DB::raw($query), 'pr_detail.id', 'po.pr_detail_id')
            ->where('pr_id', $request->id)
            ->orderBy('pr_detail.id', 'DESC')
            ->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('quantity', function ($index) {
            return $index->quantity . ' ' . $index->unit;
        });

        $datatables->editColumn('confirm', function ($index) {
            if($index->confirm === 0)
            {
                return 'Pending';
            }

            else if($index->confirm === 1)
            {
                return 'Accepted';
            }

            else if($index->confirm === 1 && $index->count_po != 0)
            {
                return 'Ordered';
            }

            else if($index->confirm === -1)
            {
                return 'Rejected';
            }

            else if($index->confirm === -2)
            {
                return 'Need Revision';
            }
            
        });

        $datatables->editColumn('item', function ($index) {
            if($index->type == 'PAYMENT')
            {
                return $index->item . ' ' . number_format($index->value);
            }
            else
            {
                return $index->item;
            }
            
            
        });

        $datatables->editColumn('date_request', function ($index) {
            return date('d/m/Y H:i', strtotime($index->date_request));
            
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if($index->confirm !== 1 && ($index->pr->user_id == Auth::id() || Auth::user()->can('allUser-pr')) )
            {
                $html .= '
                    <input type="checkbox" class="check-detail" value="'.$index->id.'" name="id[]" form="action-detail">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(($index->confirm === 0 || $index->confirm === -2) && Auth::user()->can('edit-pr') && ( $index->pr->user_id == Auth::id() || Auth::user()->can('allUser-pr') ) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning edit-detail" data-toggle="modal" data-target="#edit-detail" 
                        data-id="'.$index->id.'"
                        data-detail_item="'.$index->item.'"
                        data-detail_quantity="'.$index->quantity.'"
                        data-detail_unit="'.$index->unit.'"
                        data-detail_purchasing_id="'.$index->purchasing_id.'"
                        data-detail_date_request="'.date('d F Y', strtotime($index->date_request)).'"
                        data-no_rekening="'.$index->no_rekening.'"
                        data-value="'.$index->value.'"
                    ><i class="fa fa-pencil" aria-hidden="true"></i></button>
                ';
            }

            if(($index->confirm === 0 || $index->confirm === -2) && Auth::user()->can('edit-pr') && ( $index->pr->user_id == Auth::id() || Auth::user()->can('allUser-pr') ) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function storePrDetail(Request $request)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $pr = Pr::find($request->pr_id);

        if(!$this->usergrant($pr->user_id, 'allUser-pr') || !$this->levelgrant($pr->user_id))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        // $index = new PrDetail;

        $dateNow = date('Y-m-d H:i:s');
        $data = explode(";", $request->item);
        $insert = [];

        if(in_array($pr->type, ['PROJECT', 'OFFICE']))
        {

            $message = [
                'item.required' => 'This field required.',
                'quantity.required' => 'This field required.',
                'quantity.integer' => 'Number only.',
                'unit.required' => 'This field required.',
                'purchasing_id.required' => 'Select one.',
                'purchasing_id.integer' => 'Invalid.',
            ];

            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'quantity' => 'required|integer',
                'unit' => 'required',
                'purchasing_id' => 'required|integer',
            ], $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            foreach ($data as $list) {
                $insert[] = [
                    'pr_id'         => $request->pr_id,
                    'item'          => $list,
                    'quantity'      => $request->quantity,
                    'unit'          => $request->unit,
                    'date_request'  => date('Y-m-d H:i:s', strtotime($request->date_request)),
                    'purchasing_id' => $request->purchasing_id,
                    'created_at'    => $dateNow,
                    'updated_at'    => $dateNow,
                ];
            } 

            PrDetail::insert($insert);

            // $index->pr_id         = $request->pr_id;
            // $index->item          = $request->item;
            // $index->quantity      = $request->quantity;
            // $index->unit          = $request->unit;
            // $index->date_request  = date('Y-m-d H:i:s', strtotime($request->date_request));
            // $index->purchasing_id = $request->purchasing_id;

            // $index->save();
        }
        else
        {
            $message = [
                'item.required' => 'This field required.',
                'purchasing_id.required' => 'Select one.',
                'purchasing_id.integer' => 'Invalid.',
            ];

            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'purchasing_id' => 'required|integer',
            ], $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            foreach ($data as $list) {
                $insert[] = [
                    'pr_id'         => $request->pr_id,
                    'item'          => $list,
                    'quantity'      => 1,
                    'unit'          => '',
                    'date_request'  => date('Y-m-d H:i:s', strtotime($request->date_request)),
                    'purchasing_id' => $request->purchasing_id,
                    'no_rekening'   => $request->no_rekening,
                    'value'         => $request->value,
                    'created_at'    => $dateNow,
                    'updated_at'    => $dateNow,
                ];
            } 

            PrDetail::insert($insert);

            // $index->pr_id         = $request->pr_id;
            // $index->item          = $request->item;
            // $index->quantity      = 1;
            // $index->unit          = '';
            // $index->date_request  = date('Y-m-d H:i:s', strtotime($request->date_request));
            // $index->purchasing_id = $request->purchasing_id;
            // $index->no_rekening   = $request->no_rekening;
            // $index->value         = $request->value;

            // $index->save();
        }

        $super_admin_notif = User::where(function ($query) use ($super_admin_position, $super_admin_user) {
                $query->whereIn('position', explode(', ', $super_admin_position->value))
                ->orWhereIn('id', explode(', ', $super_admin_user->value));
            })
            ->get();

        $html = '
            New Purchase Request, Item : '.$request->item.'
        ';

        foreach ($super_admin_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.unconfirm') ) );
        }

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePrDetail(Request $request)
    {
        $index = PrDetail::find($request->id);

        if(!$this->usergrant($index->pr->user_id, 'allUser-pr') || !$this->levelgrant($index->pr->user_id))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        if(in_array($index->pr->type, ['PROJECT', 'OFFICE']))
        {

            $message = [
                'item.required' => 'This field required.',
                'quantity.required' => 'This field required.',
                'quantity.integer' => 'Number only.',
                'unit.required' => 'This field required.',
                'purchasing_id.required' => 'Select one.',
                'purchasing_id.integer' => 'Invalid.',
            ];

            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'quantity' => 'required|integer',
                'unit' => 'required',
                'purchasing_id' => 'required|integer',
            ], $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            $this->saveArchive('App\Models\PrDetail', 'UPDATED', $index);

            $index->item          = $request->item;
            $index->quantity      = $request->quantity;
            $index->unit          = $request->unit;
            $index->date_request  = date('Y-m-d H:i:s', strtotime($request->date_request));
            $index->purchasing_id = $request->purchasing_id;
            $index->value         = 0;
            $index->confirm       = 0;

            $index->save();
        }
        else
        {
            $message = [
                'item.required' => 'This field required.',
                'purchasing_id.required' => 'Select one.',
                'purchasing_id.integer' => 'Invalid.',
            ];

            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'purchasing_id' => 'required|integer',
            ], $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            $this->saveArchive('App\Models\PrDetail', 'UPDATED', $index);


            $index->item          = $request->item;
            $index->quantity      = 1;
            $index->unit          = 'Pay';
            $index->date_request  = date('Y-m-d H:i:s', strtotime($request->date_request));
            $index->purchasing_id = $request->purchasing_id;
            $index->value         = $request->value;

            $index->save();
        }
    	

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deletePrDetail(Request $request)
    {
        $index = PrDetail::find($request->id);

        if(!$this->usergrant($index->pr->user_id, 'allUser-pr') || !$this->levelgrant($index->pr->user_id))
        {
            $this->saveArchive('App\Models\PrDetail', 'DELETED', $index);

            return redirect()->back()->with('failed', 'Access Denied');
        }

        PrDetail::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionPrDetail(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('edit-pr')) {
            $index = PrDetail::find($request->id);
            $this->saveMultipleArchive('App\Models\PrDetail', 'DELETED', $index);

            PrDetail::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
    }

    public function confirmItem(Request $request)
    {
        $date = date('Y-m-d H:i:s');

    	if (!empty($request->confirm)) {
            $index = PrDetail::whereIn('id', $request->confirm)->orderBy('purchasing_id', 'ASC')->get();

            $this->saveMultipleArchive('App\Models\PrDetail', 'CONFIRMED', $index);

            $number_item_purchasing = 0;
            $current_purchasing = -1;
            $data = '';
            foreach ($index as $list) {
                if($current_purchasing == $list->purchasing_id)
                {
                    $number_item_purchasing++;
                    array_push($data, $list->id);

                    
                }
                else
                {
                    if($current_purchasing != -1)
                    {
                        $html = '
                            New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
                        ';

                        User::find($current_purchasing)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => implode(',', $data)]) ) );
                    }

                    $number_item_purchasing = 1;
                    $current_purchasing = $list->purchasing_id;
                    $data = [$list->id];
                }
            }

            $html = '
                New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
            ';

            // User::find($current_purchasing)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => implode(',', $data)]) ) );

            PrDetail::whereIn('id', $request->confirm)->update(['confirm' => 1, 'datetime_confirm' => $date]);
        }
        
        if (!empty($request->reject)) {
            $index = PrDetail::whereIn('id', $request->reject)->orderBy('pr_id', 'ASC')->get();

            $this->saveArchive('App\Models\PrDetail', 'CONFIRMED', $index);

            $number_item_pr = 0;
            $user_id        = -1;
            $current_pr     = -1;

            foreach ($index as $list) {
                if($current_pr == $list->pr_id)
                {
                    $number_item_pr++;
                }
                else
                {
                    if($current_pr != -1)
                    {
                        $html = '
                            Item has been rejected, Count Item : '.$number_item_pr.'
                        ';

                        User::find($user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.edit', $current_pr) ) );
                    }

                    
                    $number_item_pr = 1;
                    $current_pr = $list->pr_id;
                    $user_id = Pr::find($current_pr)->user_id;
                }
            }

            $html = '
                Item has been rejected, Count Item : '.$number_item_pr.'
            ';

            // User::find($user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.edit', $current_pr) ) );

            PrDetail::whereIn('id', $request->reject)->update(['confirm' => -1, 'datetime_confirm' => $date]);
        }

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function unconfirmItem(Request $request)
    {
        $index = PrDetail::find($request->id);

        $this->saveArchive('App\Models\PrDetail', 'UNCONFIRMED', $index);

        $index->confirm = -2;
        $index->datetime_confirm = null;
        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function storePoProject(Request $request)
    {
        $prDetail = PrDetail::find($request->pr_detail_id);

        if(!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id))
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $request->pr_detail_id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

    	$message = [
            'pr_detail_id.required' => 'Error',
            'pr_detail_id.integer' => 'Error',
            'quantity.required' => 'This field required.',
            'quantity.integer' => 'Number only.',
            'quantity.max' => 'Maximum ' . $max,
            'no_po.required' => 'This field required.',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
            'type.required' => 'Select one.',
            'supplier_id.required' => 'This field required.',
            'name_supplier.required_if' => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'pr_detail_id' => 'required|integer',
            'quantity' => 'required|integer|max:'.$max,
            'no_po' => 'required',
            'date_po' => 'required|date',
            'type' => 'required',
            'supplier_id' => 'required',
            'name_supplier' => 'required_if:supplier_id,0',
            'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('add-poProject-error', '');
        }

        $supplier = Supplier::find($request->supplier_id);

        $index = new Po;

        $index->pr_detail_id    = $request->pr_detail_id;
        $index->quantity        = $request->quantity;
        $index->no_po           = $request->no_po;
        $index->date_po         = date('Y-m-d', strtotime($request->date_po));
        $index->type            = $request->type;
        $index->bank            = $supplier->bank ?? '';
        $index->name_supplier   = $supplier->name ?? $request->name_supplier;
        $index->no_rekening     = $supplier->no_rekening ?? '';
        $index->name_rekening   = $supplier->name_rekening ?? '';
        $index->value           = $request->value;
        $index->status_received = 'WAITING';

        $index->save();

        $pr = Pr::find($prDetail->pr_id);

        $html = '
            Your item requested is on the way, Item : '.$prDetail->item.', Quantity : '.$request->quantity.'
        ';

        User::find($pr->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.item', ['f_id' => $index->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePoProject(Request $request)
    {
        $index = Po::find($request->id);

    	$prDetail = PrDetail::find($index->pr_detail_id);

        if((!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $index->pr_detail_id)->where('id', '<>', $request->id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

        $message = [
            'quantity.required' => 'This field required.',
            'quantity.integer' => 'Number only.',
            'quantity.max' => 'Maximum ' . $max,
            'no_po.required' => 'This field required.',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
            'type.required' => 'Select one.',
            'supplier_id.required' => 'This field required.',
            'name_supplier.required' => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|max:'.$max,
            'no_po' => 'required',
            'date_po' => 'required|date',
            'type' => 'required',
            'supplier_id' => 'required',
            'name_supplier' => 'required_if:supplier_id,0',
            'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-poProject-error', '');
        }

        $this->saveArchive('App\Models\Po', 'UPDATED', $index);

        $supplier = Supplier::find($request->supplier_id);

        $index->quantity      = $request->quantity;
        $index->no_po         = $request->no_po;
        $index->date_po       = date('Y-m-d', strtotime($request->date_po));
        $index->type          = $request->type;
        $index->bank          = $supplier->bank ?? '';
        $index->name_supplier = $supplier->name ?? $request->name_supplier;
        $index->no_rekening   = $supplier->no_rekening ?? '';
        $index->name_rekening = $supplier->name_rekening ?? '';
        $index->value         = $request->value;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function storePoPayment(Request $request)
    {
        $prDetail = PrDetail::find($request->pr_detail_id);

        if(!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id))
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $request->pr_detail_id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

        $message = [
            'pr_detail_id.required' => 'Error',
            'pr_detail_id.integer' => 'Error',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'pr_detail_id' => 'required|integer',
            'date_po' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('add-poPayment-error', '');
        }

        $supplier = Supplier::find($request->supplier_id);

        $index = new Po;

        $index->pr_detail_id    = $request->pr_detail_id;
        $index->quantity        = 1;
        $index->no_po           = 'Payment';
        $index->date_po         = date('Y-m-d', strtotime($request->date_po));
        $index->type            = '-';
        $index->bank            = '-';
        $index->name_supplier   = '-';
        $index->no_rekening     = $prDetail->no_rekening;
        $index->name_rekening   = '-';
        $index->value           = $request->value ?? $prDetail->value;
        $index->status_received = 'CONFIRMED';

        $index->save();

        $pr = Pr::find($prDetail->pr_id);

        $html = '
            Your item requested is on the way, Item : '.$prDetail->item.', Quantity : '.$request->quantity.'
        ';

        User::find($pr->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.item', ['f_id' => $index->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePoPayment(Request $request)
    {
        $index = Po::find($request->id);

        $prDetail = PrDetail::find($index->pr_detail_id);

        if((!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $message = [
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'date_po' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-poPayment-error', '');
        }

        $this->saveArchive('App\Models\Po', 'UPDATED', $index);

        $supplier = Supplier::find($request->supplier_id);

        $index->value   = $request->value;
        $index->date_po = date('Y-m-d', strtotime($request->date_po));

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deletePo(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        if((!$this->usergrant($index->prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($index->prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\Po', 'DELETED', $index);

    	Po::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function changePurchasing(Request $request)
    {
        $index = PrDetail::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHANGE_PURCHASING', $index);

        $index->purchasing_id = $request->purchasing_id;

        $index->save();
    }

    public function changeStatus(Request $request)
    {
        $index = PrDetail::find($request->id);

        if($index->status != "CANCEL")
        {
            $this->saveArchive('App\Models\Po', 'CHANGE_STATUS', $index);

            $index->status = $request->status;

            $index->save();
        }
    }

    public function checkAudit(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHECK_AUDIT', $index);

        $index->check_audit = $request->check_audit;

        $index->save();
    }

    public function checkFinance(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHECK_FINANCE', $index);

        $index->check_finance = $request->check_finance;

        $index->save();
    }

    public function noteAudit(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'NOTE_AUDIT', $index);

        $index->note_audit = $request->note_audit;

        $index->save();
    }

    public function pdf(Request $request)
    {
        $message = [
            'size.required' => 'This field required.',
            'orientation.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size' => 'required',
            'orientation' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-pr-error', 'Something Errors');
        }

        $index = Pr::find($request->pr_id);

        $pdf = PDF::loadView('backend.pr.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream($index->no_pr.'_'.date('Y-m-d').'.pdf');
    }

    public function dashboard(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(date_order) as year'))->orderBy('date_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if (!Auth::user()->can('allSales-spk')) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        return view('backend.pr.dashboard')->with(compact('request', 'year', 'month', 'sales'));
    }

    public function ajaxDashboard(Request $request)
    {

        $f_month = $this->filter($request->f_month, date('n'));
        $f_year  = $this->filter($request->f_year, date('Y'));
        $f_sales  = $this->filter($request->f_sales, Auth::id());
        $f_budget  = $this->filter($request->f_budget);

        $sql_production = '
            (
                /* sales -> spk */
                SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                FROM
                (
                    /* spk -> production with realOmset */
                    SELECT
                        production.spk_id, 
                        production.name, 
                        production.sales_id,
                        (@totalHM := production.totalHM) as totalHM,
                        production.totalHE,
                        (@totalHJ := production.totalHJ) as totalHJ,
                        @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                        @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                        (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
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
                        GROUP BY spk.id
                    ) production
                ) production
                GROUP BY production.spk_id
            ) production
        ';

        $sql_pr = '
            (
                /* spk -> pr */
                SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                FROM
                (
                    /* spk -> pr */
                    SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                    FROM `pr`
                    LEFT JOIN
                    (
                        /* pr_detail -> po */
                        SELECT
                            `pr_detail`.`id` as pr_detail_id,
                            `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                            SUM(`po`.`value`) as totalValue
                        FROM `pr_detail`
                        JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                        GROUP BY `pr_detail`.`id`
                    ) pr_detail ON pr.id = pr_detail.pr_id
                    WHERE pr.type = "PROJECT"
                    GROUP BY pr.id
                ) pr
                GROUP BY pr.spk_id
            ) pr
        ';

        $index = Spk::select(
            'spk.id', 
            'spk.name', 
            'spk.spk', 
            'production.totalHM', 
            'production.totalHE', 
            'production.totalHJ', 
            DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
            DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
            DB::raw('(
                CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                THEN 100 
                ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                END
            ) AS margin
            '),
            DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
            DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

        )
        ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
        ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

        if($f_month != '')
        {
            $index->whereMonth('spk.date', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('spk.date', $f_year);
        }

        if($f_sales == 'staff')
        {
            $index->whereIn('spk.sales_id', Auth::user()->staff());
        }
        else if($f_sales != '')
        {
            $index->where('spk.sales_id', $f_sales);
        }

        if ($f_budget != '') {
            if ($f_budget == 1) {
                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '>=', 0);
            } else {
                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '<', 0);
            }
        }

        $index = $index->get();

        $data = '';

        $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
        foreach ($index as $list) {
            $data[] = [
                'id' => $list->id,
                'spk' => $list->spk,
                'name' => $list->name,
                'totalHM' => number_format($list->totalHM),
                'totalHE' => number_format($list->totalHE),
                'totalHJ' => number_format($list->totalHJ),
                'totalPR' => number_format($list->totalPR),
                'profit' => number_format($list->profit),
                'margin' => number_format($list->margin, 2).' %',
                'budget' => number_format($list->budget),
                'budgetE' => number_format($list->budgetE),
            ];

            $allTotalHM      += $list->totalHM;
            $allTotalHE      += $list->totalHE;
            $allTotalHJ      += $list->totalHJ;
            $allTotalPR      += $list->totalPR;
            $allTotalProfit  += $list->profit;
            $allTotalBudget  += $list->budget;
            $allTotalBudgetE += $list->budgetE; 
        }

        return compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');;
    }

    public function datatablesDetailDashboard(Request $request)
    {
        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $index = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr_detail.purchasing_id')
            ->select(
                'pr_detail.*',
                'spk.spk',
                'spk.name as spk_name',
                'pr.id as pr_id',
                'pr.name',
                'pr.deadline',
                'pr.no_pr',
                'users.fullname as purchasing',
                'pr_detail.purchasing_id',
                'po.countPO',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->where('spk.id', $request->id)
            ->orderBy('pr_detail.id', 'DESC')
            ->distinct();

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('po', function ($index){
            $html = '';

            $html .= '<table class="table table-striped">';

            $html .= '
                <tr>
                    <th>No App\Models\Po</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Value</th>
                </tr>
            ';

            foreach ($index->po as $list) {
                $html .= '
                    <tr>
                        <td>'.$list->no_po.'</td>
                        <td>'.date('d/m/Y', strtotime($list->date_po)).'</td>
                        <td>'.number_format($list->quantity).'</td>
                        <td>'.number_format($list->value).'</td>
                    </tr>
                ';
            }
            

            $html .= '</table>';

            return $html;

        });

        $datatables->editColumn('purchasing', function ($index){
            return $index->purchasing ?? 'not set';
        });

        $datatables->editColumn('date_po', function ($index){
            return date('d/m/Y', strtotime($index->date_po));
        });

        $datatables->editColumn('value', function ($index){
            return number_format($index->value);
        });

        $datatables->editColumn('quantity', function ($index){
            return $index->quantity . ' ' . $index->unit;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('view-pr') )
            {
                $html .= '
                    <a href="'.route('backend.pr.edit', ['id' => $index->pr_id]).'" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
                ';
            }
            
            // if( Auth::user()->can('delete-pr') && ($this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id)) )
            // {
            //     $html .= '
            //         <button type="button" class="btn btn-xs btn-danger delete-pr" data-toggle="modal" data-target="#delete-pr" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
            //     ';
            // }
                
            // if( Auth::user()->can('pdf-pr') )
            // {
            //     $html .= '
            //         <button type="button" class="btn btn-xs btn-primary pdf-pr" data-toggle="modal" data-target="#pdf-pr" data-id="'.$index->pr_id.'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
            //     ';
            // }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function excel(Request $request)
    {
        $f_month = $this->filter($request->xls_month, date('n'));
        $f_year  = $this->filter($request->xls_year, date('Y'));

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        Excel::create('pr-'.$f_year.$f_month.'-'.date('dmYHis'), function ($excel) use ($f_year, $f_month, $purchasing_position, $purchasing_user) {
            $excel->sheet('Dashboard', function ($sheet) use ($f_year, $f_month) {

                $sql_production = '
                    (
                        /* sales -> spk */
                        SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                        FROM
                        (
                            /* spk -> production with realOmset */
                            SELECT
                                production.spk_id, 
                                production.name, 
                                production.sales_id,
                                (@totalHM := production.totalHM) as totalHM,
                                production.totalHE,
                                (@totalHJ := production.totalHJ) as totalHJ,
                                @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                                @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                                (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
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
                                GROUP BY spk.id
                            ) production
                        ) production
                        GROUP BY production.spk_id
                    ) production
                ';

                $sql_pr = '
                    (
                        /* spk -> pr */
                        SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                        FROM
                        (
                            /* spk -> pr */
                            SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                            FROM `pr`
                            LEFT JOIN
                            (
                                /* pr_detail -> po */
                                SELECT
                                    `pr_detail`.`id` as pr_detail_id,
                                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                                    SUM(`po`.`value`) as totalValue,
                                    COUNT(`po`.`id`) as countPO,
                                    countCheckAudit,
                                    countCheckFinance
                                FROM `pr_detail`
                                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                                GROUP BY `pr_detail`.`id`
                            ) pr_detail ON pr.id = pr_detail.pr_id
                            GROUP BY pr.id
                        ) pr
                        GROUP BY pr.spk_id
                    ) pr
                ';

                $index = Spk::select(
                    'spk.id', 
                    'spk.name', 
                    'spk.spk', 
                    'production.totalHM', 
                    'production.totalHE', 
                    'production.totalHJ', 
                    DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
                    DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
                    DB::raw('(
                        CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                        THEN 100 
                        ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                        END
                    ) AS margin
                    '),
                    DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
                    DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

                )
                ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
                ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

                if($f_month != '')
                {
                    $index->whereMonth('spk.date', $f_month);
                }

                if($f_year != '')
                {
                    $index->whereYear('spk.date', $f_year);
                }

                $index = $index->get();

                $data = '';

                $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
                foreach ($index as $list) {
                    $data[] = [
                        'id' => $list->id,
                        'spk' => $list->spk,
                        'name' => $list->name,
                        'totalHM' => number_format($list->totalHM),
                        'totalHE' => number_format($list->totalHE),
                        'totalHJ' => number_format($list->totalHJ),
                        'totalPR' => number_format($list->totalPR),
                        'profit' => number_format($list->profit),
                        'margin' => number_format($list->margin, 2).' %',
                        'budget' => number_format($list->budget),
                        'budgetE' => number_format($list->budgetE),
                    ];

                    $allTotalHM      += $list->totalHM;
                    $allTotalHE      += $list->totalHE;
                    $allTotalHJ      += $list->totalHJ;
                    $allTotalPR      += $list->totalPR;
                    $allTotalProfit  += $list->profit;
                    $allTotalBudget  += $list->budget;
                    $allTotalBudgetE += $list->budgetE; 
                }

                $index = compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');

                $sheet->fromArray($index['data']);
                $sheet->row(1, [
                    'SPK',
                    'Project Name',
                    'Total Modal Price',

                    'Total Estimator Price',
                    'Total Sell Price',
                    'Total App\Pr',

                    'Profit',
                    'Margin',
                    'Budget',

                    'Budget Estimator'
                ]);
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Overbudget', function ($sheet) use ($f_year, $f_month, $purchasing_position, $purchasing_user) {

                $sql_production = '
                    (
                        /* sales -> spk */
                        SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                        FROM
                        (
                            /* spk -> production with realOmset */
                            SELECT
                                production.spk_id, 
                                production.name, 
                                production.sales_id,
                                (@totalHM := production.totalHM) as totalHM,
                                production.totalHE,
                                (@totalHJ := production.totalHJ) as totalHJ,
                                @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                                @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                                (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
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
                                GROUP BY spk.id
                            ) production
                        ) production
                        GROUP BY production.spk_id
                    ) production
                ';

                $sql_pr = '
                    (
                        /* spk -> pr */
                        SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                        FROM
                        (
                            /* spk -> pr */
                            SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                            FROM `pr`
                            LEFT JOIN
                            (
                                /* pr_detail -> po */
                                SELECT
                                    `pr_detail`.`id` as pr_detail_id,
                                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                                    SUM(`po`.`value`) as totalValue,
                                    COUNT(`po`.`id`) as countPO,
                                    countCheckAudit,
                                    countCheckFinance
                                FROM `pr_detail`
                                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                                GROUP BY `pr_detail`.`id`
                            ) pr_detail ON pr.id = pr_detail.pr_id
                            GROUP BY pr.id
                        ) pr
                        GROUP BY pr.spk_id
                    ) pr
                ';

                $index = Spk::select(
                    'spk.id', 
                    'spk.name', 
                    'spk.spk', 
                    'production.totalHM', 
                    'production.totalHE', 
                    'production.totalHJ', 
                    DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
                    DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
                    DB::raw('(
                        CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                        THEN 100 
                        ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                        END
                    ) AS margin
                    '),
                    DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
                    DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

                )
                ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
                ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

                if($f_month != '')
                {
                    $index->whereMonth('spk.date', $f_month);
                }

                if($f_year != '')
                {
                    $index->whereYear('spk.date', $f_year);
                }

                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '<', 0);

                $index = $index->get();

                $data = '';

                $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
                foreach ($index as $list) {
                    $data[] = [
                        'id' => $list->id,
                        'spk' => $list->spk,
                        'name' => $list->name,
                        'totalHM' => number_format($list->totalHM),
                        'totalHE' => number_format($list->totalHE),
                        'totalHJ' => number_format($list->totalHJ),
                        'totalPR' => number_format($list->totalPR),
                        'profit' => number_format($list->profit),
                        'margin' => number_format($list->margin, 2).' %',
                        'budget' => number_format($list->budget),
                        'budgetE' => number_format($list->budgetE),
                    ];

                    $allTotalHM      += $list->totalHM;
                    $allTotalHE      += $list->totalHE;
                    $allTotalHJ      += $list->totalHJ;
                    $allTotalPR      += $list->totalPR;
                    $allTotalProfit  += $list->profit;
                    $allTotalBudget  += $list->budget;
                    $allTotalBudgetE += $list->budgetE; 
                }

                $index = compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');

                $sheet->fromArray($index['data']);
                $sheet->row(1, [
                    'SPK',
                    'Project Name',
                    'Total Modal Price',

                    'Total Estimator Price',
                    'Total Sell Price',
                    'Total App\Pr',

                    'Profit',
                    'Margin',
                    'Budget',

                    'Budget Estimator'
                ]);
                $sheet->setFreeze('A1');
            });

            $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
                $query->whereIn('position', explode(', ' , $purchasing_position->value))
                ->orWhereIn('id', explode(', ' , $purchasing_user->value));
            })->get();

            foreach ($purchasing as $list) {

                $excel->sheet($list->fullname, function ($sheet) use ($f_year, $f_month, $list) {

                    $index = PrDetail::select(DB::raw('
                            pr_detail.*,
                            spk.spk,
                            users.fullname as purchasing,
                            pr.created_at as date_submit
                        '))
                        ->leftJoin('spk', 'pr_detail.spk_id', '=', 'spk.id')
                        ->leftJoin('users', 'pr_detail.purchasing_id', '=', 'users.id')
                        ->join('pr', 'pr_detail.pr_id', '=', 'pr.id')
                        ->where('pr_detail.confirm', 1)
                        ->where('purchasing_id', $list->id);

                    if($f_month != '')
                    {
                        $index->whereMonth('spk.date', $f_month);
                    }

                    if($f_year != '')
                    {
                        $index->whereYear('spk.date', $f_year);
                    }

                    $index = $index->get();

                    $data = '';
                    foreach ($index as $list) {
                        $data[] = [
                            'SPK' => $list->spk,
                            'No App\Pr' => $list->no_pr,
                            'Item' => $list->item,
                            'Purchasing' => $list->purchasing,
                            'No App\Models\Po' => $list->no_po,
                            'Date App\Models\Po' => $list->date_po,
                            'Type' => $list->type,
                            'Supplier' => $list->name_supplier,
                            'Name Rekening' => $list->name_rekening,
                            'No Rekening' => $list->no_rekening,
                            'Value' => number_format($list->value),
                            'Date Confirm' => $list->datetime_confirm,
                            'Check Audit' => $list->check_audit ? 'Yes' : 'No',
                        ];
                    }

                    $sheet->fromArray($data);
                    $sheet->setFreeze('A1');
                });
            }


        })->download('xls');
    }

    public function item(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(date_order) as year'))->orderBy('date_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $user = Pr::join('users', 'users.id', '=', 'pr.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-pr'))
        {
            $user->whereIn('pr.user_id', Auth::user()->staff());
        }

        $user = $user->get();

        return view('backend.pr.item')->with(compact('request', 'year', 'month', 'user'));
    }

    public function datatablesItem(Request $request)
    {
        $f_user   = $this->filter($request->f_user, Auth::id());
        $f_month  = $this->filter($request->f_month, date('n'));
        $f_year   = $this->filter($request->f_year, date('Y'));
        $f_status = $this->filter($request->f_status, 'WAITING');
        $f_id     = $this->filter($request->f_id);

        $index = Po::leftJoin('pr_detail', 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr.user_id')
            ->select('po.id', 'po.quantity', 'po.status_received', 'pr_detail.item', 'pr.no_pr', 'pr.date_order', 'pr_detail.date_request', 'spk.spk', 'spk.name', 'users.fullname', 'pr.user_id');

        if($f_id != '')
        {
            $index->where('po.id', $f_id);
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr.date_order', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr.date_order', $f_year);
            }

            if($f_user == 'staff')
            {
                $index->whereIn('pr.user_id', Auth::user()->staff());
            }
            else if($f_user != '')
            {
                $index->where('pr.user_id', $f_user);
            }

            if($f_status != '')
            {
                $index->where('po.status_received', $f_status);
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('fullname', function ($index){
            return $index->fullname ?? 'not set';
        });

        $datatables->editColumn('date_order', function ($index){
            return date('d/m/Y', strtotime($index->date_order));
        });

        $datatables->editColumn('date_request', function ($index){
            return date('d/m/Y', strtotime($index->date_request));
        });

        $datatables->editColumn('status_received', function ($index){
            if($index->status_received == "CONFIRMED")
            {
                return "Confirmed, Date Received : " . date('d/m/Y', strtotime($index->date_received));
            }
            else if($index->status_received == "COMPLAIN")
            {
                return "Complain, Reason : " . $index->note_received;
            }
            else
            {
                return "Item on process";
            }
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if( $this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id) )
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('receivedItem-pr') && $index->status_received == 'WAITING')
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success pr-confirmItem" data-toggle="modal" data-target="#pr-confirmItem" data-id="'.$index->id.'">Confirm</button>
                ';
            
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger pr-complainItem" data-toggle="modal" data-target="#pr-complainItem" data-id="'.$index->id.'">Complain</button>
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function receivedItem(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        $this->saveArchive('App\Models\Po', 'RECEIVED_ITEM', $index);

        $index->status_received = "CONFIRMED";
        $index->rating          = $request->rating;
        $index->date_received   = date('Y-m-d');

        $index->save();

        $prDetail = PrDetail::find($index->pr_detail_id);

        $html = '
            Item Has Been recieved, Item : '.$prDetail->item.'
        ';

        User::find($prDetail->purchasing_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => $prDetail->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function complainItem(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        $message = [
            'date_received.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'note_received' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('pr-complainItem-error', '');
        }

        $this->saveArchive('App\Models\Po', 'COMPLAIN', $index);

        $index->status_received = "COMPLAIN";
        $index->date_received   = date('Y-m-d');
        $index->rating          = 0;
        $index->note_received   = $request->note_received;

        $index->save();

        $prDetail = PrDetail::find($index->pr_detail_id);

        $html = '
            There is a complain item, Item : '.$prDetail->item.'
        ';

        User::find($prDetail->purchasing_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => $prDetail->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function getPr(Request $request)
    {
        $date = date('Y-m-d');
        if (isset($request->date) && $request->date != '') {
            $date = $request->date;
        }

        $user = Auth::user();
        if (isset($request->user_id) && $request->user_id != '') {
            $user = User::find($request->user_id);
        }

        $pr = Pr::select('no_pr')
            ->where('no_pr', 'like', date('y', strtotime($date)) . substr(strtolower($user->nickname), 0, 3) . "%")
            ->orderBy('no_pr', 'desc');

        $count   = $pr->count();
        $current = $pr->first();

        if ($count == 0) {
            $numberPr = 0;
        } else {
            $numberPr = intval(substr($current->no_pr, -4, 4));
        }

        return date('y', strtotime($date)) . substr(strtolower($user->nickname), 0, 3) . str_pad($numberPr + 1, 4, '0', STR_PAD_LEFT);
    }
}
