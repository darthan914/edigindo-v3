<?php

namespace App\Http\Controllers\Backend;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Config;
use App\Models\Division;
use App\Models\Offer;
use App\Models\OfferDetail;
use App\Models\Pic;
use App\Models\Archive;
use App\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use File;
use PDF;
use Validator;
use Yajra\Datatables\Facades\Datatables;

class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Offer::select(DB::raw('YEAR(date_offer) as year'))->orderBy('date_offer', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Offer::join('users as sales', 'sales.id', 'offers.sales_id')
            ->select('sales.first_name', 'sales.last_name', 'sales.id')
            ->orderBy('sales.first_name', 'ASC')->distinct();

        if (!Auth::user()->can('full-user')) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        $division = Division::all();

        return view('backend.offer.index')->with(compact('year', 'month', 'sales', 'division', 'request'));
    }

    public function datatables(Request $request)
    {

        $f_year     = $this->filter($request->f_year, date('Y'));
        $f_month    = $this->filter($request->f_month, date('n'));
        $f_sales    = $this->filter($request->f_sales, Auth::id());
        $f_division = $this->filter($request->f_division);
        $search     = $this->filter($request->search);

        $index = Offer::orderBy('id', 'DESC');

        if ($search != '') {
            $index->where(function ($query) use ($search) {
                $query->where('offers.no_document', 'like', '%' . $search . '%')
                    ->orWhere('offers.name', 'like', '%' . $search . '%');
            });
        } else {
            if ($f_month != '') {
                $index->whereMonth('offers.date_offer', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('offers.date_offer', $f_year);
            }

            if ($f_sales == 'staff') {
                $index->whereIn('offers.sales_id', Auth::user()->staff());
            } else if ($f_sales != '') {
                $index->where('offers.sales_id', $f_sales);
            }

            if ($f_division != '') {
                $index->where('offers.division_id', $f_division);
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            $html .= '
                <a href="' . route('backend.offer.edit', $index) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a><br/>
            ';

            if (Auth::user()->can('delete-offer', $index)) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-offer" data-toggle="modal" data-target="#delete-offer" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button><br/>
                ';
            }

            if (Auth::user()->can('pdf-offer', $index)) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-offer" data-toggle="modal" data-target="#pdf-offer" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button>
                ';
            }
            return $html;
        });

        $datatables->addColumn('view', function ($index) {
            return view('backend.offer.view', compact('index'));
        });

        $datatables->editColumn('no_document', function ($index) {
            $html = '<b>Name Project</b> : ' . $index->name . '<br/>';
            $html .= '<b>Sales</b> : ' . $index->sales->fullname . '<br/>';
            $html .= '<b>No Document</b> : ' . $index->no_document . '<br/>';
            $html .= '<b>Date</b> : ' . $index->date_offer_readable . '<br/>';

            return $html;
        });

        $datatables->editColumn('check', function ($index) {
            $html = '';
            if (Auth::user()->can('check-offer', $index)) {
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

        $brand    = $address    = $pic    = [];

        return view('backend.offer.create', compact('division', 'company', 'brand', 'address', 'pic', 'sales'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
                'no_document' => 'required|unique:offers,no_document',
                'name'        => 'required',
                'company_id'  => 'required',
                'pic_id'      => 'required',
                'division_id' => 'required',
                'sales_id'    => 'required',
                'address'     => 'required',
                'date_offer'  => 'required|date',
            ]);

        if ($request->company_id) {
            $validator->after(function ($validator) use ($request) {
                $check = Company::find($request->company_id);
                if ($check->lock) {
                    $validator->errors()->add('company_id', 'This company not allow add offer');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Offer;

        $index->no_document      = $request->no_document;
        $index->name             = $request->name;
        $index->company_id       = $request->company_id;
        $index->brand_id         = $request->brand_id;
        $index->pic_id           = $request->pic_id;
        $index->additional_phone = $request->additional_phone;
        $index->address          = $request->address;
        $index->sales_id         = $request->sales_id;
        $index->division_id      = $request->division_id;
        $index->date_offer       = $request->date_offer;
        $index->ppn              = $request->ppn;
        $index->note             = $request->note;
        $index->total_value      = $request->total_value ?? 0;

        $index->save();

        saveArchives($index, Auth::id(), 'Create offer', $request->except('_token'));

        return redirect()->route('backend.offer.edit', $index)->with('success', 'Data Has Been Added');
    }

    public function edit(Offer $index)
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

        $brand    = Brand::where('company_id', $index->company_id)->get();
        $address  = Address::where('company_id', $index->company_id)->get();
        $pic      = Pic::where('company_id', $index->company_id)->get();

        return view('backend.offer.edit')->with(compact('index', 'division', 'company', 'sales', 'brand', 'address', 'pic'));
    }

    public function update(Offer $index, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_document' => 'required|unique:offers,no_document,' . $index->id,
            'name'        => 'required',
            'company_id'  => 'required',
            'pic_id'      => 'required',
            'division_id' => 'required',
            'date_offer'  => 'required|date',
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

        saveArchives($index, Auth::id(), 'update offer', $request->except('_token'));

        $index->no_document      = $request->no_document;
        $index->name             = $request->name;
        $index->company_id       = $request->company_id;
        $index->brand_id         = $request->brand_id;
        $index->pic_id           = $request->pic_id;
        $index->additional_phone = $request->additional_phone;
        $index->address          = $request->address;
        $index->sales_id         = $request->sales_id;
        $index->division_id      = $request->division_id;
        $index->date_offer       = $request->date_offer;
        $index->ppn              = $request->ppn;
        $index->total_price      = $request->total_price ?? 0;
        $index->note             = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Offer::find($request->id);

        if (!Auth::user()->can('delete-offer', $index)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete offer');

        Offer::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        $id = [];
        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('delete-offer', Offer::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = Offer::whereIn('id', $id)->get();

            saveMultipleArchives(Offer::class, $index, Auth::id(), "delete offer");

            Offer::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function datatablesDetail(Offer $index, Request $request)
    {
        $index = OfferDetail::where('offer_id', $index->id)->orderBy('id', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('update-offer', $index->offers) && Auth::user()->can('status-offer', $index)) {

                $html .= '
                    <button class="btn btn-xs btn-warning edit-detail"
                    data-id="' . $index->id . '"
                    data-name="' . $index->name . '"
                    data-quantity="' . $index->quantity . '"
                    data-unit="' . $index->unit . '"
                    data-value="' . $index->value . '"
                    data-photo="' . asset($index->photo) . '"
                    data-toggle="modal" data-target="#edit-detail"><i class="fa fa-edit"></i> Update</button><br/>
                ';

                $html .= '
                    <button class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="' . $index->id . '"><i class="fa fa-trash"></i> Delete</button><br/>
                ';

                
            }

            if (Auth::user()->can('undo-offer', $index->offers) && !Auth::user()->can('status-offer', $index)) {
                $html .= ' <button type="button" class="btn btn-default btn-xs undo-detail" data-toggle="modal" data-target="#undo-detail" data-id="' . $index->id . '"><i class="fa fa-undo" aria-hidden="true"></i> Undo</button><br/>';
            }

            if (Auth::user()->can('update-offer', $index->offers) && Auth::user()->can('status-offer', $index)) {
                $html .= '
                    <button type="button" class="btn btn-primary btn-xs status-detail" data-toggle="modal" data-target="#status-detail" data-id="' . $index->id . '"> Update Status</button><br/>
                ';
            }

            return $html;
        });

        $datatables->editColumn('name', function ($index) {
            $archive = $index->archives()->where('action_data', 'UPDATE OFFER DETAIL')->count();

            $html = '<b>Name</b> : ' . $index->name .'';

            if ($archive > 1)
            {
                $html .= ' <a href="'.route('backend.offer.history', $index->id).'" target="_new">[Rev.' . $archive . ']</a>';
            }

            $html .= '<br/><b>Quantity</b> : ' . $index->quantity . ' ' . $index->unit . '<br/>';
            $html .= '<b>Price</b> :  Rp. ' . number_format($index->value) . '<br/>';

            return $html;
        });

        $datatables->editColumn('status', function ($index) {
            $html = $index->status . '?';

            if (ucwords($index->status) == 'SUCCESS') {
                $html = '<strong>Success</strong>';
                
            } else if (ucwords($index->status) == 'CANCEL') {
                $html = '<strong>Cancel</strong>';

                $html .= '</br>';
                $html .= $index->note_other;
            } else if (ucwords($index->status) == 'FAILED') {
                $html = '<strong>Failed</strong>';

                $html .= '</br>';

                if (ucwords($index->reason) === 'PRICING') {
                    $html .= 'Failed Because Pricing<br>';
                    $html .= $index->note_other;
                } else if (ucwords($index->reason) === 'TIMELINE') {
                    $html .= 'Failed Because Timeline<br>';
                    $html .= $index->note_other;
                } else {
                    $html .= $index->note_other;
                }
            } else {
                $html = '<strong>Waiting</strong>';
            }

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if (Auth::user()->can('update-offer', $index->offers) && Auth::user()->can('status-offer', $index)) {
                $html .= '
                    <input type="checkbox" class="check-detail" value="' . $index->id . '" name="id[]" form="action-detail">
                ';
            }
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function storeDetail(Request $request)
    {
        $index = Offer::find($request->offer_id);

        if (!Auth::user()->can('update-offer', $index)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'offer_id' => 'required|integer',
            'name'     => 'required',
            'quantity' => 'required|integer',
            'value'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('create-detail-error', 'Something Errors');
        }

        $offer = Offer::find($request->offer_id);
        $index = new OfferDetail;

        $index->offer_id = $request->offer_id;
        $index->name     = $request->name;
        $index->detail   = $request->detail;
        $index->quantity = ($offer->total_price > 0 ? 1 : $request->quantity);
        $index->unit     = $request->unit;
        $index->value    = ($offer->total_price > 0 ? 0 : $request->value);

        if ($request->hasFile('photo')) {
            $pathSource = 'upload/offer/detail/';
            $file       = $request->file('photo');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            $file->move($pathSource, $filename);
            $index->photo = $pathSource . $filename;
        }

        $index->save();

        saveArchives($index, Auth::id(), 'create offer detail', $request->except('_token'));

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updateDetail(Request $request)
    {
        $index = OfferDetail::find($request->id);

        if (!Auth::user()->can('update-offer', $index->offers) || !Auth::user()->can('status-offer', $index)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'quantity' => 'required|integer',
            'value'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('edit-detail-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'update offer detail', $request->except('_token'));

        $index->name     = $request->name;
        $index->detail   = $request->detail;
        $index->quantity = ($index->offers->total_price > 0 ? 1 : $request->quantity);
        $index->unit     = $request->unit;
        $index->value    = ($index->offers->total_price > 0 ? 0 : $request->value);

        if ($request->hasFile('photo')) {
            if ($index->photo) {
                File::delete($index->photo);
            }
            $pathSource = 'upload/offer/detail/';
            $fileData   = $request->file('photo');
            $filename   = time() . '.' . $fileData->getClientOriginalExtension();
            $fileData->move($pathSource, $filename);

            $index->photo = $pathSource . $filename;
        } else if (isset($request->remove)) {
            File::delete($index->photo);
            $index->photo = null;
        }

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deleteDetail(Request $request)
    {
        $index = OfferDetail::find($request->id);

        if (!Auth::user()->can('update-offer', $index->offers) || !Auth::user()->can('status-offer', $index)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete offer detail');

        OfferDetail::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionDetail(Request $request)
    {
        $id = [];
        if(is_array($request->id))
        {
            if ($request->action == 'delete') {

                foreach ($request->id as $list){
                    if (Auth::user()->can('update-offer', OfferDetail::find($list)->offers))
                    {
                        $id[] = $list;
                    }
                }

                $index = OfferDetail::whereIn('id', $id)->get();

                saveMultipleArchives(OfferDetail::class, $index, Auth::id(), "delete offer detail");

                OfferDetail::destroy($id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

            foreach ($request->id as $list){
                if (Auth::user()->can('status-offer', OfferDetail::find($list)) && Auth::user()->can('update-offer', OfferDetail::find($list)->offers))
                {
                    $id[] = $list;
                }
            }
            $index = OfferDetail::whereIn('id', $id)->get();
            saveMultipleArchives(OfferDetail::class, $index, Auth::id(), "status offer detail");

            if ($request->action == 'success') {
                DB::table('offer_details')->whereIn('id', $id)->update(['status' => 'SUCCESS']);
            }

            if ($request->action == 'cancel') {
                DB::table('offer_details')->whereIn('id', $id)->update(['status' => 'CANCEL']);
            }

            return redirect()->back()->with('success', 'Data Has Been Updated');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function statusDetail(Request $request)
    {
        $index = OfferDetail::find($request->id);

        if (!Auth::user()->can('update-offer', $index->offers) || !Auth::user()->can('status-offer', $index)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'status'              => 'required',
            'reason'              => 'required_unless:status,SUCCESS,status,CANCEL',
            'other'               => 'required_if:status,CANCEL',
            'hm'                  => 'required_if:reason,PRICING',
            'hk'                  => 'required_if:reason,PRICING',
            'timeline_company'    => 'required_if:reason,TIMELINE',
            'timeline_compotitor' => 'required_if:reason,TIMELINE',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('status-detail-error', 'Something Errors');
        }

        saveArchives($index, Auth::id(), 'status offer detail', $request->except('_token'));

        $index->status = $request->status;

        if ($request->status == 'CANCEL') {
            $index->reason = null;
            $index->note_other  = $request->other;
        } else if ($request->status == 'FAILED') {
            $index->reason = $request->reason;
            if ($request->reason == 'PRICING') {
                $index->note_other = 'Modal value : ' . number_format($request->hm) . '<br> Compotitor value : ' . number_format($request->hk);
            } else if ($request->reason == 'TIMELINE') {
                $index->note_other = 'Date Offer : ' . date('d-m-Y', strtotime($request->timeline_company)) . '<br> Compotitor Date Offer : ' . date('d-m-Y', strtotime($request->timeline_compotitor));
            } else {
                $index->note_other = $request->other;
            }
        } else {
            $index->reason = null;
            $index->note_other  = null;
        }

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function undoDetail(Request $request)
    {
        $index = OfferDetail::find($request->id);

        if (!Auth::user()->can('undo-offer', $index->offers)) {
            return redirect()->route('backend.offer')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'undo offer detail', $request->except('_token'));

        $index->status     = 'WAITING';
        $index->reason     = null;
        $index->note_other = null;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function dashboard(Request $request)
    {
        $year = Offer::select(DB::raw('YEAR(date_offer) as year'))->orderBy('date_offer', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $f_type        = $this->filter($request->f_type, 'single');
        $f_year        = $this->filter($request->f_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, date('n'));
        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));

        if ($f_type == 'single') {
            if($f_year != '')
            {
                $where_spk = 'YEAR(spk.date_spk) = ' . $f_year;
                $where_offer = 'YEAR(offers.date_offer) = ' . $f_year;
            }
            else
            {
                $where_spk = null;
                $where_offer = null;
            }
        } else {

            $where_spk = 'spk.date_spk BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
            $where_offer = 'offers.date_offer BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
        }

        $sales = User::withStatisticSpk($where_spk)->withStatisticOffer($where_offer)->where('offer_all_count', '>', 0)->get();
        $client = Company::withStatisticSpk($where_spk)->withStatisticOffer($where_offer)->where('offer_all_count', '>', 0)->get();

        return view('backend.offer.dashboard')->with(compact('year', 'month', 'request', 'f_year', 'sales', 'client'));
    }

    public function datatablesDashboardSales(Request $request)
    {

        $f_type        = $this->filter($request->f_type, 'single');
        $f_year        = $this->filter($request->f_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, date('n'));
        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));

        if ($f_type == 'single') {
            if($f_year != '')
            {
                $where_spk = 'YEAR(spk.date_spk) = ' . $f_year;
                $where_offer = 'YEAR(offers.date_offer) = ' . $f_year;
            }
            else
            {
                $where_spk = null;
                $where_offer = null;
            }
        } else {

            $where_spk = 'spk.date_spk BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
            $where_offer = 'offers.date_offer BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
        }

        $index = User::withStatisticSpk($where_spk)->withStatisticOffer($where_offer)->where('offer_all_count', '>', 0)->orderBy('total_hj', 'DESC');

        if (!Auth::user()->can('full-user')) {
            $index->whereIn('id', Auth::user()->staff());
        }

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

        $datatables->editColumn('offer_expo_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_expo_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_all_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="ALL">'.number_format($index->offer_all_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_all_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_all_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_waiting_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="WAITING">'.number_format($index->offer_waiting_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_waiting_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_waiting_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_success_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="SUCCESS">'.number_format($index->offer_success_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_success_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_success_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_cancel_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="CANCEL">'.number_format($index->offer_cancel_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_cancel_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_cancel_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="FAILED">'.number_format($index->offer_failed_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_pricing_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="PRICING">'.number_format($index->offer_failed_pricing_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_pricing_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_pricing_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_timeline_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="TIMELINE">'.number_format($index->offer_failed_timeline_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_timeline_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_timeline_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_other_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="OTHER">'.number_format($index->offer_failed_other_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_other_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_other_sum_value);
            
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function datatablesDashboardClient(Request $request)
    {

        $f_type        = $this->filter($request->f_type, 'single');
        $f_year        = $this->filter($request->f_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, date('n'));
        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));

        if ($f_type == 'single') {
            if($f_year != '')
            {
                $where_spk = 'YEAR(spk.date_spk) = ' . $f_year;
                $where_offer = 'YEAR(offers.date_offer) = ' . $f_year;
            }
            else
            {
                $where_spk = null;
                $where_offer = null;
            }
        } else {

            $where_spk = 'spk.date_spk BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
            $where_offer = 'offers.date_offer BETWEEN "' . $f_start_year . '-' . $f_start_month . '-01" AND "'. $f_end_year . '-' . $f_end_month . '-'. date('t', strtotime($f_end_year . '-' . $f_end_month . '-01')).'"';
        }

        $index = Company::withStatisticSpk($where_spk)->withStatisticOffer($where_offer)->where('offer_all_count', '>', 0)->orderBy('total_hj', 'DESC');

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('total_hj', function ($index) {
            $html = 'Rp. ' . number_format($index->total_hj);
            
            return $html;
        });

        $datatables->editColumn('offer_expo_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_expo_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_all_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="ALL">'.number_format($index->offer_all_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_all_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_all_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_waiting_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="WAITING">'.number_format($index->offer_waiting_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_waiting_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_waiting_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_success_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="SUCCESS">'.number_format($index->offer_success_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_success_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_success_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_cancel_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="CANCEL">'.number_format($index->offer_cancel_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_cancel_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_cancel_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="FAILED">'.number_format($index->offer_failed_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_pricing_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="PRICING">'.number_format($index->offer_failed_pricing_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_pricing_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_pricing_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_timeline_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="TIMELINE">'.number_format($index->offer_failed_timeline_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_timeline_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_timeline_sum_value);
            
            return $html;
        });

        $datatables->editColumn('offer_failed_other_count', function ($index) {
            $html = '<button class="btn btn-primary btn-xs data-offer" data-toggle="modal" data-target="#data-offer" data-id="'.$index->id.'" data-type="OTHER">'.number_format($index->offer_failed_other_count).'</button>';
            
            return $html;
        });

        $datatables->editColumn('offer_failed_other_sum_value', function ($index) {
            $html = 'Rp. ' . number_format($index->offer_failed_other_sum_value);
            
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getDetail(Request $request)
    {
        $index = OfferDetail::find($request->offer_id);

        return $index->detail;
    }

    public function getData(Request $request)
    {
        $f_year = $this->filter($request->f_year, date('Y'));

        $f_start_year  = $this->filter($request->f_start_year, date('Y'));
        $f_start_month = $this->filter($request->f_start_month, date('n'));
        $f_end_year    = $this->filter($request->f_end_year, date('Y'));
        $f_end_month   = $this->filter($request->f_end_month, date('n'));
        $f_type        = $this->filter($request->f_type);

        $start_range = $f_start_year . '-' . sprintf("%02d", $f_start_month) . '-01';
        $end_range   = $f_end_year . '-' . sprintf("%02d", $f_end_month) . '-' . date('t', strtotime($f_end_year . '-' . sprintf("%02d", $f_end_month) . '-01'));

        $index = Offer::join('offer_details', 'offer_details.offer_id', 'offers.id')
            ->whereNull('offer_details.deleted_at')
            ->select('offers.*', 'offer_details.name as name_detail', 'offer_details.reason', DB::raw('CASE WHEN offers.total_price > 0 THEN offers.total_price ELSE (offer_details.quantity * offer_details.value) END as value'));

        if ($request->arrange == 'CLIENT') {
            $index->where('offers.company_id', $request->id);
        } else if ($request->arrange == 'SALES') {
            $index->where('offers.sales_id', $request->id);
        }

        if ($f_type == 'range') {
            $index->whereBetween('offers.date_offer', [$start_range, $end_range]);
        } else {
            if ($f_year) {
                $index->whereYear('offers.date_offer', $f_year);
            }
        }

        if ($request->type == 'WAITING') {
            $index->where('offer_details.status', 'WAITING');
        } else if ($request->type == 'SUCCESS') {
            $index->where('offer_details.status', 'SUCCESS');
        } else if ($request->type == 'CANCEL') {
            $index->where('offer_details.status', 'CANCEL');
        } else if ($request->type == 'FAILED') {
            $index->where('offer_details.status', 'FAILED');
        } else if ($request->type == 'PRICING') {
            $index->where('offer_details.status', 'FAILED')->where('offer_details.reason', 'PRICING');
        } else if ($request->type == 'TIMELINE') {
            $index->where('offer_details.status', 'FAILED')->where('offer_details.reason', 'TIMELINE');
        } else if ($request->type == 'OTHER') {
            $index->where('offer_details.status', 'FAILED')->where('offer_details.reason', 'OTHER');
        }

        $index = $index->get();

        return $index;
    }

    public function pdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
            'option'      => 'required',
            'header'      => 'required',
            'expo'      => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('offer-pdf-error', 'Something Errors');
        }

        $index = Offer::find($request->id);

        $pdf = PDF::loadView('backend.offer.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        // return view('backend.offer.pdf', compact('index', 'request'));
        return $pdf->stream($index->no_document . '_' . date('Y-m-d') . '.pdf');
    }

    public function history(OfferDetail $index)
    {
        $offer = $index->offers;
        $index = Archive::where('archivable_id', $index->id)
            ->where('archivable_type', OfferDetail::class)
            ->where('action_data', 'UPDATE OFFER DETAIL')
            ->get();

        $count = $index->count();
        return view('backend.offer.history', compact('index', 'offer', 'count'));
    }

    public function getDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Select company first.'];
        }

        $date     = $this->filter($request->date, date('Y-m-d'));
        $sales_id = $this->filter($request->sales_id, Auth::id());

        $user = User::where('id', $sales_id)->first();

        $company = Company::find($request->company_id);

        $countDoc = Offer::where('no_document', 'like', "___/PH/" . str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($company->short_name))
            ->count();

        $noPenawaran = 1;
        if ($countDoc != 0) {
            $noPenawaran = $countDoc + 1;
        }

        $document = str_pad($noPenawaran, 3, '0', STR_PAD_LEFT) . "/PH/" . str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($company->short_name);

        return $document;
    }

}
