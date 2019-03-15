<?php

namespace App\Http\Controllers\Backend;

use App\Todo;
use App\Company;
use App\User;
use App\Config;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;

class TodoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$sales = Todo::join('users as sales', 'sales.id', 'todo.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allSales-todo'))
        {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

    	$year   = Todo::select(DB::raw('YEAR(date_todo) as year'))->orderBy('date_todo', 'ASC')->distinct()->get();
        $month  = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $status = ['PENDING', 'RESCHEDULE', 'SUCCESS', 'FAILED'];

        return view('backend.todo.index')->with(compact('request', 'sales', 'year', 'month', 'status'));
    }

    public function datatables(Request $request)
    {
    	$f_search    = $this->filter($request->f_search);
    	$f_sales     = $this->filter($request->f_sales);
    	$f_date      = $this->filter($request->f_date);
    	$f_next_date = $this->filter($request->f_next_date);
    	$f_status    = $this->filter($request->f_status);
    	$f_month     = $this->filter($request->f_month, date('n'));
    	$f_year      = $this->filter($request->f_year, date('Y'));

        $index = Todo::join('users as sales', 'sales.id', 'todo.sales_id')
        	->select('todo.*', 'sales.fullname as sales_name')
        	->orderBy('todo.date_todo', 'DESC');

        if($f_sales == 'staff')
        {
            $index->whereIn('todo.sales_id', Auth::user()->staff());
        }
        else if($f_sales != '')
        {
            $index->where('todo.sales_id', $f_sales);
        }
        
        if($f_date != '')
        {
            $index->whereDate('todo.date_todo', $f_date);
        }

        if($f_next_date != '')
        {
            $index->whereDate('todo.next_date', $f_next_date);
        }

        if($f_status != '')
        {
            $index->where('todo.status', $f_status);
        }

        if($f_month != '')
        {
            $index->whereMonth('todo.date_todo', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('todo.date_todo', $f_year);
        }

        if($f_search != '') {
			$index->where(function ($index) use ($f_search) {
				$result->where('todo.company', 'like', '%' . $f_search . '%')
					->orWhere('todo.brand', 'like', '%' . $f_search . '%');
			});
		}

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('edit-todo') && ($this->usergrant($index->sales_id, 'allSales-todo') || $this->levelgrant($index->sales_id)) )
            {
                $html .= '
                    <a href="' . route('backend.todo.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if(Auth::user()->can('delete-todo') && ($this->usergrant($index->sales_id, 'allSales-todo') || $this->levelgrant($index->sales_id)) )
            {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-todo" data-toggle="modal" data-target="#delete-todo" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('detail', function ($index) {
            $html = '';

            $html .= '<table class="table table-bordered">';

            $html .= '
            	<tr>
            		<th>Event</th>
            		<td>'.$index->event.'</td>
            	</tr>
            ';

            if($index->status == 'SUCCESS')
            {
                $html .= '
                	<tr>
                		<th>Next Date</th>
                		<td>'.date('d/m/Y', strtotime($index->next_date)).'</td>
                	</tr>
                	<tr>
                		<th>Next Event</th>
                		<td>'.$index->next.'</td>
                	</tr>
                ';
            }

            if($index->status == 'FAILED')
            {
                $html .= '
                	<tr>
                		<th>Reason Failed</th>
                		<td>'.$index->reason.'</td>
                	</tr>
                ';
            }

            if($index->status == 'RESCHEDULE')
            {
                $html .= '
                	<tr>
                		<th>Reschedule Date</th>
                		<td>'.date('d/m/Y', strtotime($index->reschedule_date)).'</td>
                	</tr>
                	<tr>
                		<th>Reason Reschedule</th>
                		<td>'.$index->reason.'</td>
                	</tr>
                ';
            }

            $html .= '</table>';

            return $html;
        });

        $datatables->editColumn('status', function ($index) {
            $html = '';

            // UPDATE `todo` SET `status`= 'PENDING' WHERE `status` = '';
            // UPDATE `todo` SET `status`= 'PENDING' WHERE `status` = 'pending';
            // UPDATE `todo` SET `status`= 'SUCCESS' WHERE `status` = 'sukses';
            // UPDATE `todo` SET `status`= 'FAILED' WHERE `status` = 'gagal';
            // UPDATE `todo` SET `status`= 'RESCHEDULE' WHERE `status` = 'rechedule';

            if($index->status == 'PENDING')
            {
                $html .= 'Pending';

                if(Auth::user()->can('status-todo') && ($this->usergrant($index->sales_id, 'allSales-todo') || $this->levelgrant($index->sales_id)) )
                {
                    $html .= '
                        <button class="btn btn-xs btn-primary status-todo" data-toggle="modal" data-target="#status-todo" data-id="'.$index->id.'">Update Status</button>
                    ';
                }
                    
            }

            if($index->status == 'SUCCESS')
            {
                $html .= 'Success';
            }

            if($index->status == 'FAILED')
            {
            	$html .= 'Failed';
            }

            if($index->status == 'RESCHEDULE')
            {
            	$html .= 'Reschdule';
            }

            if($index->status != 'PENDING' && Auth::user()->can('undo-todo') && ($this->usergrant($index->sales_id, 'allSales-todo') || $this->levelgrant($index->sales_id)) )
            {
            	$html .= '
                    <button class="btn btn-xs btn-default undo-todo" data-toggle="modal" data-target="#undo-todo" data-id="'.$index->id.'"><i class="fa fa-undo"></i></button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('date_todo', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->date_todo));

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
    	$company = Company::all();

        return view('backend.todo.create', compact('company'));
    }

    public function store(Request $request)
    {

        $message = [
			'company.required' => 'This field required.',
			'event.required' => 'This field required.',
			'date.required' => 'This field required.',
			'date.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
			'company' => 'required',
			'event' => 'required',
			'date' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Todo;

        $index->sales_id   = Auth::id();
        $index->company    = $request->company;
		$index->company_id = $request->company_id;
		$index->brand      = $request->brand;
		$index->event      = $request->event;
		$index->date_todo  = date('Y-m-d H:i:s', strtotime($request->date_todo));
		$index->status     = $request->status ?? 'PENDING';

        $index->save();

        return redirect()->route('backend.todo')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $index = Todo::find($id);

        if(!$this->usergrant($index->sales_id, 'allSales-todo') || !$this->levelgrant($index->sales_id))
        {
            return redirect()->route('backend.todo')->with('failed', 'Access Denied');
        }

        $company = Company::all();

        return view('backend.todo.edit')->with(compact('index', 'company'));
    }

    public function update($id, Request $request)
    {
        $index = Todo::find($id);

        if(!$this->usergrant($index->sales_id, 'allSales-todo') || !$this->levelgrant($index->sales_id))
        {
            return redirect()->route('backend.todo')->with('failed', 'Access Denied');
        }

        $message = [
			'company.required' => 'This field required.',
			'event.required' => 'This field required.',
			'date.required' => 'This field required.',
			'date.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
			'company' => 'required',
			'event' => 'required',
			'date' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

		$index->company    = $request->company;
        $index->company_id = $request->company_id;
		$index->brand      = $request->brand;
		$index->event      = $request->event;
		$index->date_todo  = date('Y-m-d H:i:s', strtotime($request->date_todo));

        $index->save();

        return redirect()->route('backend.todo')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Todo::find($request->id);

        if(!$this->usergrant($index->sales_id, 'allSales-todo') || !$this->levelgrant($index->sales_id))
        {
            return redirect()->route('backend.todo')->with('failed', 'Access Denied');
        }

        Todo::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if(is_array($request->id))
        {
            foreach ($request->id as $list) {

                $index = Todo::find($list);

                if($this->usergrant($index->sales_id, 'allSales-todo') || $this->levelgrant($index->sales_id))
                {
                    $id[] = $list; 
                }
            }

            if ($request->action == 'delete' && Auth::user()->can('delete-todo')) {
                Todo::destroy($id);
                return redirect()->back()->with('success', 'Data Has Been Deleted');
            }
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function status(Request $request)
    {
        $index = Todo::find($request->id);

        if(!$this->usergrant($index->sales_id, 'allSales-todo') || !$this->levelgrant($index->sales_id))
        {
            return redirect()->route('backend.todo')->with('failed', 'Access Denied');
        }

        $message = [
            'status.required' => 'This field required.',
            'reason.required_if' => 'This field required if Failed or Reschedule.',
            'next.required_if' => 'This field required if Success.',
			'reschedule_date.required_if' => 'This field required if Reschedule.',
			'reschedule_date.date' => 'Date format only.',
			'next_date.required_if' => 'This field required if Success.',
			'next_date.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'status' => 'required',
			'reason' => 'required_if:status,FAILED,RESCHEDULE',
			'next' => 'required_if:status,SUCCESS',
			'reschedule_date' => 'required_if:status,RESCHEDULE|date|nullable',
			'next_date' => 'required_if:status,SUCCESS|date|nullable',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('status-todo-error', '');
        }

        $index->status = $request->status;
		$index->reason = $request->reason;
		$index->next = $request->next;
		$index->reschedule_date = date('Y-m-d H:i:s', strtotime($request->reschedule_date));
		$index->next_date = date('Y-m-d', strtotime($request->next_date));

        $index->save();

        if($request->status == 'RESCHEDULE')
        {
        	$new            = new Todo;
			$new->sales_id  = $index->sales_id;
			$new->company   = $index->company;
			$new->brand     = $index->brand;
			$new->date_todo = date('Y-m-d H:i:s', strtotime($request->reschedule_date));
            $new->event     = $index->event;
			$new->status    = 'PENDING';
			$new->save();
        }
        

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function undo(Request $request)
    {
        $index = Todo::find($request->id);

        if(!$this->usergrant($index->sales_id, 'allSales-todo') || !$this->levelgrant($index->sales_id))
        {
            return redirect()->route('backend.todo')->with('failed', 'Access Denied');
        }

        $index->status          = 'PENDING';
        $index->reason          = NULL;
        $index->next            = NULL;
        $index->reschedule_date = NULL;
        $index->next_date       = NULL;

        $index->save();

        return redirect()->route('backend.todo')->with('success', 'Data Has Been Updated');
    }

    public function calendar(Request $request)
    {
        $index = Todo::all();

        $sales = Todo::join('users as sales', 'sales.id', 'todo.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allSales-todo'))
        {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();


        $company = Todo::join('company', 'company.id', 'todo.company_id')
            ->select('company.name', 'company.id')
            ->orderBy('company.name', 'ASC')->distinct()->get();

    	return view('backend.todo.calendar')->with(compact('index', 'sales', 'company', 'request'));
    }

    public function ajaxCalendar(Request $request)
    {
        $f_sales  = $this->filter($request->f_sales);
        $f_company = $this->filter($request->f_company);

        $index = Todo::whereBetween('date_todo', [$request->start, $request->end]);

        if($f_sales == 'staff')
        {
            $index->whereIn('todo.sales_id', Auth::user()->staff());
        }
        else if($f_sales != '')
        {
            $index->where('todo.sales_id', $f_sales);
        }


        if($f_company != '')
        {
            $index->where('todo.company_id', $f_company);
        }

        $index = $index->get();

        $event = '';
        $status = ['' => 'Pending', 'PENDING' => 'Pending', 'SUCCESS' => 'Success', 'FAILED' => 'Failed', 'RESCHEDULE' => 'Reschedule'];

        // return $status;

        foreach ($index as $list) {
            $event [] = [
                "title"      => '['.substr($list->sales->fullname, 0, 1) .'] '. $list->event,
                "sales_name" => $list->sales->fullname,
                "event"      => $list->event,
                "date"       => date('d F Y', strtotime($list->date_todo)),
                "company"    => $list->company,
                "brand"      => $list->brand,
                "status"     => $status[$list->status],
                "start"      => date('Y-m-d H:i', strtotime($list->date_todo)),
                "end"        => date('Y-m-d 23:59', strtotime($list->date_todo)),
                "color"      => $this->strtocolor($list->sales->fullname, 25),
                "textColor"  => $this->strtocolor($list->sales->fullname, 100),
            ];
        }

        return $event;
    }

    public function dashboard(Request $request)
    {
        $year = Todo::select(DB::raw('YEAR(date_todo) as year'))->orderBy('date_todo', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Todo::join('users as sales', 'sales.id', 'todo.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allSales-todo'))
        {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        $company = Todo::join('company', 'company.id', 'todo.company_id')
            ->select('company.name', 'company.id')
            ->orderBy('company.name', 'ASC')->distinct()->get();

        $f_year  = $this->filter($request->f_year, date('Y'));

        return view('backend.todo.dashboard')->with(compact('year', 'month', 'sales', 'company', 'request', 'f_year'));
    }

    public function ajaxSales(Request $request)
    {
        $f_year    = $this->filter($request->f_year, date('Y'));
        $f_month   = $this->filter($request->f_month, date('n'));
        $f_company = $this->filter($request->f_company);

        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $index = Todo::join('users', 'users.id', 'todo.sales_id')
            ->select('todo.sales_id', 'users.fullname', DB::raw('COUNT(todo.id) AS count_todo'), DB::raw('COUNT(DISTINCT todo.company_id) as count_company'));

        if($f_year)
        {
            $index->whereYear('todo.date_todo', $f_year);
        }

        if($f_month)
        {
            $index->whereMonth('todo.date_todo', $f_month);
        }

        if($f_company)
        {
            $index->whereMonth('todo.company_id', $f_company);
        }

        if(!in_array(Auth::user()->position, explode(', ', $super_admin_position->value)))
        {
            $index->whereNotIn('users.position', explode(', ', $super_admin_position->value));
        }

        if(!in_array(Auth::id(), explode(', ', $super_admin_user->value)))
        {
            $index->whereNotIn('users.id', explode(', ', $super_admin_user->value));
        }


        $index =  $index->groupBy('todo.sales_id')->get();

        $countTodo = $countCompany = 0;
        foreach ($index as $list) {

                $data[] = [
                    "id"           => $list->sales_id,
                    "fullname"     => $list->fullname,
                    "countTodo"    => number_format($list->count_todo),
                    "countCompany" => number_format($list->count_company),
                ];

                $countTodo    += $list->countTodo;
                $countCompany += $list->countCompany;
            }

        return compact('data', 'countTodo', 'countCompany');
    }

    public function datatablesDetailSales(Request $request)
    {
        $f_year    = $this->filter($request->f_year, date('Y'));
        $f_month   = $this->filter($request->f_month, date('n'));
        $f_company = $this->filter($request->f_company);

        $index = Todo::select(
                'todo.id',
                'todo.company as prosposal_compacy',
                'todo.company_id',
                'company.name as name_company',
                'todo.event',
                'todo.date_todo'
            )
            ->leftJoin('company', 'todo.company_id', 'company.id')
            ->where('todo.sales_id', $request->sales_id);

        if($f_year != '')
        {
            $index->whereYear('todo.date_todo', $f_year);
        }

        if($f_month != '')
        {
            $index->whereMonth('todo.date_todo', $f_month);
        }

        if($f_company)
        {
            $index->whereMonth('todo.company_id', $f_company);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('date_todo', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->date_todo));

            return $html;
        });

        $datatables->editColumn('name_company', function ($index) {
            if($index->company_id == 0)
            {
                $html = '(Prosposal Compacy) '. $index->prosposal_compacy;
            }
            else
            {
                $html = $index->name_company;
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }
}
