<?php

namespace App\Http\Controllers\Backend;

use App\Advertisment;
use App\DetailAd;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use Validator;
use Datatables; 

class AdController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('backend.advertisment.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Advertisment::all();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('create-advertisment'))
            {
                $html .= '
                    <button class="btn btn-xs btn-success create-detail" data-toggle="modal" data-target="#create-detail" 
                    	data-id="'.$index->id.'
                    "><i class="fa fa-plus"></i></button>
                ';
            }

            if(Auth::user()->can('edit-advertisment'))
            {
                $html .= '
                    <button class="btn btn-xs btn-warning edit" data-toggle="modal" data-target="#edit" 
                    	data-id="'.$index->id.'"
                    	data-name="'.$index->name.'"
                    ><i class="fa fa-pencil"></i></button>
                ';
            }

            if(Auth::user()->can('delete-advertisment'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete" data-toggle="modal" data-target="#delete" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('detail', function ($index) {
            $html = '';

            $html .= '<table class="table table-bordered">';

            $html .= '
            	<thead>
					<tr>
						<th>Detail</th>
						<th>Payment</th>
						<th>Date Valid</th>
						<th>Total</th>
						<th>End Valid</th>
						<th>Note</th>
						<th>Action</th>
					</tr>
				</thead>
            ';

            foreach ($index->detailAd as $list) {
            	$html .= '
	            	<tbody>
						<tr>
							<td>'.$list->detail.'</td>
							<td>Rp. '.number_format($list->payment).'</td>
							<td>'.$list->date_valid.'</td>
							<td>'.$list->count.'</td>
							<td>'.date('d/m/Y', strtotime($list->end_valid)).'</td>
							<td>'.$list->note.'</td>
							<td>
				';

				if(Auth::user()->can('edit-advertisment'))
	            {
	                $html .= '
	                    <button class="btn btn-xs btn-warning edit-detail" data-toggle="modal" data-target="#edit-detail" 
	                    	data-id="'.$list->id.'"
	                    	data-detail="'.$list->detail.'"
	                    	data-payment="'.$list->payment.'"
	                    	data-date_valid="'.$list->date_valid.'"
	                    	data-count="'.$list->count.'"
	                    	data-end_valid="'.date('d F Y', strtotime($list->end_valid)).'"
	                    	data-note="'.$list->note.'"
	                    ><i class="fa fa-pencil"></i></button>
	                ';
	            }

	            if(Auth::user()->can('delete-advertisment'))
	            {
	                $html .= '
	                    <button class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$list->id.'"><i class="fa fa-trash"></i></button>
	                ';
	            }

				$html .= '
							</td>
						</tr>
					</tbody>
	            ';
            }

            $html .= '</table>';

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

    public function store(Request $request)
    {

        $message = [
            'name.required' => 'This feild required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-error', 'Something Errors');;
        }

        $index = new Advertisment;

        $index->name = $request->name;

        $index->save();

        return redirect()->route('backend.advertisment')->with('success', 'Data Has Been Added');
    }

    public function update(Request $request)
    {
        $message = [
            'name.required' => 'This feild required.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-error', 'Something Errors');;
        }

        $index = Advertisment::find($request->id);

        $this->saveArchive('App\\Advertisment', 'UPDATED', $index);

        $index->name = $request->name;

        $index->save();

        return redirect()->route('backend.advertisment')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Advertisment::find($request->id);
        $this->saveArchive('App\\Advertisment', 'DELETED', $index);

        Advertisment::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && Auth::user()->can('delete-advertisment')) {
            $index = Advertisment::find($request->id);
            $this->saveMultipleArchive('App\\Advertisment', 'DELETED', $index);

            Advertisment::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }

        return redirect()->back()->with('success', 'Access Denied');
    }

    public function storeDetail(Request $request)
    {
        $message = [
            'advertisment_id.required' => 'Error.',
            'advertisment_id.integer' => 'Error.',
			'detail' => 'required',
			'payment' => 'nullable|numeric',
			'end_valid.date' => 'date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'advertisment_id' => 'required|integer',
			'detail' => 'required',
			'payment' => 'nullable|numeric',
			'end_valid' => 'nullable|date',
			'note' => 'nullable',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('create-detail-error', 'Something Errors');
        }

        $index = new DetailAd;

        $index->advertisment_id = $request->advertisment_id;
		$index->detail = $request->detail;
		$index->payment = $request->payment;
		$index->date_valid = $request->date_valid;
		$index->count = $request->count;
		$index->end_valid = date('Y-m-d', strtotime($request->end_valid));
		$index->note = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updateDetail(Request $request)
    {
        $message = [
			'detail' => 'required',
			'payment' => 'nullable|numeric',
			'end_valid.date' => 'date format only.',
        ];

        $validator = Validator::make($request->all(), [
			'detail' => 'required',
			'payment' => 'nullable|numeric',
			'date_valid' => 'nullable',
			'count' => 'nullable',
			'end_valid' => 'nullable|date',
			'note' => 'nullable',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit-detail-error', 'Something Errors');
        }

        $index = DetailAd::find($request->id);

        $this->saveArchive('App\\Advertisment_DETAIL', 'UPDATED', $index);

		$index->detail = $request->detail;
		$index->payment = $request->payment;
		$index->date_valid = $request->date_valid;
		$index->count = $request->count;
		$index->end_valid = date('Y-m-d', strtotime($request->end_valid));
		$index->note = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deleteDetail(Request $request)
    {
        $index = DetailAd::find($request->id);
        $this->saveArchive('App\\Advertisment_DETAIL', 'DELETED', $index);

        DetailAd::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }
}
