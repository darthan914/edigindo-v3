<?php

namespace App\Http\Controllers\Backend;

use App\MainPageModel\Quote;
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

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store']]);
    }

    public function index(Request $request)
    {
        return view('backend.quote.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $f_id       = $this->filter($request->f_id);

        $index = Quote::orderBy('id', 'DESC');

        if($f_id != '')
        {
            $index->where('id', $f_id);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('delete-quote'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-quote" data-toggle="modal" data-target="#delete-quote" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }

            return $html;
        });

        $datatables->addColumn('fullname', function ($index) {
            $html = $index->firstname . ' ' . $index->lastname;
            return $html;
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->created_at));
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

    public function store(Request $request)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $store = new Quote;

		$store->firstname 			= $request->firstname;
		$store->lastname 			= $request->lastname;
		$store->email 				= $request->email;
		$store->phone 				= $request->phone;
		$store->company 			= $request->company;
		$store->region 				= $request->region;
		$store->interested 			= $request->interested;
		$store->services 			= $request->services;
		$store->deadline 			= $request->deadline;
		$store->budget 				= $request->budget;
		$store->project_description = $request->project_description;

		$store->save();
        
        $hrd_admin_notif = User::where(function ($query) use ($hrd_admin_position, $hrd_admin_user) {
                $query->whereIn('position', explode(', ', $hrd_admin_position->value))
                ->orWhereIn('id', explode(', ', $hrd_admin_user->value));
            })
            ->get();

        $html = '
            New Quote : '.$store->firstname.'
        ';

        foreach ($hrd_admin_notif as $list) {
            $list->notify(new Notif($request->firstname, $html, route('backend.quote', ['f_id' => $store->id]) ) );
        }

        return redirect('/#career')->with('success', 'Data has been Submited');
    }

    public function delete(Request $request)
    {
        $index = Quote::find($request->id);
        $this->saveArchive('App\Models\Quote', 'DELETED', $index);

        $delete = Quote::destroy($request->id);

        return redirect()->back()->with('success', 'Data has been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-supplier')) {
            $index = Quote::find($request->id);
            $this->saveMultipleArchive('App\Models\Quote', 'DELETED', $index);

            Quote::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
