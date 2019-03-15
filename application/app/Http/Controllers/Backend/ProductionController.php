<?php


namespace App\Http\Controllers\Backend;

use App\Models\Production;
use App\Models\Spk;
use App\Models\Division;
use App\Models\User;

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

class ProductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();
        $yeard = Production::select(DB::raw('YEAR(deadline) as year'))->orderBy('deadline', 'ASC')->distinct()->get();
        $division = Division::all();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.first_name', 'sales.last_name', 'sales.id')
            ->orderBy('sales.first_name', 'ASC')->distinct()->get();

        return view('backend.production.index')->with(compact('sales', 'month', 'year', 'yeard', 'division', 'request'));
    }

    public function datatables(Request $request)
    {
    	$f_year     = $this->filter($request->f_year);
        $f_month    = $this->filter($request->f_month);
        $f_yeard    = $this->filter($request->f_yeard, date('Y'));
        $f_monthd   = $this->filter($request->f_monthd, date('n'));
        $f_sales    = $this->filter($request->f_sales);
        $f_division = $this->filter($request->f_division, Auth::user()->division_id);
        $f_source   = $this->filter($request->f_source);
        $f_finish   = $this->filter($request->f_finish);
        $search     = $this->filter($request->search);


    	$index = Production::join('spk', function ($join) {
                $join->on('spk.id', '=', 'productions.spk_id')
                     ->whereNull('spk.deleted_at');
            })
            ->join('users as sales', function ($join) {
                $join->on('sales.id', '=', 'spk.sales_id')
                     ->whereNull('sales.deleted_at');
            })
            ->select(
                'productions.*',
                'spk.no_spk',
                'spk.name as spk_name',
                'spk.main_division_id',
                'spk.date_spk',
                'sales.first_name',
                'sales.last_name'
            )
            ->orderBy('productions.id', 'DESC');

        if($search != '')
        {
            $index->where('spk.no_spk', 'like', '%'.$search.'%')
                ->orWhere('spk.name', 'like', '%'.$search.'%')
                ->orWhere('productions.name', 'like', '%'.$search.'%');
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('spk.date_spk', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('spk.date_spk', $f_year);
            }

            if($f_monthd != '')
            {
                $index->whereMonth('productions.deadline', $f_monthd);
            }

            if($f_yeard != '')
            {
                $index->whereYear('productions.deadline', $f_yeard);
            }

            if($f_sales != '')
            {
                $index->where('sales_id', $f_sales);
            }

            if($f_division != '')
            {
                $index->where('productions.division_id', $f_division);
            }

            if($f_source != '')
            {
                $index->where('productions.source', $f_source);
            }

            if($f_finish != '' && $f_finish == 0)
            {
                $index->whereColumn('productions.quantity', '>', 'productions.count_finish');
            }
            else if($f_finish == 1)
            {
                $index->whereColumn('productions.quantity', '<=', 'productions.count_finish');
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('pdf-production', $index))
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-production" data-toggle="modal" data-target="#pdf-production" data-id="'.$index->id.'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button> <br/>
                ';
            }

            if(Auth::user()->can('complete-production', $index))
            {
               $html .= '<button class="btn btn-xs btn-success complete-production" data-toggle="modal" data-target="#complete-production" data-id="'.$index->id.'"><i class="fa fa-flag-checkered" aria-hidden="true"></i> Finish</button>';
            }
            return $html;
        });

        $datatables->editColumn('no_spk', function ($index) {
            $html = '<b>Name Project</b> : ' . $index->spk->name . '<br/>';
            $html .= '<b>Main Division</b> : ' . $index->spk->divisions->name . '<br/>';
            $html .= '<b>Sales</b> : ' . $index->spk->sales->fullname . '<br/>';
            $html .= '<b>No SPK</b> : ' . $index->spk->no_spk . '<br/>';
            $html .= '<b>Date</b> : ' . $index->spk->date_spk_readable . '<br/>';

            return $html;
        });

        $datatables->editColumn('name', function ($index) {
            $html = '<b>Name</b> : ' . $index->name . '<br/>';
            $html .= '<b>Division</b> : ' . $index->divisions->name . '<br/>';
            $html .= '<b>Source</b> : ' . $index->source . '<br/>';
            $html .= '<b>Deadline</b> : ' . $index->deadline_readable . '<br/>';
            $html .= '<b>Count Finish</b> : ' . number_format($index->count_finish) . '<br/>';
            $html .= '<b>Remaining</b> : ' . number_format($index->quantity - $index->count_finish) . '';

            if($index->datetime_finish)
            {
            	$html .= '<br/><b>Production Finish At</b> : ' . date('d-m-Y', strtotime($index->datetime_finish));
            }
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if (Auth::user()->can('check-production', $index)) {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function complete(Request $request)
    {
    	$index = Production::find($request->id);

        if (!Auth::user()->can('complete-production', $index)) {
            return redirect()->route('backend.production')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'count_finish' => 'required|integer|min:1|max:'.($index->quantity - $index->count_finish),
        ]);

		if($validator->fails())
		{
			return redirect()->back()->withErrors($validator)->withInput()->with('complete-production-error', 'Something Errors');;
		}

        saveArchives($index, Auth::id(), 'Add production finish', $request->except('_token'));

        $quantity_finish = $index->count_finish + $request->count_finish;

		$index->count_finish += $request->count_finish;

		if($quantity_finish >= $index->quantity)
		{
			$index->datetime_finish = date('Y-m-d H:i:s');

            $html = '
                '. $index->spk->no_spk .' Production has been finish
            ';
		}
        else
        {
            $html = '
                '. $index->spk->no_spk .', Detail : '.$index->name.', Quantity : '.$request->count_finish.'
            ';
        }

        $index->spk->sales->notify(new Notif(Auth::user()->nickname, $html, route('backend.spk.edit', $index->spk)));

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Updated');
    }
    
    public function action(Request $request)
    {
        if ($request->action == 'complete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('complete-production', Production::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = Production::whereIn('id', $id)->get();

            saveMultipleArchives(Production::class, $index, Auth::id(), "all production finish");

            foreach ($index as $list) {
                if($list->datetime_finish == null)
                {
                    DB::table('productions')->where('id', $list->id)->update(['count_finish' => $list->quantity, 'datetime_finish' => date('Y-m-d H:i:s')]);
                }
            }

            return redirect()->back()->with('success', 'Data Selected Has Been Updated');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function history(Request $request)
    {
    	$index = Production::find($request->id);

        $data = [];

        foreach ($index->archives as $list) {
            $data[] = [
                "action" => $list->action_data,
                "user_name" => $list->users->fullname,
                "quantity" => $list->insert_data->quantity ?? 0,
                "count_finish" => $list->insert_data->count_finish ?? 0,
                "count_repair" => $list->insert_data->repair ?? 0,
                "created_at" => $list->created_at,
            ];
        }

		return $data;
    }

    public function pdf(Request $request)
    {
        $production = Production::find($request->id);
        $index      = Spk::find($production->spk_id);

        if (!Auth::user()->can('pdf-production', $production)) {
            return redirect()->route('backend.production')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'size' => 'required',
            'orientation' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-production-error', 'Something Errors');
        }


        $pdf = PDF::loadView('backend.production.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream($index->no_spk.'_'.date('Y-m-d').'_production.pdf');
    }

    public function calendar(Request $request)
    {
        $division = Division::all();

        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.first_name', 'sales.last', 'sales.id')
            ->orderBy('sales.first_name', 'ASC')->distinct()->get();

    	return view('backend.production.calendar')->with(compact('sales', 'division', 'request'));
    }

    public function ajaxCalendar(Request $request)
    {
        $f_sales   = $this->filter($request->f_sales);
        $f_maindiv = $this->filter($request->f_maindiv);
        $f_division  = $this->filter($request->f_division);

        $index = Production::join('spk', 'production.spk_id', '=', 'spk.id')
            ->join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('productions.*', 'sales.first_name as sales_name')
            ->where(function ($query) use ($request) {
                $query->whereBetween('productions.created_at', [$request->start, $request->end])
                    ->orwhereBetween('productions.deadline', [$request->start, $request->end]);
            });

        if($f_sales != '')
        {
            $index->where('spk.sales_id', $f_sales);
        }

        if($f_maindiv != '')
        {
            $index->where('spk.main_division_id', $f_maindiv);
        }

        if($f_division != '')
        {
            $index->where('productions.division_id', $f_division);
        }

        $index = $index->get();

        $event = '';

        foreach ($index as $list) {
            $status = $list->datetime_finish ? 'Finish : ' . date('d-m-Y H:i:s', strtotime($list->datetime_finish)) : $list->count_finish .'finish of '. $list->quantity;

            $spk = Spk::find($list->spk_id);

            $event [] = [
                "title"           => '['.$list->divisions->name. ' - ' .$list->source.'] '. $list->name,
                "production_name" => $list->name,
                "description"     => $list->detail,
                "main_division"   => $list->spk->divisions->name,
                "prod_division"   => $list->divisions->name,
                "spk"             => $list->spk->no_spk ?? '',
                "spk_name"        => $list->spk->name ?? '',
                "sales_name"      => $list->sales->fullname,
                "start_date"      => date('d-m-Y H:i:s', strtotime($list->created_at)),
                "deadline"        => date('d-m-Y H:i:s', strtotime($list->deadline)),
                "status"          => $status,
                "start"           => date('Y-m-d H:i', strtotime($list->created_at)),
                "end"             => date('Y-m-d 23:59', strtotime($list->deadline)),
                "color"           => $this->strtocolor($list->divisions->name, 25),
                "textColor"       => $this->strtocolor($list->divisions->name, 100),
            ];
        }

        return $event;
    }
}
