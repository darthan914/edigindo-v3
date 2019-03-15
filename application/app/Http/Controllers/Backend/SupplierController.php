<?php

namespace App\Http\Controllers\Backend;

use App\Supplier;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.supplier.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Supplier::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-supplier'))
            {
                $html .= '
                    <a href="' . route('backend.supplier.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if(Auth::user()->can('delete-supplier'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-supplier" data-toggle="modal" data-target="#delete-supplier" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
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
        return view('backend.supplier.create');
    }

    public function store(Request $request)
    {

        $message = [
            'name.required' => 'This field required.',
            'bank.required' => 'This field required.',
            'name_rekening.required' => 'This field required.',
            'no_rekening.required' => 'This field required.',
            'cp.required' => 'This field required.',
            'phone_home.required' => 'This field required.',
            'phone_mobile.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
			'bank' => 'required',
			'name_rekening' => 'required',
			'no_rekening' => 'required',
			'cp' => 'nullable',
			'phone_home' => 'required',
			'phone_mobile' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Supplier;

        $index->name = $request->name;
		$index->bank = $request->bank;
		$index->name_rekening = $request->name_rekening;
		$index->no_rekening = $request->no_rekening;
		$index->cp = $request->cp;
		$index->phone_home = $request->phone_home;
		$index->phone_mobile = $request->phone_mobile;

        $index->save();

        return redirect()->route('backend.supplier')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = Supplier::find($id);
        return view('backend.supplier.edit')->with(compact('index'));
    }

    public function update($id, Request $request)
    {
        $message = [
            'name.required' => 'This field required.',
            'bank.required' => 'This field required.',
            'name_rekening.required' => 'This field required.',
            'no_rekening.required' => 'This field required.',
            'cp.required' => 'This field required.',
            'phone_home.required' => 'This field required.',
            'phone_mobile.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
			'bank' => 'required',
			'name_rekening' => 'required',
			'no_rekening' => 'required',
			'cp' => 'nullable',
			'phone_home' => 'required',
			'phone_mobile' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = Supplier::find($id);

        $this->saveArchive('App\Supplier', 'UPDATED', $index);

        $index->name = $request->name;
		$index->bank = $request->bank;
		$index->name_rekening = $request->name_rekening;
		$index->no_rekening = $request->no_rekening;
		$index->cp = $request->cp;
		$index->phone_home = $request->phone_home;
		$index->phone_mobile = $request->phone_mobile;

        $index->save();

        return redirect()->route('backend.supplier')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Supplier::find($request->id);
        $this->saveArchive('App\Supplier', 'DELETED', $index);

        Supplier::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-supplier')) {
            $index = Supplier::find($request->id);
            $this->saveMultipleArchive('App\Supplier', 'DELETED', $index);

            Supplier::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
