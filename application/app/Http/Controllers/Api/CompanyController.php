<?php

namespace App\Http\Controllers\Api;

use App\Company;

use App\Pic;
use App\Address;
use App\Brand;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function get(Request $request){
    	$status  = 'OK';
        $message = '';
        $data    = '';

        $f_lock = $this->filter($request->f_lock);
        $f_company = $this->filter($request->f_company);
        $f_search = $this->filter($request->f_search);

    	$company = Company::orderBy('name', 'ASC');
    	if ($f_lock != '') {
            $company->where('lock', $f_lock);
        }

        if ($f_search != '') {
            $company->where('name', 'like', '%'.$f_search.'%');
        }

        $brand = Brand::orderBy('brand', 'ASC');
        $address = Address::orderBy('address', 'ASC');
        $pic = Pic::orderBy('fullname', 'ASC');

        if ($f_company != '') {
            $brand->where('company_id', $f_company);
            $address->where('company_id', $f_company);
            $pic->where('company_id', $f_company);
        }

        $company = $company->get();
        $brand = $brand->get();
        $address = $address->get();
        $pic = $pic->get();

		$data = compact('company', 'brand', 'address', 'pic');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function getDetail(Request $request){
        $status  = 'OK';
        $message = '';
        $data    = '';

        $f_company = $this->filter($request->f_company);

        $brand = Brand::orderBy('brand', 'ASC');
        $address = Address::orderBy('address', 'ASC');
        $pic = Pic::orderBy('fullname', 'ASC');

        if ($f_company != '') {
            $brand->where('company_id', $f_company);
            $address->where('company_id', $f_company);
            $pic->where('company_id', $f_company);
        }

        $brand = $brand->get();
        $address = $address->get();
        $pic = $pic->get();

        $data = compact('brand', 'address', 'pic');

        return response()->json(compact('status', 'message', 'data'));
    }
}
