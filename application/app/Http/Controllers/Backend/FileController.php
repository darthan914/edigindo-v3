<?php

namespace App\Http\Controllers\Backend;

use App\Models\File ;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use File as Upload;

use Validator;
use Datatables;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.file.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = File::join('users', 'users.id', 'file.user_id')->select('file.*', 'fullname');

        if(!Auth::user()->can('allUser-file'))
        {
            $sales->whereIn('user_id', Auth::user()->staff());
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-file'))
            {
                $html .= '
                    <a href="' . route('backend.file.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if(Auth::user()->can('delete-file'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-file" data-toggle="modal" data-target="#delete-file" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('preview', function ($index) {
            $html = '';

            if($index->type == 'IMAGE')
            {
                $html .= '
                    <img src="'. asset($index->file) .'" alt="'.$index->name.'" style="width:100px;">
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

        $datatables->editColumn('created_at', function ($index) {
            return date('d/m/Y H:i:s', strtotime($index->created_at));
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        return view('backend.file.create');
    }

    public function store(Request $request)
    {

        $message = [
            'name.required' => 'This field required.',
            'file.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
			'file' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new File;

        $index->name = $request->name;

        if ($request->hasFile('file')) {
            $pathSource = 'upload/file/';
            $file       = $request->file('file');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            $file->move($pathSource, $filename);

            if(in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg']))
            {
                $index->type = 'IMAGE';
            }
            else
            {
                $index->type = 'FILE';
            }
            
            $index->file = $pathSource . $filename;
        }

        $index->user_id = Auth::id();

        $index->save();

        return redirect()->route('backend.file')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = File::find($id);
        return view('backend.file.edit')->with(compact('index'));
    }

    public function update($id, Request $request)
    {
    	$index = File::find($id);

    	if( !$this->usergrant($index->user_id, 'allUser-file') || !$this->levelgrant($index->user_id) )
		{
			return redirect()->route('backend.file')->with('failed', 'Access Denied');
		}

        $message = [
            'name.required' => 'This field required.',
            'file.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
			'file' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index->name = $request->name;

        if ($request->hasFile('file')) {
            
            $pathSource = 'upload/file/';
            $file       = $request->file('file');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            if($file->move($pathSource, $filename))
            {
                

                if(in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg']))
                {
                    $index->type = 'IMAGE';
                }
                else
                {
                    $index->type = 'FILE';
                }
                $index->file = $pathSource . $filename;
            }
        }

        $index->user_id = Auth::id();

        $index->save();

        return redirect()->route('backend.file')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
    	$index = File::find($request->id);

    	if( !$this->usergrant($index->user_id, 'allUser-file') || !$this->levelgrant($index->user_id) )
		{
			return redirect()->route('backend.file')->with('failed', 'Access Denied');
		}

        if($index->file != '')
        {
            Upload::delete($index->file);
        }

        File::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-file')) {
            File::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
