<?php

namespace App\Http\Controllers\Backend;

use App\Models\Activity;
use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Datatables;
use Validator;

use App\Notifications\Notif;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$year = Activity::select(DB::raw('YEAR(date_activity) as year'))->orderBy('date_activity', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    	$user = User::orderBy('fullname', 'ASC')->orderBy('Active', 'DESC')->get();

        return view('backend.activity.index')->with(compact('request', 'user', 'year', 'month'));
    }

    public function datatables(Request $request)
    {
        $f_id    = $this->filter($request->f_id);

    	$f_year  = $this->filter($request->f_year, date('Y'));
        $f_month = $this->filter($request->f_month, date('n'));
        $f_user  = $this->filter($request->f_user, Auth::id());

        $f_start_range = $this->filter($request->f_start_range);
        $f_end_range = $this->filter($request->f_end_range);
        $f_check = $this->filter($request->f_check);

        $index = Activity::join('users', 'activities.user_id', 'users.id')->select('activities.*', 'users.fullname');

        if($f_id != '')
        {
            $index->where('activities.id', $f_id);
        }
        else
        {
            if ($f_month != '') {
                $index->whereMonth('date_activity', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('date_activity', $f_year);
            }

            if ($f_user == 'staff') {
                $index->whereIn('user_id', Auth::user()->staff());
            } else if ($f_user != '') {
                $index->where('user_id', $f_user);
            }

            if($f_start_range != '' || $f_end_range != '')
            {
                if($f_start_range == '' && $f_end_range != '')
                {
                    $index->where('activities.created_at', '<=', $f_start_range . ' 00:00:00');
                }
                else if ($f_start_range != '' && $f_end_range == '')
                {
                    $index->where('activities.created_at', '>=', $f_end_range . ' 00:00:00');
                }
                else
                {
                    $index->whereBetween('activities.created_at', [$f_start_range, $f_end_range]);
                }
                
            }

            if ($f_check != '')
            {
                $index->where('activities.check_hrd', $f_check);
            }
        }

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-activity') && $index->confirm == null) {
                $html .= '
                    <a href="'.route('backend.activity.edit', $index->id).'" class="btn btn-xs btn-warning"><i class="fa fa-edit" aria-hidden="true"></i></a>
                ';
            }

            if (Auth::user()->can('delete-activity') && $index->confirm == null) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-activity" data-toggle="modal" data-target="#delete-activity" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('confirm-activity') && $this->childgrant($index->user_id)) {
                if ($index->confirm) {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-dark unconfirm-activity" data-toggle="modal" data-target="#unconfirm-activity"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-times" aria-hidden="true"></i></button>
	                ';
                } else {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-info confirm-activity" data-toggle="modal" data-target="#confirm-activity"
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

        $datatables->editColumn('date_activity', function ($index) {
            $html = date('d M Y', strtotime($index->date_activity));
            
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
        return view('backend.activity.create');
    }

    public function store(Request $request)
    {
    	
        $message = [
            'date_activity.required' => 'This field required.',
            'date_activity.date'     => 'Date Format.',
            'activity.required'      => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'date_activity' => 'required|date',
            'activity'      => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-activity-error', 'Error');
        }

	    DB::transaction(function () use ($request){
	        $index = new Activity;

	        $index->user_id       = Auth::id();
	        $index->date_activity = $request->date_activity;
	        $index->activity      = $request->activity;

	        $index->save();

            $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

            $html = '
                Activity : '.Auth::user()->fullname.'
            ';

            foreach ($parent as $list) {
                $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.activity', ['f_id' => $index->id]) ) );
            }
	    });

        return redirect()->route('backend.activity')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = Activity::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-activity') || $this->childgrant($index->user_id))
        {
            return redirect()->route('backend.activity')->with('failed', 'Access Denied');
        }

        return view('backend.activity.edit')->with(compact('index'));
    }

    public function update($id, Request $request)
    {
        $index = Activity::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-activity') || $this->childgrant($index->user_id))
        {
            return redirect()->route('backend.activity')->with('failed', 'Access Denied');
        }

        DB::transaction(function () use ($request, $index){

	        $message = [
	            'date_activity.required' => 'This field required.',
	            'date_activity.date'     => 'Date Format.',
	            'activity.required'      => 'This field required.',
	        ];

	        $validator = Validator::make($request->all(), [
	            'date_activity' => 'required|date',
	            'activity'      => 'required',
	        ], $message);

	        if ($validator->fails()) {
	            return redirect()->back()->withErrors($validator)->withInput();
	        }


	        $this->saveArchive('App\\Models\\Activity', 'UPDATED', $index);

	        $index->date_activity = $request->date_activity;
	        $index->activity      = $request->activity;

	        $index->save();

            $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

            $html = '
                Form Absence : '.Auth::user()->fullname.'
            ';

            foreach ($parent as $list) {
                $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.absence', ['f_id' => $index->id]) ) );
            }
	    });

        return redirect()->route('backend.activity')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
    	DB::transaction(function () use ($request){
	        $index = Activity::find($request->id);
	        $this->saveArchive('App\\Models\\Activity', 'DELETED', $index);

	        Activity::destroy($request->id);
	    });

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function confirm(Request $request)
    {
        $index = Activity::find($request->id);

        if (!$this->childgrant($index->user_id)) {
            return redirect()->route('backend.activity')->with('failed', 'Access Denied');
        }
        
        DB::transaction(function () use ($request, $index){
	        $this->saveArchive('App\\Models\\Activity', 'CONFIRM', $index);

	        $index->confirm = $index->confirm ? null : date('Y-m-d H:i:s');
	        $index->save();
	    });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-activity')) {

        	DB::transaction(function () use ($request){
	            $index = Activity::find($request->id);
	            $this->saveMultipleArchive('App\\Models\\Activity', 'DELETED', $index);

	            Activity::destroy($request->id);
	        });

            return redirect()->back()->with('success', 'Data Has Been Deleted');
        } else if ($request->action == 'confirm' && Auth::user()->can('confirm-activity')) {

        	DB::transaction(function () use ($request){
	            $index = Activity::whereIn('id', $request->id);
	            $this->saveMultipleArchive('App\\Models\\Activity', 'CONFIRM', $index);

	            Activity::whereIn('id', $request->id)->update(['confirm' => date('Y-m-d H:i:s')]);
	        });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        } else if ($request->action == 'unconfirm' && Auth::user()->can('confirm-activity')) {

            DB::transaction(function () use ($request){
                $index = Activity::whereIn('id', $request->id);
                $this->saveMultipleArchive('App\\Models\\Activity', 'CONFIRM', $index);

                Activity::whereIn('id', $request->id)->update(['confirm' => null]);
            });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }

    public function checkHRD(Request $request)
    {
        $index = Activity::find($request->id);

        $this->saveArchive('App\\Models\\Activity', 'CHECK_HRD', $index);

        $index->check_hrd = $request->check_hrd;

        $index->save();
    }
}
