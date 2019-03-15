<?php

namespace App\Http\Controllers\Api;

use App\Models\ArModel;
use App\User;
use App\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArModelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'model']]);
    }

    public function index(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $limit  = $this->filter($request->limit, 20);

        $find = $this->filter($request->find);

        $sort  = $this->filter($request->sort, 'id');
        $order = $this->filter($request->order, 'DESC');

        $index = ArModel::where('active', 1)
        	->where(function ($query) use ($find) {
        		$query->where(function ($subquery)  use ($find) {
        			$subquery->where('email', '<>', '')->where('email', $find);
        		})->orWhere(function ($subquery) use ($find) {
        			$subquery->where('phone', '<>', '')->where('phone', $find);
        		});
        	})
        	->select('ar_models.*', 'ar_models.asset_bundle_android as asset_bundle');

        $index = $index->orderBy($sort, $order)->get();

        $data = compact('index');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function model(Request $request)
    {
        $status  = 'OK';
        $message = 'View Object';
        $data    = '';

        $limit  = $this->filter($request->limit, 20);

        $find = $this->filter($request->find);

        $sort  = $this->filter($request->sort, 'id');
        $order = $this->filter($request->order, 'DESC');

        $index = ArModel::where('active', 1)
            ->where('token', $request->token)
            ->select('ar_models.*', 'ar_models.asset_bundle_android as asset_bundle');

        $index = $index->first();

        if($index)
        {
            $data = compact('index');
        }
        else
        {
            $status = 'ERROR';
            $message = 'Object Not Available';
        }

        return response()->json(compact('status', 'message', 'data'));
    }
}
