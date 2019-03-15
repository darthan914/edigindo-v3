<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Session;

use File;

use App\Quote;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create', 'store']]);
    }

    public function index()
    {
    	$index = Quote::paginate(15);

    	return view('cms.quote.index', ['index' => $index]);
    }

    public function create(Request $request)
    {
    	return view('main.quotes');
    }
    
    public function store(Request $request)
	{
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

		return redirect::back();
	}

	public function delete(Request $request)
	{
		$delete = Quote::destroy($request->delete);
		Session::flash('success', 'Data has been Deleted');
		return redirect::back();
	}
}
