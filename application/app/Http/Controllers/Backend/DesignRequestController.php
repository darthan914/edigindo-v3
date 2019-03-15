<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\DesignRequest;
use App\Models\DesignCandidate;
use App\Models\DesignCandidatePreview;

use App\Config;
use App\Division;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;

class DesignRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $client = DesignRequest::join('users as client', 'client.id', '=', 'design_request.client_id')
            ->select('client.fullname', 'client.id')
            ->orderBy('client.fullname', 'ASC')->distinct();

        return view('backend.designRequest.index')->with(compact('request', 'client'));
    }

    public function datatables(Request $request)
    {
        $f_client = $this->filter($request->f_client);

        $index = DesignRequest::leftJoin('design_candidate', 'design_candidate.design_request_id', 'design_request.id')
        	->select('design_request.*', DB::raw('COUNT(design_candidate.id) AS count_design'))
        	->groupBy('design_request.id');

        if(Auth::user()->can('allUser-designRequest') && $f_client != '')
        {
            $index->where('design_request.client_id', $f_client);
        }
        else
        {
            $index->where('design_request.client_id', Auth::id());
        }

        $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-designRequest'))
            {
                $html .= '
                    <a href="' . route('backend.designRequest.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
                ';
            }

            if(Auth::user()->can('delete-designRequest'))
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-designRequest" data-toggle="modal" data-target="#delete-designRequest" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->editColumn('budget', function ($index) {
            return number_format($index->budget);
        });

        $datatables->editColumn('datetime_deadline', function ($index) {
            return date('d/m/Y H:i', strtotime($index->datetime_deadline));
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
        $division = Division::where('client_available', 1)->get();
        return view('backend.designRequest.create', compact('division'));
    }

    public function store(Request $request)
    {
        $message = [
            'title_request.required' => 'This field required.',
            'note_request.required' => 'This field required.',
            'division.required' => 'This field required.',
            'budget.required' => 'This field required.',
            'budget.numeric' => 'Numeric only.',
            'datetime_deadline.required' => 'This field required.',
            'datetime_deadline.date' => 'Date Time format only.',
        ];

        $validator = Validator::make($request->all(), [
            'title_request' => 'required',
            'note_request' => 'required',
	        'division' => 'required',
	        'budget' => 'required|numeric',
	        'datetime_deadline' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new DesignRequest;

        $index->sales_id = Auth::user()->leader;
		$index->client_id = Auth::id();
		$index->title_request = $request->title_request;
		$index->note_request = $request->note_request;
		$index->division = $request->division;
		$index->budget = $request->budget;
		$index->datetime_deadline = date('Y-m-d 23:59', strtotime($request->datetime_deadline));

        $index->save();

        return redirect()->route('backend.designRequest')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = DesignRequest::find($id);

        if( !$this->usergrant($index->client_id, 'allClient-designRequest') || !$this->levelgrant($index->client_id) )
        {
            return redirect()->route('backend.designRequest')->with('failed', 'Access Denied');
        }

        $design_candidate = DesignCandidate::where('design_request_id', $id)->get();
        $division = Division::where('client_available', 1)->get();

        return view('backend.designRequest.edit')->with(compact('index', 'design_candidate', 'division'));
    }


    public function datatablesDesignCandidate(Request $request)
    {
        $index = DesignCandidate::leftJoin('users', 'design_candidate.designer_id', 'users.id')
            ->select('design_candidate.*', 'users.fullname')
            ->where('design_request_id', $request->id)
            ->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('setStatus-designRequest'))
            {
                $html .= '
                    <button class="btn btn-xs btn-success setStatus-designRequest" data-toggle="modal" data-target="#setStatus-designRequest" data-design_candidate_id="'.$index->id.'">SELECT</button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('status_design', function ($index) {
            $html = '';

            if($index->status_design == 'PENDING')
            {
                $html .= '
                    <span class="label label-default">PENDING</span>
                ';
            }
            else if($index->status_design == 'CHOSEN')
            {
                $html .= '
                    <span class="label label-success">CHOSEN</span>
                ';
            }
            else
            {
                $html .= '
                    <span class="label label-danger">REJECT</span>
                ';
            }

            return $html;
        });

        $datatables->addColumn('image_preview', function ($index) {
            $preview = DesignCandidatePreview::where('design_candidate_id', $index->id)->get();

            return view('backend.designRequest.detail.preview', compact('preview'));
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function update($id, Request $request)
    {
        $index = DesignRequest::find($id);

        if( !$this->usergrant($index->client_id, 'allClient-designRequest') || !$this->levelgrant($index->client_id) )
        {
            return redirect()->route('backend.designRequest')->with('failed', 'Access Denied');
        }
        
        $message = [
            'title_request.required' => 'This field required.',
            'note_request.required' => 'This field required.',
            'division.required' => 'This field required.',
            'budget.required' => 'This field required.',
            'budget.numeric' => 'Numeric only.',
            'datetime_deadline.required' => 'This field required.',
            'datetime_deadline.date' => 'Date Time format only.',
        ];

        $validator = Validator::make($request->all(), [
            'title_request' => 'required',
            'note_request' => 'required',
	        'division' => 'required',
	        'budget' => 'required|numeric',
	        'datetime_deadline' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->saveArchive('App\Models\DesignRequest', 'UPDATED', $index);

        $index->title_request = $request->title_request;
		$index->note_request = $request->note_request;
		$index->division = $request->division;
		$index->budget = $request->budget;
		$index->datetime_deadline = date('Y-m-d 23:59', strtotime($request->datetime_deadline));

        $index->save();

        return redirect()->route('backend.designRequest')->with('success', 'Data Has Been Updated');
    }

    public function setStatus($id, Request $request)
    {
        $index = DesignRequest::find($id);
        $this->saveArchive('App\Models\DesignRequest', 'UPDATE_STATUS', $index);
    	DesignRequest::find($id)->update(['status_approval' => 'APPROVED']);

        $index = DesignCandidate::where('design_request_id', $id)->get();
        $this->saveMultipleArchive('App\Models\DesignCandidate', 'UPDATE_STATUS', $index);

    	DesignCandidate::where('design_request_id', $id)->where('id', $request->design_candidate_id)->update(['status_design' => 'CHOSEN']);
    	DesignCandidate::where('design_request_id', $id)->where('id', '<>', $request->design_candidate_id)->update(['status_design' => 'REJECT']);

    	return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = DesignRequest::find($request->id);
        $this->saveArchive('App\Models\DesignRequest', 'DELETED', $index);

        DesignRequest::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        // if ($request->action == 'delete' && Auth::user()->can('delete-designRequest')) {
        //     DesignRequest::destroy($request->id);
        //     return redirect()->back()->with('success', 'Data Has Been Deleted');
        // }

        return redirect()->back()->with('success', 'Access Denied');
    }
}
