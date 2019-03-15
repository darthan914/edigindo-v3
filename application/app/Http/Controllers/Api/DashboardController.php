<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Company;
use App\Models\DesignRequest;


class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
    	$data = DesignRequest::leftJoin('design_candidate', 'design_candidate.design_request_id', 'design_request.id')
        	// ->select('design_request.*', DB::raw('COUNT(design_candidate.id) AS count_design'))
        	// ->where('design_request.client_id', $user->id)
        	// ->groupBy('design_request.id')
        	->get();

    	return response()->json(compact('data'));
    }
}
