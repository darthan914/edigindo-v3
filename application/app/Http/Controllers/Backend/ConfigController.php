<?php

namespace App\Http\Controllers\Backend;

use App\Models\Configuration;
use App\Models\Position;
use App\Models\Division;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;

use Session;
use File;
use Hash;
use Validator;
use PDF;
use Excel;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class ConfigController extends Controller
{

	public function index()
	{
		$index    = Configuration::all();
        $position = Position::all();
        $user     = User::all();

        // $data = '';
        // foreach ($index as $list) {
        //     eval("\$".$list->for." = App\Config::find(".$list->id.");");
        //     $data[] = [$list->for];
        // }

	    return view('backend.config.index')->with(compact('index', 'position', 'user'));
	}

    public function edit(Configuration $index)
    {
        $position = Position::all();
        $division = Division::all();
        $user     = User::all();

        switch ($index->type)
        {
            case 'TEXT': 
                return view('backend.config.type.text')->with(compact('index'));

            case 'MULTIPLE': 
                switch ($index->selection)
                {
                    case 'POSITION' : $selection = $position; break;
                    case 'DIVISION' : $selection = $division; break;
                    case 'USER' : $selection = $user; break;
                    default : $selection = $user; break;
                }
                return view('backend.config.type.multiple')->with(compact('index', 'selection'));

            case 'SELECTION': 
                switch ($index->selection)
                {
                    case 'POSITION' : $selection = $position; break;
                    case 'DIVISION' : $selection = $division; break;
                    case 'USER' : $selection = $user; break;
                    default : $selection = $user; break;
                }
                return view('backend.config.type.selection')->with(compact('index', 'selection'));

            case 'INTEGER': 
                return view('backend.config.type.integer')->with(compact('index'));

            case 'DOUBLE': 
                return view('backend.config.type.integer')->with(compact('index'));

            case 'HTML': 
                return view('backend.config.type.html')->with(compact('index'));

        }
        
    }
	

	public function update(Configuration $index, Request $request)
	{
        switch ($index->type)
        {
            case 'TEXT': 
                $validator = Validator::make($request->all(), [
                    'value' => 'nullable',

                ]);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;

            case 'MULTIPLE': 
                $validator = Validator::make($request->all(), [
                    'value' => 'nullable',

                ]);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;

            case 'SELECTION': 
                $validator = Validator::make($request->all(), [
                    'value' => 'nullable',

                ]);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;

            case 'INTEGER': 
                $message = [
                    'value.integer' => 'Integer Only.',
                ];

                $validator = Validator::make($request->all(), [
                    'value' => 'integer|nullable',

                ], $message);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;

            case 'DOUBLE': 
                $message = [
                    'value.numeric' => 'Numeric Only.',
                ];

                $validator = Validator::make($request->all(), [
                    'value' => 'numeric|nullable',

                ], $message);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;

            case 'HTML': 

                $validator = Validator::make($request->all(), [
                    'value' => 'nullable',

                ]);

                $validator->after(function ($validator) use ($index, $request) {
                    if ($index->nullable == 0 && $request->value == '') {
                        $validator->errors()->add('value', 'This field required');
                    }
                });

                if($validator->fails())
                {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
                break;
            default: break;

        }


        if($index->type == 'MULTIPLE')
        {
            $value = $request->value ? implode($request->value, ', ') : null;
        }
        else
        {
            $value = $request->value;
        }

        $index->value = $value;

        $index->save();

        return redirect()->route('backend.config')->with('success', 'Data has been updated');
	}

    public function sql()
    {
        return view('backend.config.sql');
    }

    public function runSql(Request $request)
    {
        try
        {
            // DB::statement($request->sql);

            // return redirect::back()->with('success', 'Database has been updated');
        }
        catch (Exception $e) {
            return redirect::back()->with('error', $e)->withInput();
        }

    }

    public function toggleSidebar()
    {
        if(Session::has('toggleSidebar'))
        {
            Session::forget('toggleSidebar');
            return 0;
        }
        else
        {
            Session::put('toggleSidebar', 1);
            return 1;
        }
    }

    public function checkSidebar()
    {
        if(Session::has('toggleSidebar'))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
}
