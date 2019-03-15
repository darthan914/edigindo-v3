<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;

use App\Models\DesignRequest;
use App\Models\DesignCandidate;

class DesignRequestController extends Controller
{
    public function __construct()
    {
        // $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
    	$user = JWTAuth::parseToken()->authenticate();

    	if(!$user->hasAccess( 'list-designRequest' ))
    	{
    		return 'access_denied';
    		return response()->json(['error' => 'access_denied'], 401);
    	}

    	$data = DesignRequest::leftJoin('design_candidate', 'design_candidate.design_request_id', 'design_request.id')
        	->select('design_request.*', DB::raw('COUNT(design_candidate.id) AS count_design'))
        	->where('design_request.client_id', $user->id)
        	->groupBy('design_request.id')
        	->get();

        return response()->json(compact('data'));
    }

    public function view(Request $request)
    {
    	$user = JWTAuth::parseToken()->authenticate();

    	$data = DesignRequest::find($id);

    	if(!$user->hasAccess( 'list-designRequest' ) || $data->client_id != $user->id)
    	{
    		return 'access_denied';
    		return response()->json(['error' => 'access_denied'], 401);
    	}

    	$data = DesignRequest::find($id);
        $design_candidate = DesignCandidate::where('design_request_id', $id)->get();
        $division = Division::where('client_available', 1)->get();

        return response()->json(compact('data', 'design_candidate', 'division'));
    }


    public function store(Request $request)
    {
    	$user = JWTAuth::parseToken()->authenticate();

    	if(!$user->hasAccess( 'create-designRequest' ))
    	{
    		return response()->json(['error' => 'access_denied'], 401);
    	}

        $data = new DesignRequest;

        $data->sales_id          = $user->leader;
		$data->client_id         = $user->id;
		$data->title_request     = $request->title_request;
		$data->note_request      = $request->note_request;
		$data->division          = $request->division;
		$data->budget            = $request->budget;
		$data->datetime_deadline = date('Y-m-d 23:59', strtotime($request->datetime_deadline));

        $data->save();

        $status = 'success';

        return response()->json(compact('status', 'data'));
    }

    public function update(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $data = DesignRequest::find($request->id);

    	if(!$user->hasAccess( 'edit-designRequest' ) || $data->client_id != $user->id)
    	{
    		return response()->json(['error' => 'access_denied'], 401);
    	}

        $data->title_request     = $request->title_request;
		$data->note_request      = $request->note_request;
		$data->division          = $request->division;
		$data->budget            = $request->budget;
		$data->datetime_deadline = date('Y-m-d 23:59', strtotime($request->datetime_deadline));

        $data->save();

        $status = 'success';

        return response()->json(compact('status', 'data'));
    }

    public function setStatus(Request $request)
    {
    	$user = JWTAuth::parseToken()->authenticate();

    	if(!$user->hasAccess( 'setStatus-designRequest' ))
    	{
    		return response()->json(['error' => 'access_denied'], 401);
    	}

    	DesignRequest::find($request->id)->update(['status_approval' => 'APPROVED']);
    	DesignCandidate::where('design_request_id', $request->id)->where('id', $request->design_candidate_id)->update(['status_design' => 'CHOSEN']);
    	DesignCandidate::where('design_request_id', $request->id)->where('id', '<>', $request->design_candidate_id)->update(['status_design' => 'REJECT']);

    	$status = 'success';

        return response()->json(compact('status'));
    }

    public function delete(Request $request)
    {
    	$user = JWTAuth::parseToken()->authenticate();

    	$data = DesignRequest::find($request->id);

    	if(!$user->hasAccess( 'delete-designRequest' )  || $data->client_id != $user->id)
    	{
    		return response()->json(['error' => 'access_denied'], 401);
    	}

        $data->delete();

        $status = 'success';

        return response()->json(compact('status'));
    }
}
