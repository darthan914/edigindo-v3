<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

use App\User;
use App\Config;

use App\Models\AccountClass;
use App\Models\AccountType;
use App\Models\AccountList;
use App\Models\AccountGeneral;
use App\Models\AccountGeneralDetail;
use App\Models\AccountSales;
use App\Models\AccountSalesDetail;
use App\Models\AccountBanking;
use App\Models\AccountBankingDetail;
use App\Models\AccountPurchasing;
use App\Models\AccountPurchasingDetail;

use App\Company;
use App\Spk;
use App\Supplier;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Validator;
use Datatables;
use PDF;

class AccountController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}

	public function accountClass(Request $request)
	{
		return view('backend.account.accountClass', compact('request'));
	}

	public function datatablesAccountClass(Request $request)
	{
		$index = AccountClass::all();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountClass-account')) {
                $html .= '
                   <button type="button" class="btn btn-xs btn-warning editAccountClass-account" data-toggle="modal" data-target="#editAccountClass-account" data-id="' . $index->id . '" data-name="' . $index->name . '"><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('deleteAccountClass-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountClass-account" data-toggle="modal" data-target="#deleteAccountClass-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
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

        $datatables = $datatables->make(true);
        return $datatables;
	}

	public function storeAccountClass(Request $request)
	{
		$message = [
            'id.required'           => 'This field required.',
            'id.unique'             => 'This id already exist.',
            'id.integer'            => 'integer only.',
            'name.required'         => 'This field required.',
            'name.unique'           => 'This name already exist.',
        ];

        $validator = Validator::make($request->all(), [
			'id' => 'required|unique:account_classes,id|integer',
			'name' => 'required|unique:account_classes,name',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('createAccountClass-account-error', 'Error')->withInput();
		}

		$index = new AccountClass;

		$index->id = $request->id; 
		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Added'); 
	}

	public function updateAccountClass(Request $request)
	{
		$index = AccountClass::find($request->id);

		$message = [
			'id.required'           => 'This field required.',
            'id.unique'             => 'This id already exist.',
            'id.integer'            => 'integer only.',
            'name.required'         => 'This field required.',
            'name.unique'           => 'This name already exist.',
        ];

        $validator = Validator::make($request->all(), [
        	'id' => 'required|integer|unique:account_classes,id,'.$request->id,
			'name' => 'required|unique:account_classes,name,'.$request->id,
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('editAccountClass-account-error', 'Error')->withInput();
		}

		$this->saveArchive('App\\Models\\AccountClass', 'UPDATED', $index);

		$index->id   = $request->id; 
		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
	}

	public function deleteAccountClass(Request $request)
	{
		$index = AccountClass::find($request->id);

		$this->saveArchive('App\\Models\\AccountClass', 'DELETED', $index);

		$index->delete();

		return redirect()->back()->with('success', 'Data Has Been Deleted'); 
	}

	public function actionAccountClass(Request $request)
    {
        if (is_array($request->id)) {

            if ($request->action == 'delete' && Auth::user()->can('deleteAccountClass-account')) {

                $index = AccountClass::find($request->id);
                $this->saveMultipleArchive('App\\Models\\AccountClass', 'DELETED', $index);

                AccountClass::destroy($request->id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function accountType(Request $request)
	{
		return view('backend.account.accountType', compact('request'));
	}

	public function datatablesAccountType(Request $request)
	{
		$index = AccountType::all();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountType-account')) {
                $html .= '
                   <button type="button" class="btn btn-xs btn-warning editAccountType-account" data-toggle="modal" data-target="#editAccountType-account" data-id="' . $index->id . '" data-name="' . $index->name . '"><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('deleteAccountType-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountType-account" data-toggle="modal" data-target="#deleteAccountType-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
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

        $datatables = $datatables->make(true);
        return $datatables;
	}

	public function storeAccountType(Request $request)
	{
		$message = [
            'name.required'         => 'This field required.',
            'name.unique'           => 'This name already exist.',
        ];

        $validator = Validator::make($request->all(), [
			'name' => 'required|unique:account_types,name',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('createAccountType-account-error', 'Error')->withInput();
		}

		$index = new AccountType;

		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Added'); 
	}

	public function updateAccountType(Request $request)
	{
		$index = AccountType::find($request->id);

		$message = [
            'name.required'         => 'This field required.',
            'name.unique'           => 'This name already exist.',
        ];

        $validator = Validator::make($request->all(), [
			'name' => 'required|unique:account_types,name,'.$request->id,
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('editAccountType-account-error', 'Error')->withInput();
		}

		$this->saveArchive('App\\Models\\AccountType', 'UPDATED', $index);

		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
	}

	public function deleteAccountType(Request $request)
	{
		$index = AccountType::find($request->id);

		$this->saveArchive('App\\Models\\AccountType', 'DELETED', $index);

		$index->delete();

		return redirect()->back()->with('success', 'Data Has Been Deleted'); 
	}

	public function actionAccountType(Request $request)
    {
        if (is_array($request->id)) {

            if ($request->action == 'delete' && Auth::user()->can('deleteAccountType-account')) {

                $index = AccountType::find($request->id);
                $this->saveMultipleArchive('App\\Models\\AccountType', 'DELETED', $index);

                AccountType::destroy($request->id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function accountList(Request $request)
	{
		$relation = ["PARENT" => "Set As Parent", "CHILD" => "Set As Child"];
		$account_class  = AccountClass::all();
		$account_type   = AccountType::all();
		$account_parent = AccountList::where('relation', 'PARENT')->where('active', 1)->get();
		$account_child  = AccountList::where('relation', 'CHILD')->where('active', 1)->get();

		return view('backend.account.accountList', compact('request', 'relation', 'account_class', 'account_type', 'account_parent', 'account_child'));
	}

	public function datatablesAccountList(Request $request)
	{
		$f_account_class = $this->filter($request->f_account_class);

		$index = AccountList::join('account_classes', 'account_lists.account_class_id', 'account_classes.id')
			->join('account_types', 'account_lists.account_type_id', 'account_types.id')
			->select(
                'account_lists.*',
                'account_classes.name AS account_class',
                'account_types.name AS account_type',
                DB::raw('
                    (CASE WHEN `relation` = "PARENT" THEN ( SELECT SUM(`node`.`account_balance`) FROM `account_lists` AS `node` WHERE `node`.`_lft` BETWEEN `account_lists`.`_lft` AND `account_lists`.`_rgt` ) ELSE `account_lists`.`account_balance` END ) AS `account_balance`'
                )
            )
            ->withDepth()
            ->orderBy('account_lists.account_class_id', 'ASC')
			->orderBy('account_lists.account_number', 'ASC')
		;

		if ($f_account_class != '') {
            $index->where('account_lists.account_class_id', $f_account_class);
        }

        $index = $index->get();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountList-account')) {
                $html .= '
                   <button type="button" class="btn btn-xs btn-warning editAccountList-account" data-toggle="modal" data-target="#editAccountList-account" 
	                   data-id="' . $index->id . '"
	                   data-relation="' . $index->relation . '"
	                   data-parent_id="' . $index->parent_id . '"
	                   data-account_class_id="' . $index->account_class_id . '"
	                   data-account_type_id="' . $index->account_type_id . '"
	                   data-account_number="' . $index->account_number . '"
	                   data-account_name="' . $index->account_name . '"
	                   data-account_balance="' . ($index->relation == "CHILD" ? $index->account_balance : ''). '"
	                   data-active="' . $index->active . '"
                   ><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('deleteAccountList-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountList-account" data-toggle="modal" data-target="#deleteAccountList-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            // if (Auth::user()->can('relationAccountList-account')) {
            //     $html .= '
            //        <button type="button" class="btn btn-xs btn-primary setParentAccountList-account" data-toggle="modal" data-target="#setParentAccountList-account" 
	           //         data-id="' . $index->id . '"
            //        ><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
            //     ';

            //     $html .= '
            //        <button type="button" class="btn btn-xs btn-primary setChildAccountList-account" data-toggle="modal" data-target="#setChildAccountList-account" 
	           //         data-id="' . $index->id . '"
            //        ><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
            //     ';
            // }

            if (Auth::user()->can('mergeAccountList-account')) {
                $html .= '
                   <button type="button" class="btn btn-xs btn-warning mergeAccountList-account" data-toggle="modal" data-target="#mergeAccountList-account" 
	                   data-id="' . $index->id . '"
                   ><i class="fa fa-compress" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('activeAccountList-account')) {

            	if($index->active)
            	{
            		$html .= '
	                   <button type="button" class="btn btn-xs btn-dark inactiveAccountList-account" data-toggle="modal" data-target="#inactiveAccountList-account" 
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
	                ';
            	}
            	else
            	{
            		$html .= '
	                   <button type="button" class="btn btn-xs btn-info activeAccountList-account" data-toggle="modal" data-target="#activeAccountList-account" 
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-eye" aria-hidden="true"></i></button>
	                ';
            	}
            }

            return $html;
        });

        $datatables->editColumn('active', function ($index) {
            $html = '';
            if ($index->active == 1) {
                $html .= '
                    <span class="label label-info">Active</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Inactive</span>
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

        $datatables->editColumn('account_balance', function ($index) {
            $html = number_format($index->account_balance, 2);

            return ($index->relation == "PARENT" ? '<b>'.$html.'</b>' : $html);
        });

        $datatables->editColumn('account_number', function ($index) {
            $html = str_repeat ( '&nbsp;' , $index->depth * 4 ) . $index->account_class_id . '-' . $index->account_number . ' ' . $index->account_name;

            return ($index->relation == "PARENT" ? '<b>'.$html.'</b>' : $html);
        });

        $datatables = $datatables->make(true);
        return $datatables;
	}

	public function storeAccountList(Request $request)
	{
        DB::transaction(function () use ($request) {
    		$message = [
                'relation.required'            => 'This field required.',
                'account_class_id.required_if' => 'This field required if set as root.',
                'account_type_id.required'     => 'This field required.',
                'account_number.required'      => 'This field required.',
                'account_name.required'        => 'This field required.',
                'account_balance.required_if'  => 'This field required if set as child.',
                'account_balance.numeric'      => 'This field numeric only.',
            ];

            $validator = Validator::make($request->all(), [
                'relation'         => 'required',
                'account_class_id' => 'required_if:parent_id,',
                'account_type_id'  => 'required',
                'account_number'   => 'required',
                'account_name'     => 'required',
                'account_balance'  => 'required_if:relation,CHILD|numeric|nullable',
            ], $message);

    		$validator->after(function ($validator) use ($request) {
                $check = AccountList::where('account_class_id', $request->account_class_id)
                	->where('account_number', $request->account_number)
                	->first();

                if ($check) {
                    $validator->errors()->add('account_number', 'This number already exist');
                }
            });

    		if ($validator->fails()) {
    			return redirect()->back()->withErrors($validator)->with('createAccountList-account-error', 'Error')->withInput();
    		}

    		$parent = AccountList::find($request->parent_id);

        
    		$index = new AccountList;

    		$index->relation         = $request->relation;
    		$index->parent_id        = $request->parent_id ?? null;
    		$index->account_class_id = $parent ? $parent->account_class_id : $request->account_class_id;
    		$index->account_type_id  = $request->account_type_id;
    		$index->account_number   = $request->account_number;
    		$index->account_name     = $request->account_name;
    		$index->account_balance  = $request->relation == "PARENT" ? null : $request->account_balance;
    		$index->active           = $request->active ? 1 : 0;

    		$index->save();
        });

		return redirect()->back()->with('success', 'Data Has Been Added'); 
	}

	public function updateAccountList(Request $request)
	{
		$parent = AccountList::find($request->parent_id);
		$index = AccountList::find($request->id);

		$message = [
            'relation.required'            => 'This field required.',
            'account_class_id.required_if' => 'This field required if set as root.',
            'account_type_id.required'     => 'This field required.',
            'account_number.required'      => 'This field required.',
            'account_name.required'        => 'This field required.',
            'account_balance.required_if'  => 'This field required if set as child.',
            'account_balance.numeric'      => 'This field numeric only.',
        ];

        $validator = Validator::make($request->all(), [
			'relation'         => 'required',
			'account_class_id' => 'required_if:parent_id,',
			'account_type_id'  => 'required',
			'account_number'   => 'required',
			'account_name'     => 'required',
			'account_balance'  => 'required_if:relation,CHILD|numeric|nullable',
		], $message);

		$validator->after(function ($validator) use ($request) {
            $check = AccountList::where('account_class_id', $request->account_class_id)
            	->where('account_number', $request->account_number)
            	->where('id', '<>', $request->id)
            	->first();

            if ($check) {
                $validator->errors()->add('account_number', 'This number already exist');
            }
        });

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('editAccountList-account-error', 'Error')->withInput();
		}

		$this->saveArchive('App\\Models\\AccountList', 'UPDATED', $index);

        DB::transaction(function () use ($request){
            
            $index = AccountList::find($request->id);
            $parent = AccountList::find($request->parent_id);

    		$index->relation         = $request->relation;
    		$index->parent_id        = $request->parent_id ?? null;
    		$index->account_class_id = $parent ? $parent->account_class_id : $request->account_class_id;
    		$index->account_type_id  = $request->account_type_id;
    		$index->account_number   = $request->account_number;
    		$index->account_name     = $request->account_name;
    		$index->account_balance  = $request->relation == "PARENT" ? null : $request->account_balance;
    		$index->active           = $request->active ? 1 : 0;

    		$index->save();
        });

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
	}

	public function deleteAccountList(Request $request)
	{
        DB::transaction(function () use ($request){

    		$index = AccountList::find($request->id);

    		$this->saveArchive('App\\Models\\AccountList', 'DELETED', $index);
        
    		$index->delete();
        });

		return redirect()->back()->with('success', 'Data Has Been Deleted'); 
	}

	public function actionAccountList(Request $request)
    {
        DB::transaction(function () use ($request){
            if (is_array($request->id)) {

            	switch ($request->action) {
            		case 'delete':
            			if (Auth::user()->can('deleteAccountList-account')) {

    		                $index = AccountList::find($request->id);
    		                $this->saveMultipleArchive('App\\Models\\AccountList', 'DELETED', $index);

    		                AccountList::destroy($request->id);
    		                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
    		            }
            			break;

            		case 'active':
            			if (Auth::user()->can('activeAccountList-account')) {

    		                $index = AccountList::find($request->id);
    		                $this->saveMultipleArchive('App\\Models\\AccountList', 'ACTIVE', $index);

    		                AccountList::whereIn('id', $request->id)->update(['active' => 1]);
    		                return redirect()->back()->with('success', 'Data Selected Has Been Active');
    		            }
            			break;

            		case 'inactive':
            			if (Auth::user()->can('activeAccountList-account')) {

    		                $index = AccountList::find($request->id);
    		                $this->saveMultipleArchive('App\\Models\\AccountList', 'INACTIVE', $index);

    		                AccountList::whereIn('id', $request->id)->update(['active' => 0]);
    		                return redirect()->back()->with('success', 'Data Selected Has Been Inactive');
    		            }
            			break;
            		
            		default:
            			return redirect()->back()->with('info', 'No data change');
            	}
            }
        });

        return redirect()->back()->with('info', 'No data change');
    }

    public function activeAccountList(Request $request)
    {
        DB::transaction(function () use ($request){
        	$index = AccountList::find($request->id);

        	if($index->active)
        	{
        		$this->saveArchive('App\\Models\\AccountList', 'INACTIVE', $index);
    	        AccountList::where('id', $request->id)->update(['active' => 0]);
    	        return redirect()->back()->with('success', 'Data Selected Has Been Inactive');
        	}
        	else
        	{
        		$this->saveArchive('App\\Models\\AccountList', 'ACTIVE', $index);
    	        AccountList::where('id', $request->id)->update(['active' => 1]);
    	        return redirect()->back()->with('success', 'Data Selected Has Been Active');
        	}
        });
    }

    public function setChildAccountList(Request $request)
    {
        DB::transaction(function () use ($request){
        	$index = AccountList::find($request->id);

    		$message = [
                'parent_id.required_if'     => 'This field required.',
                'account_balance.required'  => 'This field required.',
                'account_balance.numeric'   => 'This field numeric only.',
            ];

            $validator = Validator::make($request->all(), [
    			'parent_id' => 'required',
    			'account_balance' => 'required|numeric',
    		], $message);


    		if ($validator->fails()) {
    			return redirect()->back()->withErrors($validator)->with('setChildAccountList-account-error', 'Error')->withInput();
    		}

    		$this->saveArchive('App\\Models\\AccountList', 'SET_CHILD', $index);

    		$index->relation         = "CHILD";
    		$index->parent_id        = $request->parent_id;
    		$index->account_balance  = $request->account_balance;

    		$index->save();
        });

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function setParentAccountList(Request $request)
    {
        DB::transaction(function () use ($request){
        	$index = AccountList::find($request->id);

    		$this->saveArchive('App\\Models\\AccountList', 'SET_CHILD', $index);

    		$index->relation         = "PARENT";
    		$index->parent_id        = null;
    		$index->account_balance  = null;

    		$index->save();
        });

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function mergeAccountList(Request $request)
    {
        DB::transaction(function () use ($request){
        	$message = [
                'from_id.required' => 'This field required.',
                'to_id.required'   => 'This field required.',
            ];

            $validator = Validator::make($request->all(), [
    			'from_id' => 'required',
    			'to_id'     => 'required',
    		], $message);

    		$validator->after(function ($validator) use ($request) {
                if ($request->from_id == $request->to_id) {
                    $validator->errors()->add('to_id', 'Cannot merge with same account');
                }
            });

    		if ($validator->fails()) {
    			return redirect()->back()->withErrors($validator)->with('mergeAccountList-account-error', 'Error')->withInput();
    		}

	    	$from = AccountList::find($request->from_id);
	    	$this->saveArchive('App\\Models\\AccountList', 'MERGE_FROM', $from);

	    	$to   = AccountList::find($request->to_id);
	    	$this->saveArchive('App\\Models\\AccountList', 'MERGE_TO', $to);

	    	$to->account_balance = $to->account_balance + $from->account_balance;
	    	$to->save();

	    	$from->delete();
        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function AccountJournal(Request $request)
    {
        return view('backend.account.accountJournal', compact('request'));
    }

    public function datatablesAccountGeneral(Request $request)
    {
        $f_account_class = $this->filter($request->f_account_class);

        $index = AccountGeneral::orderBy('account_generals.date', 'DESC');

        $account_lists = AccountList::where('active', 1)->get();

        $account_lists_array = [];

        foreach ($account_lists as $list) {
            $account_lists_array[$list->id] = $list->account_name;
        }

        if ($f_account_class != '') {
            $index->where('account_lists.account_class_id', $f_account_class);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountGeneral-account')) {
                $html .= '
                   <a href="'.route('backend.account.editAccountGeneral', $index->id).'" class="btn btn-xs btn-warning editAccountGeneral-account"><i class="fa fa-edit" aria-hidden="true"></i></a>
                ';
            }

            if (Auth::user()->can('deleteAccountGeneral-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountGeneral-account" data-toggle="modal" data-target="#deleteAccountGeneral-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
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

        $datatables->editColumn('detail', function ($index) {
            $detail = $index->account_general_details;
            return view('backend.account.general.json', compact('detail'));
        });

        $datatables->editColumn('date', function ($index) {
            $html = date('d/m/Y', strtotime($index->date));

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function createAccountGeneral(Request $request)
    {
        return view('backend.account.general.create', compact('request'));
    }

    public function storeAccountGeneral(Request $request)
    {
        
        $message = [
            'date.required'             => 'This field required.',
            'date.date'                 => 'Date Format Only',
        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ], $message);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new AccountGeneral;

        $index->date        = $request->date;
        $index->note        = $request->note;

        $index->save();

        return redirect()->route('backend.account.editAccountGeneral', ['id' => $index->id]); 
    }

    public function editAccountGeneral(Request $request, $id)
    {
        $index = AccountGeneral::find($id);
        $detail = AccountGeneralDetail::where('account_general_id', $id)->get();

        $account_lists = AccountList::where('active', 1)->get();

        return view('backend.account.general.edit', compact('request', 'account_lists', 'index', 'detail'));
    }

    public function updateAccountGeneral(Request $request, $id)
    {
        $message = [
            'date.required'             => 'This field required.',
            'date.date'                 => 'Date Format Only',
        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $id) {
            $index = AccountGeneral::find($id);

            $this->saveArchive('App\\Models\\AccountGeneral', 'UPDATED', $index);

            $index->date        = $request->date;
            $index->note        = $request->note;

            $index->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function storeAccountGeneralDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'debit.required_without'           => 'This field required.',
            'credit.required_without'          => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'debit'           => 'required_without:credit',
            'credit'          => 'required_without:debit',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($request->debit && $request->credit) {
                $validator->errors()->add('debit', 'Only One Can Fill');
                $validator->errors()->add('credit', 'Only One Can Fill');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
        }

        DB::transaction(function () use ($request) {

            $index = new AccountGeneralDetail;

            $index->account_general_id = $request->account_general_id;
            $index->account_list_id    = $request->account_list_id;
            $index->debit              = $request->debit ?? 0;
            $index->credit             = $request->credit ?? 0;
            $index->ppn                = $request->ppn ?? 0;
            $index->note               = $request->note;

            $index->save();

            $header = AccountGeneral::find($index->account_general_id);
            $this->saveArchive('App\\Models\\AccountGeneral', 'UPDATED', $header);

            if($header->account_general_details()->sum(DB::raw('debit * (1 + (ppn/100))')) == $header->account_general_details()->sum(DB::raw('credit * (1 + (ppn/100))')))
            {
                $header->status = "OK";
            }
            else
            {
                $header->status = "DRAFTED";
            }
            
            $header->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Added'); 
    }

    public function updateAccountGeneralDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'debit.required_without'           => 'This field required.',
            'credit.required_without'          => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'debit'           => 'required_without:credit',
            'credit'          => 'required_without:debit',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($request->debit && $request->credit) {
                $validator->errors()->add('debit', 'Only One Can Fill');
                $validator->errors()->add('credit', 'Only One Can Fill');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-detail-error', '');
        }

        DB::transaction(function () use ($request) {

            $index = AccountGeneralDetail::find($request->id);
            $this->saveArchive('App\\Models\\AccountGeneralDetail', 'UPDATED', $index);

            $index->account_list_id    = $request->account_list_id;
            $index->debit              = $request->debit;
            $index->credit             = $request->credit;
            $index->ppn                = $request->ppn ?? 0;
            $index->note               = $request->note;

            $index->save();

            $header = AccountGeneral::find($index->account_general_id);
            $this->saveArchive('App\\Models\\AccountGeneral', 'UPDATED', $header);

            if($header->account_general_details()->sum(DB::raw('debit * (1 + (ppn/100))')) == $header->account_general_details()->sum(DB::raw('credit * (1 + (ppn/100))')))
            {
                $header->status = "OK";
            }
            else
            {
                $header->status = "DRAFTED";
            }
            
            $header->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function deleteAccountGeneralDetail(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountGeneralDetail::find($request->id);

            $this->saveArchive('App\\Models\\AccountGeneralDetail', 'DELETED', $index);

            $index->delete();

            $header = AccountGeneral::find($index->account_general_id);
            $this->saveArchive('App\\Models\\AccountGeneral', 'UPDATED', $header);

            if($header->account_general_details()->sum(DB::raw('debit * (1 + (ppn/100))')) == $header->account_general_details()->sum(DB::raw('credit * (1 + (ppn/100))')))
            {
                $header->status = "OK";
            }
            else
            {
                $header->status = "DRAFTED";
            }
            
            $header->save();
        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function deleteAccountGeneral(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountGeneral::find($request->id);

            $this->saveArchive('App\\Models\\AccountGeneral', 'DELETED', $index);
        
            $index->delete();
        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function actionAccountGeneral(Request $request)
    {
        DB::transaction(function () use ($request){
            if (is_array($request->id)) {

                switch ($request->action) {
                    case 'delete':
                        if (Auth::user()->can('deleteAccountGeneral-account')) {

                            $index = AccountGeneral::find($request->id);
                            $this->saveMultipleArchive('App\\Models\\AccountGeneral', 'DELETED', $index);

                            AccountGeneral::destroy($request->id);
                            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
                        }
                        break;
                    
                    default:
                        return redirect()->back()->with('info', 'No data change');
                }
            }
        });

        return redirect()->back()->with('info', 'No data change');
    }

    public function accountSales(Request $request)
    {
        $account_lists = AccountList::where('active', 1)->get();

        return view('backend.account.accountSales', compact('request', 'account_lists'));
    }

    public function datatablesAccountSales(Request $request)
    {
        $f_status = $this->filter($request->f_status);

        $index = AccountSales::join('company', 'company.id', 'account_sales.company_id')
            ->join('spk', 'spk.id', 'account_sales.spk_id')
            ->join('users', 'users.id', 'account_sales.sales_id')
            ->select('account_sales.*', 'spk.spk', 'spk.name as spk_project','company.name as company_name', 'users.fullname as sales_fullname')
            ->orderBy('account_sales.date', 'DESC');

        $account_lists = AccountList::join('account_classes', 'account_lists.account_class_id', 'account_classes.id')
            ->join('account_types', 'account_lists.account_type_id', 'account_types.id')
            ->select(
                'account_lists.*',
                'account_classes.name AS account_class',
                'account_types.name AS account_type',
                DB::raw('
                    (CASE WHEN `relation` = "PARENT" THEN ( SELECT SUM(`node`.`account_balance`) FROM `account_lists` AS `node` WHERE `node`.`_lft` BETWEEN `account_lists`.`_lft` AND `account_lists`.`_rgt` ) ELSE `account_lists`.`account_balance` END ) AS `account_balance`'
                )
            )
            ->withDepth()
            ->where('active', 1)
            ->orderBy('account_lists.account_class_id', 'ASC')
            ->orderBy('account_lists.account_number', 'ASC')
            ->get();

        $account_lists_array = [];
        $account_lists_price_array = [];

        foreach ($account_lists as $list) {
            $account_lists_array[$list->id] = $list->account_name;
            $account_lists_price_array[$list->id] = $list->account_balance;
        }


        if($f_status)
        {
            switch ($f_status) {
                case 'ORDER':
                    $index->whereNotNull('datetime_order')->whereNull('datetime_invoice')->whereNull('datetime_closed');
                    break;
                case 'INVOICE':
                    $index->whereNotNull('datetime_order')->whereNotNull('datetime_invoice')->whereNull('datetime_closed');
                    break;
                case 'CLOSED':
                    $index->whereNotNull('datetime_order')->whereNotNull('datetime_invoice')->whereNotNull('datetime_closed');
                    break;
                default:
                    break;
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if($index->datetime_closed == null)
            {
                if (Auth::user()->can('editAccountSales-account')) {
                    $html .= '
                       <a href="'.route('backend.account.editAccountSales', $index->id).'" class="btn btn-xs btn-warning editAccountSales-account"><i class="fa fa-edit" aria-hidden="true"></i></a>
                    ';
                }

                if (Auth::user()->can('deleteAccountSales-account')) {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-danger deleteAccountSales-account" data-toggle="modal" data-target="#deleteAccountSales-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    ';
                }

                
            }

            $html .= '
                <button type="button" class="btn btn-xs btn-primary pdfAccountSales-account" data-toggle="modal" data-target="#pdfAccountSales-account" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
            ';

            if (Auth::user()->can('statusAccountSales-account')) {

                if($index->datetime_order != null && $index->datetime_invoice == null && $index->datetime_closed == null)
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-primary statusAccountSales-account" data-toggle="modal" data-target="#statusAccountSales-account" data-id="' . $index->id . '" data-status="INVOICE">Set Invoice</button>
                    ';
                }
                else if ($index->datetime_order != null && $index->datetime_invoice != null && $index->datetime_closed == null)
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-primary statusClosedAccountSales-account" data-toggle="modal" data-target="#statusClosedAccountSales-account" data-id="' . $index->id . '" data-status="CLOSED">Set Closed</button>
                    ';
                }
                
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

        $datatables->addColumn('detail', function ($index) {
            $detail = $index->account_sales_details;
            return view('backend.account.sales.json', compact('detail'));
        });

        $datatables->editColumn('date', function ($index) {
            $html = date('d/m/Y', strtotime($index->date));

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function createAccountSales(Request $request)
    {
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();

        $company = Company::all();
        $spk = Spk::all();
        $sales = User::whereIn('position', explode(', ', $sales_position->value))->orWhereIn('id', explode(', ', $sales_user->value))->get();

        return view('backend.account.sales.create', compact('request', 'company', 'spk', 'sales', 'account_lists'));
    }

    public function storeAccountSales(Request $request)
    {
        $message = [
            'company_id.required' => 'This field required.',
            'spk_id.required' => 'This field required.',
            'invoice.required' => 'This field required.',
            'sales_id.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'company_id' => 'required',
            'spk_id' => 'required',
            'invoice' => 'required',
            'sales_id' => 'required',

        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new AccountSales;

        $index->company_id     = $request->company_id;
        $index->invoice        = $request->invoice;
        $index->date           = $request->date;
        $index->spk_id         = $request->spk_id;
        $index->sales_id       = $request->sales_id;
        $index->note           = $request->note_header;
        $index->datetime_order = date('Y-m-d H:i:s');

        $index->save();

        return redirect()->route('backend.account.editAccountSales', ['id' => $index->id]); 
    }

    public function editAccountSales(Request $request, $id)
    {
        $index = AccountSales::find($id);
        $detail = AccountSalesDetail::where('account_sales_id', $id)->get();

        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();

        $company = Company::all();
        $spk = Spk::all();
        $sales = User::whereIn('position', explode(', ', $sales_position->value))->orWhereIn('id', explode(', ', $sales_user->value))->get();

        return view('backend.account.sales.edit', compact('index', 'request', 'company', 'spk', 'sales', 'account_lists', 'detail'));
    }

    public function updateAccountSales(Request $request, $id)
    {
        $message = [
            'company_id.required' => 'This field required.',
            'spk_id.required' => 'This field required.',
            'invoice.required' => 'This field required.',
            'sales_id.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'company_id' => 'required',
            'spk_id' => 'required',
            'invoice' => 'required',
            'sales_id' => 'required',

        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $id, $json_detail, $total_debit, $total_credit) {
            $index = AccountGeneral::find($id);

            $this->saveArchive('App\\Models\\AccountSales', 'UPDATED', $index);

            $index->company_id     = $request->company_id;
            $index->invoice        = $request->invoice;
            $index->date           = $request->date;
            $index->spk_id         = $request->spk_id;
            $index->sales_id       = $request->sales_id;
            $index->note           = $request->note_header;

            $index->save();
        });

        return redirect()->route('backend.account.accountSales', ['tab' => 'ORDER'])->with('success', 'Data Has Been Updated'); 
    }

    public function storeAccountSalesDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
            'qty.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
            'qty' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
        }

        $index = new AccountSalesDetail;

        $index->account_sales_id = $request->account_sales_id;
        $index->account_list_id  = $request->account_list_id;
        $index->price            = $request->price;
        $index->qty              = $request->qty;
        $index->discount         = $request->discount ?? 0;
        $index->ppn              = $request->ppn ?? 0;
        $index->note             = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Added'); 
    }

    public function updateAccountSalesDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
            'qty.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
            'qty' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-detail-error', '');
        }

        DB::transaction(function () use ($request) {

            $index = AccountSalesDetail::find($request->id);
            $this->saveArchive('App\\Models\\AccountSalesDetail', 'UPDATED', $index);

            $index->account_list_id  = $request->account_list_id;
            $index->price            = $request->price;
            $index->qty              = $request->qty;
            $index->discount         = $request->discount ?? 0;
            $index->ppn              = $request->ppn ?? 0;
            $index->note             = $request->note;

            $index->save();

        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function deleteAccountSalesDetail(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountSalesDetail::find($request->id);

            $this->saveArchive('App\\Models\\AccountSalesDetail', 'DELETED', $index);

            $index->delete();

        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }


    public function statusAccountSales(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
        ], $message);

        if ($validator->fails() && $request->status == "CLOSED") {
            return redirect()->back()->withErrors($validator)->withInput()->with('status-closed-crm-error', 'Something Errors');
        }


        DB::transaction(function () use ($request) {
            $index = AccountSales::find($request->id);

            $this->saveArchive('App\\Models\\AccountSales', 'UPDATED', $index);

            if($request->status == "INVOICE")
            {
                $index->datetime_invoice = date('Y-m-d H:i:s');
            }

            else if($request->status == "CLOSED")
            {
                $index->account_list_id = $request->account_list_id;
                $index->datetime_closed = date('Y-m-d H:i:s');
            }
            

            $index->save();
        });

        return redirect()->route('backend.account.accountSales', ['tab' => 'ORDER'])->with('success', 'Data Has Been Updated');
    }

    public function deleteAccountSales(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountSales::find($request->id);

            $this->saveArchive('App\\Models\\AccountSales', 'DELETED', $index);
        
            $index->delete();
        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function actionAccountSales(Request $request)
    {
        DB::transaction(function () use ($request){
            if (is_array($request->id)) {

                switch ($request->action) {
                    case 'delete':
                        if (Auth::user()->can('deleteAccountSales-account')) {

                            $index = AccountSales::find($request->id);
                            $this->saveMultipleArchive('App\\Models\\AccountSales', 'DELETED', $index);

                            AccountSales::destroy($request->id);
                            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
                        }
                        break;
                    
                    default:
                        return redirect()->back()->with('info', 'No data change');
                }
            }
        });

        return redirect()->back()->with('info', 'No data change');
    }

    public function pdfAccountSales(Request $request)
    {

        $message = [
            'size.required'        => 'This field required.',
            'orientation.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-error', 'Something Errors');
        }

        $index = AccountSales::find($request->id);

        $pdf = PDF::loadView('backend.account.sales.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream('Sales-'.date('Y-m-d H:i:s') . '.pdf');
    }

    public function accountBanking(Request $request)
    {
        $account_lists = AccountList::where('active', 1)->get();

        return view('backend.account.accountBanking', compact('request', 'account_lists'));
    }

    public function datatablesAccountBanking(Request $request)
    {
        $index = AccountBanking::join('account_lists', 'account_lists.id', 'account_banking.account_list_id')
            ->select('account_banking.*', 'account_lists.account_name')
            ->orderBy('account_banking.date', 'DESC');

        $account_lists = AccountList::where('active', 1)->get();

        $account_lists_array = [];

        foreach ($account_lists as $list) {
            $account_lists_array[$list->id] = $list->account_name;
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountBanking-account')) {
                $html .= '
                   <a href="'.route('backend.account.editAccountBanking', $index->id).'" class="btn btn-xs btn-warning editAccountSales-account"><i class="fa fa-edit" aria-hidden="true"></i></a>
                ';
            }

            if (Auth::user()->can('deleteAccountBanking-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountBanking-account" data-toggle="modal" data-target="#deleteAccountBanking-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            $html .= '
                        <button type="button" class="btn btn-xs btn-primary pdfAccountBanking-account" data-toggle="modal" data-target="#pdfAccountBanking-account" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
                    ';

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables->editColumn('detail', function ($index) {
            $detail = $index->account_banking_details;
            return view('backend.account.banking.json', compact('detail'));
        });

        $datatables->editColumn('date', function ($index) {
            $html = date('d/m/Y', strtotime($index->date));

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function createAccountBanking(Request $request)
    {
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();


        return view('backend.account.banking.create', compact('request', 'account_lists'));
    }

    public function storeAccountBanking(Request $request)
    {
        
        $message = [
            'account_list_id_header.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

            'account_list_id.*.required' => 'This field required',
            'price.*.numeric' => 'Numeric Only',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id_header' => 'required',
            'date' => 'required|date',

            'account_list_id.*' => 'required',
            'price.*' => 'nullable|numeric',
        ], $message);

        $total_price = 0;
        $total_ppn = 0;
        for ($i=0; $i < count($request->account_list_id); $i++) { 
            $total_price += $request->price[$i];
            $total_ppn += $request->price[$i] * (($request->ppn [$i] ?? 0) / 100);
        }


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(compact('total_price', 'total_ppn'))->withInput();
        }

        $json_detail = [];
        for ($i=0; $i < count($request->account_list_id); $i++) { 
            $json_detail[] = [
                'account_list_id' => $request->account_list_id[$i],
                'note'             => $request->note[$i],
                'price'            => $request->price[$i],
                'ppn'              => $request->ppn[$i] ?? 0,
            ];
        }

        $index = new AccountBanking;

        $index->account_list_id = $request->account_list_id_header;
        $index->date            = $request->date;
        $index->note            = $request->note_header;
        $index->json_detail     = json_encode($json_detail);
        $index->total_price     = $total_price + $total_ppn;

        $index->save();

        return redirect()->route('backend.account.editAccountBanking', ['id' => $index->id])->with('success', 'Data Has Been Added'); 
    }

    public function editAccountBanking(Request $request, $id)
    {
        $index = AccountBanking::find($id);
        $detail = $index->account_banking_details;

        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();

        return view('backend.account.banking.edit', compact('index', 'request', 'account_lists', 'detail'));
    }

    public function updateAccountBanking(Request $request, $id)
    {
        $message = [
            'account_list_id_header.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

            'account_list_id.*.required' => 'This field required',
            'price.*.numeric' => 'Numeric Only',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id_header' => 'required',
            'date' => 'required|date',

            'account_list_id.*' => 'required',
            'price.*' => 'nullable|numeric',
        ], $message);


        $array = $request->account_list_id;

        $total_price = $total_ppn = 0;

        while ($current = current($array)) {
            $key = key($array);


            $total_price += $request->price[$key];
            $total_ppn += $request->price[$key] * (($request->ppn [$key] ?? 0) / 100);

            next($array);
        }


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(compsct('total_price', 'total_ppn'))->withInput();
        }

        $json_detail = [];

        reset($array);
        while ($current = current($array)) {
            $key = key($array);
            
            $json_detail[] = [
                'account_list_id' => $request->account_list_id[$key],
                'note'             => $request->note[$key],
                'price'            => $request->price[$key],
                'ppn'              => $request->ppn[$key] ?? 0,
            ];

            next($array);
        }

        DB::transaction(function () use ($request, $id, $json_detail, $total_price, $total_ppn) {
            $index = AccountBanking::find($id);

            $this->saveArchive('App\\Models\\AccountBanking', 'UPDATED', $index);

            $index->account_list_id = $request->account_list_id_header;
            $index->date            = $request->date;
            $index->note            = $request->note_header;
            $index->json_detail     = json_encode($json_detail);
            $index->total_price     = $total_price + $total_ppn;

            $index->save();
        });

        return redirect()->route('backend.account.accountBanking')->with('success', 'Data Has Been Updated'); 
    }

    public function storeAccountBankingDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
        }

        $index = new AccountBankingDetail;

        $index->account_banking_id = $request->account_banking_id;
        $index->account_list_id  = $request->account_list_id;
        $index->price            = $request->price;
        $index->ppn              = $request->ppn ?? 0;
        $index->note             = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Added'); 
    }

    public function updateAccountBankingDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-detail-error', '');
        }

        DB::transaction(function () use ($request) {

            $index = AccountBankingDetail::find($request->id);
            $this->saveArchive('App\\Models\\AccountBankingDetail', 'UPDATED', $index);

            $index->account_list_id  = $request->account_list_id;
            $index->price            = $request->price;
            $index->ppn              = $request->ppn ?? 0;
            $index->note             = $request->note;

            $index->save();

        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function deleteAccountBankingDetail(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountBankingDetail::find($request->id);

            $this->saveArchive('App\\Models\\AccountBankingDetail', 'DELETED', $index);

            $index->delete();

        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function deleteAccountBanking(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountBanking::find($request->id);

            $this->saveArchive('App\\Models\\AccountBanking', 'DELETED', $index);
        
            $index->delete();
        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function actionAccountBanking(Request $request)
    {
        DB::transaction(function () use ($request){
            if (is_array($request->id)) {

                switch ($request->action) {
                    case 'delete':
                        if (Auth::user()->can('deleteAccountBanking-account')) {

                            $index = AccountBanking::find($request->id);
                            $this->saveMultipleArchive('App\\Models\\AccountBanking', 'DELETED', $index);

                            AccountBanking::destroy($request->id);
                            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
                        }
                        break;
                    
                    default:
                        return redirect()->back()->with('info', 'No data change');
                }
            }
        });

        return redirect()->back()->with('info', 'No data change');
    }

    public function pdfAccountBanking(Request $request)
    {

        $message = [
            'size.required'        => 'This field required.',
            'orientation.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-error', 'Something Errors');
        }

        $index = AccountBanking::find($request->id);

        $pdf = PDF::loadView('backend.account.banking.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream('Banking-'.date('Y-m-d H:i:s') . '.pdf');
    }

    public function accountPurchasing(Request $request)
    {
        $account_lists = AccountList::where('active', 1)->get();

        return view('backend.account.accountPurchasing', compact('request', 'account_lists'));
    }

    public function datatablesAccountPurchasing(Request $request)
    {
        $f_status = $this->filter($request->f_status);

        $index = AccountPurchasing::join('company', 'company.id', 'account_purchasing.company_id')
            ->join('spk', 'spk.id', 'account_purchasing.spk_id')
            ->join('supplier', 'supplier.id', 'account_purchasing.supplier_id')
            ->select('account_purchasing.*', 'spk.spk', 'spk.name as spk_project','company.name as company_name', 'supplier.name as supplier_name')
            ->orderBy('account_purchasing.date', 'DESC');

        $account_lists = AccountList::join('account_classes', 'account_lists.account_class_id', 'account_classes.id')
            ->join('account_types', 'account_lists.account_type_id', 'account_types.id')
            ->select(
                'account_lists.*',
                'account_classes.name AS account_class',
                'account_types.name AS account_type',
                DB::raw('
                    (CASE WHEN `relation` = "PARENT" THEN ( SELECT SUM(`node`.`account_balance`) FROM `account_lists` AS `node` WHERE `node`.`_lft` BETWEEN `account_lists`.`_lft` AND `account_lists`.`_rgt` ) ELSE `account_lists`.`account_balance` END ) AS `account_balance`'
                )
            )
            ->withDepth()
            ->where('active', 1)
            ->orderBy('account_lists.account_class_id', 'ASC')
            ->orderBy('account_lists.account_number', 'ASC')
            ->get();

        $account_lists_array = [];
        $account_lists_price_array = [];

        foreach ($account_lists as $list) {
            $account_lists_array[$list->id] = $list->account_name;
            $account_lists_price_array[$list->id] = $list->account_balance;
        }


        if($f_status)
        {
            switch ($f_status) {
                case 'ORDER':
                    $index->whereNotNull('datetime_order')->whereNull('datetime_open_bill')->whereNull('datetime_debit')->whereNull('datetime_closed');
                    break;
                case 'OPEN_BILL':
                    $index->whereNotNull('datetime_order')->whereNotNull('datetime_open_bill')->whereNull('datetime_debit')->whereNull('datetime_closed');
                    break;
                case 'DEBIT':
                    $index->whereNotNull('datetime_order')->whereNotNull('datetime_open_bill')->whereNotNull('datetime_debit')->whereNull('datetime_closed');
                    break;
                case 'CLOSED':
                    $index->whereNotNull('datetime_order')->whereNotNull('datetime_open_bill')->whereNotNull('datetime_debit')->whereNotNull('datetime_closed');
                    break;
                default:
                    break;
            }
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editAccountPurchasing-account')) {
                $html .= '
                   <a href="'.route('backend.account.editAccountPurchasing', $index->id).'" class="btn btn-xs btn-warning editAccountPurchasing-account"><i class="fa fa-edit" aria-hidden="true"></i></a>
                ';
            }

            if (Auth::user()->can('deleteAccountPurchasing-account')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteAccountPurchasing-account" data-toggle="modal" data-target="#deleteAccountPurchasing-account" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            $html .= '
                <button type="button" class="btn btn-xs btn-primary pdfAccountPurchasing-account" data-toggle="modal" data-target="#pdfAccountPurchasing-account" data-id="' . $index->id . '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
            ';

            if (Auth::user()->can('statusAccountPurchasing-account')) {

                if($index->datetime_order != null && $index->datetime_open_bill == null && $index->datetime_debit == null && $index->datetime_closed == null)
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-primary statusAccountPurchasing-account" data-toggle="modal" data-target="#statusAccountPurchasing-account" data-id="' . $index->id . '" data-status="OPEN_BILL">Set Open Bill</button>
                    ';
                }
                else if ($index->datetime_order != null && $index->datetime_open_bill != null && $index->datetime_debit == null && $index->datetime_closed == null)
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-primary statusAccountPurchasing-account" data-toggle="modal" data-target="#statusAccountPurchasing-account" data-id="' . $index->id . '" data-status="DEBIT">Set Debit Return</button>
                    ';
                }
                else if ($index->datetime_order != null && $index->datetime_open_bill != null && $index->datetime_debit != null && $index->datetime_closed == null)
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-primary statusClosedAccountPurchasing-account" data-toggle="modal" data-target="#statusClosedAccountPurchasing-account" data-id="' . $index->id . '" data-status="CLOSED">Set Closed</button>
                    ';
                }
                
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

        $datatables->editColumn('detail', function ($index) {
            $detail = $index->account_purchasing_details;
            return view('backend.account.purchasing.json', compact('detail'));
        });

        $datatables->editColumn('date', function ($index) {
            $html = date('d/m/Y', strtotime($index->date));

            return $html;
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function createAccountPurchasing(Request $request)
    {
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();

        $company = Company::all();
        $spk = Spk::all();
        $supplier = Supplier::all();

        return view('backend.account.purchasing.create', compact('request', 'company', 'spk', 'supplier', 'account_lists'));
    }

    public function storeAccountPurchasing(Request $request)
    {
        
        $message = [
            'company_id.required' => 'This field required.',
            'spk_id.required' => 'This field required.',
            'no_pr.required' => 'This field required.',
            'supplier_id.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

            'account_list_id.*.required' => 'This field required',
            'qty.*.numeric' => 'Numeric Only',
            'price.*.numeric' => 'Numeric Only',
        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'company_id' => 'required',
            'spk_id' => 'required',
            'no_pr' => 'required',
            'supplier_id' => 'required',

            'account_list_id.*' => 'required',
            'qty.*' => 'nullable|numeric',
            'price.*' => 'nullable|numeric',
        ], $message);

        $total_price = 0;
        $total_ppn = 0;
        for ($i=0; $i < count($request->account_list_id); $i++) { 
            $total_price += $request->price[$i] * ($request->discount[$i] / 100);
            $total_ppn += $request->price[$i] * ($request->discount[$i] / 100) * (($request->ppn [$i] ?? 0) / 100);
        }


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(compact('total_price', 'total_ppn'))->withInput();
        }

        $json_detail = [];
        for ($i=0; $i < count($request->account_list_id); $i++) { 
            $json_detail[] = [
                'qty'              => $request->qty[$i],
                'account_list_id' => $request->account_list_id[$i],
                'note'             => $request->note[$i],
                'price'            => $request->price[$i],
                'discount'         => $request->discount[$i] ?? 0,
                'ppn'              => $request->ppn[$i] ?? 0,
            ];
        }

        DB::transaction(function () use ($request, $json_detail, $total_price, $total_ppn) {
            $index = new AccountPurchasing;

            $index->company_id     = $request->company_id;
            $index->no_pr          = $request->no_pr;
            $index->date           = $request->date;
            $index->spk_id         = $request->spk_id;
            $index->supplier_id    = $request->supplier_id;
            $index->note           = $request->note_header;
            $index->json_detail    = json_encode($json_detail);
            $index->total_price    = $total_price;
            $index->total_ppn      = $total_ppn;
            $index->datetime_order = date('Y-m-d H:i:s');

            $index->save();
        });

        return redirect()->route('backend.account.accountPurchasing', ['tab' => 'ORDER'])->with('success', 'Data Has Been Added'); 
    }

    public function editAccountPurchasing(Request $request, $id)
    {
        $index = AccountPurchasing::find($id);
        $detail = $index->account_purchasing_details;

        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }
        $account_lists = AccountList::where('active', 1)->get();

        $company = Company::all();
        $spk = Spk::all();
        $supplier = Supplier::all();

        return view('backend.account.purchasing.edit', compact('index', 'request', 'company', 'spk', 'supplier', 'account_lists', 'detail'));
    }

    public function updateAccountPurchasing(Request $request, $id)
    {
        $message = [
            'company_id.required' => 'This field required.',
            'spk_id.required' => 'This field required.',
            'no_pr.required' => 'This field required.',
            'supplier_id.required' => 'This field required.',
            'date.required' => 'This field required.',
            'date.date'     => 'Date Format Only',

            'account_list_id.*.required' => 'This field required',
            'qty.*.numeric' => 'Numeric Only',
            'price.*.numeric' => 'Numeric Only',
        ];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'company_id' => 'required',
            'spk_id' => 'required',
            'no_pr' => 'required',
            'supplier_id' => 'required',

            'account_list_id.*' => 'required',
            'qty.*' => 'nullable|numeric',
            'price.*' => 'nullable|numeric',
        ], $message);

        $total_debit = 0;
        $total_credit = 0;

        $array = $request->account_list_id;

        while ($current = current($array)) {
            $key = key($array);


            $total_price += $request->price[$key] * ($request->discount[$key] / 100);
            $total_ppn += $request->price[$key] * ($request->discount[$key] / 100) * (($request->ppn [$key] ?? 0) / 100);

            next($array);
        }


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(compsct('total_price', 'total_ppn'))->withInput();
        }

        $json_detail = [];

        reset($array);
        while ($current = current($array)) {
            $key = key($array);
            
            $json_detail[] = [
                'qty'              => $request->qty[$key],
                'account_list_id'  => $request->account_list_id[$key],
                'note'             => $request->note[$key],
                'price'            => $request->price[$key],
                'discount'         => $request->discount[$key] ?? 0,
                'ppn'              => $request->ppn[$key] ?? 0,
            ];

            next($array);
        }

        DB::transaction(function () use ($request, $id, $json_detail, $total_debit, $total_credit) {
            $index = AccountGeneral::find($id);

            $this->saveArchive('App\\Models\\AccountPurchasing', 'UPDATED', $index);

            $index->company_id     = $request->company_id;
            $index->no_pr          = $request->no_pr;
            $index->date           = $request->date;
            $index->spk_id         = $request->spk_id;
            $index->supplier_id    = $request->supplier_id;
            $index->note           = $request->note_header;
            $index->json_detail    = json_encode($json_detail);
            $index->total_price    = $total_price;
            $index->total_ppn      = $total_ppn;

            $index->save();
        });

        return redirect()->route('backend.account.accountPurchasing', ['tab' => 'ORDER'])->with('success', 'Data Has Been Updated'); 
    }

    public function statusAccountPurchasing(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
        ], $message);

        if ($validator->fails() && $request->status == "CLOSED") {
            return redirect()->back()->withErrors($validator)->withInput()->with('status-closed-crm-error', 'Something Errors');
        }


        DB::transaction(function () use ($request) {
            $index = AccountPurchasing::find($request->id);

            $this->saveArchive('App\\Models\\AccountPurchasing', 'UPDATED', $index);

            if($request->status == "OPEN_BILL")
            {
                $index->datetime_open_bill = date('Y-m-d H:i:s');
            }

            if($request->status == "DEBIT")
            {
                $index->datetime_debit = date('Y-m-d H:i:s');
            }

            else if($request->status == "CLOSED")
            {
                $index->account_list_id = $request->account_list_id;
                $index->datetime_closed = date('Y-m-d H:i:s');
            }

            else if($request->status == "CLOSED")
            {
                $index->account_list_id = $request->account_list_id;
                $index->datetime_invoice = date('Y-m-d H:i:s');
            }
            

            $index->save();
        });

        return redirect()->route('backend.account.accountPurchasing', ['tab' => 'ORDER'])->with('success', 'Data Has Been Updated');
    }

    public function storeAccountPurchasingDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
            'qty.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
            'qty' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('create-detail-error', '');
        }

        $index = new AccountPurchasingDetail;

        $index->account_purchasing_id = $request->account_purchasing_id;
        $index->account_list_id  = $request->account_list_id;
        $index->price            = $request->price;
        $index->qty              = $request->qty;
        $index->discount         = $request->discount ?? 0;
        $index->ppn              = $request->ppn ?? 0;
        $index->note             = $request->note;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Added'); 
    }

    public function updateAccountPurchasingDetail(Request $request)
    {
        $message = [
            'account_list_id.required' => 'This field required.',
            'price.required' => 'This field required.',
            'qty.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_list_id' => 'required',
            'price' => 'required',
            'qty' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-detail-error', '');
        }

        DB::transaction(function () use ($request) {

            $index = AccountPurchasingDetail::find($request->id);
            $this->saveArchive('App\\Models\\AccountPurchasingDetail', 'UPDATED', $index);

            $index->account_list_id  = $request->account_list_id;
            $index->price            = $request->price;
            $index->qty              = $request->qty;
            $index->discount         = $request->discount ?? 0;
            $index->ppn              = $request->ppn ?? 0;
            $index->note             = $request->note;

            $index->save();

        });

        return redirect()->back()->with('success', 'Data Has Been Updated'); 
    }

    public function deleteAccountPurchasingDetail(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountPurchasingDetail::find($request->id);

            $this->saveArchive('App\\Models\\AccountPurchasingDetail', 'DELETED', $index);

            $index->delete();

        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function deleteAccountPurchasing(Request $request)
    {
        DB::transaction(function () use ($request){

            $index = AccountPurchasing::find($request->id);

            $this->saveArchive('App\\Models\\AccountPurchasing', 'DELETED', $index);
        
            $index->delete();
        });

        return redirect()->back()->with('success', 'Data Has Been Deleted'); 
    }

    public function actionAccountPurchasing(Request $request)
    {
        DB::transaction(function () use ($request){
            if (is_array($request->id)) {

                switch ($request->action) {
                    case 'delete':
                        if (Auth::user()->can('deleteAccountPurchasing-account')) {

                            $index = AccountPurchasing::find($request->id);
                            $this->saveMultipleArchive('App\\Models\\AccountPurchasing', 'DELETED', $index);

                            AccountPurchasing::destroy($request->id);
                            return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
                        }
                        break;
                    
                    default:
                        return redirect()->back()->with('info', 'No data change');
                }
            }
        });

        return redirect()->back()->with('info', 'No data change');
    }

    public function pdfAccountPurchasing(Request $request)
    {
        $message = [
            'size.required'        => 'This field required.',
            'orientation.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'size'        => 'required',
            'orientation' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('pdf-error', 'Something Errors');
        }

        $index = AccountPurchasing::find($request->id);

        $pdf = PDF::loadView('backend.account.purchasing.pdf', compact('index', 'request'))->setPaper($request->size, $request->orientation);

        return $pdf->stream('Banking-'.date('Y-m-d H:i:s') . '.pdf');
    }

}
