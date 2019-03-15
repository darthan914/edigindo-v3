<?php

namespace App\Http\Controllers\Backend;

use App\User;
use App\Models\Position;
use App\Config;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables;
use Hash;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.position.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $search  = $this->filter($request->search);

        $index = Position::withDepth()->orderBy('_lft', 'ASC');

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('positions.name', 'like', '%'.$search.'%');
            });
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            if (Auth::user()->can('update-position', $index))
        	{
        		$html .= '
	                <a href="' . route('backend.position.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
	            ';

    			if($index->active)
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-dark inactive-position" data-toggle="modal" data-target="#inactive-position" data-id="'.$index->id.'"><i class="fa fa-eye-slash"></i></button>
		            ';
	            }
	            else
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-info active-position" data-toggle="modal" data-target="#active-position" data-id="'.$index->id.'"><i class="fa fa-eye"></i></button>
		            ';
	            }
    		}
	            
    		if (Auth::user()->can('delete-position', $index))
        	{
	            $html .= '
	                <button class="btn btn-xs btn-danger delete-position" data-toggle="modal" data-target="#delete-position" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
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

            if (Auth::user()->can('check-position', $index))
            {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }
            return $html;
        });

        $datatables->editColumn('_lft', function ($index) {
            $html = str_repeat ( '-&nbsp;&nbsp;&nbsp;' , $index->depth ) . $index->name;
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $parent = Position::where('active', 1);

        if(!Auth::user()->can('full-position'))
        {
            $parent->whereBetween('_lft', [Auth::user()->positions->_lft, Auth::user()->positions->_rgt]);
        }
            
        $parent = $parent->get();

        $key    = User::keypermission();

        return view('backend.position.create', compact('key', 'parent'));
    }

    public function store(Request $request)
    {
        $permission = $request->permission ? implode($request->permission, ', ') : '';

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Position;

        

        $index->name       = $request->name;
        $index->active     = isset($request->active) ? 1 : 0;
        $index->permission = $permission;
        $index->parent_id  = $request->parent_id;

        $index->save();

        saveArchives($index, Auth::id(), "Create Position", $request->except(['_token', 'password']));

        return redirect()->route('backend.position')->with('success', 'Data Has Been Added');
    }

    public function edit(Position $index)
    {

        $parent = Position::where('active', 1);

        if(!Auth::user()->can('full-position'))
        {
            $parent->whereBetween('_lft', [Auth::user()->positions->_lft, Auth::user()->positions->_rgt]);
        }
            
        $parent = $parent->get();

        $key    = User::keypermission();

        return view('backend.position.edit')->with(compact('index', 'key', 'parent'));
    }

    public function update(Position $index, Request $request)
    {
    	$permission= $request->permission ? implode($request->permission, ', ') : '';
    	
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        saveArchives($index, Auth::id(), "Edit Position", $request->except(['_token']));

        $index->name       = $request->name;
        $index->active     = isset($request->active) ? 1 : 0;
        $index->permission = $permission;
        $index->parent_id  = $request->parent_id;

        $index->save();

        return redirect()->route('backend.position')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        if (!Auth::user()->can('update-position', Position::find($request->id)))
        {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        saveArchives(Position::find($request->id), Auth::id(), "Delete Position");

        Position::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function active(Request $request)
    {
    	$index = Position::find($request->id);

        if ($index->active == 0)
        {
            saveArchives($index, Auth::id(), "Active Position");

            $index->active = 1;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($index->active == 1)
        {
            saveArchives($index, Auth::id(), "Inactive Position");

            $index->active = 0;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }
    }

    public function action(Request $request)
    {
        $id = [];
        if ($request->action == 'delete') {

            foreach ($request->id as $list) {
                if (Auth::user()->can('delete-position', Position::find($list)))
                {
                    $id[] = $list;
                }
            }

            saveMultipleArchives(Position::class, Position::find($id), Auth::id(), "Delete Position");

            Position::destroy($id);

            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
        else if ($request->action == 'enable')
        {
            foreach ($request->id as $list) {
                if (Auth::user()->can('update-position', Position::find($list)))
                {
                    $id[] = $list;
                }
            }

            saveMultipleArchives(Position::class, Position::find($id), Auth::id(), "Enabled Position");

            $index = Position::whereIn('id', $id)->update(['active' => 1]);

            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($request->action == 'disable')
        {
            foreach ($request->id as $list) {
                if (Auth::user()->can('update-position', Position::find($list)))
                {
                    $id[] = $list;
                }
            }

            saveMultipleArchives(Position::class, Position::find($id), Auth::id(), "Enabled Position");

            $index = Position::whereIn('id', $id)->update(['active' => 0]);

            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }
}
