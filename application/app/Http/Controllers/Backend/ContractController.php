<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Config;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Offer;
use App\OfferList;
use App\Production;
use App\Spk;
use App\User;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use PDF;
use Validator;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Offer::select(DB::raw('YEAR(date) as year'))->orderBy('date', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Contract::join('users as sales', 'sales.id', '=', 'contract.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if (!Auth::user()->can('allSales-contract')) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        return view('backend.contract.index')->with(compact('request', 'year', 'month', 'sales'));
    }

    public function datatables(Request $request)
    {
        $f_year     = $this->filter($request->f_year, date('Y'));
        $f_month    = $this->filter($request->f_month, date('n'));
        $f_sales    = $this->filter($request->f_sales, Auth::id());
        $s_contract = $this->filter($request->s_contract);

        $index = Contract::leftJoin('users as sales', 'sales.id', '=', 'contract.sales_id')
            ->join('offer', 'offer.id', '=', 'contract.offer_id')
            ->select(
                'contract.*',
                'offer.no_document',
                'offer.name',
                'sales.fullname as sales'
            );

        if ($s_contract != '') {
            if ($s_contract != '') {
                $index->where('contract.no_contract', 'like', '%' . $s_contract . '%');
            }
        } else {
            if ($f_month != '') {
                $index->whereMonth('contract.date', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('contract.date', $f_year);
            }

            if ($f_sales == 'staff') {
                $index->whereIn('contract.sales_id', Auth::user()->staff());
            } else if ($f_sales != '') {
                $index->where('contract.sales_id', $f_sales);
            }
        }

        $index = $index->orderBy('id', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-contract')) {
                $html .= '
                    <a href="' . route('backend.contract.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';
            }

            if (Auth::user()->can('delete-contract')) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-contract" data-toggle="modal" data-target="#delete-contract" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('pdf-contract')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-contract" data-toggle="modal" data-target="#pdf-contract" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('date', function ($index) {
            $html = date('d/m/Y', strtotime($index->date));

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
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }

        $offer = Offer::select('*');

        if (!Auth::user()->can('allSales-contract')) {
            $offer->whereIn('offer.sales_id', Auth::user()->staff());
        }

        $offer = $offer->get();

        $sales = User::whereIn('position', explode(', ', $sales_position->value))->orWhereIn('id', explode(', ', $sales_user->value))->get();

        return view('backend.contract.create', compact('offer', 'sales', $data));
    }

    public function store(Request $request)
    {
        $message = [
            'offer_id.required'    => 'This field required.',
            'no_contract.required' => 'This field required.',
            'no_contract.unique'   => 'Data already exist.',
            'date.required'        => 'This field required.',
            'date.date'            => 'Date format only.',
            'material.required'    => 'This field required.',
            'material.numeric'     => 'Numeric only.',
            'material.max'         => 'Maximum 100.',
            'services.required'    => 'This field required.',
            'services.numeric'     => 'Numeric only.',
            'services.max'         => 'Maximum 100.',
            'director.required'    => 'This field required.',
            'client.required'      => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'offer_id'    => 'required|integer',
            'date'        => 'required|date',
            'no_contract' => 'required|unique:contract,no_contract',
            'material'    => 'required|numeric|max:100',
            'services'    => 'required|numeric|max:100',
            'director'    => 'required',
            'client'      => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Contract;

        $index->offer_id    = $request->offer_id;
        $index->sales_id    = isset($request->sales_id) ? $request->sales_id : Auth::id();
        $index->no_contract = $request->no_contract;
        $index->date        = date('Y-m-d', strtotime($request->date));
        $index->material    = $request->material;
        $index->services    = $request->services;
        $index->director    = $request->director;
        $index->client      = $request->client;
        $index->note        = $request->note;

        $index->save();

        if (isset($request->create_spk)) {
            $offer      = Offer::find($request->offer_id);
            $offer_list = OfferList::where('offer_id', $request->offer_id)->get();

            $spk = new Spk;

            $spk->spk           = $this->getSpk($index->sales_id, $index->date);
            $spk->name          = $offer->name;
            $spk->main_division = 'EXPO';
            $spk->company_id    = $offer->company_id;
            $spk->brand_id      = $offer->brand_id;
            $spk->address       = $offer->address;
            $spk->pic_id        = $offer->pic_id;
            $spk->sales_id      = $offer->sales_id;
            $spk->user_id       = Auth::id();
            $spk->second_phone  = $offer->second_phone;
            $spk->date          = $offer->date;
            $spk->ppn           = $offer->ppn;
            $spk->note          = 'Contract From ' . $index->no_contract;
            $spk->transaction   = 0;

            $spk->save();

            $detail = [];
            foreach ($offer_list as $list) {

                $detail[] = [
                    'spk_id'     => $spk->id,
                    'name'       => $list->name,
                    'material'   => $list->detail,
                    'quantity'   => $list->quantity,
                    'hm'         => 0,
                    'he'         => 0,
                    'hj'         => $list->price,
                    'free'       => $list->price == 0 ? 1 : 0,
                    'division'   => $spk->main_division,
                    'source'     => 'Insource',
                    'deadline'   => date('Y-m-d', strtotime($offer->date . '+7 days')),
                    'profitable' => 1,
                ];
            }

            Production::insert($detail);
        }

        return redirect()->route('backend.contract')->with('success', 'Data Has Been Added');
    }

    public function edit($id)
    {
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }

        $offer = Offer::select('*');

        if (!Auth::user()->can('allSales-contract')) {
            $offer->whereIn('offer.sales_id', Auth::user()->staff());
        }

        $offer = $offer->get();

        $sales = User::whereIn('position', explode(', ', $sales_position->value))->orWhereIn('id', explode(', ', $sales_user->value))->get();

        $index = Contract::find($id);
        return view('backend.contract.edit')->with(compact('index', 'offer', 'sales', $data));
    }

    public function update($id, Request $request)
    {
        $index = Contract::find($id);

        if (!$this->usergrant($index->sales_id, 'allSales-contract') || !$this->levelgrant($index->sales_id)) {
            return redirect()->route('backend.contract')->with('failed', 'Access Denied');
        }

        $message = [
            'offer_id.required'    => 'This field required.',
            'no_contract.required' => 'This field required.',
            'no_contract.unique'   => 'Data already exist.',
            'date.required'        => 'This field required.',
            'date.date'            => 'Date format only.',
            'material.required'    => 'This field required.',
            'material.numeric'     => 'Numeric only.',
            'material.max'         => 'Maximum 100.',
            'services.required'    => 'This field required.',
            'services.numeric'     => 'Numeric only.',
            'services.max'         => 'Maximum 100.',
            'director.required'    => 'This field required.',
            'client.required'      => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'offer_id'    => 'required|integer',
            'date'        => 'required|date',
            'no_contract' => 'required|unique:contract,no_contract,' . $id,
            'material'    => 'required|numeric|max:100',
            'services'    => 'required|numeric|max:100',
            'director'    => 'required',
            'client'      => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->saveArchive('App\Models\Contract', 'UPDATED', $index);

        $index->offer_id    = $request->offer_id;
        $index->sales_id    = isset($request->sales_id) ? $request->sales_id : Auth::id();
        $index->no_contract = $request->no_contract;
        $index->date        = date('Y-m-d', strtotime($request->date));
        $index->material    = $request->material;
        $index->services    = $request->services;
        $index->director    = $request->director;
        $index->client      = $request->client;
        $index->note        = $request->note;

        $index->save();

        return redirect()->route('backend.contract')->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = Contract::find($request->id);

        if (!$this->usergrant($index->sales_id, 'allSales-contract') || !$this->levelgrant($index->sales_id)) {
            return redirect()->route('backend.contract')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\Contract', 'DELETED', $index);

        Contract::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if (is_array($request->id)) {
            foreach ($request->id as $list) {

                $index = Contract::find($list);

                if (($this->usergrant($index->sales_id, 'allSales-contract') || $this->levelgrant($index->sales_id))) {
                    $id[] = $list;
                }
            }

            if ($request->action == 'delete' && Auth::user()->can('delete-contract')) {
                $index = Contract::find($id);
                $this->saveMultipleArchive('App\Models\Contract', 'DELETED', $index);

                Contract::destroy($id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function getContract(Request $request)
    {
        $message = [
            'offer_id.required' => 'Select company first.',
        ];

        $validator = Validator::make($request->all(), [
            'offer_id' => 'required',
        ], $message);

        if ($validator->fails()) {
            return ['error' => 'Select No Offer first.'];
        }

        $date     = $this->filter($request->date, date('Y-m-d'));
        $sales_id = $this->filter($request->sales_id, Auth::id());

        $user = User::where('id', $sales_id)->first();

        $offer = Offer::find($request->offer_id);

        $getCompany = Company::find($offer->company_id);

        $countDoc = Contract::whereMonth('date', date('m', strtotime($date)))
            ->whereYear('date', date('Y', strtotime($date)))
            ->where('sales_id', $sales_id)
            ->count();

        $noContract = 1;
        if ($countDoc != 0) {
            $noContract = $countDoc + 1;
        }

        $contract = str_pad($noContract, 3, '0', STR_PAD_LEFT) . "/CTR/" . str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($getCompany->short_name);

        return $contract;
    }

    public function getOffer(Request $request)
    {
        $index = Offer::find($request->id);

        return $index;
    }

    public function getSpk($request_sales_id, $request_date)
    {
        $date = date('Y-m-d');
        if (isset($request_date) && $request_date != '') {
            $date = $request_date;
        }

        $sales_id = Auth::id();
        if (isset($request_sales_id) && $request_sales_id != '') {
            $sales_id = $request_sales_id;
        }
        // return $sales_id;
        $user = User::where('id', $sales_id)->first();

        $spk = Spk::select('spk')
            ->where('spk', 'like', str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-%")
            ->orderBy('spk', 'desc');

        $count = $spk->count();
        $year  = $spk->first();

        if ($count == 0) {
            $numberSpk = 0;
        } else {
            $numberSpk = intval(substr($year->spk, -3, 3));
        }

        return str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-" . str_pad($numberSpk + 1, 3, '0', STR_PAD_LEFT);
    }

    public function generateSpk($id)
    {
        $contract   = Contract::find($id);
        $offer      = Offer::find($contract->offer_id);
        $offer_list = OfferList::where('offer_id', $offer->id)->get();

        $spk = new Spk;

        $spk->spk           = $this->getSpk($offer->sales_id, $offer->date);
        $spk->name          = $offer->name;
        $spk->main_division = 'EXPO';
        $spk->company_id    = $offer->company_id;
        $spk->brand_id      = $offer->brand_id;
        $spk->address       = $offer->address;
        $spk->pic_id        = $offer->pic_id;
        $spk->sales_id      = $offer->sales_id;
        $spk->user_id       = Auth::id();
        $spk->second_phone  = $offer->second_phone;
        $spk->date          = $offer->date;
        $spk->ppn           = $offer->ppn;
        $spk->note          = 'Contract From ' . $contract->no_contract;
        $spk->transaction   = 0;

        $spk->save();

        $detail = [];
        foreach ($offer_list as $list) {
            $detail[] = [
                'spk_id'     => $spk->id,
                'name'       => $list->name,
                'material'   => $list->detail,
                'quantity'   => $list->quantity,
                'hm'         => 0,
                'he'         => 0,
                'hj'         => $list->price,
                'free'       => $list->price == 0 ? 1 : 0,
                'division'   => $spk->main_division,
                'source'     => 'Insource',
                'deadline'   => date('Y-m-d', strtotime($offer->date . '+7 days')),
                'profitable' => 1,
            ];
        }

        Production::insert($detail);

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function pdf(Request $request)
    {
        $message = [
            'size.required'        => 'This field required.',
            'orientation.required' => 'This field required.',
            'option.required'      => 'This field required.',
            'header.required'      => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
            'option'      => 'required',
            'header'      => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-contract-error', 'Something Errors');
        }

        $index = Contract::find($request->id);
        $offer = Offer::find($index->offer_id);

        $pdf = PDF::loadView('backend.contract.pdf', compact('index', 'offer', 'request'))->setPaper($request->size, $request->orientation);

        // return view('backend.contract.pdf', compact('index', 'offer', 'request'));
        return $pdf->stream($index->no_contract . '_' . date('Y-m-d') . '.pdf');
    }
}
