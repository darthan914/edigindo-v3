<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Role;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables;
use Hash;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.role.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Role::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            if (Auth::user()->can('edit-role'))
        	{
        		$html .= '
	                <a href="' . route('backend.role.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
	            ';
        	}
            
        	if (Auth::user()->can('active-role'))
    		{
    			if($index->active)
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-dark inactive-role" data-toggle="modal" data-target="#inactive-role" data-id="'.$index->id.'"><i class="fa fa-eye-slash"></i></button>
		            ';
	            }
	            else
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-info active-role" data-toggle="modal" data-target="#active-role" data-id="'.$index->id.'"><i class="fa fa-eye"></i></button>
		            ';
	            }
    		}
	            
    		if (Auth::user()->can('delete-role'))
        	{
	            $html .= '
	                <button class="btn btn-xs btn-danger delete-role" data-toggle="modal" data-target="#delete-role" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
	            ';
	        }
            return $html;
        });

        $datatables->editColumn('active', function ($index) {
            $html = '';
            if($index->active)
            {
            	$html .= '
	                <span class="label label-info">Active</span>
	            ';
            }
            else
            {
            	$html .= '
	                <span class="label label-default">Inactive</span>
	            ';
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

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $key = User::keypermission();

        return view('backend.role.create')->with(compact('key'));
    }

    public function store(Request $request)
    {

    	$permission = $request->permission ? implode($request->permission, ', ') : '';

        $message = [
            'name.required' => 'This field required.',
            'code.required' => 'This field required.',
            'code.unique' => 'Code already exits.',
            'password.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|unique:role,code',
            'password' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Role;

        $index->name       = $request->name;
        $index->code       = $request->code;
        $index->active     = isset($request->active) ? 1 : 0;
        $index->permission = $permission;

        $index->save();

        return redirect()->route('backend.role')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = Role::find($id);
        $key = User::keypermission();
        return view('backend.role.edit')->with(compact('index', 'key'));
    }

    public function update($id, Request $request)
    {
    	$permission = $request->permission ? implode($request->permission, ', ') : '';
    	
        $message = [
            'name.required' => 'This field required.',
            'code.required' => 'This field required.',
            'code.unique' => 'Code already exits.',
            'password.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|unique:role,code,'.$id,
            'password' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = Role::find($id);

        $index->name       = $request->name;
        $index->code       = $request->code;
        $index->active     = isset($request->active) ? 1 : 0;
        $index->permission = $permission;

        $index->save();

        return redirect()->route('backend.role')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        Role::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function active(Request $request)
    {
    	$index = Role::find($request->id);

        if ($index->active == 0)
        {
            $index->active = 1;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($index->active == 1)
        {
            $index->active = 0;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }
    }

    public function action(Request $request)
    {

        if ($request->action == 'delete' && Auth::user()->can('delete-role')) {
            Role::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
        else if ($request->action == 'enable' && Auth::user()->can('active-role'))
        {
            $index = Role::whereIn('id', $request->id)->update(['active' => 1]);
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($request->action == 'disable' && Auth::user()->can('active-role'))
        {
            $index = Role::whereIn('id', $request->id)->update(['active' => 0]);
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }
}
