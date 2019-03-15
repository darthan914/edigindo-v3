<?php

namespace App\Http\Controllers\Backend;

use App\Models\FormAbsence;
use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Datatables;
use Validator;

use App\Notifications\Notif;

class AbsenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$year = FormAbsence::select(DB::raw('YEAR(date_absence) as year'))->orderBy('date_absence', 'ASC')->distinct()->get();

    	$user = User::orderBy('fullname', 'ASC')->orderBy('Active', 'DESC')->get();

        return view('backend.absence.index')->with(compact('request', 'user', 'year'));
    }

    public function datatables(Request $request)
    {
        $f_id    = $this->filter($request->f_id);
    	$f_year  = $this->filter($request->f_year, date('Y'));
        $f_user  = $this->filter($request->f_user, Auth::id());

        $f_start_range = $this->filter($request->f_start_range);
        $f_end_range = $this->filter($request->f_end_range);
        $f_check = $this->filter($request->f_check);
        $f_confirm = $this->filter($request->f_confirm);

        $index = FormAbsence::join('users', 'form_absences.user_id', 'users.id')->select('form_absences.*', 'users.fullname');

        if($f_id != '')
        {
            $index->where('form_absences.id', $f_id);
        }
        else
        {
            if ($f_year != '') {
                $index->whereYear('date_absence', $f_year);
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
                    $index->where('date_absence', '<=', $f_start_range);
                }
                else if ($f_start_range != '' && $f_end_range == '')
                {
                    $index->where('date_absence', '>=', $f_end_range);
                }
                else
                {
                    $index->whereBetween('form_absences.created_at', [$f_start_range, $f_end_range]);
                }
                
            }

            if ($f_check != '')
            {
                $index->where('form_absences.check_hrd', $f_check);
            }

            if ($f_confirm != '')
            {
                if($f_confirm == 1)
                {
                    $index->whereNotNull('form_absences.confirm');
                }
                else
                {
                    $index->whereNull('form_absences.confirm');
                }
                
            }
        }
        

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-absence') && $index->confirm == null) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning edit-absence" data-toggle="modal" data-target="#edit-absence"
	                   data-id="' . $index->id . '"
	                   data-date_absence="' . $index->date_absence . '"
	                   data-time_check_in="' . $index->time_check_in . '"
	                   data-time_check_out="' . $index->time_check_out . '"
	                   data-note="' . $index->note . '"
                   ><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('delete-absence') && $index->confirm == null) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-absence" data-toggle="modal" data-target="#delete-absence" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('confirm-absence') && $this->childgrant($index->user_id)) {
                if ($index->confirm) {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-dark unconfirm-absence" data-toggle="modal" data-target="#unconfirm-absence"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-times" aria-hidden="true"></i></button>
	                ';
                } else {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-info confirm-absence" data-toggle="modal" data-target="#confirm-absence"
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

        $datatables->editColumn('date_absence', function ($index) {
            $html = date('d M Y', strtotime($index->date_absence));
            
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

        $datatables->editColumn('check_hrd', function ($index) {
            $html = '';

            // Class checkHRD
            if(Auth::user()->can('checkHRD-absence'))
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

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        return view('backend.absence.create');
    }

    public function store(Request $request)
    {
    	
        $message = [
            'date_absence.required'   => 'This field required.',
            'date_absence.date'       => 'Date Format.',
            'time_check_in.required_without'  => 'This field required.',
            'time_check_out.required_without' => 'This field required.',
            'note.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'date_absence'   => 'required|date',
            'time_check_in'  => 'required_without:time_check_out',
            'time_check_out' => 'required_without:time_check_in',
            'note' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($request->time_check_in && $request->time_check_out) {
                $validator->errors()->add('time_check_in', 'Only One Can Fill');
                $validator->errors()->add('time_check_out', 'Only One Can Fill');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-absence-error', 'Error');
        }

	    DB::transaction(function () use ($request){
	        $index = new FormAbsence;

	        $index->user_id        = Auth::id();
	        $index->date_absence   = $request->date_absence;
	        $index->time_check_in  = $request->time_check_in;
	        $index->time_check_out = $request->time_check_out;
	        $index->note           = $request->note;

	        $index->save();

            $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

            $html = '
                Form Absence : '.Auth::user()->fullname.'
            ';

            foreach ($parent as $list) {
                $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.absence', ['f_id' => $index->id]) ) );
            }
	    });

        return redirect()->route('backend.absence')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = FormAbsence::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-absence') || $this->childgrant($index->user_id))
    	{
    		return redirect()->route('backend.absence')->with('failed', 'Access Denied');
    	}

        return view('backend.absence.edit')->with(compact('index'));
    }

    public function update(Request $request)
    {
        $index = FormAbsence::find($request->id);

        if(!$this->usergrant($index->user_id, 'allUser-absence') || $this->childgrant($index->user_id))
    	{
    		return redirect()->route('backend.absence')->with('failed', 'Access Denied');
    	}

    	DB::transaction(function () use ($request, $index){

	        $message = [
	            'date_absence.required'   => 'This field required.',
	            'date_absence.date'       => 'Date Format.',
	            'time_check_in.required'  => 'This field required.',
                'time_check_out.required' => 'This field required.',
	            'note.required' => 'This field required.',
	        ];

	        $validator = Validator::make($request->all(), [
	            'date_absence'   => 'required|date',
	            'time_check_in'  => 'required',
                'time_check_out' => 'required',
	            'note' => 'required',
	        ], $message);

            $validator->after(function ($validator) use ($request) {
                if ($request->time_check_in && $request->time_check_out) {
                    $validator->errors()->add('time_check_in', 'Only One Can Fill');
                    $validator->errors()->add('time_check_out', 'Only One Can Fill');
                }
            });

	        if ($validator->fails()) {
	            return redirect()->back()->withErrors($validator)->withInput()->with('update-absence-error', 'Error');
	        }


	        $this->saveArchive('App\\Models\\FormAbsence', 'UPDATED', $index);

	        $index->date_absence   = $request->date_absence;
	        $index->time_check_in  = $request->time_check_in;
	        $index->time_check_out = $request->time_check_out;
	        $index->note           = $request->note;

	        $index->save();

            $parent = User::whereAncestorOf(Auth::user()->_lft)->get();

            $html = '
                Form Absence : '.Auth::user()->fullname.'
            ';

            foreach ($parent as $list) {
                $list->notify(new Notif(Auth::user()->fullname, $html, route('backend.absence', ['f_id' => $index->id]) ) );
            }
	    });

        return redirect()->route('backend.absence')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
    	DB::transaction(function () use ($request){
	        $index = FormAbsence::find($request->id);
	        $this->saveArchive('App\\Models\\FormAbsence', 'DELETED', $index);

	        FormAbsence::destroy($request->id);
	    });

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function confirm(Request $request)
    {
        $index = FormAbsence::find($request->id);

        if (!$this->childgrant($index->user_id)) {
            return redirect()->route('backend.absence')->with('failed', 'Access Denied');
        }
        
        DB::transaction(function () use ($request, $index){
	        $this->saveArchive('App\\Models\\FormAbsence', 'CONFIRM', $index);

	        $index->confirm = $index->confirm ? null : date('Y-m-d H:i:s');
	        $index->save();
	    });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function action(Request $request)
    {
        if(is_array($request->id))
        {
            if ($request->action == 'delete' && Auth::user()->can('delete-absence')) {

                DB::transaction(function () use ($request){
                    $index = FormAbsence::find($request->id);
                    $this->saveMultipleArchive('App\\Models\\FormAbsence', 'DELETED', $index);

                    FormAbsence::destroy($request->id);
                });

                return redirect()->back()->with('success', 'Data Has Been Deleted');
            } else if ($request->action == 'confirm' && Auth::user()->can('confirm-absence')) {

                DB::transaction(function () use ($request){
                    $index = FormAbsence::whereIn('id', $request->id);
                    $this->saveMultipleArchive('App\\Models\\FormAbsence', 'CONFIRM', $index);

                    FormAbsence::whereIn('id', $request->id)->update(['confirm' => date('Y-m-d H:i:s')]);
                });

                return redirect()->back()->with('success', 'Data Has Been Updated');
            } else if ($request->action == 'unconfirm' && Auth::user()->can('confirm-absence')) {

                DB::transaction(function () use ($request){
                    $index = FormAbsence::whereIn('id', $request->id);
                    $this->saveMultipleArchive('App\\Models\\FormAbsence', 'CONFIRM', $index);

                    FormAbsence::whereIn('id', $request->id)->update(['confirm' => null]);
                });

                return redirect()->back()->with('success', 'Data Has Been Updated');
            }

            return redirect()->back()->with('failed', 'Access Denied');
        }
        
        else
        {
            return redirect()->back()->with('info', 'Nothing selected');
        }
    }

    public function checkHRD(Request $request)
    {
        $index = FormAbsence::find($request->id);

        $this->saveArchive('App\\Models\\FormAbsence', 'CHECK_HRD', $index);

        $index->check_hrd = $request->check_hrd;

        $index->save();
    }
}

