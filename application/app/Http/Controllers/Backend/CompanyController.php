<?php

namespace App\Http\Controllers\Backend;

use App\Models\Company;
use App\Models\Pic;
use App\Models\Brand;
use App\Models\Address;
use App\Models\Spk;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Mail;
use Datatables;

class CompanyController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
	{
		return view('backend.company.index')->with(compact('request'));
	}

	public function datatables(Request $request)
	{
		$search = $this->filter($request->search);

		$index = Company::orderBy('name', 'ASC');

		if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('companies.name', 'like', '%'.$search.'%');
            });
        }

        $index->get();

		$datatables = Datatables::of($index);

		$datatables->addColumn('view', function ($index) {
			return view('backend.company.view', compact('index'));
		});

		$datatables->addColumn('action', function ($index) {
			$html = '';

			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<a href="' . route('backend.company.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
				';
			}

			if(Auth::user()->can('delete-company'))
			{
				$html .= '
					<button class="btn btn-xs btn-danger delete-company" data-toggle="modal" data-target="#delete-company" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
				';
			}

			if (Auth::user()->can('lock-company'))
			{
				if($index->lock)
				{
					$html .= '
						<button class="btn btn-xs btn-success open-company" data-toggle="modal" data-target="#open-company" data-id="'.$index->id.'"><i class="fa fa-unlock"></i></button>
					';
				}
				else
				{
					$html .= '
						<button class="btn btn-xs btn-dark lock-company" data-toggle="modal" data-target="#lock-company" data-id="'.$index->id.'"><i class="fa fa-lock"></i></button>
					';
				}
			}

			if (Auth::user()->can('confirm-company')) {
                if ($index->confirm) {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-dark unconfirm-company" data-toggle="modal" data-target="#unconfirm-company"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-times" aria-hidden="true"></i></button>
	                ';
                } else {
                    $html .= '
	                   <button type="button" class="btn btn-xs btn-info confirm-company" data-toggle="modal" data-target="#confirm-company"
		                   data-id="' . $index->id . '"
	                   ><i class="fa fa-check" aria-hidden="true"></i></button>
	                ';
                }
            }
				
			return $html;
		});

		$datatables->editColumn('name', function ($index) {
			$html = $index->name . ' (' . $index->short_name . ')<br/>';
			$html .= 'Phone :' . $index->phone . '<br/>';
			$html .= 'Fax : ' . $index->fax;
			
			return $html;
		});

		$datatables->addColumn('status', function ($index) {
			$html = '';
			if($index->lock)
			{
				$html .= '
					<span class="label label-info">Lock</span>
				';
			}
			else
			{
				$html .= '
					<span class="label label-success">Open</span>
				';
			}

			$html .= '<br/>';

			if ($index->confirm == 1) {
                $html .= '
                    <span class="label label-success">Confirm</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Unconfirm</span>
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

	public function create()
	{
		return view('backend.company.create');
	}

	public function store(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'name'          => 'required',
			'gender'        => 'required',
			'phone_company' => 'max:15',
			'fax'           => 'max:15',
			
			'first_name' => 'required',
			'gender'     => 'required',
			'phone_pic'  => 'max:15',
			'email'      => 'nullable|email',
		]);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput();
		}

		$index = new Company;

		$index->name       = $request->name;
		$index->short_name = $request->short_name;
		$index->phone      = $request->phone_company;
		$index->fax        = $request->fax;

		$index->save();

		$pic = new Pic;

		$pic->company_id = $index->id;
		$pic->first_name = $request->first_name;
		$pic->last_name  = $request->last_name;
		$pic->gender     = $request->gender;
		$pic->position   = $request->position;
		$pic->phone      = $request->phone_pic;
		$pic->email      = $request->email;

		$pic->save();

		saveArchives($index, Auth::id(), "Create company", $request->except(['_token']));
		saveArchives($pic, Auth::id(), "Create pic", $request->except(['_token']));

		return redirect()->route('backend.company.edit', $index)->with('success', 'Data Has Been Added');
	}

	public function edit(Company $index, Request $request)
	{
		return view('backend.company.edit', compact('index', 'request'));
	}

	public function update(Company $index, Request $request)
	{
		$message = [
			'name.required' => 'Fill the name',
		];

		$validator = Validator::make($request->all(), [
			'name' => 'required',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput();
		}

		saveArchives($index, Auth::id(), "Update company", $request->except(['_token']));

		$index->name       = $request->name;
		$index->short_name = $request->short_name;
		$index->phone      = $request->phone;
		$index->fax        = $request->fax;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Updated');
	}

	public function delete(Request $request)
	{
		$index = Company::find($request->id);

		saveArchives($index, Auth::id(), "delete company");

		Company::destroy($request->id);

		return redirect()->back()->with('success', 'Data Has Been Deleted');
	}

	public function action(Request $request)
	{
		if ($request->action == 'delete' && Auth::user()->can('delete-company')) {
			
			$index = Company::find($request->id);

			saveMultipleArchives(Company::class, $index, Auth::id(), "delete company");

			Company::destroy($request->id);

			return redirect()->back()->with('success', 'Data Has Been Deleted');
		} else if ($request->action == 'confirm' && Auth::user()->can('update-company')) {

        	DB::transaction(function () use ($request){

	            $index = Company::whereIn('id', $request->id);

	            saveMultipleArchives(Company::class, $index, Auth::id(), "comfirm company");

	            Activity::whereIn('id', $request->id)->update(['confirm' => 1]);
	        });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        } else if ($request->action == 'unconfirm' && Auth::user()->can('update-company')) {

            DB::transaction(function () use ($request){

                $index = Company::whereIn('id', $request->id);

                saveMultipleArchives(Company::class, $index, Auth::id(), "unconfirm company");

                Activity::whereIn('id', $request->id)->update(['confirm' => 1]);
            });

            return redirect()->back()->with('success', 'Data Has Been Updated');
        }

		return redirect()->back()->with('success', 'Access Denied');
	}

	public function datatablesPic(Company $index, Request $request)
	{
		$datatables = Datatables::of($index->pic);

		$datatables->addColumn('fullname', function ($index) {
			return $index->fullname;
		});

		$datatables->editColumn('gender', function ($index) {
			return $index->long_gender;
		});

		$datatables->addColumn('action', function ($index) {
			$html = '';

			if(Auth::user()->can('send-company'))
			{
				if($index->phone)
				{
					$html .= '
						<button class="btn btn-xs btn-success whatsapp-pic" 
							data-id="'. $index->id .'" 
							data-phone="'. $index->phone .'" 
							data-toggle="modal" data-target="#whatsapp-pic"><i class="fa fa-whatsapp"></i></button>
					';
				}

				if($index->email)
				{
					$html .= '
						<button class="btn btn-xs btn-info email-pic" 
							data-id="'. $index->id .'" 
							data-email="'. $index->email .'" 
							data-toggle="modal" data-target="#email-pic"><i class="fa fa-envelope"></i></button>
					';
				}
			}

			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<button class="btn btn-xs btn-warning edit-pic" 
						data-id="'. $index->id .'" 
						data-first_name="'. $index->first_name .'" 
						data-last_name="'. $index->last_name .'" 
						data-gender="'. $index->gender .'" 
						data-position="'. $index->position .'" 
						data-phone="'. $index->phone .'" 
						data-email="'. $index->email .'" 
						data-toggle="modal" data-target="#edit-pic"><i class="fa fa-pencil"></i></button>
				';

				$html .= '
					<button class="btn btn-xs btn-danger delete-pic" data-toggle="modal" data-target="#delete-pic" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
				';
			}
			return $html;
		});

		$datatables->addColumn('check', function ($index) {
			$html = '';
			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<input type="checkbox" class="check-pic" value="' . $index->id . '" name="id[]" form="action-pic">
				';
			}
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function storePic(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'company_id' => 'required|integer',
			'first_name' => 'required',
			'gender' => 'required',
			'phone' => 'max:15',
			'email' => 'nullable|email',
		]);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('create-pic-error', 'Something Errors');
		}

		$index = new Pic;

		$index->company_id = $request->company_id;
		$index->first_name = $request->first_name;
		$index->last_name  = $request->last_name;
		$index->gender     = $request->gender;
		$index->position   = $request->position;
		$index->phone      = $request->phone;
		$index->email      = $request->email;

		$index->save();

		saveArchives($index, Auth::id(), "Create pic", $request->except(['_token']));

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('success', 'Data Has Been Added');
	}

	public function updatePic(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'first_name' => 'required',
			'gender' => 'required',
			'phone' => 'max:15',
			'email' => 'nullable|email',
		]);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('edit-pic-error', 'Something Errors');
		}

		$index = Pic::find($request->id);
		saveArchives($index, Auth::id(), "update pic", $request->except(['_token']));

		$index->first_name = $request->first_name;
		$index->last_name  = $request->last_name;
		$index->gender     = $request->gender;
		$index->position   = $request->position;
		$index->phone      = $request->phone;
		$index->email      = $request->email;

		$index->save();

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('success', 'Data Has Been Updated');
	}

	public function deletePic(Request $request)
	{
		$index = Pic::find($request->id);

		$countPIC = Pic::where('company_id', $index->company_id)->count();

		if($countPIC > 1)
		{
			$index = Pic::find($request->id);

			saveArchives($index, Auth::id(), "delete pic");

			Pic::destroy($request->id);

			return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('success', 'Data Has Been Deleted');
		}
		else
		{
			return redirect()->back()->with('failed', 'Data can not be delete');
		}
	}

	public function actionPic(Request $request)
	{
		DB::beginTransaction();

		if(is_array($request->id))
		{

			$index = Pic::whereIn('id', $request->id)->get();
			saveMultipleArchives(Pic::class, $index, Auth::id(), "delete pic");

			if ($request->action == 'delete') {

				$index = Pic::find($request->id[0]);

				Pic::destroy($request->id);

				$countPIC = Pic::where('company_id', $index->company_id)->count();

				if($countPIC == 0)
				{
					DB::rollback();
					return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('failed', 'Data can not be delete');
				}
				else
				{
					DB::commit();
					return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('success', 'Data Has Been Deleted');
				}

				
			}
		}

		return redirect()->back()->with('info', 'Nothing selected');
	}

	public function whatsappPic(Request $request)
	{
		$index = Pic::find($request->id);

		$phone = phone_number_format($index->phone);
		$text  = urlencode($request->text);

		return redirect('https://wa.me/'.$phone.'?text='.$text);
	}

	public function emailPic(Request $request)
	{
		$index = Pic::find($request->id);

		Mail::send('email.company', compact('index', 'request'), function ($message) use ($index, $request) {
            $message->from(Auth::user()->email)->to($index->email)->subject($request->subject);
        });

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'pic'])->with('success', 'Email successfully sended');
	}


	public function datatablesAddress(Company $index, Request $request)
	{
		$datatables = Datatables::of($index->addresses);

		$datatables->addColumn('action', function ($index) {
			$html = '';
			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<button class="btn btn-xs btn-warning edit-address" data-id="'. $index->id .'" data-address="'. $index->address .'"  data-latitude="'. $index->latitude .'"  data-longitude="'. $index->longitude .'" data-toggle="modal" data-target="#edit-address"><i class="fa fa-pencil"></i></button>
				';
				$html .= '
					<button class="btn btn-xs btn-danger delete-address" data-toggle="modal" data-target="#delete-address" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
				';
			}
			return $html;
		});

		$datatables->addColumn('check', function ($index) {
			$html = '';
			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<input type="checkbox" class="check-address" value="' . $index->id . '" name="id[]" form="action-address">
				';
			}
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function storeAddress(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'address' => 'required',
		]);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('create-address-error', 'Something Errors');
		}

		$index = new Address;

		$index->company_id = $request->company_id;
		$index->address    = $request->address;
		$index->latitude   = $request->latitude;
		$index->longitude  = $request->longitude;

		$index->save();

		saveArchives($index, Auth::id(), 'create address', $request->except(['_token']));

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'address'])->with('success', 'Data Has Been Added');
	}

	public function updateAddress(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'address' => 'required',
		]);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('edit-address-error', 'Something Errors');
		}

		$index = Address::find($request->id);
		saveArchives($index, Auth::id(), 'update address', $request->except(['_token']));

		$index->address   = $request->address;
		$index->latitude  = $request->latitude;
		$index->longitude = $request->longitude;

		$index->save();

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'address'])->with('success', 'Data Has Been Updated');
	}

	public function deleteAddress(Request $request)
	{
		$index = Address::find($request->id);
		saveArchives($index, Auth::id(), 'delete address');

		Address::destroy($request->id);

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'address'])->with('success', 'Data Has Been Deleted');
	}

	public function actionAddress(Request $request)
	{
		if(is_array($request->id))
		{
			if ($request->action == 'delete') {
				
				$index = Address::whereIn('id', $request->id)->get();
				saveMultipleArchives(Address::class, $index, Auth::id(), 'delete address');

				Address::destroy($request->id);
				return redirect()->back()->with('success', 'Data Has Been Deleted');
			}
		}

		return redirect()->back()->with('info', 'Nothing selected');
	}

	public function datatablesBrand(Company $index, Request $request)
	{
		$datatables = Datatables::of($index->brands);

		$datatables->addColumn('action', function ($index) {
			$html = '';
			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<button class="btn btn-xs btn-warning edit-brand" data-id="'. $index->id .'" data-name="'. $index->name .'" data-toggle="modal" data-target="#edit-brand"><i class="fa fa-pencil"></i></button>
				';
				$html .= '
					<button class="btn btn-xs btn-danger delete-brand" data-toggle="modal" data-target="#delete-brand" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
				';
			}
			return $html;
		});

		$datatables->addColumn('check', function ($index) {
			$html = '';
			if(Auth::user()->can('update-company'))
			{
				$html .= '
					<input type="checkbox" class="check-brand" value="' . $index->id . '" name="id[]" form="action-brand">
				';
			}
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function storeBrand(Request $request)
	{
		$message = [
			'name.required' => 'This field required.',
		];

		$validator = Validator::make($request->all(), [
			'name' => 'required',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('create-brand-error', 'Something Errors');
		}

		$index = new Brand;

		$index->company_id = $request->company_id;
		$index->name       = $request->name;

		$index->save();

		saveArchives($index, Auth::id(), 'create brand', $request->except(['_token']));

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'brand'])->with('success', 'Data Has Been Added.');
	}

	public function updateBrand(Request $request)
	{
		$message = [
			'name.required' => 'This field required.',
		];

		$validator = Validator::make($request->all(), [
			'name' => 'required',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()
				->withErrors($validator)
				->withInput()
				->with('edit-brand-error', 'Something Errors');
		}

		$index = Brand::find($request->id);
		saveArchives($index, Auth::id(), 'update brand', $request->except(['_token']));

		$index->name = $request->name;

		$index->save();

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'brand'])->with('success', 'Data Has Been Updated');
	}

	public function deleteBrand(Request $request)
	{
		$index = Brand::find($request->id);
		saveArchives($index, Auth::id(), 'delete brand');

		Brand::destroy($request->id);

		return redirect()->route('backend.company.edit', ['index' => $index->company_id, 'tab' => 'brand'])->with('success', 'Data Has Been Deleted');
	}

	public function actionBrand(Request $request)
	{
		if(is_array($request->id))
		{
			if ($request->action == 'delete') {
				$index = Brand::whereIn('id', $request->id)->get();
				saveMultipleArchives(Brand::class, $index, Auth::id(), 'delete brand');

				Brand::destroy($request->id);
				return redirect()->back()->with('success', 'Data Has Been Deleted');
			}
		}

		return redirect()->back()->with('info', 'Nothing selected');
	}

	public function lock(Request $request)
	{
		$index = Company::find($request->id);

		if ($index->datetime_lock == null)
		{
			saveArchives($index, Auth::id(), 'lock company');

			$index->datetime_lock = date('Y-m-d H:i:s');
			$index->save();
			return redirect()->back()->with('success', 'Data Has Been Locked');
		} 
		else if ($index->datetime_lock != null)
		{
			saveArchives($index, Auth::id(), 'unlock company');

			$index->datetime_lock = null;
			$index->save();
			return redirect()->back()->with('success', 'Data Has Been Unlock');
		}
	}

	public function confirm(Request $request)
	{
		$index = Company::find($request->id);

		if ($index->datetime_confirm == null)
		{
			saveArchives($index, Auth::id(), 'confirm company');

			$index->datetime_confirm = date('Y-m-d H:i:s');
			$index->save();
			return redirect()->back()->with('success', 'Data Has Been Updated');
		} 
		else if ($index->datetime_confirm != null)
		{
			saveArchives($index, Auth::id(), 'unconfirm company');

			$index->datetime_confirm = null;
			$index->save();
			return redirect()->back()->with('success', 'Data Has Been Updated');
		}
	}

	public function dashboard(Request $request)
	{
		$year = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();

		$month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

		$sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
            ->select('sales.first_name', 'sales.last_name', 'sales.id')
            ->where('sales.active', 1)
            ->orderBy('sales.first_name', 'ASC')->distinct()->get();

		return view('backend.company.dashboard')->with(compact('month', 'year', 'sales', 'request'));
	}

	public function autoLatLong()
	{
		$index = Address::whereNull('latitude')->get();

		foreach ($index as $list) {
			$update = Address::find($list->id);

			$geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.urlencode($list->address).'&key=AIzaSyB7nvRGWqvtGGXyrdyfcG-xJsMfWaoGVY8');
        	$output= json_decode($geocode);

        	if($output->status != "ZERO_RESULTS")
        	{
        		$update->latitude = $output->results[0]->geometry->location->lat;
				$update->longitude = $output->results[0]->geometry->location->lng;

				$update->save();
        	}
		}
	}

	public function getDetail(Request $request)
	{
		$index     = Company::find($request->id);

		$pic       = $index->pic;
		$brands    = $index->brands;
		$addresses = $index->addresses;

		return compact('pic', 'brands', 'addresses');
	}
	
}
