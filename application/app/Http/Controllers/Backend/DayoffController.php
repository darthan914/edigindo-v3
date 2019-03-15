<?php

namespace App\Http\Controllers\Backend;

use App\Models\DayoffUser;
use App\Models\Dayoff;
use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Datatables;
use Validator;

use App\Notifications\Notif;

class DayoffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$f_year  = $this->filter($request->f_year, date('Y'));

    	$year = Dayoff::select(DB::raw('YEAR(date_dayoff) as year'))->orderBy('date_dayoff', 'ASC')->distinct()->get();

    	$user = User::orderBy('fullname', 'ASC')->orderBy('Active', 'DESC')->get();

    	$user_available = User::select('id')->where('active', 1)->get();
    	$month = 12 - (date('n') - 1);
        foreach ($user_available as $list) {

            $count_dayoff = Dayoff::where('user_id', $list->id)
                ->whereYear('date_dayoff', date('Y') - 1)
                ->where('confirm', 1)
                ->sum('total_dayoff');

            if($count_dayoff < 0)
            {
                $month = $month + ($count_dayoff > 12 ? 12 - $count_dayoff : 0);
            }

            DayoffUser::firstOrCreate(['user_id' => $list->id, 'year' => $f_year], ['number_available' => $month]);
        };

        $type         = ["CUTI" => "Cuti"];
        $total_dayoff = ["1" => "Full", "0.5" => "Half"];

        $count_dayoff = Dayoff::where('user_id', Auth::id())
            ->whereYear('date_dayoff', date('Y'))
            ->where('confirm', 1)
            ->sum('total_dayoff');

        $number_available = DayoffUser::where('user_id', Auth::id())->where('year', date('Y'))->first()->number_available - $count_dayoff;

        return view('backend.dayoff.index')->with(compact('request', 'user', 'year', 'type', 'total_dayoff', 'number_available'));
    }

    public function datatables(Request $request)
    {
        $f_id    = $this->filter($request->f_id);

    	$f_year  = $this->filter($request->f_year, date('Y'));
        $f_user  = $this->filter($request->f_user, Auth::id());

        $f_start_range = $this->filter($request->f_start_range);
        $f_end_range = $this->filter($request->f_end_range);
        $f_check = $this->filter($request->f_check);

        $sql_remains = "
        (
            SELECT `dayoffs`.`user_id`, `dayoff_users`.`year`, `dayoff_users`.`number_available` - SUM(`dayoffs`.`total_dayoff`) AS `leave_remains`, `dayoff_users`.`number_available`
            FROM `dayoffs`
            LEFT JOIN `dayoff_users` 
            ON `dayoffs`.`user_id` = `dayoff_users`.`user_id` AND YEAR(`dayoffs`.`date_dayoff`) = `dayoff_users`.`year` AND `dayoffs`.confirm IS NOT NULL
            WHERE 1
            GROUP BY `dayoffs`.`user_id`, `dayoff_users`.`year`
        ) AS `dayoff_users`
        ";

        $index = Dayoff::join('users', 'dayoffs.user_id', 'users.id')
            ->leftjoin(DB::raw($sql_remains), function($join){
                $join->on('dayoffs.user_id', '=', 'dayoff_users.user_id')
                ->whereRaw('YEAR(`dayoffs`.`date_dayoff`) = `dayoff_users`.`year`');
            })
            ->select('dayoffs.*', 'users.fullname', 'dayoff_users.leave_remains');

        if($f_id != '')
        {
            $index->where('dayoffs.id', $f_id);
        }
        else
        {
            if ($f_year != '') {
                $index->whereYear('date_dayoff', $f_year);
            }

            if ($f_user == 'staff') {
                $index->whereIn('dayoffs.user_id', Auth::user()->staff());
            } else if ($f_user != '') {
                $index->where('dayoffs.user_id', $f_user);
            }

            if($f_start_range != '' || $f_end_range != '')
            {
                if($f_start_range == '' && $f_end_range != '')
                {
                    $index->where('date_dayoff', '<=', $f_start_range);
                }
                else if ($f_start_range != '' && $f_end_range == '')
                {
                    $index->where('date_dayoff', '>=', $f_end_range);
                }
                else
                {
                    $index->whereBetween('dayoffs.created_at', [$f_start_range, $f_end_range]);
                }
                
            }

            if ($f_check != '')
            {
                $index->where('dayoffs.check_hrd', $f_check);
            }
        }

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-dayoff') && $index->confirm == null) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning edit-dayoff" data-toggle="modal" data-target="#edit-dayoff"
	                   data-id="' . $index->id . '"
	                   data-date_dayoff="' . $index->date_dayoff . '"
	                   data-total_dayoff="' . $index->total_dayoff . '"
	                   data-type="' . $index->type . '"
	                   data-note="' . $index->note . '"
                   ><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('delete-dayoff')) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-dayoff" data-toggle="modal" data-target="#delete-dayoff" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('confirm-dayoff') && $this->childgrant($index->user_id)) {
                if ($index->confirm) {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-dark unconfirm-dayoff" data-toggle="modal" data-target="#unconfirm-dayoff"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-times" aria-hidden="true"></i></button>
	                ';
                } else {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-info confirm-dayoff" data-toggle="modal" data-target="#confirm-dayoff"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-check" aria-hidden="true"></i></button>
	                ';
                }
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

        $datatables->editColumn('check_hrd', function ($index) {
            $html = '';

            // Class checkHRD
            if(Auth::user()->can('checkHRD-activity'))
            {
                $html .= '
                    <input type="checkbox" data-id="' . $index->id . '" value="1" name="check_hrd" '.($index->check_hrd ? 'checked' : '').'>
                ';
            }
            else if($index->check_hrd)
            {
                $html .=
                '
                    <i class="fa fa-check" aria-hidden="true"></i>
                ';
            }
           
            return $html;
        });

        $datatables->editColumn('date_dayoff', function ($index) {
            $html = date('d M Y', strtotime($index->date_dayoff));
            
            return $html;
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d M Y', strtotime($index->created_at));
            
            return $html;
        });

        $datatables->editColumn('confirm', function ($index) {
            $html = '';
            if ($index->confirm) {
                $html .= '
                    <span class="label label-success">'.date('d/M/Y H:i', strtotime($index->confirm)).'</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Unconfirm</span>
                ';
            }
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $count_dayoff = Dayoff::where('user_id', Auth::id())->whereYear('date_dayoff', date('Y', strtotime($request->date_dayoff)))->sum('total_dayoff');
        $number_available = DayoffUser::where('user_id', Auth::id())->where('year', date('Y'))->first()->number_available - $count_dayoff;

        return view('backend.dayoff.create', compact('number_available'));
    }

    public function store(Request $request)
    {
    	
        $message = [
            'date_dayoff.required' => 'This field required.',
            'date_dayoff.date'     => 'Date Format.',
            'total_dayoff.required'        => 'This field required.',
            'type.required'        => 'This field required.',
            'note.required'        => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'date_dayoff' => 'required|date',
            'total_dayoff'        => 'required',
            'type'        => 'required',
            'note'        => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-dayoff-error', 'Error');
        }

        $count_dayoff = Dayoff::where('user_id', Auth::id())
            ->whereYear('date_dayoff', date('Y'))
            ->where('confirm', 1)
            ->sum('total_dayoff');

        $number_available = DayoffUser::where('user_id', Auth::id())->where('year', date('Y'))->first()->number_available - (12 - date('n'));

	    DB::transaction(function () use ($request, $number_available){
	        $index = new Dayoff;

	        $index->user_id      = Auth::id();
	        $index->date_dayoff  = $request->date_dayoff;
	        $index->total_dayoff = $request->total_dayoff;
	        $index->type         = $request->type;
	        $index->note         = $request->note;

            if($number_available < -6)
            {
                $index->save();

                $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

                $html = '
                    Dayoff : '.Auth::user()->fullname.'
                ';

                foreach ($parent as $list) {
                    $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.dayoff', ['f_id' => $index->id]) ) );
                }
            }
	    });

        if($number_available <= -6)
        {
            return redirect()->route('backend.dayoff')->with('error', 'Not Allow to Leave');
        }
        else if($count_dayoff > $number_available)
        {
            return redirect()->route('backend.dayoff')->with('warning', 'Potensial Reject Leave');
        }
        else if($request->date_dayoff < date('Y-m-d', strtotime('- 2weeks')))
        {
            return redirect()->route('backend.dayoff')->with('warning', 'Potensial Reject Leave Over than 2 week');
        }
        else
        {
            return redirect()->route('backend.dayoff')->with('success', 'Data Has Been Added');
        }
    }

    public function edit($id)
    {
        $index = Dayoff::find($id);

        $number_available = DayoffUser::where('user_id', Auth::id())->where('year', date('Y'))->where('id', '<>', $id)->first()->number_available;

        if(!$this->usergrant($index->user_id, 'allUser-dayoff') || !$this->childgrant($index->user_id))
    	{
    		return redirect()->route('backend.dayoff')->with('failed', 'Access Denied');
    	}

        return view('backend.dayoff.edit')->with(compact('index', 'number_available'));
    }

    public function update($id, Request $request)
    {
        $index = Dayoff::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-dayoff') || !$this->childgrant($index->user_id))
    	{
    		return redirect()->route('backend.dayoff')->with('failed', 'Access Denied');
    	}

        $count_dayoff = Dayoff::where('user_id', Auth::id())
            ->whereYear('date_dayoff', date('Y'))
            ->where('confirm', 1)
            ->sum('total_dayoff');

        $number_available = DayoffUser::where('user_id', Auth::id())->where('year', date('Y'))->first()->number_available - (12 - date('n'));

    	DB::transaction(function () use ($request, $index, $number_available){

	        $message = [
	            'date_dayoff.required' => 'This field required.',
	            'date_dayoff.date'     => 'Date Format.',
	            'total_dayoff.required'        => 'This field required.',
	            'type.required'        => 'This field required.',
	            'note.required'        => 'This field required.',
	        ];

	        $validator = Validator::make($request->all(), [
	            'date_dayoff' => 'required|date',
	            'total_dayoff'        => 'required',
	            'type'        => 'required',
	            'note'        => 'required',
	        ], $message);

	        if ($validator->fails()) {
	            return redirect()->back()->withErrors($validator)->withInput();
	        }


	        $this->saveArchive('App\Models\DayOff', 'UPDATED', $index);

	        $index->date_dayoff  = $request->date_dayoff;
	        $index->total_dayoff = $request->total_dayoff;
	        $index->type         = $request->type;
	        $index->note         = $request->note;

            if($number_available < -6)
            {
                $index->save();

                $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

                $html = '
                    Dayoff : '.Auth::user()->fullname.'
                ';

                foreach ($parent as $list) {
                    $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.dayoff', ['f_id' => $index->id]) ) );
                }
            }

	    });

       if($number_available <= -6)
        {
            return redirect()->route('backend.dayoff')->with('error', 'Not Allow to Leave');
        }
        else if($count_dayoff > $number_available)
        {
            return redirect()->route('backend.dayoff')->with('warning', 'Potensial Reject Leave');
        }
        else if($request->date_dayoff < date('Y-m-d', strtotime('- 2weeks')))
        {
            return redirect()->route('backend.dayoff')->with('warning', 'Potensial Reject Leave Over than 2 week');
        }
        else
        {
            return redirect()->route('backend.dayoff')->with('success', 'Data Has Been Added');
        }
    }

    public function delete(Request $request)
    {
    	DB::transaction(function () use ($request){
	        $index = Dayoff::find($request->id);
	        $this->saveArchive('App\Models\DayOff', 'DELETED', $index);

	        Dayoff::destroy($request->id);
	    });

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function confirm(Request $request)
    {
        $index = Dayoff::find($request->id);

        if (!$this->childgrant($index->user_id)) {
            return redirect()->route('backend.dayoff')->with('failed', 'Access Denied');
        }
        
        DB::transaction(function () use ($request, $index){
	        $this->saveArchive('App\Models\DayOff', 'CONFIRM', $index);

	        $index->confirm = $index->confirm ? null : date('Y-m-d H:i:s');
	        $index->save();
	    });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-dayoff')) {

        	DB::transaction(function () use ($request){
	            $index = Dayoff::find($request->id);
	            $this->saveMultipleArchive('App\Models\DayOff', 'DELETED', $index);

	            Dayoff::destroy($request->id);
	        });

            return redirect()->back()->with('success', 'Data Has Been Deleted');
        } else if ($request->action == 'confirm' && Auth::user()->can('confirm-dayoff')) {

        	DB::transaction(function () use ($request){
	            $index = Dayoff::whereIn('id', $request->id);
	            $this->saveMultipleArchive('App\Models\DayOff', 'CONFIRM', $index);

	            Dayoff::whereIn('id', $request->id)->update(['confirm' => date('Y-m-d H:i:s')]);
	        });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        } else if ($request->action == 'unconfirm' && Auth::user()->can('confirm-dayoff')) {

        	DB::transaction(function () use ($request){
	            $index = Dayoff::whereIn('id', $request->id);
	            $this->saveMultipleArchive('App\Models\DayOff', 'CONFIRM', $index);

	            Dayoff::whereIn('id', $request->id)->update(['confirm' => null]);
	        });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }

    public function setting(Request $request)
    {
        $user_available = User::select('id')->where('active', 1)->get();
        $month = 12 - (date('n') - 1);
        $f_year  = $this->filter($request->f_year, date('Y'));
        foreach ($user_available as $list) {

            $count_dayoff = Dayoff::where('user_id', $list->id)
                ->whereYear('date_dayoff', date('Y') - 1)
                ->where('confirm', 1)
                ->sum('total_dayoff');

            if($count_dayoff < 0)
            {
                $month = $month + ($count_dayoff < 0 ? $count_dayoff : 0);
            }

            DayoffUser::firstOrCreate(['user_id' => $list->id, 'year' => $f_year], ['number_available' => $month]);
        };


    	$year = Dayoff::select(DB::raw('YEAR(date_dayoff) as year'))->orderBy('date_dayoff', 'ASC')->distinct()->get();

        return view('backend.dayoff.setting')->with(compact('request', 'year'));
    }

    public function datatablesSetting(Request $request)
    {
    	$f_year  = $this->filter($request->f_year, date('Y'));

        $index = DayoffUser::where('year', $f_year)
        	->join('users', 'users.id', 'dayoff_users.user_id')
        	->select('dayoff_users.*', 'users.fullname')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            $html .= '
                <button type="button" class="btn btn-xs btn-warning updateSetting-dayoff" data-toggle="modal" data-target="#updateSetting-dayoff"
	                   data-id="' . $index->id . '"
	                   data-number_available="' . $index->number_available . '"
                   ><i class="fa fa-edit" aria-hidden="true"></i></button>
            ';
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }


    public function updateSetting(Request $request)
    {
        $message = [
            'number_available.required' => 'This field required.',
            'number_available.integer'  => 'Integer only.',
        ];

        $validator = Validator::make($request->all(), [
            'number_available' => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('updateSetting-dayoff-error', '');
        }

        $index = DayoffUser::find($request->id);

        $this->saveArchive('App\Models\DayOffUser', 'UPDATED', $index);

        $index->number_available = $request->number_available;

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function checkHRD(Request $request)
    {
        $index = Dayoff::find($request->id);

        $this->saveArchive('App\Models\DayOff', 'CHECK_HRD', $index);

        $index->check_hrd = $request->check_hrd;

        $index->save();
    }
}
