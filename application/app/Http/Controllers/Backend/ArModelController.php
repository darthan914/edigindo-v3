<?php

namespace App\Http\Controllers\Backend;

use App\Models\ArModel;

use App\User;
use App\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;
use File;
use PDF;
use QRCode;

class ArModelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$user = ArModel::join('users', 'ar_models.user_id', 'users.id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if (!Auth::user()->can('allUser-arModel')) {
            $user->whereIn('user_id', Auth::user()->staff());
        }

        $user = $user->get();

        return view('backend.arModel.index')->with(compact('request', 'user'));
    }

    public function datatables(Request $request)
    {
    	$f_user = $this->filter($request->f_user, Auth::id());

        $index = ArModel::join('users', 'ar_models.user_id', 'users.id')->select('ar_models.*', 'users.fullname')->orderBy('id', 'DESC');

        if ($f_user == 'staff') {
            $index->whereIn('user_id', Auth::user()->staff());
        } else if ($f_user != '') {
            $index->where('user_id', $f_user);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

//             $html .= '
//                 <a class="btn btn-xs btn-default" href="'.route('backend.arModel.qrCode', ['id' => $index->id]).'" target="_blank"><i class="fa fa-qrcode"></i></a>
//             ';

//             $html .= '
//                 <button class="btn btn-xs btn-primary pdf-arModel" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o
// "></i></button>
//             ';

            if (Auth::user()->can('edit-arModel') && ($this->usergrant($index->user_id, 'allUser-arModel') || $this->levelgrant($index->user_id))) {
                $html .= '
                    <a class="btn btn-xs btn-warning" href="' . route('backend.arModel.edit', ['id' => $index->id]) . '"><i class="fa fa-edit"></i></a>
                ';
            }

            if (Auth::user()->can('delete-arModel') && ($this->usergrant($index->user_id, 'allUser-arModel') || $this->levelgrant($index->user_id))) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-arModel" data-toggle="modal" data-target="#delete-arModel" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('active-arModel') && ($this->usergrant($index->user_id, 'allUser-arModel') || $this->levelgrant($index->user_id))) {
                if ($index->active) {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-dark inactive-arModel" data-toggle="modal" data-target="#inactive-arModel"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-times" aria-hidden="true"></i></button>
	                ';
                } else {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-info active-arModel" data-toggle="modal" data-target="#active-arModel"
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

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->created_at));

            return $html;
        });

        $datatables->editColumn('updated_at', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->updated_at));

            return $html;
        });

        $datatables->editColumn('active', function ($index) {
            $html = '';
            if ($index->active == 1) {
                $html .= '
                    <span class="label label-success">Active</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Inactive</span>
                ';
            }
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        return view('backend.arModel.create');
    }

    public function store(Request $request)
    {

        $message = [
            'code.required'    => 'This field required.',
            'name.required'             => 'This field required.',
            'name_game_object.required' => 'This field required.',
            'asset_bundle_android.required'     => 'This field required.',
            'asset_bundle_ios.required'     => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'code'            => 'required',
            'name'             => 'required',
            'name_game_object' => 'required',
            'asset_bundle_android'     => 'required',
            'asset_bundle_ios'     => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-arModel-error', 'Error');
        }

        DB::transaction(function () use ($request) {
            $index = new ArModel;

            $index->user_id          = Auth::id();
            $index->email            = $request->email;
            $index->phone            = $request->code;
            $index->name             = $request->name;
            $index->name_game_object = $request->name_game_object;
            $index->active           = $request->active ? 1 : 0;
            $index->token            = str_random(30);

            if ($request->hasFile('asset_bundle_android')) {
                $pathSource = 'upload/arModel/android/';
                $file       = $request->file('asset_bundle_android');
                $filename   = time();

                $file->move($pathSource, $filename);
                $index->asset_bundle_android = $pathSource . $filename;
            }


            if ($request->hasFile('asset_bundle_ios')) {
                $pathSource = 'upload/arModel/ios/';
                $file       = $request->file('asset_bundle_ios');
                $filename   = time();

                $file->move($pathSource, $filename);
                $index->asset_bundle_ios = $pathSource . $filename;
            }

            $index->save();
        });

        return redirect()->route('backend.arModel')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
    	$index = ArModel::find($id);

    	if (!$this->usergrant($index->user_id, 'allUser-arModel') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.arModel')->with('failed', 'Access Denied');
        }

        return view('backend.arModel.edit', compact('index'));
    }

    public function update(Request $request, $id)
    {
    	$index = ArModel::find($id);

    	if (!$this->usergrant($index->user_id, 'allUser-arModel') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.arModel')->with('failed', 'Access Denied');
        }

        $message = [
            'code.required'            => 'This field required.',
            'name.required'             => 'This field required.',
            'name_game_object.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'code'            => 'required',
            'name'             => 'required',
            'name_game_object' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-arModel-error', 'Error');
        }

        DB::transaction(function () use ($request, $index) {
            $this->saveArchive('App\\Models\\ArModel', 'UPDATED', $index);

            $index->email            = $request->email;
            $index->phone            = $request->code;
            $index->name             = $request->name;
            $index->name_game_object = $request->name_game_object;
            $index->active           = $request->active ? 1 : 0;

            if ($request->hasFile('asset_bundle_android')) {
                if ($index->asset_bundle_android) {
                    File::delete($index->asset_bundle_android);
                }
                $pathSource = 'upload/arModel/android/';
                $fileData   = $request->file('asset_bundle_android');
                $filename   = time() . '.' . $fileData->getClientOriginalExtension();
                $fileData->move($pathSource, $filename);

                $index->asset_bundle_android = $pathSource . $filename;
            } else if (isset($request->remove)) {
                File::delete($index->file);
                $index->file = null;
            }

            if ($request->hasFile('asset_bundle_ios')) {
                if ($index->asset_bundle_ios) {
                    File::delete($index->asset_bundle_ios);
                }
                $pathSource = 'upload/arModel/ios/';
                $fileData   = $request->file('asset_bundle_ios');
                $filename   = time() . '.' . $fileData->getClientOriginalExtension();
                $fileData->move($pathSource, $filename);

                $index->asset_bundle_ios = $pathSource . $filename;
            } else if (isset($request->remove)) {
                File::delete($index->file);
                $index->file = null;
            }

            $index->save();
        });

        return redirect()->route('backend.arModel')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = ArModel::find($request->id);
        $this->saveArchive('App\\Models\\ArModel', 'DELETED', $index);

        ArModel::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
    	if (is_array($request->id)) {
            foreach ($request->id as $list) {

                $index = ArModel::find($list);

                if (($this->usergrant($index->user_id, 'allUser-arModel') || $this->levelgrant($index->sales_id))) {
                    $id[] = $list;
                }
            }

	        if ($request->action == 'delete' && Auth::user()->can('delete-arModel')) {

	        	DB::transaction(function () use ($request){
		            $index = ArModel::find($id);
		            $this->saveMultipleArchive('App\\Models\\ArModel', 'DELETED', $index);

		            ArModel::destroy($id);
		        });

	            return redirect()->back()->with('success', 'Data Has Been Deleted');
	        } else if ($request->action == 'active' && Auth::user()->can('active-arModel')) {

	        	DB::transaction(function () use ($request){
		            $index = ArModel::whereIn('id', $id);
		            $this->saveMultipleArchive('App\\Models\\ArModel', 'ACTIVE', $index);

		            ArModel::whereIn('id', $id)->update(['active' => 1]);
		        });

	            return redirect()->back()->with('success', 'Data Has Been Updated');
	        } else if ($request->action == 'inactive' && Auth::user()->can('active-arModel')) {

	        	DB::transaction(function () use ($request){
		            $index = ArModel::whereIn('id', $id);
		            $this->saveMultipleArchive('App\\Models\\ArModel', 'ACTIVE', $index);

		            ArModel::whereIn('id', $id)->update(['active' => 1]);
		        });

	            return redirect()->back()->with('success', 'Data Has Been Updated');
	        }

	        return redirect()->back()->with('failed', 'Access Denied');
	    }

        return redirect()->back()->with('failed', 'No Data Selected');
    }

    public function active(Request $request)
    {
        $index = ArModel::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-arModel') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.arModel')->with('failed', 'Access Denied');
        }
        
        DB::transaction(function () use ($request, $index){
	        $this->saveArchive('App\\Models\\ArModel', 'ACTIVE', $index);

	        $index->active = $index->active ? 0 : 1;
	        $index->save();
	    });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function pdf(Request $request)
    {
        $index = ArModel::find($request->id);

        $pdf = PDF::loadView('backend.arModel.pdf', compact('index', 'request'))->setPaper('A4', 'portrait');
        // return view('backend.arModel.pdf', compact('index', 'request'));

        return $pdf->stream($index->name . '.pdf');
    }

    public function qrCode(Request $request)
    {
        $index = ArModel::find($request->id);
        return QRCode::size(500)->generate($index->token);   
    }
}
