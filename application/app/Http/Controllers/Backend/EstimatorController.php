<?php

namespace App\Http\Controllers\Backend;

use App\Models\Estimator;
use App\Models\EstimatorDetail;
use App\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Notifications\Notif;

use Validator;
use Datatables;
use File;

class EstimatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
    	$sales = Estimator::join('users as sales', 'sales.id', '=', 'estimators.sales_id')
            ->select('sales.id', 'sales.first_name', 'sales.last_name')
            ->orderBy('sales.first_name', 'ASC')->distinct();

        if (!Auth::user()->can('all-user') && !in_array(Auth::id(), getConfigValue('estimator_user', true)) && !in_array(Auth::user()->position_id, getConfigValue('estimator_position', true))) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        $estimator = Estimator::join('users as estimator', 'estimator.id', 'estimators.user_estimator_id')
            ->select('estimator.id', 'estimator.first_name', 'estimator.last_name')
            ->orderBy('estimator.first_name', 'ASC')->distinct();

        if (!Auth::user()->can('all-user') && !in_array(Auth::id(), getConfigValue('sales_user', true)) && !in_array(Auth::user()->position_id, getConfigValue('sales_position', true))) {
            $estimator->whereIn('user_estimator_id', Auth::user()->staff());
        }

        $estimator = $estimator->get();

        return view('backend.estimator.index')->with(compact('request', 'sales', 'estimator'));
    }

    public function datatables(Request $request)
    {
        $f_sales        = $this->filter($request->f_sales, (in_array(Auth::id(), getConfigValue('sales_user')) || in_array(Auth::user()->position_id, getConfigValue('sales_position')) ? Auth::id() : ''));
        $f_estimator    = $this->filter($request->f_estimator, (in_array(Auth::id(), getConfigValue('sales_user')) || in_array(Auth::user()->position_id, getConfigValue('sales_position')) ? Auth::id() : ''));
        $f_price        = $this->filter($request->f_price);
    	$search         = $this->filter($request->search);

        $index = Estimator::withStatisticDetail()->orderBy('id', 'DESC');

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('estimators.no_estimator', 'like', '%'.$search.'%')
                    ->orWhere('estimators.name', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if($f_sales == 'staff')
            {
                $index->whereIn('estimators.sales_id', Auth::user()->staff());
            }
            else if($f_sales != '')
            {
                $index->where('estimators.sales_id', $f_sales);
            }

            if($f_estimator == 'staff')
            {
                $index->whereIn('estimators.user_estimator_id', Auth::user()->staff());
            }
            else if($f_estimator != '')
            {
                $index->where('estimators.user_estimator_id', $f_estimator);
            }

            if($f_price == 'unprice')
            {
                $index->where('count_created', 0);
            }
            else if($f_price == 'price')
            {
                $index->where('count_created', '>', 0);
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('update-estimator', $index)) {
                $html .= '
                    <a href="' . route('backend.estimator.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a><br/>
                ';
            }

            if (Auth::user()->can('createPrice-estimator', $index) && Auth::user()->can('price-estimator')) {
                $html .= '
                    <a href="' . route('backend.estimator.price', ['id' => $index->id]) . '" class="btn btn-xs btn-default"><i class="fa fa-usd"></i> Add Price</a><br/>
                ';
            }

            if (Auth::user()->can('delete-estimator', $index)) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-estimator" data-toggle="modal" data-target="#delete-estimator" data-id="' . $index->id . '"><i class="fa fa-trash"></i> Delete</button><br/>
                ';
            }

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if(Auth::user()->can('check-estimator', $index))
            {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }

            return $html;
        });

        $datatables->addColumn('detail', function ($index) {
            return view('backend.estimator.detail', compact('index'));
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = '<b>No Estimator</b> : ' . $index->no_estimator . '<br/>';
            $html .= '<b>Sales</b> : ' . $index->sales->fullname . '<br/>';
            $html .= '<b>Create At</b> : ' . $index->created_at_readable . '<br/>';
            $html .= '<b>Name Project</b> : ' . $index->name . '<br/>';
            $html .= '<b>Description</b> : ' . $index->description . '<br/>';

            return $html;
        });

        $datatables->editColumn('photo', function ($index) {
            $html = '';
            if($index->photo)
            {
                $html .= '
                    <a href="'.asset($index->photo).'" target="_blank">
                        <span style="display: block;width: 100px; height: 100px; background-image: url(\''.asset($index->photo).'\'); background-size: cover;"></span>
                    </a>
                ';
            }
            return $html;
        });

        $datatables->editColumn('sum_value', function ($index) {
            if($index->user_estimator)
            {
                $html = '<b>Estimator</b> : ' . $index->user_estimator->fullname . '<br/>';
                $html .= '<b>Count Created</b> : ' . $index->count_created . '<br/>';
                $html .= '<b>Sum Value</b> : ' . $index->sum_value . '<br/>';
            }
            else
            {
                $html = 'Waiting for estimator';
            }
            

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
    	$sales    = User::where(function ($query) {
            $query->whereIn('position_id',  getConfigValue('sales_position', true))
                ->orWhereIn('id', getConfigValue('sales_user', true));
            })
        ->where('active', 1);

        if(!Auth::user()->can('full-user'))
        {
            $sales->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $sales = $sales->get();

        $estimator = Estimator::select('no_estimator')
            ->where('no_estimator', 'like', str_pad(Auth::user()->no_ae, 2, '0', STR_PAD_LEFT) . "/%")
            ->orderBy('no_estimator', 'desc');

        $count = $estimator->count();
        $number = $estimator->first();

        if ($count == 0) {
            $numberEstimator = 0;
        } else {
            $numberEstimator = intval(substr($number->no_estimator, -5, 5));
        }

        $no_estimator = str_pad(Auth::user()->no_ae, 2, '0', STR_PAD_LEFT) . "/" . str_pad($numberEstimator + 1, 5, '0', STR_PAD_LEFT);

        return view('backend.estimator.create')->with(compact('sales', 'no_estimator'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_estimator' => 'required|unique:estimators,no_estimator',
            'name'         => 'required',
            'photo'        => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Estimator;

        $index->no_estimator = $request->no_estimator;
        $index->sales_id     = $request->sales_id;
        $index->name         = $request->name;
        $index->description  = $request->description;

        if ($request->hasFile('photo')) {
            $pathSource = 'upload/estimator/';
            $file       = $request->file('photo');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            $file->move($pathSource, $filename);
            
            $index->photo = $pathSource . $filename;
        }

        $estimator_notif = User::where(function ($query) {
                $query->whereIn('position_id', getConfigValue('estimator_position', true))
                ->orWhereIn('id', getConfigValue('estimator_user', true));
            })->where('active', 1)->get();

        $html = '
            New Estimator, No : '.$request->no_estimator.', Project : '.$request->name.'
        ';

        $index->save();

        saveArchives($index, Auth::id(), 'create estimator', $request->except(['_token']));

        foreach ($estimator_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.estimator.price', $index)));
        }

        return redirect()->route('backend.estimator')->with('success', 'Data Has Been Added');
    }

    public function edit(Estimator $index)
    {
        $sales    = User::where(function ($query) {
            $query->whereIn('position_id',  getConfigValue('sales_position', true))
                ->orWhereIn('id', getConfigValue('sales_user', true));
            })
        ->where('active', 1);

        if(!Auth::user()->can('full-user'))
        {
            $sales->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $sales = $sales->get();

        return view('backend.estimator.edit', compact('index', 'sales'))->with('warning', 'After update price will be reset');
    }

    public function update(Estimator $index, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_estimator' => 'required|unique:estimators,no_estimator,'.$index->id,
            'name'         => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        saveArchives($index, Auth::id(), 'update estimator', $request->except(['_token']));

        $index->no_estimator = $request->no_estimator;
        $index->sales_id     = $request->sales_id;
        $index->name         = $request->name;
        $index->description  = $request->description;

        if ($request->hasFile('photo')) {
            $pathSource = 'upload/estimator/';
            $file       = $request->file('photo');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            if($file->move($pathSource, $filename))
            {
                File::delete($index->photo);
                $index->photo = $pathSource . $filename;
            }
        }

        $index->save();

        EstimatorDetail::where('estimator_id', $index->id)->delete();

        $estimator_notif = User::where(function ($query) {
                $query->whereIn('position_id', getConfigValue('estimator_position', true))
                ->orWhereIn('id', getConfigValue('estimator_user', true));
            })->where('active', 1)->get();

        $html = '
            New Estimator, No : '.$request->no_estimator.', Project : '.$request->name.'
        ';

        foreach ($estimator_notif as $list) {
            $list->notify(new Notif(Auth::user()->first_name, $html, route('backend.estimator.price', $index)));
        }

        return redirect()->route('backend.estimator')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Estimator::find($request->id);

        if (!Auth::user()->can('delete-estimator', $index)) {
            return redirect()->route('backend.estimator')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete estimator');

        Estimator::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('delete-estimator', Estimator::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = Estimator::whereIn('id', $id)->get();

            saveMultipleArchives(Estimator::class, $index, Auth::id(), "delete estimator");

            Estimator::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function price(Estimator $index, Request $request)
    {
        return view('backend.estimator.price')->with(compact('request', 'index'));
    }

    public function datatablesPrice(Estimator $index, Request $request)
    {
        $datatables = Datatables::of($index->estimator_details);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('updatePrice-estimator', $index->estimators)) {
                $html .= '
                <button class="btn btn-xs btn-warning editPrice-estimator" data-toggle="modal" data-target="#editPrice-estimator" data-id="' . $index->id . '" data-item="' . $index->item . '" data-value="' . $index->value . '" data-note="' . $index->note . '"><i class="fa fa-edit"></i> Edit</button><br/>
                ';
            }

            if (Auth::user()->can('deletePrice-estimator', $index->estimators)) {
                $html .= '
                    <button class="btn btn-xs btn-danger deletePrice-estimator" data-toggle="modal" data-target="#deletePrice-estimator" data-id="' . $index->id . '"><i class="fa fa-trash"></i> Delete</button><br/>
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

        $datatables->editColumn('value', function ($index) {
            $html = number_format($index->value);
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function storePrice(Estimator $index, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item'  => 'required',
            'value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('storePrice-estimator-error', 'Something Errors');
        }

        $detail = new EstimatorDetail;

        $detail->estimator_id = $request->estimator_id;
        $detail->item         = $request->item;
        $detail->value        = $request->value;
        $detail->note         = $request->note;
        $detail->save();

        saveArchives($detail, Auth::id(), 'create estimator detail', $request->except(['_token']));

        if($index->user_estimator_id == null)
        {
            $index->user_estimator_id  = Auth::id();
            $index->save();
        }

        $html = '
            Your estimator has been update, No : '.$index->no_estimator.', Project : '.$index->name.'
        ';

        User::find($index->sales_id)->notify(new Notif(Auth::user()->first_name, $html, route('backend.estimator', ['s_no_estimator' => $index->no_estimator])));

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function updatePrice(Request $request)
    {
        $index     = EstimatorDetail::find($request->id);
        $estimator = Estimator::find($index->estimator_id);

        if (!Auth::user()->can('updatePrice-estimator', $estimator)) {
            return redirect()->route('backend.estimator')->with('failed', 'Access Denied');
        }


        $validator = Validator::make($request->all(), [
            'item'  => 'required',
            'value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('updatePrice-estimator-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'update estimator detail', $request->except(['_token']));

        $index->item  = $request->item;
        $index->value = $request->value;
        $index->note  = $request->note;
        $index->save();


        $html = '
            Your estimator has been update, No : '.$estimator->no_estimator.', Project : '.$estimator->project.'
        ';

        User::find($estimator->sales_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.estimator.edit', ['s_no_estimator' => $estimator->no_estimator])));

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deletePrice(Request $request)
    {
        $index  = EstimatorDetail::find($request->id);

        if (!Auth::user()->can('deletePrice-estimator', $index->estimators)) {
            return redirect()->route('backend.estimator')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete estimator detail', $request->except(['_token']));

        EstimatorDetail::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionPrice(Request $request)
    {

        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('deletePrice-estimator', Estimator::find(EstimatorDetail::find($list)->estimator_id)))
                {
                    $id[] = $list;
                }
            }

            $index = EstimatorDetail::whereIn('id', $id)->get();

            saveMultipleArchives(EstimatorDetail::class, $index, Auth::id(), "delete estimator");

            EstimatorDetail::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function dashboard(Request $request)
    {
        $year = Estimator::select(DB::raw('YEAR(created_at) as year'))->orderBy('created_at', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $f_year  = $this->filter($request->f_year, date('Y'));

        return view('backend.estimator.dashboard')->with(compact('year', 'month', 'request', 'f_year'));
    }

    public function datatablesDashboard(Request $request)
    {
        $f_year     = $this->filter($request->f_year, date('Y'));
        $f_month    = $this->filter($request->f_month, date('n'));

        $index = Estimator::withStatisticDetail()
            ->select(
                'user_estimator_id', 
                'datetime_estimator', 
                DB::raw('SUM(sum_value) AS sum_value'), 
                DB::raw('SUM(count_created) AS count_created'), 
                DB::raw('SUM(less_than_24_sum_value) AS less_than_24_sum_value'), 
                DB::raw('SUM(less_than_24_count_created) AS less_than_24_count_created'), 
                DB::raw('SUM(more_than_24_sum_value) AS more_than_24_sum_value'), 
                DB::raw('SUM(more_than_24_count_created) AS more_than_24_count_created')
            )
            ->groupBy('estimators.user_estimator_id');

        if($f_year != '')
        {
            $index->whereYear('estimators.created_at', $f_year);
        }

        if($f_month != '')
        {
            $index->whereMonth('estimators.created_at', $f_month);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('estimator_name', function ($index) {
            $html = $index->user_estimator->fullname;

            return $html;
        });

        $datatables->editColumn('sum_value', function ($index) {
            $html = 'Rp.'. number_format($index->sum_value);

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function datatablesDetailEstimator(Request $request)
    {
        $f_year     = $this->filter($request->f_year, date('Y'));
        $f_month    = $this->filter($request->f_month, date('n'));

        $index = Estimator::withStatisticDetail()->select(
                'estimators.id',
                'estimators.no_estimator',
                'estimators.name',
                'estimators.created_at',
                'sum_value',
                'datetime_estimator'
            )
            ->where('estimators.user_estimator_id', $request->user_estimator_id);

        if($f_year != '')
        {
            $index->whereYear('estimators.created_at', $f_year);
        }

        if($f_month != '')
        {
            $index->whereMonth('estimators.created_at', $f_month);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('datetime_estimator', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->datetime_estimator));

            return $html;
        });

        $datatables->editColumn('sum_value', function ($index) {
            $html = 'Rp.'. number_format($index->sum_value);

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('createPrice-estimator', $index) )
            {
                $html .= '
                    <a href="'.route('backend.estimator.price', $index->id).'" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
                ';
            }

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getDetail(Request $request)
    {
        $index = EstimatorDetail::where('estimator_id', $request->id)->get();
        return $index;
    }

    public function getEstimator(Request $request)
    {
        $sales_id = $this->filter($request->sales_id, Auth::id());

        $user = User::where('id', $sales_id)->first();

        $estimator = Estimator::select('no_estimator')
            ->where('no_estimator', 'like', str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/%")
            ->orderBy('no_estimator', 'desc');

        $count = $estimator->count();
        $number = $estimator->first();

        if ($count == 0) {
            $numberEstimator = 0;
        } else {
            $numberEstimator = intval(substr($number->no_estimator, -5, 5));
        }

        return str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/" . str_pad($numberEstimator + 1, 5, '0', STR_PAD_LEFT);
    }
}
