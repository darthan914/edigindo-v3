<?php

namespace App\Http\Controllers\Backend;

use App\MainPageModel\Career;
use App\Config;
use App\User;
use App\Http\Controllers\Controller;
use File;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Session;
use Datatables;

use App\Notifications\Notif;

class JobApplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store']]);
    }

    public function index(Request $request)
    {
        return view('backend.jobApply.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $f_id       = $this->filter($request->f_id);

        $index = Career::orderBy('id', 'DESC');

        if($f_id != '')
        {
            $index->where('id', $f_id);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('delete-jobApply'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-jobApply" data-toggle="modal" data-target="#delete-jobApply" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }

            $html .= '
                <a class="btn btn-xs btn-primary" href="'.asset($index->attachment).'"><i class="fa fa-paperclip"></i></a>
            ';
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->created_at));
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function store(Request $request)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $validator = Validator::make($request->all(), [
            'fullname'             => 'required|max:255',
            'email'                => 'required|email',
            'phone'                => 'required',
            'position'             => 'required',
            'attachment'           => 'required|mimes:zip|max:5120',
            // 'g-recaptcha-response' => 'required',

        ]);

        if ($validator->fails()) {
            return redirect('/#career')
                ->withErrors($validator)
                ->withInput();
        }

        $store = new Career;

        $store->fullname = $request->fullname;
        $store->email    = $request->email;
        $store->phone    = $request->phone;
        $store->position = $request->position;
        $store->message  = $request->message;

        if ($request->hasFile('attachment')) {
            $pathSource = 'upload/attachment/';
            $image      = $request->file('attachment');
            $filename   = time() . '.' . $image->getClientOriginalExtension();
            $image->move($pathSource, $filename);
            $store->attachment = $pathSource . $filename;
        }

        $store->save();
        
        $hrd_admin_notif = User::where(function ($query) use ($hrd_admin_position, $hrd_admin_user) {
                $query->whereIn('position', explode(', ', $hrd_admin_position->value))
                ->orWhereIn('id', explode(', ', $hrd_admin_user->value));
            })
            ->get();

        $html = '
            New Job Apply : '.$store->fullname.'
        ';

        foreach ($hrd_admin_notif as $list) {
            $list->notify(new Notif($request->fullname, $html, route('backend.jobApply', ['f_id' => $store->id]) ) );
        }



        return redirect('/#career')->with('success', 'Data has been Submited');
    }

    public function delete(Request $request)
    {
        $index = Career::find($request->id);
        $this->saveArchive('App\Models\JobApply', 'DELETED', $index);

        $delete = Career::destroy($request->id);

        return redirect()->back()->with('success', 'Data has been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-supplier')) {
            $index = Career::find($request->id);
            $this->saveMultipleArchive('App\Models\JobApply', 'DELETED', $index);

            Career::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
