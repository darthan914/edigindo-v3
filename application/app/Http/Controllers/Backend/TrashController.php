<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Brand;
use App\Address;
use App\Pic;
use App\Spk;
use App\Production;
use App\Invoice;
use App\Offer;
use App\OfferList;
use App\Pr;
use App\Models\PrDetail;
use App\Models\Po;
use App\Config;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;

use App\Notifications\Production as ProductionNotif;


use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class TrashController extends Controller
{
	public function delete(Request $request)
	{
		switch ($request->model) {
			case 'company':
				if($request->id != -1)
				{
					Company::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Company::onlyTrashed()->forceDelete();
				}
				break;

			case 'brand':
				if($request->id != -1)
				{
					Brand::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Brand::onlyTrashed()->forceDelete();
				}
				break;

			case 'address':
				if($request->id != -1)
				{
					Address::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Address::onlyTrashed()->forceDelete();
				}
				break;

			case 'pic':
				if($request->id != -1)
				{
					Pic::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Pic::onlyTrashed()->forceDelete();
				}
				break;

			case 'spk':
				if($request->id != -1)
				{
					Spk::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Spk::onlyTrashed()->forceDelete();
				}
				break;

			case 'production':
				if($request->id != -1)
				{
					Production::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Production::onlyTrashed()->forceDelete();
				}
				break;

			case 'invoice':
				if($request->id != -1)
				{
					Invoice::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Invoice::onlyTrashed()->forceDelete();
				}
				break;

			case 'offer':
				if($request->id != -1)
				{
					Offer::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Offer::onlyTrashed()->forceDelete();
				}
				break;

			case 'offer_list':
				if($request->id != -1)
				{
					OfferList::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					OfferList::onlyTrashed()->forceDelete();
				}
				break;

			case 'pr':
				if($request->id != -1)
				{
					Pr::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Pr::onlyTrashed()->forceDelete();
				}
				break;

			case 'pr_detail':
				if($request->id != -1)
				{
					PrDetail::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					PrDetail::onlyTrashed()->forceDelete();
				}
				break;

			case 'po':
				if($request->id != -1)
				{
					Po::onlyTrashed()->where('id', $request->id)->forceDelete();
				}
				else
				{
					Po::onlyTrashed()->forceDelete();
				}
				break;
			
			default:
				break;
		}

		return redirect()->back()->with('success', 'Data Has Been Deleted');
	}

	public function restore(Request $request)
	{
		switch ($request->model) {
			case 'company':
				if($request->id != -1)
				{
					Company::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Company::onlyTrashed()->restore();
				}
				break;

			case 'brand':
				if($request->id != -1)
				{
					Brand::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Brand::onlyTrashed()->restore();
				}
				break;

			case 'address':
				if($request->id != -1)
				{
					Address::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Address::onlyTrashed()->restore();
				}
				break;

			case 'pic':
				if($request->id != -1)
				{
					Pic::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Pic::onlyTrashed()->restore();
				}
				break;

			case 'spk':
				if($request->id != -1)
				{
					Spk::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Spk::onlyTrashed()->restore();
				}
				break;

			case 'production':
				if($request->id != -1)
				{
					Production::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Production::onlyTrashed()->restore();
				}
				break;

			case 'invoice':
				if($request->id != -1)
				{
					Invoice::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Invoice::onlyTrashed()->restore();
				}
				break;

			case 'offer':
				if($request->id != -1)
				{
					Offer::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Offer::onlyTrashed()->restore();
				}
				break;

			case 'offer_list':
				if($request->id != -1)
				{
					OfferList::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					OfferList::onlyTrashed()->restore();
				}
				break;

			case 'pr':
				if($request->id != -1)
				{
					Pr::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Pr::onlyTrashed()->restore();
				}
				break;

			case 'pr_detail':
				if($request->id != -1)
				{
					PrDetail::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					PrDetail::onlyTrashed()->restore();
				}
				break;

			case 'po':
				if($request->id != -1)
				{
					Po::onlyTrashed()->where('id', $request->id)->restore();
				}
				else
				{
					Po::onlyTrashed()->restore();
				}
				break;
			
			default:
				break;
		}

		return redirect()->back()->with('success', 'Data Has Been Restored');
	}

	public function action(Request $request)
    {
    	if(is_array($request->id))
    	{
    		switch ($request->model) {
				case 'company':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Company::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Company::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
					break;

				case 'brand':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Brand::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Brand::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'address':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Address::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Address::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'pic':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Pic::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Pic::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'spk':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Spk::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Spk::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'production':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Production::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Production::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'invoice':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Invoice::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Invoice::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'offer':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Offer::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Offer::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'offer_list':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            OfferList::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	OfferList::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'pr':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Pr::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Pr::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'pr_detail':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            PrDetail::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	PrDetail::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;

				case 'po':
					if ($request->action == 'delete' && Auth::user()->can('delete-trash')) {
			            Po::onlyTrashed()->whereIn('id', $request->id)->forceDelete();
			            return redirect()->back()->with('success', 'Data Has Been Deleted');
			        } 
			        else if ($request->action == 'restore' && Auth::user()->can('restore-trash')) {
			        	Po::onlyTrashed()->whereIn('id', $request->id);
			            return redirect()->back()->with('success', 'Data Has Been Restored')->restore();
			        }
			        
					break;
				
				default:
					break;
			}

			return redirect()->back()->with('failed', 'Access Denied');

    	}
    	else
    	{
    		return redirect()->back()->with('failed', 'No item selected');
    	}
    }

    public function company(Request $request)
    {
    	$name  = "Company";
    	$url   = route('backend.trash.datatablesCompany');
    	$model = "company";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesCompany(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Company::onlyTrashed();

        if($f_month != '')
        {
            $index->whereMonth('deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="company"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="company"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables->editColumn('deleted_at', function ($index) {
            return date('d/m/Y H:i:s', strtotime($index->deleted_at));
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function brand(Request $request)
    {
    	$name  = "Brand";
    	$url   = route('backend.trash.datatablesBrand');
    	$model = "brand";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesBrand(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Brand::onlyTrashed()
        	->join('company', 'brand.company_id', 'company.id')
        	->select(
        		'brand.*', 
        		DB::raw('CONCAT(company.name, " - ", brand.brand) AS name')
        	);

        if($f_month != '')
        {
            $index->whereMonth('brand.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('brand.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="brand"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="brand"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function address(Request $request)
    {
    	$name  = "Address";
    	$url   = route('backend.trash.datatablesAddress');
    	$model = "address";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesAddress(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Address::onlyTrashed()
        	->join('company', 'address.company_id', 'company.id')
        	->select(
        		'address.*', 
        		DB::raw('CONCAT(company.name, " - ", address.address) AS name')
        	);

        if($f_month != '')
        {
            $index->whereMonth('address.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('address.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="address"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="address"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function pic(Request $request)
    {
    	$name  = "Pic";
    	$url   = route('backend.trash.datatablesPic');
    	$model = "pic";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesPic(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Pic::onlyTrashed()
        	->join('company', 'pic.company_id', 'company.id')
        	->select(
        		'pic.*',
        		DB::raw('CONCAT(company.name, " - ", pic.fullname) AS name')
        	);

        if($f_month != '')
        {
            $index->whereMonth('pic.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('pic.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="pic"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="pic"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function spk(Request $request)
    {
    	$name  = "App\Spk";
    	$url   = route('backend.trash.datatablesSpk');
    	$model = "spk";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesSpk(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Spk::onlyTrashed()->select('spk.*', DB::raw('CONCAT(spk.spk, " - ", spk.name) AS name'));

        if($f_month != '')
        {
            $index->whereMonth('spk.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('spk.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="spk"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="spk"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function production(Request $request)
    {
    	$name  = "Production";
    	$url   = route('backend.trash.datatablesProduction');
    	$model = "production";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesProduction(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Production::onlyTrashed()->join('spk', 'production.spk_id', 'spk.id')->select('production.*', DB::raw('CONCAT(spk.spk, " - ", spk.name, " - ", production.name) AS name'));

        if($f_month != '')
        {
            $index->whereMonth('production.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('production.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="production"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="production"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function invoice(Request $request)
    {
    	$name  = "Invoice";
    	$url   = route('backend.trash.datatablesInvoice');
    	$model = "invoice";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesInvoice(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Invoice::onlyTrashed()->join('spk', 'invoice.spk_id', 'spk.id')->select('invoice.*', DB::raw('CONCAT(spk.spk, " - ", spk.name, " - ", invoice.no_invoice) AS name'));

        if($f_month != '')
        {
            $index->whereMonth('invoice.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('invoice.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="invoice"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="invoice"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function offer(Request $request)
    {
    	$name  = "Offer";
    	$url   = route('backend.trash.datatablesOffer');
    	$model = "offer";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesOffer(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Offer::onlyTrashed()
        	->select(
        		'offer.*',
        		DB::raw('CONCAT(offer.no_document, " - ", offer.name) AS name')
        	);

        if($f_month != '')
        {
            $index->whereMonth('offer.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('offer.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="offer"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="offer"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function offerList(Request $request)
    {
    	$name  = "Offer List";
    	$url   = route('backend.trash.datatablesOffer');
    	$model = "offer_list";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesOfferList(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = OfferList::onlyTrashed()->join('offer', 'offerList.offer_id', 'offer.id')->select('offerList.*', DB::raw('CONCAT(offer.no_document, " - ", offer.name, " - ", offerlist.name) AS name'));

        if($f_month != '')
        {
            $index->whereMonth('offerList.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('offerList.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="offer_list"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="offer_list"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function pr(Request $request)
    {
    	$name  = "App\Pr";
    	$url   = route('backend.trash.datatablesPr');
    	$model = "pr";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesPr(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = Pr::onlyTrashed()->select('pr.*', 'pr.no_pr as name');

        if($f_month != '')
        {
            $index->whereMonth('pr.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('pr.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="pr"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="pr"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function prDetail(Request $request)
    {
    	$name  = "App\Pr Detail";
    	$url   = route('backend.trash.datatablesPrDetail');
    	$model = "pr_detail";

    	return view('backend.trash.index', compact('request', 'name', 'url', 'model'));
    }

    public function datatablesPrDetail(Request $request)
    {
    	$f_year  = $this->filter($request->f_year);
        $f_month = $this->filter($request->f_month);

        $index = PrDetail::onlyTrashed()->join('pr', 'pr_detail.pr_id', 'pr.id')->select('pr_detail.*', DB::raw('CONCAT(pr.no_pr, " - ", pr_detail.item) AS name'));

        if($f_month != '')
        {
            $index->whereMonth('pr_detail.deleted_at', $f_month);
        }

        if($f_year != '')
        {
            $index->whereYear('pr_detail.deleted_at', $f_year);
        }

        $index = $index->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';
            
            if( Auth::user()->can('restore-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="'.$index->id.'" data-model="pr_detail"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                ';
            }
                
            if( Auth::user()->can('delete-trash') )
            {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="'.$index->id.'" data-model="pr_detail"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            {
                $html .= '
                    <input type="checkbox" class="check" value="'.$index->id.'" name="id[]" form="action">
                ';
            }
                
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

}
