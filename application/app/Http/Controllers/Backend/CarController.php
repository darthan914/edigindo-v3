<?php

namespace App\Http\Controllers\Backend;

use App\Car;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables;

class CarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.car.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Car::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-car'))
            {
                $html .= '
                    <a href="' . route('backend.car.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if(Auth::user()->can('delete-car'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-car" data-toggle="modal" data-target="#delete-car" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->editColumn('stnk', function ($index) {
            $html = $index->stnk ? date('d/m/Y', strtotime($index->stnk)) : '';

            return $html;
        });

        $datatables->editColumn('kir1', function ($index) {
            $html = $index->kir1 ? date('d/m/Y', strtotime($index->kir1)) : '';

            return $html;
        });

        $datatables->editColumn('kir2', function ($index) {
            $html = $index->kir2 ? date('d/m/Y', strtotime($index->kir2)) : '';

            return $html;
        });

        $datatables->editColumn('gps', function ($index) {
            $html = $index->gps ? date('d/m/Y', strtotime($index->gps)) : '';

            return $html;
        });

        $datatables->editColumn('insurance', function ($index) {
            $html = $index->insurance ? date('d/m/Y', strtotime($index->insurance)) : '';

            return $html;
        });

        $datatables->editColumn('date_km', function ($index) {
            $html = $index->date_km ? date('d/m/Y', strtotime($index->date_km)) : '';

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
        return view('backend.car.create');
    }

    public function store(Request $request)
    {

        $message = [
            'num_car.required' => 'This feild required.',
            'stnk.date' => 'Date Format only.',
            'kir1.date' => 'Date Format only.',
            'kir2.date' => 'Date Format only.',
            'gps.date' => 'Date Format only.',
            'insurance.date' => 'Date Format only.',
            'date_km.date' => 'Date Format only.',
        ];

        $validator = Validator::make($request->all(), [
            'num_car' => 'required',
			'stnk' => 'nullable|date',
			'kir1' => 'nullable|date',
			'kir2' => 'nullable|date',
			'gps' => 'nullable|date',
			'insurance' => 'nullable|date',
			'date_km' => 'nullable|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Car;

        $index->num_car = $request->num_car;
		$index->stnk = $request->stnk != '' ? date('Y-m-d', strtotime($request->stnk)) : NULL;
		$index->kir1 = $request->kir1 != '' ? date('Y-m-d', strtotime($request->kir1)) : NULL;
		$index->kir2 = $request->kir2 != '' ? date('Y-m-d', strtotime($request->kir2)) : NULL;
		$index->gps = $request->gps != '' ? date('Y-m-d', strtotime($request->gps)) : NULL;
		$index->insurance = $request->insurance != '' ? date('Y-m-d', strtotime($request->insurance)) : NULL;
		$index->date_km = $request->date_km != '' ? date('Y-m-d', strtotime($request->date_km)) : NULL;
		$index->weekly_km = $request->weekly_km;
		$index->paper_km = $request->paper_km;

        $index->save();

        return redirect()->route('backend.car')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = Car::find($id);

        return view('backend.car.edit')->with(compact('index'));
    }

    public function update($id, Request $request)
    {
        $message = [
            'num_car.required' => 'This feild required.',
            'stnk.date' => 'Date Format only.',
            'kir1.date' => 'Date Format only.',
            'kir2.date' => 'Date Format only.',
            'gps.date' => 'Date Format only.',
            'insurance.date' => 'Date Format only.',
            'date_km.date' => 'Date Format only.',
        ];

        $validator = Validator::make($request->all(), [
            'num_car' => 'required',
			'stnk' => 'nullable|date',
			'kir1' => 'nullable|date',
			'kir2' => 'nullable|date',
			'gps' => 'nullable|date',
			'insurance' => 'nullable|date',
			'date_km' => 'nullable|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = Car::find($id);

        $this->saveArchive('App\\Car', 'UPDATED', $index);

        $index->num_car = $request->num_car;
		$index->stnk = $request->stnk != '' ? date('Y-m-d', strtotime($request->stnk)) : NULL;
		$index->kir1 = $request->kir1 != '' ? date('Y-m-d', strtotime($request->kir1)) : NULL;
		$index->kir2 = $request->kir2 != '' ? date('Y-m-d', strtotime($request->kir2)) : NULL;
		$index->gps = $request->gps != '' ? date('Y-m-d', strtotime($request->gps)) : NULL;
		$index->insurance = $request->insurance != '' ? date('Y-m-d', strtotime($request->insurance)) : NULL;
		$index->date_km = $request->date_km != '' ? date('Y-m-d', strtotime($request->date_km)) : NULL;
		$index->weekly_km = $request->weekly_km;
		$index->paper_km = $request->paper_km;

        $index->save();

        return redirect()->route('backend.car')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Car::find($request->id);
        $this->saveArchive('App\\Car', 'DELETED', $index);

        Car::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-car')) {
            $index = Car::find($request->id);
            $this->saveMultipleArchive('App\\Car', 'DELETED', $index);

            Car::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
