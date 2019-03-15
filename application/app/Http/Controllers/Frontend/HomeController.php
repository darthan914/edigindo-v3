<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    //
    public function index(){
    	return view('frontend.index');
    }

    public function portofolio(Request $request)
    {
    	$division = $this->filter($request->division, 'signage');

    	return view('frontend.portofolio', compact('division'));
    }

    public function triD(){
    	return view('frontend.carrier.3d');
    }
    public function finance(){
        return view('frontend.carrier.finance');
    }
    public function graphic(){
        return view('frontend.carrier.graphic');
    }
    public function account(){
        return view('frontend.carrier.account');
    }
    public function marketing(){
        return view('frontend.carrier.marketing');
    }

    public function quotes(Request $request)
    {
        return view('frontend.quotes');
    }
}
