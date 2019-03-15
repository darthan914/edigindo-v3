<?php

namespace App\Http\Controllers\Backend;

use App\Models\Target;
use App\Models\TargetSales;
use App\User;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use App\Http\Controllers\Controller;

use File;
use Excel;
use PDF;
use Validator;
use Yajra\Datatables\Facades\Datatables;

class TargetController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	return view('backend.target.index', compact('request'));
    }

    public function datatables(Request $request)
    {
    	$index = Target::orderBy('year', 'DESC')->get();

    	$datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $html .= '
                <a href="' . route('backend.target.edit', $index) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a><br/>
            ';

            if (Auth::user()->can('delete-target', $index)) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-target" data-toggle="modal" data-target="#delete-target" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button><br/>
                ';
            }

            if (Auth::user()->can('dashboard-target', $index)) {
                $html .= '
                    <a href="' . route('backend.target.dashboard', $index) . '" class="btn btn-xs btn-primary"><i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard</a><br/>
                ';
            }

            return $html;
        });

        $datatables->editColumn('value', function($index) {
            return 'Rp. ' . number_format($index->value);
        });

        $datatables->addColumn('check', function ($index) {
            $html = '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create(Request $request)
    {
    	$sales = User::where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('id', getConfigValue('sales_user', true));
                })
			->where('active', 1);

        if(!Auth::user()->can('full-user'))
        {
            $sales->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $sales = $sales->get();

    	return view('backend.target.create', compact('request', 'sales'));
    }

    public function store(Request $request)
    {
        $deleted = Target::onlyTrashed()->where('year', $request->year)->get();

        if($deleted)
        {
            Target::onlyTrashed()->where('year', $request->year)->forceDelete();
        }

    	$validator = Validator::make($request->all(), [
            'year'        => 'required|unique:targets,year',
            'value'       => 'required',
            'sales_value' => 'required_with:sales_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Target;

        $index->year  = $request->year;
        $index->value = $request->value;
        $index->note  = $request->note;

        $index->save();

        saveArchives($index, Auth::id(), 'create target', $request->except(['_token']));

        $datetime = date('Y-m-d H:i:s');

        if(is_array($request->sales_id))
        {
            foreach ($request->sales_id as $list) {
                DB::table('target_sales')->insert(
                    [   'target_id'    => $index->id,
                        'sales_id'     => $list,
                        'value'        => $request->sales_value,
                        'less_target'  => $request->less_target,
                        'reach_target' => $request->reach_target,
                        'created_at'   => $datetime,
                        'updated_at'   => $datetime,
                    ]
                );
            }
        }
        $target_sales = TargetSales::where('target_id', $index->id)->whereIn('sales_id', $request->sales_id)->get();

        saveMultipleArchives(TargetSales::class, $target_sales, Auth::id(), "create target", $request->except(['_token']));

        return redirect()->route('backend.target')->with('success', 'Data Has Been Added');
    }

    public function edit(Target $index, Request $request)
    {
        $exclude = [];
        foreach ($index->target_sales as $list) {
            $exclude[] = [$list->sales_id];
        }

    	$sales = User::where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('id', getConfigValue('sales_user', true));
                })
            ->where('active', 1)
            ->whereNotIn('id', $exclude);

        if(!Auth::user()->can('full-user'))
        {
            $sales->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $sales = $sales->get();

        return view('backend.target.edit', compact('index', 'request', 'sales'));
    }

    public function update(Target $index, Request $request)
    {
        $deleted = Target::onlyTrashed()->where('year', $request->year)->get();

        if($deleted)
        {
            Target::onlyTrashed()->where('year', $request->year)->forceDelete();
        }

    	$validator = Validator::make($request->all(), [
            'year'        => 'required|unique:targets,year,'.$index->id,
            'value'       => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        saveArchives($index, Auth::id(), 'create target', $request->except(['_token']));

        $index->year  = $request->year;
        $index->value = $request->value;
        $index->note  = $request->note;

        $index->save();

        return redirect()->route('backend.target.edit', $index)->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
    	$index = Target::find($request->id);

        saveArchives($index, Auth::id(), "Delete target");
        Target::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
    	if ($request->action == 'delete' && is_array($request->id)) {

            $index = Target::whereIn('id', $request->id)->get();

            saveMultipleArchives(Target::class, $index, Auth::id(), "delete target");

            Target::destroy($request->id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function datatablesDetail(Target $index, Request $request)
    {
        $datatables = Datatables::of($index->target_sales);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('update-target')) {
                $html .= '
                    <button class="btn btn-xs btn-warning edit-detail"
                    data-id="' . $index->id . '"
                    data-value="' . $index->value . '"
                    data-less_target="' . $index->less_target . '"
                    data-reach_target="' . $index->reach_target . '"
                    data-toggle="modal" data-target="#edit-detail"><i class="fa fa-edit"></i> Edit</button><br/>
                ';
                $html .= '
                    <button class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="' . $index->id . '"><i class="fa fa-trash"></i> Delete</button><br/>
                ';

            }

            return $html;
        });

        $datatables->editColumn('sales_id', function($index) {
            return $index->sales->fullname;
        });

        $datatables->editColumn('value', function($index) {
            return 'Rp. ' . number_format($index->value);
        });


        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check-detail" value="' . $index->id . '" name="id[]" form="action-detail">
            ';
            return $html;
        });

        $datatables = $datatables->make(true);

        return $datatables;
    }

    public function storeDetail(Request $request)
    {
        $index = Target::find($request->target_id);

        if (!Auth::user()->can('update-target')) {
            return redirect()->route('backend.target')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'sales_id'     => 'required',
            'value'        => 'required|numeric',
            'less_target'  => 'required',
            'reach_target' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('create-detail-error', 'Something Errors');
        }

        $datetime = date('Y-m-d H:i:s');

        if(is_array($request->sales_id))
        {
            foreach ($request->sales_id as $list) {
                DB::table('target_sales')->insert(
                    [   'target_id'    => $index->id,
                        'sales_id'     => $list,
                        'value'        => $request->value,
                        'less_target'  => $request->less_target,
                        'reach_target' => $request->reach_target,
                        'created_at'   => $datetime,
                        'updated_at'   => $datetime,
                    ]
                );
            }
        }
        $target_sales = TargetSales::where('target_id', $index->id)->whereIn('sales_id', $request->sales_id)->get();

        saveMultipleArchives(TargetSales::class, $target_sales, Auth::id(), "create target sales", $request->except(['_token']));

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updateDetail(Request $request)
    {
        $index = TargetSales::find($request->id);

        if (!Auth::user()->can('update-target')) {
            return redirect()->route('backend.target')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'value'        => 'required|numeric',
            'less_target'  => 'required',
            'reach_target' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit-detail-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'edit target sales', $request->except(['_token']));

        $index->value        = $request->value;
        $index->less_target  = $request->less_target;
        $index->reach_target = $request->reach_target;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deleteDetail(Request $request)
    {
        $index = TargetSales::find($request->id);

        if (!Auth::user()->can('update-target')) {
            return redirect()->route('backend.target')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete target sales');

        TargetSales::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionDetail(Request $request)
    {

        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('update-target'))
                {
                    $id[] = $list;
                }
            }

            $index = TargetSales::whereIn('id', $id)->get();

            saveMultipleArchives(TargetSales::class, $index, Auth::id(), "delete target sales");

            TargetSales::destroy($id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
    }

    public function dashboard(Target $index, Request $request)
    {
        $master = User::withStatisticSpk('YEAR(spk.date_spk) = ' . $index->year)->get();

        return view('backend.target.dashboard', compact('request', 'index', 'master'));
    }

    public function datatablesDashboard(Target $index, Request $request)
    {
        $sales = User::where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('users.id', getConfigValue('sales_user', true));
                })
            ->where('active', 1)
            ->where('count_spk', '>', 0)
            ->withStatisticSpk('YEAR(spk.date_spk) = ' . $index->year)
            ->leftJoin('target_sales', function ($join) use ($index) {
                $join->on('users.id', '=', 'target_sales.sales_id')
                ->where('target_sales.target_id', $index->id)
                ->whereNull('target_sales.deleted_at');
            });

        if(!Auth::user()->can('full-user'))
        {
            $sales->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $sales = $sales->get();

        $datatables = Datatables::of($sales);

        $datatables->editColumn('first_name', function($index) {
            return $index->fullname;
        });

        $datatables->editColumn('total_profit', function($index) {
            return 'Rp. ' . number_format($index->total_profit);
        });

        $datatables->editColumn('value', function ($index) {
            return 'Rp. ' . number_format($index->value);
        });

        $datatables->addColumn('total_to_reach_target', function ($index) {
            return 'Rp. ' . number_format(max($index->value - $index->total_profit, 0));
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }
}
