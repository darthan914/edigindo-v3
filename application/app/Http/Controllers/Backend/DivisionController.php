<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables;
use Hash;

use App\User;
use App\Models\Division;

class DivisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.division.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $search  = $this->filter($request->search);

        $index = Division::orderBy('id', 'ASC');

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('divisions.name', 'like', '%'.$search.'%');
            });
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            if (Auth::user()->can('update-division'))
        	{
        		$html .= '
	                <a href="' . route('backend.division.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
	            ';

    			if($index->active)
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-dark inactive-division" data-toggle="modal" data-target="#inactive-division" data-id="'.$index->id.'"><i class="fa fa-eye-slash"></i></button>
		            ';
	            }
	            else
	            {
	            	$html .= '
		                <button class="btn btn-xs btn-info active-division" data-toggle="modal" data-target="#active-division" data-id="'.$index->id.'"><i class="fa fa-eye"></i></button>
		            ';
	            }

                if($index->client_available)
                {
                    $html .= '
                        <button class="btn btn-xs btn-dark inactiveClient-division" data-toggle="modal" data-target="#inactiveClient-division" data-id="'.$index->id.'"><i class="fa fa-eye-slash"></i> Client</button>
                    ';
                }
                else
                {
                    $html .= '
                        <button class="btn btn-xs btn-info activeClient-division" data-toggle="modal" data-target="#activeClient-division" data-id="'.$index->id.'"><i class="fa fa-eye"></i> Client</button>
                    ';
                }
            }
	            
    		if (Auth::user()->can('delete-division',$index))
        	{
	            $html .= '
	                <button class="btn btn-xs btn-danger delete-division" data-toggle="modal" data-target="#delete-division" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
	            ';
	        }
            return $html;
        });

        $datatables->editColumn('client_available', function ($index) {
            $html = '';
            if($index->client_available)
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
            if (Auth::user()->can('check-division', $index))
            {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        return view('backend.division.create');
    }

    public function store(Request $request)
    {
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

        $index = new Division;

        $index->name               = $request->name;
        $index->active             = isset($request->active) ? 1 : 0;
        $index->client_available   = isset($request->client_available) ? 1 : 0;

        $index->save();

        saveArchives($index, Auth::id(), "Create Division", $request->except(['_token']));

        return redirect()->route('backend.division')->with('success', 'Data Has Been Added');
    }

    public function edit(Division $index)
    {
        return view('backend.division.edit', compact('index'));
    }

    public function update(Division $index, Request $request)
    {
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

        saveArchives($index, Auth::id(), "Update Division", $request->except(['_token']));

        $index->name               = $request->name;
        $index->active             = isset($request->active) ? 1 : 0;
        $index->client_available   = isset($request->client_available) ? 1 : 0;

        $index->save();

        return redirect()->route('backend.division')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        if(!Auth::user()->can('delete-division', Division::find($request->id)))
        {
            return redirect()->back()->with('failed', 'Access Denied');
        }
        
        saveArchives($index, Auth::id(), "Delete Division");

        Division::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function active(Request $request)
    {
    	$index = Division::find($request->id);

        if ($index->active == 0)
        {
            saveArchives($index, Auth::id(), "Enabled Division");

            $index->active = 1;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($index->active == 1)
        {
            saveArchives($index, Auth::id(), "Disabled Division");

            $index->active = 0;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }
    }

    public function activeClient(Request $request)
    {
        $index = Division::find($request->id);

        if ($index->client_available == 0)
        {
            saveArchives($index, Auth::id(), "Enabled Division for Client");

            $index->client_available = 1;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($index->client_available == 1)
        {
            saveArchives($index, Auth::id(), "Disabled Division for Client");

            $index->client_available = 0;
            $index->save();
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }
    }

    public function action(Request $request)
    {
        $id = [];
        if ($request->action == 'delete' && Auth::user()->can('delete-division')) {

            foreach ($request->id as $list) {
                if (Auth::user()->can('delete-division', Position::find($list)))
                {
                    $id[] = $list;
                }
            }

            saveMultipleArchives(Division::class, Division::find($id), Auth::id(), "Delete Division");

            Division::destroy($id);

            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
        else if ($request->action == 'enable' && Auth::user()->can('update-division'))
        {
            saveMultipleArchives(Division::class, Division::find($id), Auth::id(), "Enabled Division");

            $index = Division::whereIn('id', $request->id)->update(['active' => 1]);
            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($request->action == 'disable' && Auth::user()->can('update-division'))
        {
            saveMultipleArchives(Division::class, Division::find($id), Auth::id(), "Disabled Division");

            $index = Division::whereIn('id', $request->id)->update(['active' => 0]);
            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }

        return redirect()->back()->with('failed', 'Access Denied');
    }
}
