<?php

namespace App\Http\Controllers\Backend;

use App\Models\Pr;
use App\Models\PrDetail;
use App\Models\Po;
use App\User;
use App\Models\Division;
use App\Models\Spk;
use App\Models\Supplier;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;

use Session;
use File;
use Hash;
use Validator;
use PDF;
use Excel;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class PrController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(datetime_order) as year'))->orderBy('datetime_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $user = Pr::join('users', 'users.id', '=', 'pr.user_id')
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->orderBy('users.first_name', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-pr'))
        {
            $user->whereIn('pr.user_id', Auth::user()->staff());
        }

        $user = $user->get();

        $division = Division::where('active', 1)->get();
        $spk     = Spk::all();

    	return view('backend.pr.index')->with(compact('request', 'year', 'month', 'user', 'spk', 'division'));
    }

    public function datatables(Request $request)
    {
        $f_user  = $this->filter($request->f_user, Auth::id());
        $f_month = $this->filter($request->f_month, date('n'));
        $f_year  = $this->filter($request->f_year, date('Y'));

        $search = $this->filter($request->search);

        $index = Pr::leftJoin('spk', 'pr.spk_id', 'spk.id')
            ->leftJoin('users', 'users.id', '=', 'pr.user_id')

            ->select('pr.*')
            ->addSelect('spk.no_spk', 'spk.name as spk_name', 'users.first_name', 'users.last_name')
            ->orderBy('pr.id', 'DESC');

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('spk.no_spk', 'like', '%'.$search.'%')
                    ->orWhere('spk.name', 'like', '%'.$search.'%')
                    ->orWhere('pr.no_pr', 'like', '%'.$search.'%')
                    ->orWhere('pr.barcode', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr.datetime_order', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr.datetime_order', $f_year);
            }

            if($f_user == 'staff')
            {
                $index->whereIn('pr.user_id', Auth::user()->staff());
            }
            else if($f_user != '')
            {
                $index->where('pr.user_id', $f_user);
            }
        }

        

    	$index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('name', function ($index) {
            $html = '<b>Name</b> : ' . $index->name . '<br/>';
            $html .= '<b>User</b> : ' . $index->users->fullname . '<br/>';
            $html .= '<b>No SPK</b> : ' . ($index->no_spk ?? $index->type) . '<br/>';
            $html .= '<b>Created At</b> : ' . date('d-m-Y H:i', strtotime($index->created_at)) . '<br/>';
            $html .= '<b>Division</b> : ' . $index->divisions->name . '<br/>';
            $html .= '<b>No PR</b> : ' . $index->no_pr . '<br/>';

            return $html;
        });

        $datatables->editColumn('spk', function ($index){
            return $index->no_spk ?? $index->type;
        });

        $datatables->editColumn('first_name', function ($index){
            return $index->users->fullname ?? 'No Name';
        });

        $datatables->editColumn('datetime_order', function ($index){
            return date('d-m-Y', strtotime($index->datetime_order));
        });

        $datatables->editColumn('created_at', function ($index){
            return date('d-m-Y H:i', strtotime($index->created_at));
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d-m-Y', strtotime($index->deadline));
        });

        $datatables->editColumn('division_id', function ($index){
            return $index->divisions->name;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if( Auth::user()->can('check-pr', $index) )
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('update-pr', $index) )
            {
                $html .= '
                    <a href="'.route('backend.pr.edit', ['id' => $index->id]).'" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a><br/>
                ';
            }
            
            if( Auth::user()->can('delete-pr', $index) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-pr" data-toggle="modal" data-target="#delete-pr" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button><br/>
                ';
            }
                
            if( Auth::user()->can('pdf-pr') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary pdf-pr" data-toggle="modal" data-target="#pdf-pr" data-id="'.$index->id.'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button><br/>
                ';
            }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function storeProjectPr(Request $request)
    {
        $deleted = Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->get();

        if($deleted)
        {
            Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->forceDelete();
        }

        $index = new Pr;

        $spk     = Spk::find($request->spk_id);

        $validator = Validator::make($request->all(), [
            'spk_id' => 'required',
            'division_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('createProjectPr-pr-error', 'Something Errors');;
        }


        $index->spk_id         = $request->spk_id;
        $index->user_id        = Auth::id();
        $index->type           = 'PROJECT';
        $index->no_pr          = $this->getPr($request);
        $index->name           = $request->name;
        $index->datetime_order = date('Y-m-d H:i:s');
        $index->division_id    = $request->division_id;
        $index->barcode        = substr($spk->spk, -3) . substr($index->no_pr, -4) . date('dm', strtotime($index->datetime_order)) . getConfigValue('pr_code');

        $index->save();

        saveArchives($index, Auth::id(), 'Create pr', $request->except('_token'));

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function storeOfficePr(Request $request)
    {
        $deleted = Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->get();

        if($deleted)
        {
            Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->forceDelete();
        }

        $index = new Pr;

        $index->spk_id         = null;
        $index->user_id        = Auth::id();
        $index->type           = 'OFFICE';
        $index->no_pr          = $this->getPr($request);
        $index->name           = "For Office";
        $index->datetime_order = date('Y-m-d H:i:s');
        $index->division_id    = Auth::user()->division_id;
        $index->barcode        = '000' . substr($index->no_pr, -4) . date('dm', strtotime($index->datetime_order)) . getConfigValue('pr_code');

        $index->save();

        saveArchives($index, Auth::id(), 'Create pr', $request->except('_token'));

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function storePaymentPr(Request $request)
    {
        $deleted = Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->get();

        if($deleted)
        {
            Pr::onlyTrashed()->where('no_pr', $this->getPr($request))->forceDelete();
        }

        $index = new Pr;

        $index->spk_id         = NULL;
        $index->user_id        = Auth::id();
        $index->type           = 'PAYMENT';
        $index->no_pr          = $this->getPr($request);
        $index->name           = "For Payment";
        $index->datetime_order = date('Y-m-d H:i:s');
        $index->division_id    = $request->division_id;
        $index->barcode        = '001' . substr($index->no_pr, -4) . date('dm', strtotime($index->datetime_order)) . getConfigValue('pr_code');

        $index->save();

        saveArchives($index, Auth::id(), 'Create pr', $request->except('_token'));

        return redirect()->route('backend.pr.edit', [$index->id]);
    }

    public function edit(Pr $index)
    {
        if(!Auth::user()->can('update-pr', $index))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        if($index->type == 'PAYMENT')
        {
            $purchasing = User::where(function ($query) {
                $query->whereIn('position_id', getConfigValue('financial_position', true))
                ->orWhereIn('id', getConfigValue('financial_user', true));
            })->where('active', 1)->get();
        }
        else
        {
            $purchasing = User::where(function ($query) {
                $query->whereIn('position_id', getConfigValue('purchasing_position', true))
                ->orWhereIn('id', getConfigValue('purchasing_user', true));
            })->where('active', 1)->get();
        }
        

        $division   = Division::all();
        $spk        = Spk::all();
        
        return view('backend.pr.edit')->with(compact('index', 'purchasing', 'division', 'spk'));
    }

    public function update(Pr $index, Request $request)
    {
        if(!Auth::user()->can('update-pr', $index))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'spk_id'   => 'required',
            'name'     => 'required',
            'division_id' => 'required',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        saveArchives($index, Auth::id(), 'update pr', $request->except('_token'));

        $index->spk_id      = $request->spk_id;
        $index->name        = $request->name;
        $index->division_id = $request->division_id;

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function delete(Request $request)
    {
        $index = Pr::find($request->id);

        if(!Auth::user()->can('delete-pr', $index))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete pr');

        Pr::destroy($request->id);

        return redirect()->back()->with('success', 'Data has been deleted');
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('delete-pr', Pr::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = Pr::whereIn('id', $id)->get();

            saveMultipleArchives(Pr::class, $index, Auth::id(), "delete pr");

            Pr::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function datatablesPrDetail(Pr $index, Request $request)
    {
        $datatables = Datatables::of($index->pr_details);

        $datatables->editColumn('quantity', function ($index) {
            return $index->quantity . ' ' . $index->unit;
        });

        $datatables->editColumn('purchasing_id', function ($index) {

            return $index->purchasing->fullname;
            
        });

        $datatables->editColumn('status', function ($index) {
            $html = $index->status;

            if($index->po->count() > 0)
            {
                $html .= ' (ORDERED)';
            }

            return $html;
        });

        $datatables->editColumn('item', function ($index) {
            if($index->type == 'PAYMENT')
            {
                return $index->item . ' ' . number_format($index->value);
            }
            else
            {
                return $index->item;
            }
            
            
        });

        $datatables->editColumn('deadline', function ($index) {
            return date('d-m-Y H:i', strtotime($index->deadline));
            
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if(Auth::user()->can('checkDetail-pr', $index) )
            {
                $html .= '
                    <input type="checkbox" class="check-detail" value="'.$index->id.'" name="id[]" form="action-detail">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if(Auth::user()->can('updateDetail-pr', $index))
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning edit-detail" data-toggle="modal" data-target="#edit-detail" 
                        data-id="'.$index->id.'"
                        data-item="'.$index->item.'"
                        data-quantity="'.$index->quantity.'"
                        data-unit="'.$index->unit.'"
                        data-purchasing_id="'.$index->purchasing_id.'"
                        data-deadline="'.date('d F Y', strtotime($index->deadline)).'"
                        data-no_rekening="'.$index->no_rekening.'"
                        data-value="'.$index->value.'"
                    ><i class="fa fa-pencil" aria-hidden="true"></i></button>
                ';
            }

            if(Auth::user()->can('deleteDetail-pr', $index))
            {

                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function storePrDetail(Request $request)
    {
        $pr = Pr::find($request->pr_id);

        if(!Auth::user()->can('update-pr', $pr))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        $index = new PrDetail;

        if(isset($pr->spk))
        {
            if($pr->spk->code_admin != 0 && $pr->spk->code_admin != -2)
            {
                $index->service = 1;
            }
        }

        

        if(in_array($pr->type, ['PROJECT', 'OFFICE']))
        {
            $validator = Validator::make($request->all(), [
                'item'          => 'required',
                'quantity'      => 'required|integer',
                'unit'          => 'required',
                'purchasing_id' => 'required|integer',
                'deadline'      => 'required|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            $index->pr_id            = $request->pr_id;
            $index->item             = $request->item;
            $index->quantity         = $request->quantity;
            $index->unit             = $request->unit;
            $index->deadline         = date('Y-m-d H:i:s', strtotime($request->deadline));
            $index->purchasing_id    = $request->purchasing_id;

            $index->save();
        }
        else
        {
            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'purchasing_id' => 'required|integer',
                'deadline' => 'deadline|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }

            $index->pr_id         = $request->pr_id;
            $index->item          = $request->item;
            $index->quantity      = 1;
            $index->unit          = '';
            $index->deadline      = date('Y-m-d H:i:s', strtotime($request->deadline));
            $index->purchasing_id = $request->purchasing_id;
            $index->no_rekening   = $request->no_rekening;
            $index->value         = $request->value;

            $index->save();
        }

        saveArchives($index, Auth::id(), 'create pr detail', $request->except('_token'));

        $super_admin_notif = User::where(function ($query) {
                $query->whereIn('position_id', getConfigValue('super_admin_position', true))
                ->orWhereIn('id', getConfigValue('super_admin_user', true));
            })
            ->get();

        $html = '
            New Purchase Request, Item : '.$request->item.'
        ';

        foreach ($super_admin_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.unconfirm') ) );
        }

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePrDetail(Request $request)
    {
        $index = PrDetail::find($request->id);

        if(!Auth::user()->can('updateDetail-pr', $index))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'update pr detail', $request->except('_token'));

        if(in_array($index->pr->type, ['PROJECT', 'OFFICE']))
        {

            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'quantity' => 'required|integer',
                'unit' => 'required',
                'purchasing_id' => 'required|integer',
                'deadline' => 'required|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }


            $index->item          = $request->item;
            $index->quantity      = $request->quantity;
            $index->unit          = $request->unit;
            $index->deadline      = date('Y-m-d H:i:s', strtotime($request->deadline));
            $index->purchasing_id = $request->purchasing_id;
            $index->value         = 0;
            $index->confirm       = 0;

            $index->save();
        }
        else
        {
            $validator = Validator::make($request->all(), [
                'item' => 'required',
                'purchasing_id' => 'required|integer',
                'deadline' => 'required|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
            }


            $index->item             = $request->item;
            $index->quantity         = 1;
            $index->unit             = '';
            $index->deadline = date('Y-m-d H:i:s', strtotime($request->deadline));
            $index->purchasing_id    = $request->purchasing_id;
            $index->value            = $request->value;

            $index->save();
        }
        

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deletePrDetail(Request $request)
    {
        $index = PrDetail::find($request->id);

        if(!Auth::user()->can('deleteDetail-pr', $index))
        {
            return redirect()->route('backend.pr')->with('failed', 'Access Denied');
        }

        saveArchives($index, Auth::id(), 'delete pr detail');

        PrDetail::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function actionPrDetail(Request $request)
    {
        if ($request->action == 'delete' && is_array($request->id)) {

            foreach ($request->id as $list){
                if (Auth::user()->can('deleteDetail-pr', PrDetail::find($list)))
                {
                    $id[] = $list;
                }
            }

            $index = PrDetail::whereIn('id', $id)->get();

            saveMultipleArchives(PrDetail::class, $index, Auth::id(), "delete pr detail");

            PrDetail::destroy($id);
            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function getSpkItem(Request $request)
    {
        $index = PrDetail::select(DB::raw('
                pr_details.*,
                spk.no_spk
            '))
            ->leftJoin('pr', 'pr_details.pr_id', '=', 'pr.id')
            ->leftJoin('users', 'pr.user_id', '=', 'users.id')
            ->select('pr.no_pr', 'pr_details.item', DB::raw('CONCAT(users.first_name, " ", users.last_name) AS name'), 'pr_details.quantity', 'pr_details.unit')
            ->where('pr.spk_id', $request->id)
            ->where('pr_details.status', 'CONFIRMED')
            ->get();

        return $index;
    }

    public function unconfirm(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(datetime_order) as year'))->orderBy('datetime_order', 'ASC')->distinct()->get();
        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    	return view('backend.pr.unconfirm')->with(compact('request', 'year', 'month'));
    }

    public function datatablesUnconfirm(Request $request)
    {
        $f_month = $this->filter($request->f_month);
        $f_year  = $this->filter($request->f_year);
        $f_service = $this->filter($request->f_service);
        $search = $this->filter($request->search);

    	$index = PrDetail::where('pr_details.status', 'WAITING')->leftJoin('pr', 'pr.id', 'pr_details.pr_id')->leftJoin('spk', 'spk.id', 'pr.spk_id')->select('pr_details.*');


        if($search != '')
        {
            $index->where(function($query) use ($search) {
                $query->where('pr.no_pr', 'like', '%'.$search.'%')
                    ->orWhere('pr.name', 'like', '%'.$search.'%')
                    ->orWhere('pr_details.item', 'like', '%'.$search.'%')
                    ->orWhere('spk.no_spk', 'like', '%'.$search.'%')
                    ->orWhere('spk.name', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr.datetime_order', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr.datetime_order', $f_year);
            }

            if($f_service != '')
            {
                $index->where('pr_details.service', $f_service);
            }
        }
            

    	$index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('pr_id', function ($index) {

            $html = '<b>No SPK</b> : ' . ($index->pr->spk->no_spk ?? $index->pr->type) . '<br/>';
            $html .= '<b>Name SPK</b> : ' . ($index->pr->spk->name ?? $index->pr->type) . '<br/>';
            $html .= '<b>No PR</b> : ' . ($index->pr->no_pr) . '<br/>';
            $html .= '<b>Order Name</b> : ' . ($index->pr->users->fullname) . '<br/>';

            return $html;
        });


        $datatables->editColumn('item', function ($index) {
            $html = '<b>Item</b> : ' . $index->item . '<br/>';
            $html .= '<b>Quantity</b> : ' . ($index->quantity . ' ' . $index->unit) . '<br/>';

            return $html;
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d-m-Y H:i', strtotime($index->deadline));
        });

        $datatables->addColumn('confirm', function ($index) {
            $html = '';

            if(Auth::user()->can('confirm-pr'))
            {
                $html .= '
                    <input type="checkbox" class="check-confirm" value="'.$index->id.'" name="confirm[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('confirm_not_service', function ($index) {
            $html = '';

            if(Auth::user()->can('confirm-pr'))
            {
                $html .= '
                    <input type="checkbox" class="check-confirm_not_service" value="'.$index->id.'" name="confirm_not_service[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('reject', function ($index) {
            $html = '';

            if(Auth::user()->can('confirm-pr'))
            {
                $html .= '
                    <input type="checkbox" class="check-reject" value="'.$index->id.'" name="reject[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function updateConfirm(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        if (!empty($request->confirm)) {

            $index = PrDetail::whereIn('id', $request->confirm)->orderBy('purchasing_id', 'ASC')->get();

            saveMultipleArchives(PrDetail::class, $index, Auth::id(), "confirm pr detail");

            $number_item_purchasing = 0;
            $current_purchasing = -1;
            $data = '';
            foreach ($index as $list) {
                if($current_purchasing == $list->purchasing_id)
                {
                    $number_item_purchasing++;
                    array_push($data, $list->id);
                }
                else
                {
                    if($current_purchasing != -1)
                    {
                        $html = '
                            New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
                        ';

                        User::find($current_purchasing)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => implode(',', $data)]) ) );
                    }

                    $number_item_purchasing = 1;
                    $current_purchasing = $list->purchasing_id;
                    $data = [$list->id];
                }
            }

            $html = '
                New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
            ';

            PrDetail::whereIn('id', $request->confirm)->update(['status' => 'CONFIRMED', 'datetime_confirm' => $date]);
        }

        if (!empty($request->confirm_not_service)) {
            $index = PrDetail::whereIn('id', $request->confirm_not_service)->orderBy('purchasing_id', 'ASC')->get();

            saveMultipleArchives(PrDetail::class, $index, Auth::id(), "confirm pr detail");

            $number_item_purchasing = 0;
            $current_purchasing = -1;
            $data = '';
            foreach ($index as $list) {
                if($current_purchasing == $list->purchasing_id)
                {
                    $number_item_purchasing++;
                    array_push($data, $list->id);
                }
                else
                {
                    if($current_purchasing != -1)
                    {
                        $html = '
                            New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
                        ';

                        User::find($current_purchasing)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => implode(',', $data)]) ) );
                    }

                    $number_item_purchasing = 1;
                    $current_purchasing = $list->purchasing_id;
                    $data = [$list->id];
                }
            }

            $html = '
                New Confirm Purchase Request, Count Item : '.$number_item_purchasing.'
            ';

            PrDetail::whereIn('id', $request->confirm_not_service)->update(['status' => 'CONFIRMED', 'service' => 0, 'datetime_confirm' => $date]);
        }
        
        if (!empty($request->reject)) {
            $index = PrDetail::whereIn('id', $request->reject)->orderBy('pr_id', 'ASC')->get();

            saveMultipleArchives(PrDetail::class, $index, Auth::id(), "reject pr detail");

            $number_item_pr = 0;
            $user_id        = -1;
            $current_pr     = -1;

            foreach ($index as $list) {
                if($current_pr == $list->pr_id)
                {
                    $number_item_pr++;
                }
                else
                {
                    if($current_pr != -1)
                    {
                        $html = '
                            Item has been rejected, Count Item : '.$number_item_pr.'
                        ';

                        User::find($user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.edit', $current_pr) ) );
                    }

                    
                    $number_item_pr = 1;
                    $current_pr = $list->pr_id;
                    $user_id = Pr::find($current_pr)->user_id;
                }
            }

            $html = '
                Item has been rejected, Count Item : '.$number_item_pr.'
            ';


            PrDetail::whereIn('id', $request->reject)->update(['status' => 'REJECTED', 'datetime_confirm' => $date]);
        }

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function confirm(Request $request)
    {
        $year       = Pr::select(DB::raw('YEAR(datetime_order) as year'))->orderBy('datetime_order', 'ASC')->distinct()->get();
        $month      = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $purchasing = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('purchasing_position', true))
            ->orWhereIn('id', getConfigValue('purchasing_user', true));
        })->where('active', 1);

        $finance = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('financial_position', true))
            ->orWhereIn('id', getConfigValue('financial_user', true));
        })->where('active', 1);

        $purchasing = $purchasing->get();
        $finance = $finance->get();

        $supplier   = Supplier::all();

        return view('backend.pr.confirm')->with(compact('request', 'year', 'month', 'purchasing', 'finance', 'supplier'));
    }

    public function datatablesConfirm(Request $request)
    {
        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));
        $f_purchasing = $this->filter($request->f_purchasing);
        $f_status     = $this->filter($request->f_status);
        $f_day        = $this->filter($request->f_day);
        $f_value      = $this->filter($request->f_value);
        $f_audit      = $this->filter($request->f_audit);
        $f_finance    = $this->filter($request->f_finance);
        $f_id         = $this->filter($request->f_id);
        $search       = $this->filter($request->search);

        $type = $this->filter($request->type);

        $purchasing = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('purchasing_position' , true))
            ->orWhereIn('id', getConfigValue('purchasing_user' , true));
        })
        ->get();

        $finance = User::where(function ($query) {
            $query->whereIn('position_id', getConfigValue('financial_position' , true))
            ->orWhereIn('id', getConfigValue('financial_user' , true));
        })
        ->get();


        $supplier   = Supplier::select('*');

        $index = PrDetail::withStatisticPo()
            ->join('pr', 'pr.id', 'pr_details.pr_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->select('pr_details.*')
            ->orderBy('pr_details.id', 'DESC');


        switch ($type) {
            case 'PROJECT':
                $index->whereIn('pr.type', ['PROJECT', 'OFFICE']);
                break;

            case 'PAYMENT':
                $index->where('pr.type', 'PAYMENT');
                break;
            
            default:
                # code...
                break;
        }

        if($search != '')
        {
            $index->where(function($query) use ($search) {
                $query->where('pr_details.item', 'like', '%'.$search.'%')
                    ->orWhere('pr.name', 'like', '%'.$search.'%')
                    ->orWhere('pr.no_pr', 'like', '%'.$search.'%')
                    ->orWhere('spk.no_spk', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr_details.datetime_confirm', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr_details.datetime_confirm', $f_year);
            }

            if($f_purchasing == 'staff')
            {
                $index->whereIn('pr_details.purchasing_id', Auth::user()->staff());
            }
            else if($f_purchasing != '')
            {
                $index->where('pr_details.purchasing_id', $f_purchasing);
            }

            if($f_status != '')
            {
                $index->where('pr_details.status_purchasing', $f_status);
            }

            switch ($f_day) {
                case '4':
                    $index->whereDate('pr_details.datetime_confirm', '<=', date('Y-m-d', strtotime('-4 days')));
                    break;
                case '3':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-3 days')));
                    break;
                case '2':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-2 days')));
                    break;
                case '1':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-1 day')));
                    break;
                case '0':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d'));
                    break;
                default:
                    //
                    break;
            }

            if ($f_value != '' && $f_value == 0) 
            {
                $index->whereColumn('pr_details.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            } 
            else if ($f_value == 1) 
            {
                $index->whereColumn('pr_details.quantity', '<=', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            }


            if ($f_audit != '' && $f_audit == 0) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(count_po, 0)'), '>', DB::raw('COALESCE(count_check_audit, 0)'))
                        ->orWhere(DB::raw('COALESCE(count_po, 0)'), 0);
                });
            }
            else if ($f_audit == 1) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(count_po, 0)'), '<=', DB::raw('COALESCE(count_check_audit, 0)'))
                        ->where(DB::raw('COALESCE(count_po, 0)'), '<>',0);
                });
            }

            if ($f_finance != '' && $f_finance == 0) 
            {
                $index->whereColumn(DB::raw('COALESCE(count_po, 0)'), '>', DB::raw('COALESCE(count_check_finance, 0)'));
            }
            else if ($f_finance == 1) 
            {
                $index->whereColumn(DB::raw('COALESCE(count_po, 0)'), '<=', DB::raw('COALESCE(count_check_finance, 0)'));
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('info', function ($index) {

            $html = '<b>No SPK</b> : ' . ($index->pr->spk->no_spk ?? $index->pr->type) . '<br/>';
            $html .= '<b>Name SPK</b> : ' . ($index->pr->spk->name ?? $index->pr->type) . '<br/>';
            $html .= '<b>No PR</b> : ' . ($index->pr->no_pr) . '<br/>';
            $html .= '<b>Order Name</b> : ' . ($index->pr->users->fullname) . '<br/><br/>';


            $html .= '<b>Item</b> : ' . $index->item . '<br/>';
            $html .= '<b>Quantity</b> : ' . ($index->quantity . ' ' . $index->unit) . '<br/><br/>';


            $html .= '<b>Deadline</b> : ' . date('d-m-Y H:i', strtotime($index->deadline)) . '<br/>';
            $html .= '<b>Confirm</b> : ' . date('d-m-Y H:i', strtotime($index->datetime_confirm)) . '<br/>';
            $html .= '<b>Request At</b> : ' . date('d-m-Y H:i', strtotime($index->created_at)) . '<br/>';


            return $html;
        });

        $datatables->editColumn('purchasing', function ($index) use ($purchasing, $finance, $type) {
            $html = '';

            

            if(Auth::user()->can('changePurchasing-pr'))
            {
                $html .= '<select class="form-control change-purchasing" name="purchasing_id" data-id='.$index->id.'>';

                if($type == "PROJECT")
                {
                    foreach($purchasing as $list)
                    {
                        $html .= '<option value="'.$list->id.'" '. ($list->id == $index->purchasing_id ? 'selected' : '') .'>'.$list->fullname.'</option>';
                    }
                }
                else if ($type == "PAYMENT")
                {
                    foreach($finance as $list)
                    {
                        $html .= '<option value="'.$list->id.'" '. ($list->id == $index->purchasing_id ? 'selected' : '') .'>'.$list->fullname.'</option>';
                    }
                }
                

                $html .= '</select>';
            }
            else
            {
                $html .= $index->purchasing;
            }

            $html .= '<br/><select class="form-control change-status" name="status" data-id='.$index->id.'>';


            $html .= '<option value="NONE" '. ($index->status == "NONE" ? 'selected' : '') .'>Set Status</option>';
            $html .= '<option value="PENDING" '. ($index->status == "PENDING" ? 'selected' : '') .'>Pending</option>';
            $html .= '<option value="STOCK" '. ($index->status == "STOCK" ? 'selected' : '') .'>Stock</option>';
            $html .= '<option value="CANCEL" '. ($index->status == "CANCEL" ? 'selected' : '') .'>Cancel</option>';

            $html .= '</select>';

            if(Auth::user()->can('changePurchasing-pr'))
            {
                $html .= '<br/>
                        <button type="button" class="btn btn-block btn-xs btn-warning revision-detail" data-toggle="modal" data-target="#revision-detail" data-id="'.$index->id.'">Set Revision</button>
                    ';
            }

            return $html;
        });

        // with table po
        $datatables->addColumn('po', function ($index) use ($supplier) {
            return view('backend.pr.datatables.poProject', compact('index', 'supplier'));
        });


        $datatables->editColumn('check_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkAudit-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_audit" '.($index->check_audit ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_audit ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('check_finance', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('checkFinance-pr'))
            {
                $html .= '<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_finance" '.($index->check_finance ? 'checked' : '').'>';
            }
            else
            {
                $html .= $index->check_finance ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            }

            return $html;
        });

        $datatables->editColumn('note_audit', function ($index) {

            $html = '';
            
            if($index->value !== NULL && Auth::user()->can('noteAudit-pr'))
            {
                $html .= '<textarea class="note_audit form-control" data-id="' . $index->id . '" name="note_audit">'.$index->note_audit.'</textarea>';
            }
            else
            {
                $html .= $index->note_audit;
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';
           
            if( Auth::user()->can('deleteDetail-pr', $index) )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-detail" data-toggle="modal" data-target="#delete-detail" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->setRowClass(function ($index) {
            if($index->deadline >= '2010-01-01' && $index->deadline < date('Y-m-d') && $index->status_received == 'WAITING')
            {
                return 'alert-danger';
            }
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getStatusConfirm(Request $request)
    {
        $f_month      = $this->filter($request->f_month, date('n'));
        $f_year       = $this->filter($request->f_year, date('Y'));
        $f_purchasing = $this->filter($request->f_purchasing, (
            in_array(Auth::user()->position_id, getConfigValue('purchasing_position' , true))
            || in_array(Auth::id(), getConfigValue('purchasing_user' , true)) ? Auth::id() : ''));
        $f_status     = $this->filter($request->f_status);
        $f_day        = $this->filter($request->f_day);
        $f_value      = $this->filter($request->f_value);
        $f_audit      = $this->filter($request->f_audit);
        $f_finance    = $this->filter($request->f_finance);
        $f_id         = $this->filter($request->f_id);
        $search       = $this->filter($request->search);

        $type = $this->filter($request->type);

        $index = PrDetail::withStatisticPo()
            ->join('pr', 'pr.id', 'pr_details.pr_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->select('pr_details.*')
            ->orderBy('pr_details.id', 'DESC');


        switch ($type) {
            case 'PROJECT':
                $index->whereIn('pr.type', ['PROJECT', 'OFFICE']);
                break;

            case 'PAYMENT':
                $index->where('pr.type', 'PAYMENT');
                break;
            
            default:
                # code...
                break;
        }

        if($search != '')
        {
            $index->where(function($query) use ($search) {
                $query->where('pr_details.item', 'like', '%'.$search.'%')
                    ->orWhere('pr.name', 'like', '%'.$search.'%')
                    ->orWhere('pr.no_pr', 'like', '%'.$search.'%')
                    ->orWhere('spk.no_spk', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr_details.datetime_confirm', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr_details.datetime_confirm', $f_year);
            }

            if($f_purchasing == 'staff')
            {
                $index->whereIn('pr_details.purchasing_id', Auth::user()->staff());
            }
            else if($f_purchasing != '')
            {
                $index->where('pr_details.purchasing_id', $f_purchasing);
            }

            if($f_status != '')
            {
                $index->where('pr_details.status_purchasing', $f_status);
            }

            switch ($f_day) {
                case '4':
                    $index->whereDate('pr_details.datetime_confirm', '<=', date('Y-m-d', strtotime('-4 days')));
                    break;
                case '3':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-3 days')));
                    break;
                case '2':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-2 days')));
                    break;
                case '1':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d', strtotime('-1 day')));
                    break;
                case '0':
                    $index->whereDate('pr_details.datetime_confirm', date('Y-m-d'));
                    break;
                default:
                    //
                    break;
            }

            if ($f_value != '' && $f_value == 0) 
            {
                $index->whereColumn('pr_details.quantity', '>', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            } 
            else if ($f_value == 1) 
            {
                $index->whereColumn('pr_details.quantity', '<=', DB::raw('COALESCE(`po`.`totalQuantity`, 0)'));
            }


            if ($f_audit != '' && $f_audit == 0) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(count_po, 0)'), '>', DB::raw('COALESCE(count_check_audit, 0)'))
                        ->orWhere(DB::raw('COALESCE(count_po, 0)'), 0);
                });
            }
            else if ($f_audit == 1) 
            {
                $index->where(function($query){
                    $query->whereColumn(DB::raw('COALESCE(count_po, 0)'), '<=', DB::raw('COALESCE(count_check_audit, 0)'))
                        ->where(DB::raw('COALESCE(count_po, 0)'), '<>',0);
                });
            }

            if ($f_finance != '' && $f_finance == 0) 
            {
                $index->whereColumn(DB::raw('COALESCE(count_po, 0)'), '>', DB::raw('COALESCE(count_check_finance, 0)'));
            }
            else if ($f_finance == 1) 
            {
                $index->whereColumn(DB::raw('COALESCE(count_po, 0)'), '<=', DB::raw('COALESCE(count_check_finance, 0)'));
            }
        }

        $index = $index->get();

        $count = 0;

        foreach ($index as $key => $value) {
            $count++;
        }

        return compact('count');
    }

    public function revision(Request $request)
    {
        $index = PrDetail::find($request->id);

        saveArchives($index, Auth::id(), 'revision pr detail');

        $index->status = 'REVISION';
        $index->datetime_confirm = null;
        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function storePoProject(Request $request)
    {
        $prDetail = PrDetail::find($request->pr_detail_id);

        if(!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id))
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $request->pr_detail_id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

    	$message = [
            'pr_detail_id.required' => 'Error',
            'pr_detail_id.integer' => 'Error',
            'quantity.required' => 'This field required.',
            'quantity.integer' => 'Number only.',
            'quantity.max' => 'Maximum ' . $max,
            'no_po.required' => 'This field required.',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
            'type.required' => 'Select one.',
            'supplier_id.required' => 'This field required.',
            'name_supplier.required_if' => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'pr_detail_id' => 'required|integer',
            'quantity' => 'required|integer|max:'.$max,
            'no_po' => 'required',
            'date_po' => 'required|date',
            'type' => 'required',
            'supplier_id' => 'required',
            'name_supplier' => 'required_if:supplier_id,0',
            'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('add-poProject-error', '');
        }

        $supplier = Supplier::find($request->supplier_id);

        $index = new Po;

        $index->pr_detail_id    = $request->pr_detail_id;
        $index->quantity        = $request->quantity;
        $index->no_po           = $request->no_po;
        $index->date_po         = date('Y-m-d', strtotime($request->date_po));
        $index->type            = $request->type;
        $index->bank            = $supplier->bank ?? '';
        $index->name_supplier   = $supplier->name ?? $request->name_supplier;
        $index->no_rekening     = $supplier->no_rekening ?? '';
        $index->name_rekening   = $supplier->name_rekening ?? '';
        $index->value           = $request->value;
        $index->status_received = 'WAITING';

        $index->save();

        $pr = Pr::find($prDetail->pr_id);

        $html = '
            Your item requested is on the way, Item : '.$prDetail->item.', Quantity : '.$request->quantity.'
        ';

        User::find($pr->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.item', ['f_id' => $index->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePoProject(Request $request)
    {
        $index = Po::find($request->id);

    	$prDetail = PrDetail::find($index->pr_detail_id);

        if((!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $index->pr_detail_id)->where('id', '<>', $request->id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

        $message = [
            'quantity.required' => 'This field required.',
            'quantity.integer' => 'Number only.',
            'quantity.max' => 'Maximum ' . $max,
            'no_po.required' => 'This field required.',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
            'type.required' => 'Select one.',
            'supplier_id.required' => 'This field required.',
            'name_supplier.required' => 'This field required.',
            'value.required' => 'This field required.',
            'value.numeric' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|max:'.$max,
            'no_po' => 'required',
            'date_po' => 'required|date',
            'type' => 'required',
            'supplier_id' => 'required',
            'name_supplier' => 'required_if:supplier_id,0',
            'value' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-poProject-error', '');
        }

        $this->saveArchive('App\Models\Po', 'UPDATED', $index);

        $supplier = Supplier::find($request->supplier_id);

        $index->quantity      = $request->quantity;
        $index->no_po         = $request->no_po;
        $index->date_po       = date('Y-m-d', strtotime($request->date_po));
        $index->type          = $request->type;
        $index->bank          = $supplier->bank ?? '';
        $index->name_supplier = $supplier->name ?? $request->name_supplier;
        $index->no_rekening   = $supplier->no_rekening ?? '';
        $index->name_rekening = $supplier->name_rekening ?? '';
        $index->value         = $request->value;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function storePoPayment(Request $request)
    {
        $prDetail = PrDetail::find($request->pr_detail_id);

        if(!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id))
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $sumPoQuantity = Po::where('pr_detail_id', $request->pr_detail_id)->where('status_received', '<>', 'COMPLAIN')->sum('quantity');

        $max = $prDetail->quantity - $sumPoQuantity;

        $message = [
            'pr_detail_id.required' => 'Error',
            'pr_detail_id.integer' => 'Error',
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'pr_detail_id' => 'required|integer',
            'date_po' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('add-poPayment-error', '');
        }

        $supplier = Supplier::find($request->supplier_id);

        $index = new Po;

        $index->pr_detail_id    = $request->pr_detail_id;
        $index->quantity        = 1;
        $index->no_po           = 'Payment';
        $index->date_po         = date('Y-m-d', strtotime($request->date_po));
        $index->type            = '-';
        $index->bank            = '-';
        $index->name_supplier   = '-';
        $index->no_rekening     = $prDetail->no_rekening;
        $index->name_rekening   = '-';
        $index->value           = $request->value ?? $prDetail->value;
        $index->status_received = 'CONFIRMED';

        $index->save();

        $pr = Pr::find($prDetail->pr_id);

        $html = '
            Your item requested is on the way, Item : '.$prDetail->item.', Quantity : '.$request->quantity.'
        ';

        User::find($pr->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.item', ['f_id' => $index->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function updatePoPayment(Request $request)
    {
        $index = Po::find($request->id);

        $prDetail = PrDetail::find($index->pr_detail_id);

        if((!$this->usergrant($prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $message = [
            'date_po.required' => 'This field required.',
            'date_po.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'date_po' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-poPayment-error', '');
        }

        $this->saveArchive('App\Models\Po', 'UPDATED', $index);

        $supplier = Supplier::find($request->supplier_id);

        $index->value   = $request->value;
        $index->date_po = date('Y-m-d', strtotime($request->date_po));

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function deletePo(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        if((!$this->usergrant($index->prDetail->purchasing_id, 'allPurchasing-pr') || !$this->levelgrant($index->prDetail->purchasing_id)) || $index->check_audit == 1 || $index->check_finance == 1)
        {
            return redirect()->route('backend.pr.confirm')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\Po', 'DELETED', $index);

    	Po::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function changePurchasing(Request $request)
    {
        $index = PrDetail::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHANGE_PURCHASING', $index);

        $index->purchasing_id = $request->purchasing_id;

        $index->save();
    }

    public function changeStatus(Request $request)
    {
        $index = PrDetail::find($request->id);

        if($index->status != "CANCEL")
        {
            $this->saveArchive('App\Models\Po', 'CHANGE_STATUS', $index);

            $index->status = $request->status;

            $index->save();
        }
    }

    public function checkAudit(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHECK_AUDIT', $index);

        $index->check_audit = $request->check_audit;

        $index->save();
    }

    public function checkFinance(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'CHECK_FINANCE', $index);

        $index->check_finance = $request->check_finance;

        $index->save();
    }

    public function noteAudit(Request $request)
    {
        $index = Po::find($request->id);

        $this->saveArchive('App\Models\Po', 'NOTE_AUDIT', $index);

        $index->note_audit = $request->note_audit;

        $index->save();
    }

    public function pdf(Request $request)
    {
        $message = [
            'size.required' => 'This field required.',
            'orientation.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size' => 'required',
            'orientation' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-pr-error', 'Something Errors');
        }

        $index = Pr::find($request->pr_id);

        $pdf = PDF::loadView('backend.pr.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream($index->no_pr.'_'.date('Y-m-d').'.pdf');
    }

    public function dashboard(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(datetime_order) as year'))->orderBy('datetime_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.fullname', 'sales.id')
            ->orderBy('sales.fullname', 'ASC')->distinct();

        if (!Auth::user()->can('allSales-spk')) {
            $sales->whereIn('sales_id', Auth::user()->staff());
        }

        $sales = $sales->get();

        return view('backend.pr.dashboard')->with(compact('request', 'year', 'month', 'sales'));
    }

    public function ajaxDashboard(Request $request)
    {

        $f_month = $this->filter($request->f_month, date('n'));
        $f_year  = $this->filter($request->f_year, date('Y'));
        $f_sales  = $this->filter($request->f_sales, Auth::id());
        $f_budget  = $this->filter($request->f_budget);

        $sql_production = '
            (
                /* sales -> spk */
                SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                FROM
                (
                    /* spk -> production with realOmset */
                    SELECT
                        production.spk_id, 
                        production.name, 
                        production.sales_id,
                        (@totalHM := production.totalHM) as totalHM,
                        production.totalHE,
                        (@totalHJ := production.totalHJ) as totalHJ,
                        @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                        @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                        (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
                    FROM
                    (
                        /* spk -> production */
                        SELECT 
                            spk.id AS spk_id,
                            spk.sales_id, spk.name,
                            SUM(production.hm * production.quantity) AS totalHM,
                            SUM(production.he * production.quantity) AS totalHE,
                            SUM(production.hj * production.quantity) AS totalHJ
                        FROM spk join production ON spk.id = production.spk_id
                        GROUP BY spk.id
                    ) production
                ) production
                GROUP BY production.spk_id
            ) production
        ';

        $sql_pr = '
            (
                /* spk -> pr */
                SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                FROM
                (
                    /* spk -> pr */
                    SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                    FROM `pr`
                    LEFT JOIN
                    (
                        /* pr_detail -> po */
                        SELECT
                            `pr_detail`.`id` as pr_detail_id,
                            `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                            SUM(`po`.`value`) as totalValue
                        FROM `pr_detail`
                        JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                        GROUP BY `pr_detail`.`id`
                    ) pr_detail ON pr.id = pr_detail.pr_id
                    WHERE pr.type = "PROJECT"
                    GROUP BY pr.id
                ) pr
                GROUP BY pr.spk_id
            ) pr
        ';

        $index = Spk::select(
            'spk.id', 
            'spk.name', 
            'spk.no_spk', 
            'production.totalHM', 
            'production.totalHE', 
            'production.totalHJ', 
            DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
            DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
            DB::raw('(
                CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                THEN 100 
                ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                END
            ) AS margin
            '),
            DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
            DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

        )
        ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
        ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

        if($f_month != '')
        {
            $index->whereMonth('spk.date', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('spk.date', $f_year);
        }

        if($f_sales == 'staff')
        {
            $index->whereIn('spk.sales_id', Auth::user()->staff());
        }
        else if($f_sales != '')
        {
            $index->where('spk.sales_id', $f_sales);
        }

        if ($f_budget != '') {
            if ($f_budget == 1) {
                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '>=', 0);
            } else {
                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '<', 0);
            }
        }

        $index = $index->get();

        $data = '';

        $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
        foreach ($index as $list) {
            $data[] = [
                'id' => $list->id,
                'spk' => $list->spk,
                'name' => $list->name,
                'totalHM' => number_format($list->totalHM),
                'totalHE' => number_format($list->totalHE),
                'totalHJ' => number_format($list->totalHJ),
                'totalPR' => number_format($list->totalPR),
                'profit' => number_format($list->profit),
                'margin' => number_format($list->margin, 2).' %',
                'budget' => number_format($list->budget),
                'budgetE' => number_format($list->budgetE),
            ];

            $allTotalHM      += $list->totalHM;
            $allTotalHE      += $list->totalHE;
            $allTotalHJ      += $list->totalHJ;
            $allTotalPR      += $list->totalPR;
            $allTotalProfit  += $list->profit;
            $allTotalBudget  += $list->budget;
            $allTotalBudgetE += $list->budgetE; 
        }

        return compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');;
    }

    public function datatablesDetailDashboard(Request $request)
    {
        $sql = '
            (
                /* pr_detail -> po */
                SELECT
                    `pr_detail`.`id` as pr_detail_id,
                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                    SUM(`po`.`value`) as totalValue,
                    COUNT(`po`.`id`) as countPO,
                    countCheckAudit,
                    countCheckFinance
                FROM `pr_detail`
                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                LEFT JOIN (
                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                GROUP BY `pr_detail`.`id`
            ) po
        ';

        $index = PrDetail::where('pr_detail.confirm', 1)
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin(DB::raw($sql), 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr_detail.purchasing_id')
            ->select(
                'pr_detail.*',
                'spk.no_spk',
                'spk.name as spk_name',
                'pr.id as pr_id',
                'pr.name',
                'pr.deadline',
                'pr.no_pr',
                'users.fullname as purchasing',
                'pr_detail.purchasing_id',
                'po.countPO',
                DB::raw('COALESCE(po.totalQuantity, 0) as totalPoQty'),
                DB::raw('COALESCE(countCheckAudit, 0) as countCheckAudit'),
                DB::raw('COALESCE(countCheckFinance, 0) as countCheckFinance')
            )
            ->where('spk.id', $request->id)
            ->orderBy('pr_detail.id', 'DESC')
            ->distinct();

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('po', function ($index){
            $html = '';

            $html .= '<table class="table table-striped">';

            $html .= '
                <tr>
                    <th>No App\Models\Po</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Value</th>
                </tr>
            ';

            foreach ($index->po as $list) {
                $html .= '
                    <tr>
                        <td>'.$list->no_po.'</td>
                        <td>'.date('d/m/Y', strtotime($list->date_po)).'</td>
                        <td>'.number_format($list->quantity).'</td>
                        <td>'.number_format($list->value).'</td>
                    </tr>
                ';
            }
            

            $html .= '</table>';

            return $html;

        });

        $datatables->editColumn('purchasing', function ($index){
            return $index->purchasing ?? 'not set';
        });

        $datatables->editColumn('date_po', function ($index){
            return date('d/m/Y', strtotime($index->date_po));
        });

        $datatables->editColumn('value', function ($index){
            return number_format($index->value);
        });

        $datatables->editColumn('quantity', function ($index){
            return $index->quantity . ' ' . $index->unit;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('view-pr') )
            {
                $html .= '
                    <a href="'.route('backend.pr.edit', ['id' => $index->pr_id]).'" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i></a>
                ';
            }
            
            // if( Auth::user()->can('delete-pr') && ($this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id)) )
            // {
            //     $html .= '
            //         <button type="button" class="btn btn-xs btn-danger delete-pr" data-toggle="modal" data-target="#delete-pr" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
            //     ';
            // }
                
            // if( Auth::user()->can('pdf-pr') )
            // {
            //     $html .= '
            //         <button type="button" class="btn btn-xs btn-primary pdf-pr" data-toggle="modal" data-target="#pdf-pr" data-id="'.$index->pr_id.'"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
            //     ';
            // }
                
            return $html;
        });
        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function excel(Request $request)
    {
        $f_month = $this->filter($request->xls_month, date('n'));
        $f_year  = $this->filter($request->xls_year, date('Y'));

        $config    = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        Excel::create('pr-'.$f_year.$f_month.'-'.date('dmYHis'), function ($excel) use ($f_year, $f_month, $purchasing_position, $purchasing_user) {
            $excel->sheet('Dashboard', function ($sheet) use ($f_year, $f_month) {

                $sql_production = '
                    (
                        /* sales -> spk */
                        SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                        FROM
                        (
                            /* spk -> production with realOmset */
                            SELECT
                                production.spk_id, 
                                production.name, 
                                production.sales_id,
                                (@totalHM := production.totalHM) as totalHM,
                                production.totalHE,
                                (@totalHJ := production.totalHJ) as totalHJ,
                                @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                                @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                                (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
                            FROM
                            (
                                /* spk -> production */
                                SELECT 
                                    spk.id AS spk_id,
                                    spk.sales_id, spk.name,
                                    SUM(production.hm * production.quantity) AS totalHM,
                                    SUM(production.he * production.quantity) AS totalHE,
                                    SUM(production.hj * production.quantity) AS totalHJ
                                FROM spk join production ON spk.id = production.spk_id
                                GROUP BY spk.id
                            ) production
                        ) production
                        GROUP BY production.spk_id
                    ) production
                ';

                $sql_pr = '
                    (
                        /* spk -> pr */
                        SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                        FROM
                        (
                            /* spk -> pr */
                            SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                            FROM `pr`
                            LEFT JOIN
                            (
                                /* pr_detail -> po */
                                SELECT
                                    `pr_detail`.`id` as pr_detail_id,
                                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                                    SUM(`po`.`value`) as totalValue,
                                    COUNT(`po`.`id`) as countPO,
                                    countCheckAudit,
                                    countCheckFinance
                                FROM `pr_detail`
                                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                                GROUP BY `pr_detail`.`id`
                            ) pr_detail ON pr.id = pr_detail.pr_id
                            GROUP BY pr.id
                        ) pr
                        GROUP BY pr.spk_id
                    ) pr
                ';

                $index = Spk::select(
                    'spk.id', 
                    'spk.name', 
                    'spk.no_spk', 
                    'production.totalHM', 
                    'production.totalHE', 
                    'production.totalHJ', 
                    DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
                    DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
                    DB::raw('(
                        CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                        THEN 100 
                        ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                        END
                    ) AS margin
                    '),
                    DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
                    DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

                )
                ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
                ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

                if($f_month != '')
                {
                    $index->whereMonth('spk.date', $f_month);
                }

                if($f_year != '')
                {
                    $index->whereYear('spk.date', $f_year);
                }

                $index = $index->get();

                $data = '';

                $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
                foreach ($index as $list) {
                    $data[] = [
                        'id' => $list->id,
                        'spk' => $list->spk,
                        'name' => $list->name,
                        'totalHM' => number_format($list->totalHM),
                        'totalHE' => number_format($list->totalHE),
                        'totalHJ' => number_format($list->totalHJ),
                        'totalPR' => number_format($list->totalPR),
                        'profit' => number_format($list->profit),
                        'margin' => number_format($list->margin, 2).' %',
                        'budget' => number_format($list->budget),
                        'budgetE' => number_format($list->budgetE),
                    ];

                    $allTotalHM      += $list->totalHM;
                    $allTotalHE      += $list->totalHE;
                    $allTotalHJ      += $list->totalHJ;
                    $allTotalPR      += $list->totalPR;
                    $allTotalProfit  += $list->profit;
                    $allTotalBudget  += $list->budget;
                    $allTotalBudgetE += $list->budgetE; 
                }

                $index = compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');

                $sheet->fromArray($index['data']);
                $sheet->row(1, [
                    'SPK',
                    'Project Name',
                    'Total Modal Price',

                    'Total Estimator Price',
                    'Total Sell Price',
                    'Total App\Pr',

                    'Profit',
                    'Margin',
                    'Budget',

                    'Budget Estimator'
                ]);
                $sheet->setFreeze('A1');
            });

            $excel->sheet('Overbudget', function ($sheet) use ($f_year, $f_month, $purchasing_position, $purchasing_user) {

                $sql_production = '
                    (
                        /* sales -> spk */
                        SELECT production.spk_id, SUM(production.totalHM) AS totalHM, SUM(production.totalHE) AS totalHE, SUM(production.totalHJ) As totalHJ, SUM(production.totalRealOmset) AS totalRealOmset
                        FROM
                        (
                            /* spk -> production with realOmset */
                            SELECT
                                production.spk_id, 
                                production.name, 
                                production.sales_id,
                                (@totalHM := production.totalHM) as totalHM,
                                production.totalHE,
                                (@totalHJ := production.totalHJ) as totalHJ,
                                @profit := (CASE WHEN production.totalHE > 0 THEN @totalHJ - (@totalHE + ((@totalHE * 5) / 100)) ELSE @totalHJ - (@totalHM + ((@totalHM * 5) / 100)) END) AS profit,
                                @percent := (@profit / (CASE WHEN production.totalHE > 0 THEN production.totalHE ELSE IF(@totalHM<>0,@totalHM,1) END) ) * 100 AS percent,
                                (CASE WHEN @percent < 30 THEN (@profit / 0.3) + @profit ELSE @totalHJ END) AS totalRealOmset
                            FROM
                            (
                                /* spk -> production */
                                SELECT 
                                    spk.id AS spk_id,
                                    spk.sales_id, spk.name,
                                    SUM(production.hm * production.quantity) AS totalHM,
                                    SUM(production.he * production.quantity) AS totalHE,
                                    SUM(production.hj * production.quantity) AS totalHJ
                                FROM spk join production ON spk.id = production.spk_id
                                GROUP BY spk.id
                            ) production
                        ) production
                        GROUP BY production.spk_id
                    ) production
                ';

                $sql_pr = '
                    (
                        /* spk -> pr */
                        SELECT pr.spk_id, SUM(pr.totalPR) as totalPR
                        FROM
                        (
                            /* spk -> pr */
                            SELECT pr.id AS pr_id, pr.spk_id, COALESCE(SUM(pr_detail.totalValue),0) AS totalPR 
                            FROM `pr`
                            LEFT JOIN
                            (
                                /* pr_detail -> po */
                                SELECT
                                    `pr_detail`.`id` as pr_detail_id,
                                    `pr_id`, SUM(`po`.`quantity`) as totalQuantity,
                                    SUM(`po`.`value`) as totalValue,
                                    COUNT(`po`.`id`) as countPO,
                                    countCheckAudit,
                                    countCheckFinance
                                FROM `pr_detail`
                                JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckAudit FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_audit` = 1 GROUP BY `pr_detail`.`id`
                                ) `audit` on `audit`.`pr_detail_id` = `pr_detail`.`id`
                                LEFT JOIN (
                                    SELECT `pr_detail`.`id` as pr_detail_id, COUNT(`po`.`id`) as countCheckFinance FROM `pr_detail` JOIN `po` ON `po`.`pr_detail_id` = `pr_detail`.`id` WHERE `po`.`check_finance` = 1 GROUP BY `pr_detail`.`id`
                                ) `finance` on `finance`.`pr_detail_id` = `pr_detail`.`id`
                                GROUP BY `pr_detail`.`id`
                            ) pr_detail ON pr.id = pr_detail.pr_id
                            GROUP BY pr.id
                        ) pr
                        GROUP BY pr.spk_id
                    ) pr
                ';

                $index = Spk::select(
                    'spk.id', 
                    'spk.name', 
                    'spk.no_spk', 
                    'production.totalHM', 
                    'production.totalHE', 
                    'production.totalHJ', 
                    DB::raw('COALESCE(pr.totalPR, 0) AS totalPR'),
                    DB::raw('(@profit := production.totalHJ - COALESCE(pr.totalPR, 0)) AS profit'),
                    DB::raw('(
                        CASE WHEN COALESCE(pr.totalPR, 0) = 0 
                        THEN 100 
                        ELSE ( @profit  / COALESCE(pr.totalPR, 0 ) ) * 100
                        END
                    ) AS margin
                    '),
                    DB::raw('production.totalHM - COALESCE(pr.totalPR, 0) AS budget'),
                    DB::raw('production.totalHE - COALESCE(pr.totalPR, 0) AS budgetE')

                )
                ->leftJoin(DB::raw($sql_production), 'spk.id', 'production.spk_id')
                ->leftJoin(DB::raw($sql_pr), 'spk.id', 'pr.spk_id');

                if($f_month != '')
                {
                    $index->whereMonth('spk.date', $f_month);
                }

                if($f_year != '')
                {
                    $index->whereYear('spk.date', $f_year);
                }

                $index->where(DB::raw('( production.totalHM - COALESCE(pr.totalPR, 0) )'), '<', 0);

                $index = $index->get();

                $data = '';

                $allTotalHM = $allTotalHE = $allTotalHJ = $allTotalPR = $allTotalProfit = $allTotalBudget = $allTotalBudgetE = 0; 
                foreach ($index as $list) {
                    $data[] = [
                        'id' => $list->id,
                        'spk' => $list->spk,
                        'name' => $list->name,
                        'totalHM' => number_format($list->totalHM),
                        'totalHE' => number_format($list->totalHE),
                        'totalHJ' => number_format($list->totalHJ),
                        'totalPR' => number_format($list->totalPR),
                        'profit' => number_format($list->profit),
                        'margin' => number_format($list->margin, 2).' %',
                        'budget' => number_format($list->budget),
                        'budgetE' => number_format($list->budgetE),
                    ];

                    $allTotalHM      += $list->totalHM;
                    $allTotalHE      += $list->totalHE;
                    $allTotalHJ      += $list->totalHJ;
                    $allTotalPR      += $list->totalPR;
                    $allTotalProfit  += $list->profit;
                    $allTotalBudget  += $list->budget;
                    $allTotalBudgetE += $list->budgetE; 
                }

                $index = compact('data', 'allTotalHM', 'allTotalHE', 'allTotalHJ', 'allTotalPR', 'allTotalProfit', 'allTotalBudget', 'allTotalBudgetE');

                $sheet->fromArray($index['data']);
                $sheet->row(1, [
                    'SPK',
                    'Project Name',
                    'Total Modal Price',

                    'Total Estimator Price',
                    'Total Sell Price',
                    'Total App\Pr',

                    'Profit',
                    'Margin',
                    'Budget',

                    'Budget Estimator'
                ]);
                $sheet->setFreeze('A1');
            });

            $purchasing = User::where(function ($query) use ($purchasing_position, $purchasing_user) {
                $query->whereIn('position', explode(', ' , $purchasing_position->value))
                ->orWhereIn('id', explode(', ' , $purchasing_user->value));
            })->get();

            foreach ($purchasing as $list) {

                $excel->sheet($list->fullname, function ($sheet) use ($f_year, $f_month, $list) {

                    $index = PrDetail::select(DB::raw('
                            pr_detail.*,
                            spk.no_spk,
                            users.fullname as purchasing,
                            pr.created_at as date_submit
                        '))
                        ->leftJoin('spk', 'pr_detail.spk_id', '=', 'spk.id')
                        ->leftJoin('users', 'pr_detail.purchasing_id', '=', 'users.id')
                        ->join('pr', 'pr_detail.pr_id', '=', 'pr.id')
                        ->where('pr_detail.confirm', 1)
                        ->where('purchasing_id', $list->id);

                    if($f_month != '')
                    {
                        $index->whereMonth('spk.date', $f_month);
                    }

                    if($f_year != '')
                    {
                        $index->whereYear('spk.date', $f_year);
                    }

                    $index = $index->get();

                    $data = '';
                    foreach ($index as $list) {
                        $data[] = [
                            'SPK' => $list->spk,
                            'No App\Pr' => $list->no_pr,
                            'Item' => $list->item,
                            'Purchasing' => $list->purchasing,
                            'No App\Models\Po' => $list->no_po,
                            'Date App\Models\Po' => $list->date_po,
                            'Type' => $list->type,
                            'Supplier' => $list->name_supplier,
                            'Name Rekening' => $list->name_rekening,
                            'No Rekening' => $list->no_rekening,
                            'Value' => number_format($list->value),
                            'Date Confirm' => $list->datetime_confirm,
                            'Check Audit' => $list->check_audit ? 'Yes' : 'No',
                        ];
                    }

                    $sheet->fromArray($data);
                    $sheet->setFreeze('A1');
                });
            }


        })->download('xls');
    }

    public function item(Request $request)
    {
        $year = Pr::select(DB::raw('YEAR(datetime_order) as year'))->orderBy('datetime_order', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $user = Pr::join('users', 'users.id', '=', 'pr.user_id')
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->orderBy('users.first_name', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-pr'))
        {
            $user->whereIn('pr.user_id', Auth::user()->staff());
        }

        $user = $user->get();

        return view('backend.pr.item')->with(compact('request', 'year', 'month', 'user'));
    }

    public function datatablesItem(Request $request)
    {
        $f_user   = $this->filter($request->f_user, Auth::id());
        $f_month  = $this->filter($request->f_month, date('n'));
        $f_year   = $this->filter($request->f_year, date('Y'));
        $f_status = $this->filter($request->f_status, 'WAITING');
        $f_id     = $this->filter($request->f_id);

        $index = Po::leftJoin('pr_detail', 'pr_detail.id', 'po.pr_detail_id')
            ->leftJoin('pr', 'pr.id', 'pr_detail.pr_id')
            ->leftJoin('spk', 'spk.id', 'pr.spk_id')
            ->leftJoin('users', 'users.id', 'pr.user_id')
            ->select('po.id', 'po.quantity', 'po.status_received', 'pr_detail.item', 'pr.no_pr', 'pr.datetime_order', 'pr_detail.deadline', 'spk.no_spk', 'spk.name', 'users.first_name', 'users.last_name', 'pr.user_id');

        if($f_id != '')
        {
            $index->where('po.id', $f_id);
        }
        else
        {
            if($f_month != '')
            {
                $index->whereMonth('pr.datetime_order', $f_month);
            }

            if($f_year != '')
            {
                $index->whereYear('pr.datetime_order', $f_year);
            }

            if($f_user == 'staff')
            {
                $index->whereIn('pr.user_id', Auth::user()->staff());
            }
            else if($f_user != '')
            {
                $index->where('pr.user_id', $f_user);
            }

            if($f_status != '')
            {
                $index->where('po.status_received', $f_status);
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('fullname', function ($index){
            return $index->fullname ?? 'not set';
        });

        $datatables->editColumn('datetime_order', function ($index){
            return date('d/m/Y', strtotime($index->datetime_order));
        });

        $datatables->editColumn('deadline', function ($index){
            return date('d/m/Y', strtotime($index->deadline));
        });

        $datatables->editColumn('status_received', function ($index){
            if($index->status_received == "CONFIRMED")
            {
                return "Confirmed, Date Received : " . date('d/m/Y', strtotime($index->date_received));
            }
            else if($index->status_received == "COMPLAIN")
            {
                return "Complain, Reason : " . $index->note_received;
            }
            else
            {
                return "Item on process";
            }
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if( $this->usergrant($index->user_id, 'allUser-pr') || $this->levelgrant($index->user_id) )
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if( Auth::user()->can('receivedItem-pr') && $index->status_received == 'WAITING')
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success pr-confirmItem" data-toggle="modal" data-target="#pr-confirmItem" data-id="'.$index->id.'">Confirm</button>
                ';
            
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger pr-complainItem" data-toggle="modal" data-target="#pr-complainItem" data-id="'.$index->id.'">Complain</button>
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function receivedItem(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        $this->saveArchive('App\Models\Po', 'RECEIVED_ITEM', $index);

        $index->status_received = "CONFIRMED";
        $index->rating          = $request->rating;
        $index->date_received   = date('Y-m-d');

        $index->save();

        $prDetail = PrDetail::find($index->pr_detail_id);

        $html = '
            Item Has Been recieved, Item : '.$prDetail->item.'
        ';

        User::find($prDetail->purchasing_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => $prDetail->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function complainItem(Request $request)
    {
        $index = Po::find($request->id);

        if($index->status_received == 'CONFIRMED')
        {
            return redirect()->back()->with('failed', 'Data Can\'t update, if item already confirmed');
        }

        $message = [
            'date_received.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'note_received' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('pr-complainItem-error', '');
        }

        $this->saveArchive('App\Models\Po', 'COMPLAIN', $index);

        $index->status_received = "COMPLAIN";
        $index->date_received   = date('Y-m-d');
        $index->rating          = 0;
        $index->note_received   = $request->note_received;

        $index->save();

        $prDetail = PrDetail::find($index->pr_detail_id);

        $html = '
            There is a complain item, Item : '.$prDetail->item.'
        ';

        User::find($prDetail->purchasing_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.pr.confirm', ['f_id' => $prDetail->id]) ) );

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function getPr(Request $request)
    {
        $date = date('Y-m-d');
        if (isset($request->date) && $request->date != '') {
            $date = $request->date;
        }

        $user = Auth::user();
        if (isset($request->user_id) && $request->user_id != '') {
            $user = User::find($request->user_id);
        }

        $pr = Pr::select('no_pr')
            ->where('no_pr', 'like', date('y', strtotime($date)) . substr(strtolower($user->nickname), 0, 3) . "%")
            ->orderBy('no_pr', 'desc');

        $count   = $pr->count();
        $current = $pr->first();

        if ($count == 0) {
            $numberPr = 0;
        } else {
            $numberPr = intval(substr($current->no_pr, -4, 4));
        }

        return date('y', strtotime($date)) . substr(strtolower($user->nickname), 0, 3) . str_pad($numberPr + 1, 4, '0', STR_PAD_LEFT);
    }
}
