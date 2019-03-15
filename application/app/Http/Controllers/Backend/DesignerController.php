<?php

namespace App\Http\Controllers\Backend;

use App\Designer;
use App\User;
use App\Config;

use App\Models\DesignRequest;
use App\Models\DesignCandidate;
use App\Models\DesignCandidatePreview;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Session;
use File;
use Hash;
use Validator;
use PDF;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class DesignerController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
	{
		$year     = Designer::select(DB::raw('YEAR(created_at) as year'))->distinct()->get();
		$month    = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

		$config    = Config::all();
		$data = '';
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
            $data[] = $list->for;
        }

		$designer = User::where(function ($query) use ($designer_position, $designer_user) {
            $query->whereIn('position', explode(', ' , $designer_position->value))
            ->orWhereIn('id', explode(', ' , $designer_user->value));
        })->where('active', 1)->orderBy('fullname', 'asc');

		if(!Auth::user()->can('allDesigner-designer'))
        {
            $designer->whereIn('id', Auth::user()->staff());
        }
        $designer = $designer->get();

		$sales    = User::where(function ($query) use ($sales_position, $sales_user) {
            $query->whereIn('position', explode(', ' , $sales_position->value))
            ->orWhereIn('id', explode(', ' , $sales_user->value));
        })->where('active', 1)->orderBy('fullname', 'asc');
		if(!Auth::user()->can('allSales-designer'))
        {
            $sales->whereIn('id', Auth::user()->staff());
        }
        $sales = $sales->get();

		$status   = ['pending', 'progress', 'reject', 'finish'];

		return view('backend.designer.index', compact('year', 'month', 'designer', 'sales', 'status', 'request', $data));
	}

	public function datatables(Request $request)
	{
		$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

		$f_year     = $this->filter($request->f_year);
        $f_month    = $this->filter($request->f_month);
        $f_sales    = $this->filter($request->f_sales, (in_array(Auth::user()->position, explode(', ', $sales_position->value)) || in_array(Auth::id(), explode(', ', $sales_user->value)) ? Auth::id() : ''));
        $f_designer = $this->filter($request->f_designer, (in_array(Auth::user()->position, explode(', ', $designer_position->value)) || in_array(Auth::id(), explode(', ', $designer_user->value)) ? Auth::id() : ''));
        $f_status   = $this->filter($request->f_status);
        $f_urgent   = $this->filter($request->f_urgent);
        $f_id       = $this->filter($request->f_id);
        $f_date     = $this->filter($request->f_date);

        $index    = Designer::orderBy('id', 'DESC')
        	->select('designer.*')
        	->addSelect(DB::raw(
    			'
    				(select fullname from users where id = designer.designer_id) as designer,
    				(select fullname from users where id = designer.sales_id) as sales
    			'
        	));

        if($f_date != '')
        {
        	$index->whereDate('designer.date_finish_project', $f_date);
        }
        else if($f_id != '')
        {
        	$index->where('designer.id', $f_id);
        }
        else
        {
        	if($f_designer == 'staff')
	        {
	            $index->whereIn('designer_id', Auth::user()->staff());
	        }
	        else if($f_designer != '')
	        {
	            $index->where('designer_id', $f_designer);
	        }
	        
	        if($f_sales == 'staff')
	        {
	            $index->whereIn('sales_id', Auth::user()->staff());
	        }
	        else if($f_sales != '')
	        {
	            $index->where('sales_id', $f_sales);
	        }

	        switch ($f_status) {
	        	case 'NOT_FINISH':
	        		$index->whereIn('status_project', ['pending', 'progress', 'reject']);
	        		break;
	        	case 'FINISH':
	        		$index->where('status_project', 'finish');
	        		break;
	        	default:
	        		break;
	        }

	        if($f_urgent != '')
	        {
	            $index->where('urgent', $f_urgent);
	        }

	        if($f_month != '')
	        {
	            $index->whereYear('created_at', $f_month);
	        }

	        if($f_year != '')
	        {
	            $index->whereYear('created_at', $f_year);
	        }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('sales', function ($index) {
            $html = "<b>Sales </b> : ". $index->sales . "<br/>";
            $html .= "<b>Designer </b> : ". $index->designer;
            return $html;
        });

        $datatables->editColumn('project', function ($index) {
            return $index->project . ($index->revision ? ' (Revision : '.$index->revision.')' : '');
        });

        $datatables->editColumn('created_at', function ($index) {
            return date('d/m/Y H:i', strtotime($index->created_at));
        });

        $datatables->editColumn('start_project', function ($index) {
            $html = "<b>Create </b> : ". date('d/m/Y H:i', strtotime($index->created_at)) . "<br/>";

            if($index->status_project != 'pending')
            {
            	
            	$html .= "<b>Start </b> : ". date('d/m/Y H:i', strtotime($index->start_project)) . "<br/>";
	            $html .= "<b>End </b> : ". date('d/m/Y H:i', strtotime($index->end_project));
            }

            return $html;
        });


        $datatables->editColumn('status_project', function ($index) use ($sales_position, $sales_user, $designer_position, $designer_user) {
        	$html = '';
            $html .= $index->status_project.' '.($index->urgent ? ' - urgent!' : '').'<br/>';

            if($index->status_project == 'pending')
            {
            	if( Auth::user()->can('take-designer')
					&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
						 || $this->usergrant($index->designer_id, 'allDesigner-designer')
						 || ( $this->levelgrant($index->sales_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
						 	  	 ) 
						 	)
						 || ( $this->levelgrant($index->designer_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
						 	  	 )) 
						) 
				  )
				{
					$html .= '<button class="btn btn-primary btn-xs take-designer" data-toggle="modal" data-target="#take-designer" data-id="'.$index->id.'" title="Finish Designer Project">Take</button>';
				}
            }
			else if($index->status_project == 'progress')
			{
				if( Auth::user()->can('finish-designer')
					&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
						 || $this->usergrant($index->designer_id, 'allDesigner-designer')
						 || ( $this->levelgrant($index->sales_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
						 	  	 ) 
						 	)
						 || ( $this->levelgrant($index->designer_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
						 	  	 )) 
						) 
				  )
				{
					$html .= '<button class="btn btn-primary btn-xs finish-designer" data-toggle="modal" data-target="#finish-designer" data-id="'.$index->id.'" title="Finish Designer Project">Finish</button>';
				}
			}
			else if($index->status_project == 'finish')
			{
				$html .= date('d/m/Y H:i', strtotime($index->date_finish_project));
			}

			return $html;
        });

        $datatables->editColumn('approved_sales', function ($index) use ($sales_position, $sales_user, $designer_position, $designer_user) {
        	$html = '';

        	$html .= $index->approved_sales;
			if( $index->approved_sales == 'waiting'
			    && $index->status_project == 'finish'
			    && ( !in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
		 	  	     && !in_array(Auth::id(), explode(', ', $designer_user->value))
		 	  	   )
			  )
			{
				$html .='<br/>';

				if( Auth::user()->can('approved-designer')
					&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
						 || $this->usergrant($index->designer_id, 'allDesigner-designer')
						 || ( $this->levelgrant($index->sales_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
						 	  	 ) 
						 	)
						 || ( $this->levelgrant($index->designer_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
						 	  	 )) 
						) 
				  )
				{
					$html .='<button class="btn btn-success btn-xs approve-designer" data-id="'.$index->id.'" title="Approve Designer Project"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i></button>';

					$html .='<button class="btn btn-danger btn-xs reject-designer" data-id="'.$index->id.'" title="Reject Designer Project"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i></button>';
				}
			}
			return $html;
        });

        $datatables->editColumn('result_project', function ($index) use ($sales_position, $sales_user, $designer_position, $designer_user) {
        	$html = '';

        	$html .= $index->result_project;
			if( $index->result_project == 'waiting' 
				&& $index->status_project == 'finish' 
				&& $index->approved_sales != 'waiting' 
				&& ( !in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
		 	  	     && !in_array(Auth::id(), explode(', ', $designer_user->value))
		 	  	   )
			  )
			{
				$html .='<br/>';

				if( Auth::user()->can('project-designer') 
					&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
						 || $this->usergrant($index->designer_id, 'allDesigner-designer')
						 || ( $this->levelgrant($index->sales_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
						 	  	 ) 
						 	)
						 || ( $this->levelgrant($index->designer_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
						 	  	 )) 
						) 
				  )
				{
					$html .='<button class="btn btn-success btn-xs success-designer" data-id="'.$index->id.'" title="Success Designer Project"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i></button>';

					$html .='<button class="btn btn-danger btn-xs failed-designer" data-id="'.$index->id.'" title="Failed Designer Project"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i></button>';
				}

				if( Auth::user()->can('revision-designer') 
					&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
						 || $this->usergrant($index->designer_id, 'allDesigner-designer')
						 || ( $this->levelgrant($index->sales_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
						 	  	 ) 
						 	)
						 || ( $this->levelgrant($index->designer_id) 
						 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
						 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
						 	  	 )
						 	) 
						) 
				  )
				{
					$html .='<button class="btn btn-warning btn-xs revision-designer" data-toggle="modal" data-target="#revision-designer" data-id="'.$index->id.'" title="Revision Designer Project"><i class="fa fa-undo" aria-hidden="true"></i></button>';
				}
			}
			return $html;
        });

        $datatables->addColumn('action', function ($index) use ($sales_position, $sales_user, $designer_position, $designer_user) {
            $html = '';
            if( Auth::user()->can('edit-designer') 
            	&& ( $this->usergrant($index->sales_id, 'allSales-designer')
            	     || $this->usergrant($index->designer_id, 'allDesigner-designer') 
            	     || ( $this->levelgrant($index->sales_id) 
            	     	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
					 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
					 	  	 )
            	     	)
            	     || ( $this->levelgrant($index->designer_id) 
            	     	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
					 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
					 	  	 )
            	     	)
            	    )
              )
			{
	            $html .= '
	                <a href="'. route('backend.designer.edit', ['id' => $index->id]) .'" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
	            ';
	        }

	        if( Auth::user()->can('delete-designer') 
	        	&& ( $this->usergrant($index->sales_id, 'allSales-designer') 
	        		 || $this->usergrant($index->designer_id, 'allDesigner-designer') 
	        		 || ( $this->levelgrant($index->sales_id) 
	        		 	  && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
					 	  	   || in_array(Auth::id(), explode(', ', $sales_user->value))
					 	  	 )
	        		 	) 
	        		 || ( $this->levelgrant($index->designer_id) 
	        		 	  && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
					 	  	   || in_array(Auth::id(), explode(', ', $designer_user->value))
					 	  	 )
	        		 	) 
	        		) 
	          )
			{
	            $html .= '
	                <button class="btn btn-danger btn-xs delete-designer" data-toggle="modal" data-target="#delete-designer" data-id="'.$index->id.'" title="Delete Designer Project"><i class="fa fa-trash" aria-hidden="true"></i></button>
	            ';
	        }
            return $html;
        });

        $datatables->addColumn('check', function ($index) use ($sales_position, $sales_user, $designer_position, $designer_user) {
            $html = '';
            if( $this->usergrant($index->sales_id, 'allSales-designer')
                || $this->usergrant($index->designer_id, 'allDesigner-designer') 
                || ( $this->levelgrant($index->sales_id) 
                	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
				 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
				 	  	)
                   ) 
                || ( $this->levelgrant($index->designer_id) 
                	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
				 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
				 	  	)
                   ) 
              )
        	{
        		$html .= '
	                <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
	            ';
        	}
	            
            return $html;
        });

        $datatables->setRowClass(function ($index) {
        	if($index->status_project == 'finish')
        	{
        		return 'alert-info';
        	}
            if($index->urgent == 1)
            {
            	return 'alert-danger';
            }
        });

        $datatables = $datatables->make(true);
        return $datatables;
	}

    public function create()
	{
		$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

		$designer = User::whereIn('position', explode(', ', $designer_position->value))
			->orWhereIn('id', explode(', ', $designer_user->value))
			->get();

		$leader  = User::where(function ($query) use ($designer_position, $designer_user) {
			$query->whereIn('position', explode(', ', $designer_position->value))
				->orWhereIn('id', explode(', ', $designer_user->value));
		})->where('level', 1)->get();

		return view('backend.designer.create', compact('designer', 'leader'));
	}

	public function store(Request $request)
	{
		$message = [
			'designer_id.required' => 'This field required.',
            'project.required' => 'This field required.',
            'description.required' => 'This field required.',
		];

        $validator = Validator::make($request->all(), [
            'designer_id' => 'required',
            'project' => 'required',
            'description' => 'required',
        ], $message);
    		
		if($validator->fails())
		{
			return redirect()->back()->withErrors($validator)->withInput();
		}

    	$findProgress = Designer::where('designer_id', $request->designer_id)->where('status_project', 'progress')->count();

		$index = new Designer;

		$index->sales_id       = Auth::id();
		$index->designer_id    = $request->designer_id;
		$index->project        = $request->project;
		$index->description    = $request->description;
		$index->urgent         = isset($request->urgent) ? 1 : 0;
		$index->urgent         = isset($request->urgent) ? 1 : 0;
		$index->status_project = 'pending';
		$index->approved_sales = 'waiting';
		$index->result_project = 'waiting';
		$index->revision       = 0;
		
		$index->save();

		$html = '
            New Project Design, Project : '.$request->project.'
        ';

		User::find($request->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

		return redirect()->route('backend.designer')->with('success', 'Data has been Added');
	}

	public function edit( $id )
	{
		$index    = Designer::find($id);

		$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

		if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

		$designer = User::where(function ($query) use ($designer_position, $designer_user) {
				$query->whereIn('position', explode(', ', $designer_position->value))
					->orWhereIn('id', explode(', ', $designer_user->value));
			})->get();
		$leader   = User::where(function ($query) use ($designer_position, $designer_user) {
				$query->whereIn('position', explode(', ', $designer_position->value))
					->orWhereIn('id', explode(', ', $designer_user->value));
			})->where('level', 1)->get();

		return view('backend.designer.edit', compact('index', 'designer', 'leader'));
	}

	public function update($id, Request $request)
	{
		$find = Designer::find($id);

		$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

		if( !( $this->usergrant($find->sales_id, 'allSales-designer')
		       || $this->usergrant($find->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($find->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($find->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

		$message = [
			'designer_id.required' => 'This field required.',
            'project.required' => 'This field required.',
		];

        $validator = Validator::make($request->all(), [
            'designer_id' => 'required',
            'project' => 'required',
        ], $message);
    		
		if($validator->fails())
		{
			return redirect()->back()->withErrors($validator)->withInput();
		}

		
		$index = Designer::find($id);

		$this->saveArchive('App\Designer', 'UPDATED', $index);

		$index->project = $request->project;
		$index->description = $request->description;
		$index->urgent = isset($request->urgent) ? 1 : 0;

		$index->save();

		$html = '
	        New Update Project Design, Project : '.$request->project.'
	    ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

		return redirect()->route('backend.designer')->with('success', 'Data has been Updated');
	}

	public function delete(Request $request)
	{
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'DELETED', $index);

    	$index->delete();

    	return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
    	if($request->action == 'delete' && Auth::user()->can('delete-designer'))
    	{
    		$index = Designer::find($request->id);
    		$this->saveMultipleArchive('App\Designer', 'DELETED', $index);

    		Designer::destroy($request->id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
    	}
    	return redirect()->back()->with('info', 'No data change');
    }

    public function take(Request $request)
    {
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'TAKE', $index);

        $now = date('Y-m-d H:i:s');
        $index->start_project = $now;

        if(date('w', strtotime($now . ' +20 hours')) == 0)
        {
            $index->end_project = date('Y-m-d H:i:s', strtotime($now . ' +20 hours +1 days'));
        }
        else
        {
            $index->end_project = date('Y-m-d H:i:s', strtotime($now . ' +20 hours'));
        }
        
        $index->status_project = "progress";
    	$index->approved_sales = 'waiting';
    	$index->save();

		$html = '
            Designer Has finish project, Project : '.$index->project.'
        ';

		User::find($index->sales_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	return redirect()->back()->with('success', 'Data has been Updated');
    }

    public function finish(Request $request)
    {
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$message = [
			'note_project.required' => 'This field required.',
		];

        $validator = Validator::make($request->all(), [
            'note_project' => 'required',
        ], $message);
    		
		if($validator->fails())
		{
			return redirect()->back()->withErrors($validator)->withInput()->with('finish-designer-error', '');
		}

		$this->saveArchive('App\Designer', 'FINISH', $index);

    	$index->date_finish_project = date('Y-m-d H:i:s');
    	$index->status_project      = "finish";
    	$index->note_project        = $request->note_project;
    	$index->approved_sales      = 'waiting';

    	$index->save();

		$html = '
            Designer Has finish project, Project : '.$index->project.'
        ';

		User::find($index->sales_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	return redirect()->back()->with('success', 'Data has been Updated');
    }

    public function approve(Request $request)
    {
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'APPROVED', $index);

    	$index->approved_sales = 'approved';
		// $index->note_approved_sales = $request->note_approved_sales;
		$index->date_approved_sales = date('Y-m-d H:i:s');
		

    	$index->save();

    	$html = '
            Your designer has been approved, Project : '.$index->project.'
        ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	Session::flash('success', 'Data has been Updated');
    	return redirect()->back();
    }

    public function reject(Request $request)
    {
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'REJECTED', $index);

    	$index->approved_sales = 'rejected';
		// $index->note_approved_sales = $request->note_approved_sales;
		$index->date_approved_sales = date('Y-m-d H:i:s');
		$index->status_project = 'progress';
		$index->date_finish_project = null;
		$index->note_project = null;

    	$index->save();

    	$html = '
            Your designer is reject, Project : '.$index->project.'
        ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	Session::flash('success', 'Data has been Updated');
    	return redirect()->back();
    }

    public function success(Request $request)
    {

    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'SUCCESS', $index);

    	$index->result_project = 'success';

    	$index->save();

    	$html = '
            Your designer project is BIG SUCCESS!!, Project : '.$index->project.'
        ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	Session::flash('success', 'Data has been Updated');
    	return redirect()->back();
    }

    public function failed(Request $request)
    {
    	$index = Designer::find($request->id);

    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	if( !( $this->usergrant($index->sales_id, 'allSales-designer')
		       || $this->usergrant($index->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($index->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($index->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

    	$this->saveArchive('App\Designer', 'FAILED', $index);

    	$index->result_project = 'failed';

    	$index->save();

    	$html = '
            Your designer project is failed, Project : '.$index->project.'
        ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

    	Session::flash('success', 'Data has been Updated');
    	return redirect()->back();
    }

    public function revision(Request $request)
	{
		$find = Designer::find($request->id);

		$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

		if( !( $this->usergrant($find->sales_id, 'allSales-designer')
		       || $this->usergrant($find->designer_id, 'allDesigner-designer') 
		    )
		    || ( !$this->levelgrant($find->sales_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $sales_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $sales_user->value))
			 	  	)
		       ) 
		    || ( !$this->levelgrant($find->designer_id) 
		    	 && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	  	  || in_array(Auth::id(), explode(', ', $designer_user->value))
			 	  	)
		       )
		  )
    	{
    		return redirect()->route('backend.designer')->with('failed', 'Access Denied');
    	}

		$message = [
			'description.required' => 'This field required.',
		];

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ], $message);
    		
		if($validator->fails())
		{
			return redirect()->back()->withErrors($validator)->withInput()->with('revision-designer-error', '');
		}

		$this->saveArchive('App\Designer', 'REVISION', $index);

		$find->result_project = 'revision';

    	$find->save();

		$index = new Designer;

		$index->sales_id = $find->sales_id;
		$index->designer_id = $find->designer_id;
		$index->project = $find->project;
		$index->description = $request->description;

		$findProgress = Designer::where('designer_id', $find->designer_id)->where('status_project', 'progress')->count();

		if($findProgress > 0)
		{
			$index->status_project = "pending";
		}
		else
		{
			$now = date('Y-m-d H:i:s');
			$index->start_project = $now;

			if(date('w', strtotime($now . ' +20 hours')) == 0)
			{
				$next->end_project = date('Y-m-d H:i:s', strtotime($now . ' +20 hours +1 days'));
			}
			else
			{
				$next->end_project = date('Y-m-d H:i:s', strtotime($now . ' +20 hours'));
			}
			
			$index->status_project = "progress";

		}
		
		$index->urgent = isset($request->urgent) ? 1 : 0;
		$index->approved_sales = 'waiting';
		$index->result_project = 'waiting';
		$index->revision = $find->revision + 1;

		$index->save();

		$html = '
            Your designer has revision, Project : '.$index->project.'
        ';

		User::find($index->designer_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.designer', ['f_id' => $index->id])));

		Session::flash('success', 'Data has been Updated');
		return redirect('/designer');
	}

    public function calendar(Request $request)
    {
    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	$designer = User::where(function ($query) use ($designer_position, $designer_user) {
				$query->whereIn('position', explode(', ', $designer_position->value))
					->orWhereIn('id', explode(', ', $designer_user->value));
			})->get();
    	$sales    = User::where(function ($query) use ($sales_position, $sales_user) {
				$query->whereIn('position', explode(', ', $sales_position->value))
					->orWhereIn('id', explode(', ', $sales_user->value));
			})->get();

		return view('backend.designer.calendar', compact('designer', 'sales', 'request'));
    }

    public function ajaxCalendar(Request $request)
    {
    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $f_sales    = $this->filter(in_array(Auth::user()->position, explode(', ', $sales_position->value)) || in_array(Auth::id(), explode(', ', $sales_user->value)) ? Auth::id() : '');
        $f_designer = $this->filter(in_array(Auth::user()->position, explode(', ', $designer_position->value)) || in_array(Auth::id(), explode(', ', $designer_user->value)) ? Auth::id() : '');

        $index = Designer::whereNotNull('start_project')
        	->where(function ($query) use ($request) {
                $query->whereBetween('start_project', [$request->start, $request->end])
                    ->orwhereBetween('end_project', [$request->start, $request->end]);
            });

        if($f_sales != '')
        {
            $index->where('designer.sales_id', $f_sales);
        }

        if($f_designer != '')
        {
            $index->where('designer.designer_id', $f_designer);
        }

        $index = $index->get();

        $event = '';

        foreach ($index as $list) {
            $event [] = [
                "title"         => '['.substr($list->designer->fullname, 0, 1) .'] '. $list->project,
                "designer_name" => $list->designer->fullname,
                "sales_name"    => $list->sales->fullname,
                "project"       => $list->project,
                "description"   => $list->description,
                "start_project" => date('d F Y H:i', strtotime($list->start_project)),
                "end_project"   => date('d F Y H:i', strtotime($list->end_project)),
                "status"        => ucfirst($list->status_project),
                "start"         => date('Y-m-d H:i', strtotime($list->start_project)),
                "end"           => date('Y-m-d H:i', strtotime($list->end_project)),
                "color"         => $this->strtocolor($list->designer->fullname, 25),
                "textColor"     => $this->strtocolor($list->designer->fullname, 100),
            ];
        }

        return $event;
    }

    public function dashboard(Request $request)
    {
    	$config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

		$year  = Designer::select(DB::raw('YEAR(created_at) as year'))->distinct()->get();
		$month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    	$index    = User::where(function ($query) use ($designer_position, $designer_user) {
				$query->whereIn('position', explode(', ', $designer_position->value))
					->orWhereIn('id', explode(', ', $designer_user->value));
			});
    	$designer = Designer::where('revision', '0');

		if( !Auth::user()->can('allDesigner-designer') 
			&& ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
		     	 || in_array(Auth::id(), explode(', ', $designer_user->value))
		 	   )
		  )
        {
            $index->where('id', Auth::id())->orWhereIn('id', Auth::user()->staff());
        }

    	if($f_year != '')
		{
			$designer->whereYear('created_at', $f_year);
		}

		if($f_month != '')
		{
			$designer->whereMonth('created_at', $f_month);
		}

		$designer = $designer->get();
		$index = $index->get();

		return view('backend.designer.dashboard', compact('index', 'request', 'year', 'month', 'designer'));
    }

    public function ajaxDashboard(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);
        $f_day   = $this->filter($request->f_day);

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        /* designer All */
        {
            $sql_designer_all = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE 1

            ';

            if($f_year != '')
            {
                $sql_designer_all .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_all .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_all .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_all .= '
                    GROUP BY designer.designer_id
                ) designer_all';
        }

        /* designer Pending */
        {
            $sql_designer_pending = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "pending"

            ';

            if($f_year != '')
            {
                $sql_designer_pending .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_pending .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_pending .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_pending .= '
                    GROUP BY designer.designer_id
                ) designer_pending';
        }

        /* designer Progress */
        {
            $sql_designer_progress = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "progress"

            ';

            if($f_year != '')
            {
                $sql_designer_progress .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_progress .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_progress .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_progress .= '
                    GROUP BY designer.designer_id
                ) designer_progress';
        }

        /* designer Finish */
        {
            $sql_designer_finish = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish"

            ';

            if($f_year != '')
            {
                $sql_designer_finish .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_finish .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_finish .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_finish .= '
                    GROUP BY designer.designer_id
                ) designer_finish';
        }

        /* designer Waiting */
        {
            $sql_designer_waiting = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish" AND approved_sales = "waiting"

            ';

            if($f_year != '')
            {
                $sql_designer_waiting .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_waiting .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_waiting .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_waiting .= '
                    GROUP BY designer.designer_id
                ) designer_waiting';
        }

        /* designer Great */
        {
            $sql_designer_great = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish" AND approved_sales = "approved" AND DATE(date_finish_project) < DATE(end_project)
            ';

            if($f_year != '')
            {
                $sql_designer_great .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_great .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_great .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_great .= '
                    GROUP BY designer.designer_id
                ) designer_great';
        }

        /* designer Good */
        {
            $sql_designer_good = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish" AND approved_sales = "approved" AND DATE(date_finish_project) = DATE(end_project)
            ';

            if($f_year != '')
            {
                $sql_designer_good .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_good .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_good .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_good .= '
                    GROUP BY designer.designer_id
                ) designer_good';
        }

        /* designer Bad */
        {
            $sql_designer_bad = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE (status_project = "finish" AND approved_sales = "approved" AND DATE(date_finish_project) > DATE(end_project))
            ';

            if($f_year != '')
            {
                $sql_designer_bad .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_bad .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_bad .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_bad .= '
                    GROUP BY designer.designer_id
                ) designer_bad';
        }

        /* designer Success */
        {
            $sql_designer_success = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish" AND approved_sales = "approved" AND result_project = "success"
            ';

            if($f_year != '')
            {
                $sql_designer_success .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_success .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_success .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_success .= '
                    GROUP BY designer.designer_id
                ) designer_success';
        }

        /* designer Failed */
        {
            $sql_designer_failed = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, COUNT(designer.id) as countDesigner
                    FROM designer
                    WHERE status_project = "finish" AND approved_sales = "approved" AND result_project = "failed"
            ';

            if($f_year != '')
            {
                $sql_designer_failed .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_failed .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_failed .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_failed .= '
                    GROUP BY designer.designer_id
                ) designer_failed';
        }

        /* designer AvgCreateToStart */
        {
            $sql_designer_avg = '
                (
                    /* sales -> offer */
                    SELECT designer.designer_id, AVG(TIMESTAMPDIFF(SECOND, designer.created_at, designer.start_project)) as avgCreateToStart, AVG(TIMESTAMPDIFF(SECOND, designer.start_project, designer.date_finish_project)) as avgStartToFinish
                    FROM designer 
                    WHERE status_project = "finish"
            ';

            if($f_year != '')
            {
                $sql_designer_avg .= '
                    AND YEAR(designer.created_at) = '.$f_year.'
                ';
            }

            if($f_month != '')
            {
                $sql_designer_avg .= '
                    AND MONTH(designer.created_at) = '.$f_month.'
                ';
            }

            if($f_day != '')
            {
                $sql_designer_avg .= '
                    AND DAY(designer.created_at) = '.$f_day.'
                ';
            }

            $sql_designer_avg .= '
                    GROUP BY designer.designer_id
                ) designer_avg';
        }

        $index = Designer::select(
	        	'designer.designer_id',
	        	'users.fullname',
	        	'designer_all.countDesigner as all',
	        	'designer_pending.countDesigner as pending',
	        	'designer_progress.countDesigner as progress',
	        	'designer_finish.countDesigner as finish',
	        	'designer_waiting.countDesigner as waiting',
	        	'designer_great.countDesigner as great',
	        	'designer_good.countDesigner as good',
	        	'designer_bad.countDesigner as bad',
	        	'designer_success.countDesigner as success',
	        	'designer_failed.countDesigner as failed',
	        	'designer_avg.avgCreateToStart as avgCreateToStart',
	        	'designer_avg.avgStartToFinish as avgStartToFinish'
        	)
        	->distinct()
        	->where('position', 'designer')
        	->join('users', 'designer.designer_id', 'users.id')
        	->leftJoin(DB::raw($sql_designer_all), 'designer.designer_id', 'designer_all.designer_id')
        	->leftJoin(DB::raw($sql_designer_pending), 'designer.designer_id', 'designer_pending.designer_id')
        	->leftJoin(DB::raw($sql_designer_progress), 'designer.designer_id', 'designer_progress.designer_id')
        	->leftJoin(DB::raw($sql_designer_finish), 'designer.designer_id', 'designer_finish.designer_id')
        	->leftJoin(DB::raw($sql_designer_waiting), 'designer.designer_id', 'designer_waiting.designer_id')
        	->leftJoin(DB::raw($sql_designer_great), 'designer.designer_id', 'designer_great.designer_id')
        	->leftJoin(DB::raw($sql_designer_good), 'designer.designer_id', 'designer_good.designer_id')
        	->leftJoin(DB::raw($sql_designer_bad), 'designer.designer_id', 'designer_bad.designer_id')
        	->leftJoin(DB::raw($sql_designer_success), 'designer.designer_id', 'designer_success.designer_id')
        	->leftJoin(DB::raw($sql_designer_failed), 'designer.designer_id', 'designer_failed.designer_id')
        	->leftJoin(DB::raw($sql_designer_avg), 'designer.designer_id', 'designer_avg.designer_id');

        if( !Auth::user()->can('allDesigner-designer') 
            && ( in_array(Auth::user()->position, explode(', ', $designer_position->value)) 
			 	 || in_array(Auth::id(), explode(', ', $designer_user->value))
			   )
          )
        {
            $index->where('id', Auth::id())->orWhereIn('id', Auth::user()->staff());
        }

        if($f_year != '')
		{
			$index->whereYear('designer.created_at', $f_year);
		}

		if($f_month != '')
		{
			$index->whereMonth('designer.created_at', $f_month);
		}

		if($f_day != '')
		{
			$index->whereDay('designer.created_at', $f_day);
		}

        $index = $index->get();

        $data = '';
        $all = $pending = $progress = $finish = $waiting = $great = $good = $bad = $success = $failed = 0;
        foreach ($index as $list) {
            $data[] = [
                "id"       => $list->designer_id,
                "fullname" => $list->fullname,

                "all"      => number_format($list->all),
                "pending"  => number_format($list->pending),
                "progress" => number_format($list->progress),
                "finish"   => number_format($list->finish),
                "waiting"  => number_format($list->waiting),
                "great"    => number_format($list->great),
                "good"     => number_format($list->good),
                "bad"      => number_format($list->bad),
                "success"  => number_format($list->success),
                "failed"   => number_format($list->failed),
                "avgCreateToStart" => $this->avgDiffReadable($list->avgCreateToStart),
                "avgStartToFinish" => $this->avgDiffReadable($list->avgStartToFinish),
            ];

            $all      += $list->all;
            $pending  += $list->pending;
            $progress += $list->progress;
            $finish   += $list->finish;
            $waiting  += $list->waiting;
            $great    += $list->great;
            $good     += $list->good;
            $bad      += $list->bad;
            $success  += $list->success;
            $failed   += $list->failed;
        }

        return compact(
        	'data',
        	"all",
            "pending",
            "progress",
            "finish",
            "waiting",
            "great",
            "good",
            "bad",
            "success",
            "failed"
        );
    }

    public function getData(Request $request)
    {
        $f_year  = $this->filter($request->f_year, date('Y'));
        $f_month = $this->filter($request->f_month);

        $index = Designer::where('designer_id', $request->id)->leftJoin('users', 'users.id', 'designer.sales_id');

        if($f_year != '')
		{
			$index->whereYear('designer.created_at', $f_year);
		}

		if($f_month != '')
		{
			$index->whereMonth('designer.created_at', $f_month);
		}


        if($request->type == 'PENDING')
        {
            $index->where('status_project', 'pending');
        }
        else if($request->type == 'PROGRESS')
        {
            $index->where('status_project', 'progress');
        }
        else if($request->type == 'FINISH')
        {
            $index->where('status_project', 'finish');
        }
        else if($request->type == 'WAITING')
        {
            $index->where('status_project', 'finish')->where('approved_sales', 'waiting');
        }
        else if($request->type == 'GREAT')
        {
            $index->where('status_project', 'finish')
            	->where('approved_sales', 'approved')
            	->where(DB::raw('DATE(date_finish_project)'), '<', DB::raw('DATE(end_project)'));
        }
        else if($request->type == 'GOOD')
        {
            $index->where('status_project', 'finish')
            	->where('approved_sales', 'approved')
            	->where(DB::raw('DATE(date_finish_project)'), DB::raw('DATE(end_project)'));
        }
        else if($request->type == 'BAD')
        {
            $index->where(function ($query) {
            		$query->where('status_project', 'finish')
            		->where('approved_sales', 'approved')
	            	->where(DB::raw('DATE(date_finish_project)'), '>', DB::raw('DATE(end_project)'));
            	});
        }
        else if($request->type == 'SUCCESS')
        {
            $index->where('status_project', 'finish')
            	->where('approved_sales', 'approved')
            	->where('result_project', 'success');
        }
        else if($request->type == 'FAILED')
        {
            $index->where('status_project', 'finish')
            	->where('approved_sales', 'approved')
            	->where('result_project', 'failed');
        }

        $index = $index->get();

        return $index;
    }

    function avgDiffReadable($number)
    {
    	if($number < 60)
    	{
    		return number_format($number) . ' Seconds';
    	}
    	else if($number < 3600)
    	{
    		return number_format($number/60 ). ' Minutes';
    	}
    	else if($number < 86400)
    	{
    		return number_format($number/3600 ). ' Minutes';
    	}
    	else
    	{
    		return number_format($number/86400). ' Days';
    	}
    }

    public function designCandidate(Request $request)
    {
    	$status   = ['PENDING'];

        return view('backend.designer.request')->with(compact('request', 'status'));
    }

    public function datatablesDesignCandidate(Request $request)
    {
    	$f_status   = $this->filter($request->f_status);

        $index = DesignRequest::select('design_request.*');

        if($f_status != '')
        {
            $index->where('status_approval', $f_status);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $count = DesignCandidate::where('design_request_id', $index->id)->where('designer_id', Auth::id())->count();
            $design_candidate = DesignCandidate::where('design_request_id', $index->id)->where('designer_id', Auth::id())->first();

            if(Auth::user()->can('createDesignCandidate-designer') && $count == 0 && strtotime($index->datetime_deadline) >= time())
            {
                $html .= '
                    <a href="' . route('backend.designer.createDesignCandidate', ['id' => $index->id]) . '" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></a>
                ';
            }

            else if(strtotime($index->datetime_deadline) >= time())
            {
            	if(Auth::user()->can('editDesignCandidate-designer'))
	            {
	                $html .= '
	                    <a href="' . route('backend.designer.editDesignCandidate', ['id' => $design_candidate->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
	                ';
	            }

	            if(Auth::user()->can('deleteDesignCandidate-designer'))
	            {
	                $html .= '
	                    <button class="btn btn-xs btn-danger deleteDesignCandidate-designer" data-toggle="modal" data-target="#deleteDesignCandidate-designer" data-id="'.$design_candidate->id.'"><i class="fa fa-trash"></i></button>
	                ';
	            }
            }
            
                
            return $html;
        });

        $datatables->editColumn('budget', function ($index) {
            return number_format($index->budget);
        });

        $datatables->editColumn('datetime_deadline', function ($index) {
            return date('d/m/Y H:i', strtotime($index->datetime_deadline));
        });

        $datatables->addColumn('my_design', function ($index) {

        	$my_design = DesignCandidate::where('design_request_id', $index->id)->where('designer_id', Auth::id())->first(); 

            $html = $my_design->description ?? 'No Design';
            
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

    public function createDesignCandidate($id)
    {
    	$design_request = DesignRequest::find($id);
        return view('backend.designer.addCandidate', compact('design_request'));
    }

    public function storeDesignCandidate($id, Request $request)
    {
    	$design_request = DesignRequest::find($id);

    	if(strtotime($design_request->datetime_deadline) <= time()){
    		return view('backend.designer.request')->with('failed', 'Time out');
    	}

    	DB::beginTransaction();
        $message = [
            'description.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new DesignCandidate;

        $index->design_request_id = $id;
		$index->designer_id = Auth::id();
		$index->description = $request->description;

		$index->save();

		$images = '';
		$errors = '';
	    if($files = $request->file('image_preview')){
	        foreach($files as $file){
	        	if($file->getClientSize() > (1024 * 5000))
	        	{
	        		$errors[] = [$file->getClientOriginalName() . ': file is over than 5 MB']; 
	        		continue;
	        	}

	        	if(!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg']))
	        	{
	        		$errors[] = [$file->getClientOriginalName() . ': file is not images']; 
	        		continue;
	        	}

	            $pathSource = 'upload/designCandidate/';
	            $filename   = time() .'-'. $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
	            $file->move($pathSource, $filename);

	            $images[] = [
	            	'design_candidate_id' => $index->id,
	        		'image_preview'       => $pathSource . $filename,
	            ];
	        }
	    }

	    if(is_array($images))
	    {
	    	DesignCandidatePreview::insert($images);
	    	DB::commit();
	    }
	    else
	    {
	    	DB::rollBack();
	    	if(is_array($errors))
		    {
		    	$html = '<ul>';
		    	foreach ($errors as $list) {
		    		$html .= '<li>'. $list[0] .'</li>';
		    	}

		    	$html .= '</ul>';
		    	return redirect()->back()->withInput()->with('failed', $html);
		    }
	    	return redirect()->back()->withInput()->with('failed', 'Images file required');
	    }

	    if(is_array($errors))
	    {
	    	$html = 'Some item not success upload <ul>';
	    	foreach ($errors as $list) {
	    		$html .= '<li>'. $list[0] .'</li>';
	    	}

	    	$html .= '</ul>';
	    	return redirect()->route('backend.designer.designCandidate')->with('warning', $html);
	    }
	    else
	    {
	    	return redirect()->route('backend.designer.designCandidate')->with('success', 'Data Has Been Added');
	    }
        
    }

    public function editDesignCandidate($id)
    {
        $index         = DesignCandidate::find($id);
        return view('backend.designer.editCandidate')->with(compact('index'));
    }

    public function datatablesEditDesignCandidate(Request $request)
    {
        $index = DesignCandidatePreview::where('design_candidate_id', $request->id)->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('image_preview', function ($index) {
            return '<div style="width: 10em; height: 10em;background-image: url('. asset($index->image_preview) .');background-size: cover;background-origin: center;display: inline-block;" data-image_preview="'. asset($index->image_preview) .'" data-toggle="modal" data-target="#preview-designRequest" class="preview-designRequest"></div>';
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

    public function updateDesignCandidate($id, Request $request)
    {
    	$index = DesignCandidate::find($id);

        $design_request = DesignRequest::find($index->design_request_id);

    	if(strtotime($design_request->datetime_deadline) <= time()){
    		return redirect()->back()->with('failed', 'Time out');
    	}


        $message = [
            'description.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'description' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->saveArchive('App\Models\DesignCandidate', 'UPDATED', $index);

		$index->description = $request->description;

		$index->save();

		$images = '';
		$errors = '';
	    if($files = $request->file('image_preview')){
	        foreach($files as $file){
	        	if($file->getClientSize() > (1024 * 5000))
	        	{
	        		$errors[] = [$file->getClientOriginalName() . ': file is over than 5 MB']; 
	        		continue;
	        	}

	        	if(!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg']))
	        	{
	        		$errors[] = [$file->getClientOriginalName() . ': file is not images']; 
	        		continue;
	        	}

	            $pathSource = 'upload/designCandidate/';
	            $filename   = time() .'-'. $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
	            $file->move($pathSource, $filename);

	            $images[] = [
	            	'design_candidate_id' => $index->id,
	        		'image_preview'       => $pathSource . $filename,
	            ];
	        }
	    }

	    if(is_array($images))
	    {
	    	DesignCandidatePreview::insert($images);
	    	DB::commit();
	    }

	    if(is_array($errors))
	    {
	    	$html = 'Some item not success upload <ul>';
	    	foreach ($errors as $list) {
	    		$html .= '<li>'. $list[0] .'</li>';
	    	}

	    	$html .= '</ul>';
	    	return redirect()->back()->with('warning', $html);
	    }
	    else
	    {
	    	return redirect()->back()->with('success', 'Data Has Been Updated');
	    }

	}

    public function deleteDesignCandidate(Request $request)
    {
    	$image_preview = DesignCandidatePreview::where('design_candidate_id', $request->id)->get();

    	foreach ($image_preview as $list) {
    		// File::delete($list->image_preview);
    	}

    	$index = DesignCandidatePreview::where('design_candidate_id', $request->id)->get();
    	$this->saveMultipleArchive('App\Models\DesignCandidatePreview', 'DELETED', $index);
    	DesignCandidatePreview::where('design_candidate_id', $request->id)->delete();

    	$index = DesignCandidate::find($request->id);
    	$this->saveArchive('App\Models\DesignCandidate', 'DELETED', $index);
        DesignCandidate::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionDesignCandidatePreview(Request $request)
	{
		DB::beginTransaction();

		if ($request->action == 'delete') {

			$index = DesignCandidatePreview::find($request->id[0]);
	    	$this->saveMultipleArchive('App\Models\DesignCandidatePreview', 'DELETED', $index);

			DesignCandidatePreview::destroy($request->id);

			$countPIC = DesignCandidatePreview::where('design_candidate_id', $index->design_candidate_id)->count();

			if($countPIC == 0)
			{
				DB::rollback();
				return redirect()->back()->with('failed', 'Data can not be delete');
			}
			else
			{
				DB::commit();
				return redirect()->back()->with('success', 'Data Has Been Deleted');
			}

			
		}
	}
}
