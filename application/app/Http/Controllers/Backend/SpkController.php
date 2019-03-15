<?php

namespace App\Http\Controllers\Backend;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Estimator;
use App\Models\Division;
use App\Models\Pic;
use App\Models\Production;
use App\Models\Spk;
use App\Models\Target;
use App\User;

use App\Notifications\Notif;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use File;
use Excel;
use PDF;
use Validator;
use Yajra\Datatables\Facades\Datatables;

class SpkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.id', 'sales.first_name', 'sales.last_name')
            ->orderBy('sales.first_name', 'ASC')->distinct();

        if (!Auth::user()->can('all-user')) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        return view('backend.spk.index')->with(compact('month', 'year', 'sales', 'request'));
    }

    public function datatables(Request $request)
    {
        $f_year  = $this->filter($request->f_year, date('Y'));
        $f_month = $this->filter($request->f_month);
        $f_sales = $this->filter($request->f_sales, Auth::id());
        $f_done  = $this->filter($request->f_done);
        $search     = $this->filter($request->search);

        $index = Spk::withStatisticProduction()->orderBy('id', 'DESC');

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('spk.no_spk', 'like', '%'.$search.'%')
                    ->orWhere('spk.name', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if ($f_month != '') {
                $index->whereMonth('date_spk', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('date_spk', $f_year);
            }

            if ($f_sales == 'staff') {
                $index->whereIn('sales_id', Auth::user()->staff());
            } else if ($f_sales != '') {
                $index->where('sales_id', $f_sales);
            }

            if ($f_done != '' && $f_done == 'UNFINISH_PROD') {
                $index->whereNull('finish_spk_at')->where(function ($query) {
                    $query->whereColumn('sum_quantity_production', '>', 'sum_quantity_production_finish')->orWhere('count_production', 0);
                });
            } else if ($f_done == 'UNFINISH_SPK') {
                $index->whereNull('finish_spk_at')->whereColumn('sum_quantity_production', '<=', 'sum_quantity_production_finish');
            } else if ($f_done == 'FINISH') {
                $index->whereNotNull('finish_spk_at');
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $html .= '
                <a href="' . route('backend.spk.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a><br/>
            ';

            if (Auth::user()->can('delete-spk', $index)) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-spk" data-toggle="modal" data-target="#delete-spk" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button><br/>
                ';
            }

            if (Auth::user()->can('pdf-spk', $index)) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-spk" data-toggle="modal" data-target="#pdf-spk" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button><br/>
                ';
            }

            if (Auth::user()->can('confirm-spk', $index)) {
                if ($index->datetime_confirm && Auth::user()->can('undo-spk', $index)) {
                    $html .= '
                       <button type="button" class="btn btn-xs btn-dark unconfirm-spk" data-toggle="modal" data-target="#unconfirm-spk"
                           data-id="' . $index->id . '"
                       ><i class="fa fa-times" aria-hidden="true"></i> Unconfirm</button><br/>
                    ';
                } else {
                    $html .= '
                       <button type="button" class="btn btn-xs btn-info confirm-spk" data-toggle="modal" data-target="#confirm-spk"
                           data-id="' . $index->id . '"
                       ><i class="fa fa-check" aria-hidden="true"></i> Confirm</button><br/>
                    ';
                }
            }

            if ($index->finish_spk_at) {

                if (Auth::user()->can('undo-spk', $index)) {
                    $html .= ' <button class="btn btn-xs btn-default undoFinish-spk" data-toggle="modal" data-target="#undoFinish-spk" data-id="' . $index->id . '" title="Undo Finish App\Spk"><i class="fa fa-undo" aria-hidden="true"></i> Undo Finish</button><br/>';
                }

            } else if (Auth::user()->can('finish-spk', $index)) {
                $html .= '<button class="btn btn-xs btn-success finish-spk" data-toggle="modal" data-target="#finish-spk" data-id="' . $index->id . '"><i class="fa fa-flag-checkered" aria-hidden="true"></i> Finish</button><br/>';
            }

            return $html;
        });

        $datatables->editColumn('datetime_confirm', function ($index) {
            $html = '';
            if ($index->datetime_confirm) {
                $html .= '
                    <span class="label label-success">Confirm {$index->datetime_confirm_readable}</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Unconfirm</span>
                ';
            }
            return $html;
        });

        $datatables->addColumn('view', function ($index) {
            return view('backend.spk.view', compact('index'));
        });

        $datatables->editColumn('name', function ($index) {
            $html = '<b>Name Project</b> : ' . $index->name . '<br/>';
            $html .= '<b>Sales</b> : ' . $index->sales->fullname . '<br/>';
            $html .= '<b>No SPK</b> : ' . $index->no_spk . '<br/>';
            $html .= '<b>Date</b> : ' . $index->date_spk_readable . '<br/>';
            if ($index->datetime_confirm) {
                $html .= '<b>Confirm At</b> : ' . $index->datetime_confirm_readable . '<br/>';
            }
            $html .= '<b>Finish</b> : ';

            if ($index->finish_spk_at) {
                $html .= $index->date_spk_readable;

            } else {
                $html .= 'Production Not Finish';
            }

            return $html;
        });

        $datatables->editColumn('total_loss', function ($index) {
            $html = '<b>Modal</b> : Rp. ' . number_format($index->total_hm) . '<br/>';

            if (Auth::user()->can('editHE-spk')) {
                $html .= '<b>Expo</b> : Rp. ' . number_format($index->total_he) . '<br/>';
            }
            $html .= '<b>Sell</b> : Rp. ' . number_format($index->total_hj) . '<br/>';
            $html .= '<b>PPn</b> : Rp. ' . number_format($index->total_ppn) . '<br/>';
            $html .= '<b>Real Omset</b> : Rp. ' . number_format($index->total_real_omset) . '<br/>';
            $html .= '<b>Loss</b> : Rp. ' . number_format($index->total_loss) . '<br/>';

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if (Auth::user()->can('check-spk', $index)) {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $division = Division::all();
        $company  = Company::all();
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

        $brand = $address = $pic = [];

        $date = date('Y-m-d');

        $sales_id = Auth::id();
        $user     = User::where('id', $sales_id)->first();

        $spk = Spk::select('no_spk')
            ->where('no_spk', 'like', str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-%")
            ->orderBy('no_spk', 'desc');

        $count = $spk->count();
        $year  = $spk->first();

        if ($count == 0) {
            $numberSpk = 0;
        } else {
            $numberSpk = intval(substr($year->no_spk, -3, 3));
        }

        $spk = str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-" . str_pad($numberSpk + 1, 3, '0', STR_PAD_LEFT);

        return view('backend.spk.create', compact('division', 'company', 'brand', 'address', 'pic', 'sales', 'spk'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_spk'           => 'required|unique:spk,no_spk',
            'name'             => 'required',
            'company_id'       => 'required',
            'pic_id'           => 'required',
            'address'          => 'required',
            'main_division_id' => 'required',
            'date_spk'         => 'required|date',
            'sales_id'         => 'required',
        ]);

        if ($request->company_id) {
            $validator->after(function ($validator) use ($request) {
                $check = Company::find($request->company_id);
                if ($check->datetime_lock) {
                    $validator->errors()->add('company_id', 'This company not allow add spk');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Spk;

        $index->no_spk           = $request->no_spk;
        $index->name             = $request->name;
        $index->main_division_id = $request->main_division_id;
        $index->company_id       = $request->company_id;
        $index->brand_id         = $request->brand_id;
        $index->address          = $request->address;
        $index->pic_id           = $request->pic_id;
        $index->sales_id         = $request->sales_id;
        $index->additional_phone = $request->additional_phone;
        $index->date_spk         = $request->date_spk;
        $index->ppn              = $request->ppn;
        $index->note             = $request->note;
        $index->do_transaction   = $request->do_transaction;

        $index->save();

        saveArchives($index, Auth::id(), "Create spk", $request->except(['_token']));

        return redirect()->route('backend.spk.edit', ['id' => $index->id])->with('success', 'Data Has Been Added');
    }

    public function edit(Spk $index)
    {
        $index = $index->withStatisticProduction()->where('id', $index->id)->first();
        
        $division = Division::all();
        $company  = Company::all();

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

        $brand    = Brand::where('company_id', $index->company_id)->get();
        $address  = Address::where('company_id', $index->company_id)->get();
        $pic      = Pic::where('company_id', $index->company_id)->get();

        $estimator = Estimator::where('sales_id', $index->sales_id)->get();

        return view('backend.spk.edit')->with(compact('index', 'division', 'company', 'sales', 'brand', 'address', 'pic', 'estimator'));
    }

    public function update(Spk $index, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_spk'           => 'required|unique:spk,no_spk,' . $index->id,
            'name'             => 'required',
            'company_id'       => 'required',
            'pic_id'           => 'required',
            'address'          => 'required',
            'main_division_id' => 'required',
            'sales_id'         => 'required',
            'date_spk'         => 'required|date',
        ]);

        if ($request->company_id) {
            $validator->after(function ($validator) use ($request, $index) {
                $check = Company::find($request->company_id);
                if ($check->lock && $index->company_id != $request->company_id) {
                    $validator->errors()->add('company_id', 'This company not allow add spk');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        saveArchives($index, Auth::id(), "Update spk", $request->except(['_token']));

        $index->no_spk           = $request->no_spk;
        $index->name             = $request->name;
        $index->main_division_id = $request->main_division_id;
        $index->company_id       = $request->company_id;
        $index->brand_id         = $request->brand_id;
        $index->address          = $request->address;
        $index->pic_id           = $request->pic_id;
        $index->sales_id         = $request->sales_id;
        $index->additional_phone = $request->additional_phone;
        $index->date_spk         = $request->date_spk;
        $index->ppn              = $request->ppn;
        $index->note             = $request->note;
        $index->do_transaction   = $request->do_transaction;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Spk::find($request->id);

        if (!Auth::user()->can('delete-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), "Delete spk");
        Spk::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function confirm(Request $request)
    {
        $index = Spk::find($request->id);
        
        if (!Auth::user()->can('confirm-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }
        
        DB::transaction(function () use ($request, $index){
            saveArchives($index, Auth::id(), "confirm spk");

            $index->datetime_confirm = date('Y-m-d H:i:s');
            $index->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function unconfirm(Request $request)
    {
        $index = Spk::find($request->id);
        
        if (!Auth::user()->can('undo-spk', $index) || !Auth::user()->can('confirm-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        DB::transaction(function () use ($request, $index){
            saveArchives($index, Auth::id(), "unconfirm spk");

            $index->datetime_confirm = null;
            $index->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('delete-spk', Spk::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = Spk::whereIn('id', $id)->get();

            saveMultipleArchives(Spk::class, $index, Auth::id(), "delete spk");

            Spk::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function finish(Request $request)
    {
        $index = Spk::find($request->id);

        if (!Auth::user()->can('finish-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'quality' => 'required',
            'comment' => 'required_if:quality,0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('finish-spk-error', '');
        }

        saveArchives($index, Auth::id(), "finish spk", $request->except(['_token']));

        $index->finish_spk_at = date('Y-m-d H:i:s');
        $index->quality       = $request->quality;
        $index->comment       = $request->comment;

        $invoice_notif = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('invoice_position', true))
                ->orWhereIn('id', getConfigValue('invoice_user', true));
        })
            ->get();

        $html = '
            App\Spk Finish, App\Spk : ' . $index->spk . ', Project : ' . $index->name . ' <br/>
        ';

        foreach ($invoice_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.invoice', ['s_spk' => $index->spk])));
        }

        $index->save();

        return redirect()->back()->with('success', 'Data Selected Has Been Updated');
    }

    public function undoFinish(Request $request)
    {
        $index = Spk::find($request->id);

        if (!Auth::user()->can('undo-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), "undo finish spk", $request->except(['_token']));

        $index->finish_spk_at = null;
        $index->quality       = null;
        $index->comment       = null;

        $invoice_notif = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('invoice_position', true))
                ->orWhereIn('id', getConfigValue('invoice_user', true));
        })
            ->get();

        $html = '
            App\Spk is not finish, App\Spk : ' . $index->spk . ', Project : ' . $index->name . ' <br/>
        ';

        foreach ($invoice_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.invoice', ['s_spk' => $index->spk])));
        }

        $index->save();

        return redirect()->back()->with('success', 'Data Selected Has Been Updated');
    }

    public function datatablesDetail(Spk $index, Request $request)
    {
        $datatables = Datatables::of($index->productions);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('update-spk', $index->spk)) {
                $html .= '
                    <button class="btn btn-xs btn-warning edit-detail"
                    data-id="' . $index->id . '"
                    data-name="' . $index->name . '"
                    data-quantity="' . $index->quantity . '"
                    data-hm="' . $index->hm . '"
                    data-he="' . $index->he . '"
                    data-hj="' . $index->hj . '"
                    data-division_id="' . $index->division_id . '"
                    data-source="' . $index->source . '"
                    data-deadline="' . date('d F Y', strtotime($index->deadline)) . '"
                    data-profitable="' . $index->profitable . '"
                    data-toggle="modal" data-target="#edit-detail"><i class="fa fa-edit"></i> Edit</button><br/>
                ';
                $html .= '
                    <button class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="' . $index->id . '"><i class="fa fa-trash"></i> Delete</button><br/>
                ';

                $html .= ' <button class="btn btn-warning btn-xs repair-detail" data-toggle="modal" data-production_id="' . $index->id . '" data-target="#repair-detail"><i class="fa fa-wrench" aria-hidden="true"></i> Repair</button><br/>';
            }

            $html .= ' <button class="btn btn-xs btn-default history-production" data-toggle="modal" data-target="#history-production" data-id="' . $index->id . '"><i class="fa fa-clock-o" aria-hidden="true"></i> History</button><br/>';

            return $html;
        });

        $datatables->editColumn('name', function ($index) {
            $html = $index->name . '<br/>(' .$index->divisions->name . ' | ' . $index->source . ')';
            return $html;
        });

        $datatables->editColumn('hm', function ($index) {
            $html = '<b>Quantity</b> : ' . number_format($index->quantity) . '<br/>';
            $html .= '<b>Modal</b> : Rp. ' . number_format($index->hm) . ' (Rp. '.number_format($index->hm * $index->quantity).')<br/>';

            if (Auth::user()->can('editHE-spk')) {
                $html .= '<b>Expo</b> : Rp. ' . number_format($index->he) . ' (Rp. '.number_format($index->he * $index->quantity).')<br/>';
            }
            $html .= '<b>Sell</b> : Rp. ' . number_format($index->hj) . ' (Rp. '.number_format($index->hj * $index->quantity).') <br/>';
            $html .= '<b>Profitable</b> :';

            if ($index->profitable) {
                $html .= 'Yes';
            } else {
                $html .= 'No';
            }

            return $html;
        });


        $datatables->editColumn('deadline', function ($index) {

            $html = '<b>Deadline</b> : ' . $index->deadline_readable . '<br/>';
            $html .= '<b>Count Finish</b> : ' . number_format($index->count_finish) . '<br/>';
            $html .= '<b>Remaining</b> : ' . number_format($index->quantity - $index->count_finish) . '<br/>';

            


            return $html;
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
        $index = Spk::find($request->spk_id);

        if (!Auth::user()->can('update-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'spk_id'      => 'required|integer',
            'name'        => 'required',
            'detail'      => 'required',
            'quantity'    => 'required|integer',
            'hm'          => 'required',
            'hj'          => 'required|numeric',
            'division_id' => 'required',
            'source'      => 'required',
            'deadline'    => 'required|date',
            'profitable'  => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('create-detail-error', 'Something Errors');
        }

        $index = new Production;

        $index->spk_id      = $request->spk_id;
        $index->name        = $request->name;
        $index->division_id = $request->division_id;
        $index->source      = $request->source;
        $index->deadline    = $request->deadline;
        $index->quantity    = $request->quantity;
        $index->hm          = $request->hm;
        $index->he          = $request->he ?? 0;
        $index->hj          = $request->hj;
        $index->free        = $request->hj == 0 ? 1 : 0;
        $index->profitable  = $request->profitable;
        $index->detail      = $request->detail;

        $production_notif = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('production_position', true))
                ->orWhereIn('id', getConfigValue('production_user', true));
        })
        ->where('division_id', $request->division)->where('active', 1)->get();

        $spk = Spk::withStatisticProduction()->where('id', $request->spk_id)->first();

        $html = '
            New Production, App\Spk : ' . $spk->no_spk . ', Project : ' . $spk->name . ', Detail : ' . $request->name . ', Quantity : ' . $request->quantity . '
        ';

        foreach ($production_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.production', ['f_search_spk' => $spk->no_spk])));
        }

        $index->save();

        saveArchives($index, Auth::id(), 'Create production', $request->except(['_token']));

        $spk->datetime_confirm = ($spk->total_hj >= 100000000 ? 0 : 1);
        $spk->save();

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updateDetail(Request $request)
    {
        $index = Production::find($request->id);

        if (!Auth::user()->can('update-spk', Spk::find($index->spk_id))) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'required',
            'detail'      => 'required',
            'quantity'    => 'required|integer',
            'hm'          => 'nullable|numeric',
            'hj'          => 'nullable|numeric',
            'division_id' => 'required',
            'source'      => 'required',
            'deadline'    => 'required|date',
            'profitable'  => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->hm === '' && Auth::user()->can('editHM-spk')) {
                $validator->errors()->add('hm', 'This field required.');
            }

            if ($request->hj === '' && Auth::user()->can('editHJ-spk')) {
                $validator->errors()->add('hj', 'This field required.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit-detail-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'edit production', $request->except(['_token']));

        $index->name        = $request->name;
        $index->division_id = $request->division_id;
        $index->source      = $request->source;
        $index->deadline    = $request->deadline;
        
        $index->quantity    = $request->quantity;
        if (Auth::user()->can('editHM-spk')) $index->hm = $request->hm;
        if (Auth::user()->can('editHE-spk')) $index->he = $request->he;
        if (Auth::user()->can('editHJ-spk')) $index->hj = $request->hj;
        $index->free        = $index->hj == 0 ? 1 : 0;
        $index->profitable  = $request->profitable;

        $index->detail = $request->detail;


        $production_notif = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('production_position', true))
                ->orWhereIn('id', getConfigValue('production_user', true));
        })
        ->where('division_id', $request->division)->where('active', 1)->get();

        $spk = Spk::withStatisticProduction()->where('id', $index->spk_id)->first();

        $html = '
            Update Production, App\Spk : ' . $spk->no_spk . ', Project : ' . $spk->name . ', Detail : ' . $request->name . ', Quantity : ' . $request->quantity . '
        ';

        foreach ($production_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.production', ['f_search_spk' => $spk->no_spk])));
        }

        $index->save();

        $spk->datetime_confirm = ($spk->total_hj >= 100000000 ? 0 : 1);
        $spk->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deleteDetail(Request $request)
    {
        $index = Production::find($request->id);

        if (!Auth::user()->can('update-spk', Spk::find($index->spk_id))) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete production');

        Production::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionDetail(Request $request)
    {

        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('update-spk', Spk::find(Production::find($list)->spk_id)))
                {
                    $id[] = $list;
                }
            }

            $index = Production::whereIn('id', $id)->get();

            saveMultipleArchives(Production::class, $index, Auth::id(), "delete production");

            Production::destroy($id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
    }

    public function repairDetail(Request $request)
    {
        $index = Production::find($request->production_id);

        if (!Auth::user()->can('update-spk', $index->spk)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'production_id' => 'required|integer',
            'repair'        => 'required|integer|min:1|max:' . $index->count_finish,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('repair-detail-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'repair production', $request->except(['_token']));

        $index->count_finish    -= $request->repair;
        $index->datetime_finish = null;

        $spk = Spk::find($index->spk_id);

        $production_notif = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('production_position', true))
                ->orWhereIn('id', getConfigValue('production_user', true));
        })
        ->where('division_id', $request->division)->where('active', 1)->get();

        $html = '
            Repair Production, App\Spk : ' . $spk->no_spk . ', Project : ' . $spk->name . ', Detail : ' . $index->name . ', Need ' . $request->repair . ' to repair
        ';

        foreach ($production_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.production', ['f_search_spk' => $spk->no_spk])));
        }

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function pdf(Request $request)
    {
        $index = Spk::find($request->id);

        if (!Auth::user()->can('pdf-spk', $index)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
            'type'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-spk-error', 'Something Errors');
        }


        $pdf = PDF::loadView('backend.spk.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream($index->no_spk . '_' . date('Y-m-d') . '_' . $request->type . '.pdf');
    }

    public function dashboard(Request $request)
    {
        $year  = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();
        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return view('backend.spk.dashboard')->with(compact('year', 'month', 'request'));
    }

    public function datatablesSalesDashboard(Request $request)
    {
        $f_year = $this->filter($request->f_year, date('Y'));

        $where_spk = $where_offer = null;
        if($f_year)
        {
            $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
            $where_offer = 'YEAR(offers.date_offer) = ' . $f_year;
        }

        $target = Target::where('year', $f_year)->first();


        $master = User::where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('users.id', getConfigValue('sales_user', true));
                })
            ->leftJoin('target_sales', function ($join) use ($target) {
                $join->on('users.id', '=', 'target_sales.sales_id')
                     ->where('target_sales.target_id', $target->id ?? 0);
            })
            ->where('active', 1)
            ->where('count_spk', '>', 0)
            ->withStatisticSpk($where_spk)
            ->withStatisticOffer($where_offer)
            ->select(
                DB::raw('"" AS id'),
                DB::raw('"Total" AS first_name'),
                DB::raw('"" AS last_name'),
                DB::raw('SUM(count_spk) AS count_spk'),
                DB::raw('SUM(total_offer) AS total_offer'),
                DB::raw('SUM(total_hm) AS total_hm'),
                DB::raw('SUM(total_hj) AS total_hj'),
                DB::raw('SUM(total_real_omset) AS total_real_omset'),
                DB::raw('SUM(total_loss) AS total_loss'),
                DB::raw($target->value ?? 0 . ' AS value'),
                DB::raw('CASE WHEN '.($target->value ?? 0).' > 0 THEN (SUM(total_real_omset) / '.($target->value ?? 1).') * 100 ELSE 0 END AS percent')
            );

        if(!Auth::user()->can('full-user'))
        {
            $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        $index = User::where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('users.id', getConfigValue('sales_user', true));
                })
            ->leftJoin('target_sales', function ($join) use ($target) {
                $join->on('users.id', '=', 'target_sales.sales_id')
                     ->where('target_sales.target_id', $target->id ?? 0);
            })
            ->where('active', 1)
            ->where('count_spk', '>', 0)
            ->withStatisticSpk($where_spk)
            ->withStatisticOffer($where_offer)
            ->select(
                'users.id', 'first_name', 'last_name',
                'count_spk', 'total_offer',
                'total_hm', 'total_hj', 'total_real_omset', 'total_loss',
                'target_sales.value',
                DB::raw('CASE WHEN COALESCE(target_sales.value, 0) > 0 THEN (total_real_omset / target_sales.value) * 100 ELSE 0 END AS percent')
            );

        if(!Auth::user()->can('full-user'))
        {
            $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }
        
        // $index = $index->union($master)->get();
        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if($index->id)
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-info data-spk" data-toggle="modal" data-target="#data-spk" data-id="' . $index->id . '"><i class="fa fa-eye" aria-hidden="true"></i> View More</button><br/>
                ';
            }
            

            return $html;
        });

        $datatables->editColumn('first_name', function ($index) {
            $html = $index->fullname;

            return $html;
        });

        $datatables->editColumn('count_spk', function ($index) {
            $html = number_format($index->count_spk);

            return $html;
        });

        $datatables->editColumn('total_offer', function ($index) {
            $html = 'Rp. ' . number_format($index->total_offer);

            return $html;
        });

        $datatables->editColumn('total_hm', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hm);

            return $html;
        });

        $datatables->editColumn('total_hj', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj);

            return $html;
        });

        $datatables->editColumn('total_real_omset', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset);

            return $html;
        });

        $datatables->editColumn('total_loss', function ($index) {
            $html = 'Rp. ' . number_format($index->total_loss);

            return $html;
        });

        $datatables->editColumn('value', function ($index) {
            $html = 'Rp. ' . number_format($index->value);

            return $html;
        });

        $datatables->editColumn('percent', function ($index) {
            $html = number_format($index->percent, 2) . ' %';

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function datatablesMonthlyDashboard(Request $request)
    {
        $f_type = $this->filter($request->f_type, 'single');
        $f_expo = $this->filter($request->f_expo);

        $f_year        = $this->filter($request->f_year, date('Y'));
        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, 1);
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));

        $start_range = $f_start_year . '-' . sprintf("%02d", $f_start_month) . '-01';
        $end_range   = $f_end_year . '-' . sprintf("%02d", $f_end_month) . '-' . date('t', strtotime($f_end_year . '-' . sprintf("%02d", $f_end_month) . '-01'));

        $where_spk = '1';
        if($f_type == 'single')
        {
            if($f_year)
            {
                $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
            }
        }
        else
        {
            $where_spk = 'spk.date_spk BETWEEN str_to_date("' . $start_range . '", "%Y-%m-%d") AND str_to_date("' . $end_range . '", "%Y-%m-%d")';
        }


        $where_expo = '1';
        if($f_expo == 'nonexpo')
        {
            $where_expo = 'spk.main_division_id NOT IN ('.implode(', ', getConfigValue('division_expo', true)).')';
        }
        else if($f_expo == 'expo')
        {
            $where_expo = 'spk.main_division_id IN ('.implode(', ', getConfigValue('division_expo', true)).')';
        }

        $master = User::select(
                DB::raw('"" AS id'),
                DB::raw('"Total" AS first_name'),
                DB::raw('"" AS last_name'), 
                DB::raw('SUM(statistic_spk.total_hj) AS total_hj'),
                DB::raw('SUM(statistic_spk.total_real_omset) AS total_real_omset')
            )
            ->where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('users.id', getConfigValue('sales_user', true));
                })
            ->where('active', 1)
            ->withStatisticSpk($where_spk .' AND ' . $where_expo)
            ->where('statistic_spk.count_spk', '>', 0);

        foreach (range(1, 12) as $list) {
            $master->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                ->addSelect(
                    DB::raw('SUM(statistic_spk_'.$list.'.total_hj) as total_hj_'.$list),
                    DB::raw('SUM(statistic_spk_'.$list.'.total_real_omset) as total_real_omset_'.$list)
                );
        }

        if(!Auth::user()->can('full-user'))
        {
            $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }

        $index = User::select('users.id', 'users.first_name', 'users.last_name', 'statistic_spk.total_hj', 'statistic_spk.total_real_omset')
            ->where(function ($query) {
                $query->whereIn('position_id',  getConfigValue('sales_position', true))
                    ->orWhereIn('users.id', getConfigValue('sales_user', true));
                })
            ->where('active', 1)
            ->withStatisticSpk($where_spk .' AND ' . $where_expo)
            ->where('statistic_spk.count_spk', '>', 0);

        foreach (range(1, 12) as $list) {
            $index->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                ->addSelect(
                    'statistic_spk_'.$list.'.total_hj as total_hj_'.$list,
                    'statistic_spk_'.$list.'.total_real_omset as total_real_omset_'.$list
                );
        }

        if(!Auth::user()->can('full-user'))
        {
            $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
        }

        // $index = $index->union($master)->get();
        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('first_name', function ($index) {
            $html = $index->fullname;

            return $html;
        });

        $datatables->editColumn('total_hj', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj);

            return $html;
        });

        $datatables->editColumn('total_real_omset', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset);

            return $html;
        });

        $datatables->editColumn('total_hj_1', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_1);

            return $html;
        });

        $datatables->editColumn('total_real_omset_1', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_1);

            return $html;
        });

        $datatables->editColumn('total_hj_2', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_2);

            return $html;
        });

        $datatables->editColumn('total_real_omset_2', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_2);

            return $html;
        });

        $datatables->editColumn('total_hj_3', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_3);

            return $html;
        });

        $datatables->editColumn('total_real_omset_3', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_3);

            return $html;
        });


        $datatables->editColumn('total_hj_4', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_4);

            return $html;
        });

        $datatables->editColumn('total_real_omset_4', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_4);

            return $html;
        });

        $datatables->editColumn('total_hj_5', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_5);

            return $html;
        });

        $datatables->editColumn('total_real_omset_5', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_5);

            return $html;
        });

        $datatables->editColumn('total_hj_6', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_6);

            return $html;
        });

        $datatables->editColumn('total_real_omset_6', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_6);

            return $html;
        });
        $datatables->editColumn('total_hj_7', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_7);

            return $html;
        });

        $datatables->editColumn('total_real_omset_7', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_7);

            return $html;
        });

        $datatables->editColumn('total_hj_8', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_8);

            return $html;
        });

        $datatables->editColumn('total_real_omset_8', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_8);

            return $html;
        });

        $datatables->editColumn('total_hj_9', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_9);

            return $html;
        });

        $datatables->editColumn('total_real_omset_9', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_9);

            return $html;
        });

        $datatables->editColumn('total_hj_10', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_10);

            return $html;
        });

        $datatables->editColumn('total_real_omset_10', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_10);

            return $html;
        });

        $datatables->editColumn('total_hj_11', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_11);

            return $html;
        });

        $datatables->editColumn('total_real_omset_11', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_11);

            return $html;
        });

        $datatables->editColumn('total_hj_12', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj_12);

            return $html;
        });

        $datatables->editColumn('total_real_omset_12', function ($index) {
            $html = 'Rp. ' . number_format($index->total_real_omset_12);

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function datatablesDetailDashboard(Request $request)
    {
        $f_year = $this->filter($request->f_year, date('Y'));
        // $f_month    = $this->filter($request->f_month, date('n'));

        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, date('n'));
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));
        $f_type        = $this->filter($request->f_type);

        $start_range = $f_start_year . '-' . sprintf("%02d", $f_start_month) . '-01';
        $end_range   = $f_end_year . '-' . sprintf("%02d", $f_end_month) . '-' . date('t', strtotime($f_end_year . '-' . sprintf("%02d", $f_end_month) . '-01'));

        $index = Spk::withStatisticProduction()
            ->where('spk.sales_id', $request->id);

        if ($f_type == 'range') {
            $index->whereBetween('spk.date_spk', [$start_range, $end_range]);
        } else {
            if ($f_year != '') {
                $index->whereYear('date_spk', $f_year);
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('date_spk', function ($index) {
            $html = date('d-m-Y', strtotime($index->date_spk));

            return $html;
        });

        $datatables->editColumn('total_hm', function ($index) {
            $html = 'Rp.' . number_format($index->total_hm);

            if (Auth::user()->can('editHE-spk')) {
                $html .= ' (Rp.' . number_format($index->total_he) . ')';
            }

            return $html;
        });

        $datatables->editColumn('total_hj', function ($index) {
            $html = 'Rp.' . number_format($index->total_hj);

            return $html;
        });

        $datatables->editColumn('total_real_omset', function ($index) {
            $html = 'Rp.' . number_format($index->total_real_omset);

            return $html;
        });

        $datatables->editColumn('total_loss', function ($index) {
            $html = 'Rp.' . number_format($index->total_loss);

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $html .= '
                <a href="' . route('backend.spk.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
            ';

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function excel(Request $request)
    {
        $f_year  = $this->filter($request->xls_year, date('Y'));
        $f_month = $this->filter($request->xls_month, date('n'));

        Excel::create('spk-' . $f_year . $f_month . '-' . date('dmYHis'), function ($excel) use ($f_year, $f_month) {

            $excel->sheet('Spk List', function ($sheet) use ($f_year, $f_month) {

                $index = Spk::withStatisticProduction();

                if ($f_month != '') {
                    $index->whereMonth('date_spk', $f_month);
                }

                if ($f_year != '') {
                    $index->whereYear('date_spk', $f_year);
                }

                if (!Auth::user()->can('allSales-spk')) {
                    $index->whereIn('sales_id', Auth::user()->staff());
                }

                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->no_spk,
                        $list->name,
                        $list->divisions->name,
                        $list->companies->name ?? '',
                        $list->brands->name ?? '',
                        $list->pic->fullname ?? '',
                        $list->date_spk,
                        $list->finish_spk_at,
                        $list->total_hm,
                        $list->total_hj,
                        $list->ppn,
                        $list->total_real_omset,
                    ];
                }

                $sheet->setColumnFormat(array(
                    'I' => '0',
                    'J' => '0',
                    'L' => '0',
                ));
                $sheet->fromArray($data);
                $sheet->row(1, array('Spk', 'Name Project', 'Main Division', 'Company', 'Brand', 'Pic', 'Date', 'Project Finish', 'Modal Price', 'Sell Price', 'PPn', 'Real Omset'));
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Data Sales', function ($sheet) use ($f_year) {

                $where_spk = $where_offer = null;
                if($f_year)
                {
                    $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
                    $where_offer = 'YEAR(offers.date_offer) = ' . $f_year;
                }

                $target = Target::where('year', $f_year)->first();

                $index = User::where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->leftJoin('target_sales', function ($join) use ($target) {
                        $join->on('users.id', '=', 'target_sales.sales_id')
                             ->where('target_sales.target_id', $target->id ?? 0);
                    })
                    ->where('active', 1)
                    ->where('count_spk', '>', 0)
                    ->withStatisticSpk($where_spk)
                    ->withStatisticOffer($where_offer)
                    ->select(
                        'users.id', 'first_name', 'last_name',
                        'count_spk', 'total_offer',
                        'total_hm', 'total_hj', 'total_real_omset', 'total_loss',
                        'target_sales.value',
                        DB::raw('CASE WHEN COALESCE(target_sales.value, 0) > 0 THEN (total_real_omset / target_sales.value) * 100 ELSE 0 END AS percent')
                    );

                if(!Auth::user()->can('full-user'))
                {
                    $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }
                
                // $index = $index->union($master)->get();
                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->fullname,
                        $list->total_hj,
                        $list->total_offer,
                        $list->total_real_omset,
                        $list->value,
                        $list->percent,
                    ];
                }

                $sheet->fromArray($data);
                $sheet->setColumnFormat(array(
                    'B' => '0',
                    'C' => '0',
                    'D' => '0',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0%',
                ));
                $sheet->row(1, array('Sales', 'Sell Price', 'Offer Price', 'Real Omset', 'Target', 'Percent'));
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Sell Price Monthly', function ($sheet) use ($f_year) {

                $where_spk = '1';
                if($f_year)
                {
                    $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
                }

                $where_expo = 'spk.main_division_id NOT IN ('.implode(', ', getConfigValue('division_expo', true)).')';

                $master = User::select(
                        DB::raw('"" AS id'),
                        DB::raw('"Total" AS first_name'),
                        DB::raw('"" AS last_name'), 
                        DB::raw('SUM(statistic_spk.total_hj) AS total_hj'),
                        DB::raw('SUM(statistic_spk.total_real_omset) AS total_real_omset')
                    )
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $master->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            DB::raw('SUM(statistic_spk_'.$list.'.total_hj) as total_hj_'.$list),
                            DB::raw('SUM(statistic_spk_'.$list.'.total_real_omset) as total_real_omset_'.$list)
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                $index = User::select('users.id', 'users.first_name', 'users.last_name', 'statistic_spk.total_hj', 'statistic_spk.total_real_omset')
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $index->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            'statistic_spk_'.$list.'.total_hj as total_hj_'.$list,
                            'statistic_spk_'.$list.'.total_real_omset as total_real_omset_'.$list
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                // $index = $index->union($master)->get();
                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->fullname,
                        $list->total_hj_1,
                        $list->total_hj_2,
                        $list->total_hj_3,
                        $list->total_hj_4,
                        $list->total_hj_5,
                        $list->total_hj_6,
                        $list->total_hj_7,
                        $list->total_hj_8,
                        $list->total_hj_9,
                        $list->total_hj_10,
                        $list->total_hj_11,
                        $list->total_hj_12,
                        $list->total_hj,
                    ];
                }

                $sheet->fromArray($data);
                $sheet->setColumnFormat(array(
                    'B' => '0',
                    'C' => '0',
                    'D' => '0',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0',
                    'H' => '0',
                    'I' => '0',
                    'J' => '0',
                    'K' => '0',
                    'L' => '0',
                    'M' => '0',
                    'N' => '0',
                ));
                $sheet->row(1, array('Sales', 'January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Total'));
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Real Omset Monthly', function ($sheet) use ($f_year) {

                $where_spk = '1';
                if($f_year)
                {
                    $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
                }

                $where_expo = 'spk.main_division_id NOT IN ('.implode(', ', getConfigValue('division_expo', true)).')';

                $master = User::select(
                        DB::raw('"" AS id'),
                        DB::raw('"Total" AS first_name'),
                        DB::raw('"" AS last_name'), 
                        DB::raw('SUM(statistic_spk.total_hj) AS total_hj'),
                        DB::raw('SUM(statistic_spk.total_real_omset) AS total_real_omset')
                    )
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $master->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            DB::raw('SUM(statistic_spk_'.$list.'.total_hj) as total_hj_'.$list),
                            DB::raw('SUM(statistic_spk_'.$list.'.total_real_omset) as total_real_omset_'.$list)
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                $index = User::select('users.id', 'users.first_name', 'users.last_name', 'statistic_spk.total_hj', 'statistic_spk.total_real_omset')
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $index->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            'statistic_spk_'.$list.'.total_hj as total_hj_'.$list,
                            'statistic_spk_'.$list.'.total_real_omset as total_real_omset_'.$list
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                // $index = $index->union($master)->get();
                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->fullname,
                        $list->total_real_omset_1,
                        $list->total_real_omset_2,
                        $list->total_real_omset_3,
                        $list->total_real_omset_4,
                        $list->total_real_omset_5,
                        $list->total_real_omset_6,
                        $list->total_real_omset_7,
                        $list->total_real_omset_8,
                        $list->total_real_omset_9,
                        $list->total_real_omset_10,
                        $list->total_real_omset_11,
                        $list->total_real_omset_12,
                        $list->total_real_omset,
                    ];
                }

                $sheet->fromArray($data);
                $sheet->setColumnFormat(array(
                    'B' => '0',
                    'C' => '0',
                    'D' => '0',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0',
                    'H' => '0',
                    'I' => '0',
                    'J' => '0',
                    'K' => '0',
                    'L' => '0',
                    'M' => '0',
                    'N' => '0',
                ));
                $sheet->row(1, array('Sales', 'January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Total'));
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Sell Price Monthly Expo', function ($sheet) use ($f_year) {

                $where_spk = '1';
                if($f_year)
                {
                    $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
                }

                $where_expo = 'spk.main_division_id IN ('.implode(', ', getConfigValue('division_expo', true)).')';

                $master = User::select(
                        DB::raw('"" AS id'),
                        DB::raw('"Total" AS first_name'),
                        DB::raw('"" AS last_name'), 
                        DB::raw('SUM(statistic_spk.total_hj) AS total_hj'),
                        DB::raw('SUM(statistic_spk.total_real_omset) AS total_real_omset')
                    )
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $master->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            DB::raw('SUM(statistic_spk_'.$list.'.total_hj) as total_hj_'.$list),
                            DB::raw('SUM(statistic_spk_'.$list.'.total_real_omset) as total_real_omset_'.$list)
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                $index = User::select('users.id', 'users.first_name', 'users.last_name', 'statistic_spk.total_hj', 'statistic_spk.total_real_omset')
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $index->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            'statistic_spk_'.$list.'.total_hj as total_hj_'.$list,
                            'statistic_spk_'.$list.'.total_real_omset as total_real_omset_'.$list
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                // $index = $index->union($master)->get();
                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->fullname,
                        $list->total_hj_1,
                        $list->total_hj_2,
                        $list->total_hj_3,
                        $list->total_hj_4,
                        $list->total_hj_5,
                        $list->total_hj_6,
                        $list->total_hj_7,
                        $list->total_hj_8,
                        $list->total_hj_9,
                        $list->total_hj_10,
                        $list->total_hj_11,
                        $list->total_hj_12,
                        $list->total_hj,
                    ];
                }

                $sheet->fromArray($data);
                $sheet->setColumnFormat(array(
                    'B' => '0',
                    'C' => '0',
                    'D' => '0',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0',
                    'H' => '0',
                    'I' => '0',
                    'J' => '0',
                    'K' => '0',
                    'L' => '0',
                    'M' => '0',
                    'N' => '0',
                ));
                $sheet->row(1, array('Sales', 'January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Total'));
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Real Omset Monthly Expo', function ($sheet) use ($f_year) {

                $where_spk = '1';
                if($f_year)
                {
                    $where_spk   = 'YEAR(spk.date_spk) = ' . $f_year;
                }

                $where_expo = 'spk.main_division_id IN ('.implode(', ', getConfigValue('division_expo', true)).')';

                $master = User::select(
                        DB::raw('"" AS id'),
                        DB::raw('"Total" AS first_name'),
                        DB::raw('"" AS last_name'), 
                        DB::raw('SUM(statistic_spk.total_hj) AS total_hj'),
                        DB::raw('SUM(statistic_spk.total_real_omset) AS total_real_omset')
                    )
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $master->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            DB::raw('SUM(statistic_spk_'.$list.'.total_hj) as total_hj_'.$list),
                            DB::raw('SUM(statistic_spk_'.$list.'.total_real_omset) as total_real_omset_'.$list)
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $master->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                $index = User::select('users.id', 'users.first_name', 'users.last_name', 'statistic_spk.total_hj', 'statistic_spk.total_real_omset')
                    ->where(function ($query) {
                        $query->whereIn('position_id',  getConfigValue('sales_position', true))
                            ->orWhereIn('users.id', getConfigValue('sales_user', true));
                        })
                    ->where('active', 1)
                    ->withStatisticSpk($where_spk .' AND ' . $where_expo)
                    ->where('statistic_spk.count_spk', '>', 0);

                foreach (range(1, 12) as $list) {
                    $index->withStatisticSpk($where_spk . ' AND ' . $where_expo . ' AND MONTH(spk.date_spk) = '.$list, 'statistic_spk_'.$list)
                        ->addSelect(
                            'statistic_spk_'.$list.'.total_hj as total_hj_'.$list,
                            'statistic_spk_'.$list.'.total_real_omset as total_real_omset_'.$list
                        );
                }

                if(!Auth::user()->can('full-user'))
                {
                    $index->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt]);
                }

                // $index = $index->union($master)->get();
                $index = $index->get();

                $data = '';
                foreach ($index as $list) {
                    $data[] = [
                        $list->fullname,
                        $list->total_real_omset_1,
                        $list->total_real_omset_2,
                        $list->total_real_omset_3,
                        $list->total_real_omset_4,
                        $list->total_real_omset_5,
                        $list->total_real_omset_6,
                        $list->total_real_omset_7,
                        $list->total_real_omset_8,
                        $list->total_real_omset_9,
                        $list->total_real_omset_10,
                        $list->total_real_omset_11,
                        $list->total_real_omset_12,
                        $list->total_real_omset,
                    ];
                }

                $sheet->fromArray($data);
                $sheet->setColumnFormat(array(
                    'B' => '0',
                    'C' => '0',
                    'D' => '0',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0',
                    'H' => '0',
                    'I' => '0',
                    'J' => '0',
                    'K' => '0',
                    'L' => '0',
                    'M' => '0',
                    'N' => '0',
                ));
                $sheet->row(1, array('Sales', 'January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Total'));
                $sheet->setFreeze('A1');
            });
        })->download('xls');
    }

    public function getSpk(Request $request)
    {
        $date = date('Y-m-d');
        if (isset($request->date_spk) && $request->date_spk != '') {
            $date = $request->date_spk;
        }

        $sales_id = Auth::id();
        if (isset($request->sales_id) && $request->sales_id != '') {
            $sales_id = $request->sales_id;
        }
        // return $sales_id;
        $user = User::where('id', $sales_id)->first();

        $spk = Spk::select('no_spk')
            ->where('no_spk', 'like', str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-%")
            ->orderBy('no_spk', 'desc');

        $count = $spk->count();
        $year  = $spk->first();

        if ($count == 0) {
            $numberSpk = 0;
        } else {
            $numberSpk = intval(substr($year->no_spk, -3, 3));
        }

        return str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-" . str_pad($numberSpk + 1, 3, '0', STR_PAD_LEFT);
    }

    public function getDetail(Request $request)
    {
        $index = Production::find($request->production_id);

        return $index->detail;
    }

}
