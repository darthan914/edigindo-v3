<?php

namespace App\Http\Controllers\Backend;

use App\Config;

use App\Models\Position;
use App\Division;
use App\User;

use App\Company;
use App\Brand;
use App\Pic;
use App\Address;

use App\Spk;
use App\Production;

use App\Offer;
use App\OfferList;

use App\Todo;

use App\Supplier;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;

use App\Notifications\Notif;

use Faker\Factory as Faker;


use Mail;

class DummyController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth', ['except' => ['test']]);
    }

    public function view()
    {
        return view('backend.dummy.attandance');
    }

    public function test()
    {
        Auth::user()->notify(new Notif(Auth::user()->nickname, 'Test the messages', route('test') ) );
        // return view('backend.documentEditor.index');
        return redirect()->route('backend.home');
    }

    public function users()
    {
    	$position = Position::where('active', 1)->get();
    	$division = Division::where('active', 1)->get();

    	return view('backend.dummy.users', compact('position', 'division'));
    }

    public function createDummyUsers(Request $request)
    {
    	$message = [
            'loop.required'        => 'This field required.',
            'loop.integer'         => 'Please try again.',
            'position_id.required' => 'This field required.',
            'position_id.integer'  => 'Please try again.',
            'division_id.required' => 'This field required.',
            'division_id.integer'  => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'        => 'required|integer',
            'position_id' => 'required|integer',
            'division_id' => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$no_position = User::where('position_id', $request->position_id)->count();
    	$code_position = Position::find($request->position_id)->code;
    	$code_division = Division::find($request->division_id)->name;

    	$gender = ['male', 'female'];

    	$array = '';

    	for ($i=0; $i < $request->loop; $i++) {

    		$name = $faker->name($gender[rand(0,1)]);

    		$array[] = [
    			'username'    => strtolower( str_replace(' ', '.', $name) ),
		        'email'       => strtolower( str_replace(' ', '.', $name) . '@digindo.com' ),
		        'password'    => bcrypt('secret'),
		        'fullname'    => $name,
		        'nickname'    => explode(' ',trim($name))[0],
		        'position'    => $code_position,
		        'division'    => $code_division,
		        'phone'       => $faker->e164PhoneNumber,
		        'no_ae'       => $i + 1 + $no_position,
		        'level'       => 0,
		        'leader'      => 0,
		        'active'      => 1,
		        'position_id' => $request->position_id,
    		];
    	}

    	User::insert($array);

    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }

    public function company()
    {
    	return view('backend.dummy.company');
    }

    public function createDummyCompany(Request $request)
    {
    	// DB::beginTransaction();
    	$message = [
            'loop.required'         => 'This field required.',
            'loop.integer'          => 'Please try again.',
            'loop_pic.required'     => 'This field required.',
            'loop_pic.integer'      => 'Please try again.',
            'loop_brand.required'   => 'This field required.',
            'loop_brand.integer'    => 'Please try again.',
            'loop_address.required' => 'This field required.',
            'loop_address.integer'  => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'         => 'required|integer',
            'loop_pic'     => 'required|integer',
            'loop_brand'   => 'required|integer',
            'loop_address' => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$gender = ['male', 'female'];
    	$array = '';

    	for ($i=0; $i < $request->loop; $i++) {

    		$company = $faker->company($gender[rand(0,1)]);

    		$id = Company::insertGetId([
    			'name'       => $company,
		        'short_name' => strtoupper( explode(' ',trim($company))[1] ),
		        'phone'      => $faker->phoneNumber,
		        'fax'        => $faker->phoneNumber,
    		]);

    		// App\\Pic
    		$array = '';
    		$rand_loop = rand(1, $request->loop_pic);

    		for ($j=0; $j < $rand_loop; $j++) {

    			$gen  = $gender[rand(0,1)];
    			$name = $faker->name($gen);

    			$array[] = [
	    			'company_id' => $id,
			        'fullname'   => $name,
			        'nickname'   => explode(' ',trim($name))[0],
			        'gender'     => strtoupper($gen[0]),
			        'position'   => ucfirst($faker->jobTitle),
			        'phone'      => $faker->e164PhoneNumber,
			        'email'      => strtolower(str_replace(' ', '.', $name) . '@'. strtolower( explode(' ',trim($company))[0] ) .'.com'),
	    		];
    		}
    		Pic::insert($array);

    		// Brand
    		$array = '';
    		$rand_loop = rand(1, $request->loop_brand);
    		for ($j=0; $j < $rand_loop; $j++) {

    			$array[] = [
	    			'company_id' => $id,
			        'brand'      => ucfirst($faker->word),
	    		];
    		}
    		if(is_array($array))
    		{
    			Brand::insert($array);
    		}

    		// Address
    		$array = '';
    		$rand_loop = rand(1, $request->loop_address);
    		for ($j=0; $j < $rand_loop; $j++) {

    			$address = $faker->address;
    			$geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.urlencode($address).'&key=AIzaSyB7nvRGWqvtGGXyrdyfcG-xJsMfWaoGVY8');
	        	$output= json_decode($geocode);

    			$array[] = [
	    			'company_id' => $id,
			        'address'    => $address,
			        'latitude'   => $output->results[0]->geometry->location->lat ?? 0,
			        'longitude'  => $output->results[0]->geometry->location->lng ?? 0,
	    		];
    		}
    		if(is_array($array))
    		{
    			Address::insert($array);
    		}
    	}

    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }

    public function spk()
    {
    	return view('backend.dummy.spk');
    }

    public function createDummySpk(Request $request)
    {
    	// DB::beginTransaction();
    	$message = [
            'loop.required'            => 'This field required.',
            'loop.integer'             => 'Please try again.',
            'loop_production.required' => 'This field required.',
            'loop_production.integer'  => 'Please try again.',
            'rand_month.required'      => 'This field required.',
            'rand_month.integer'       => 'Please try again.',
            'rand_year.required'       => 'This field required.',
            'rand_year.integer'        => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'            => 'required|integer',
            'loop_production' => 'required|integer',
            'rand_month'      => 'required|integer',
            'rand_year'       => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$sales        = User::where('position', 'marketing')->get();
    	$number_sales = User::where('position', 'marketing')->count();

    	$company        = Company::where('lock', 0)->get();
    	$number_company = Company::where('lock', 0)->count();


    	for ($i=0; $i < $request->loop; $i++) {

    		$date = $request->rand_year . '-' . $request->rand_month . '-' . rand(1,28);

    		if(date('w', strtotime($date)) == 0)
    		{
    			$date = date('Y-m-d', strtotime($date) + '+1 day');
    		}

    		$company_id = Company::find($company[rand(0, $number_company-1)]->id)->id;

    		$pic            = Pic::where('company_id', $company_id)->inRandomOrder()->first();
	    	$brand          = Brand::where('company_id', $company_id)->inRandomOrder()->first();
	    	$address        = Address::where('company_id', $company_id)->inRandomOrder()->first();

	    	$data_sales = User::find($sales[rand(0, $number_sales-1)]->id);

    		$id = Spk::insertGetId([
    			'spk'           => $this->getSpk($date, $data_sales),
		        'name'          => $faker->sentence,
		        'main_division' => $data_sales->division,
		        'company_id'    => $company_id,
		        'brand_id'      => $brand->id,
		        'address'       => $address->address ?? '',
		        'pic_id'        => $pic->id,
		        'sales_id'      => $data_sales->id,
		        'user_id'       => $data_sales->id,
		        'second_phone'  => '00000000000000',
		        'date'          => $date,
		        'ppn'           => rand(0,1) * 10,
		        'note'          => $faker->paragraph,
		        'transaction'   => rand(0,1),
		        'no_admin'      => 0,
    		]);

    		// Production
    		$source = ['Insource', 'Outsource'];
    		$array = '';
    		$rand_loop = rand(1, $request->loop_production);

    		$deadline = date('Y-m-d', strtotime($date) + ('+10 days'));

    		if(date('w', strtotime($deadline)) == 0)
    		{
    			$deadline = date('Y-m-d', strtotime($deadline) + '+1 day');
    		}


    		for ($j=0; $j < $rand_loop; $j++) {

    			$array[] = [
			        'spk_id'   => $id,
			        'name'     => $faker->sentence,
			        'material' => $faker->sentence,
			        'quantity' => rand(1, 10),
			        'hm'       => $hm = rand(1, 10) * pow(10, rand(3,6)),
			        'hj'       => $hm * (1 + (rand(25, 70) / 100)),
			        'division' => $data_sales->division,
			        'source'   => $source[rand(0,1)],
			        'deadline' => $deadline,
			    ];
    		}

    		if(is_array($array))
    		{
    			Production::insert($array);
    		}
    	}

    	// DB::commit();
    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }

    public function getSpk($date, $sales)
    {
        $spk = Spk::select('spk')
            ->where('spk', 'like', str_pad(($sales->no_ae == 0 ? $sales->id : $sales->no_ae), 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-%")
            ->orderBy('spk', 'desc');

        $count = $spk->count();
        $year = $spk->first();

        if ($count == 0) {
            $numberSpk = 0;
        } else {
            $numberSpk = intval(substr($year->spk, -3, 3));
        }

        return str_pad($sales->no_ae, 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-" . str_pad($numberSpk + 1, 3, '0', STR_PAD_LEFT);
    }

    public function offer()
    {
    	return view('backend.dummy.offer');
    }

    public function createDummyOffer(Request $request)
    {
    	// DB::beginTransaction();
    	$message = [
            'loop.required'        => 'This field required.',
            'loop.integer'         => 'Please try again.',
            'loop_detail.required' => 'This field required.',
            'loop_detail.integer'  => 'Please try again.',
            'rand_month.required'  => 'This field required.',
            'rand_month.integer'   => 'Please try again.',
            'rand_year.required'   => 'This field required.',
            'rand_year.integer'    => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'        => 'required|integer',
            'loop_detail' => 'required|integer',
            'rand_month'  => 'required|integer',
            'rand_year'   => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$sales        = User::where('position', 'marketing')->get();
    	$number_sales = User::where('position', 'marketing')->count();

    	$company        = Company::where('lock', 0)->get();
    	$number_company = Company::where('lock', 0)->count();


    	for ($i=0; $i < $request->loop; $i++) {

    		$date = $request->rand_year . '-' . $request->rand_month . '-' . rand(1,28);

    		if(date('w', strtotime($date)) == 0)
    		{
    			$date = date('Y-m-d', strtotime($date) + '+1 day');
    		}

    		$data_company = Company::find($company[rand(0, $number_company-1)]->id);

    		$pic            = Pic::where('company_id', $data_company->id)->get();
	    	$number_pic     = Pic::where('company_id', $data_company->id)->count();
	    	$brand          = Brand::where('company_id', $data_company->id)->get();
	    	$number_brand   = Brand::where('company_id', $data_company->id)->count();
	    	$address        = Address::where('company_id', $data_company->id)->get();
	    	$number_address = Address::where('company_id', $data_company->id)->count();

	    	$data_sales = User::find($sales[rand(0, $number_sales-1)]->id);

    		$id = Offer::insertGetId([
    			'no_document'   => $this->getDocument($date, $data_sales, $data_company),
		        'name'          => $faker->sentence,
		        'company_id'    => $data_company->id,
		        'brand_id'      => Brand::find($brand[rand(0, $number_brand-1)]->id)->id,
		        'pic_id'        => Pic::find($pic[rand(0, $number_pic-1)]->id)->id,
		        'second_phone'  => '00000000000000',
		        'address'       => Address::find($address[rand(0, $number_address-1)]->id)->address,
		        'sales_id'      => $data_sales->id,
		        'division'      => $data_sales->division,
		        'date'          => $date,
		        'ppn'           => rand(0,1) * 10,
		        'note'          => $faker->paragraph,
    		]);

    		// OfferList
    		$source = ['Insource', 'Outsource'];
    		$array = '';
    		$rand_loop = rand(1, $request->loop_detail);

    		for ($j=0; $j < $rand_loop; $j++) {

    			$array[] = [
			        'offer_id' => $id,
			        'name'     => $faker->sentence,
			        'detail'   => $faker->sentence,
			        'quantity' => rand(1, 10) * pow(10, rand(0,3)),
			        'units'    => 'pcs',
			        'price'    => rand(30, 100) * pow(10, rand(4,6)),
			    ];
    		}

    		if(is_array($array))
    		{
    			OfferList::insert($array);
    		}
    	}

    	// DB::commit();
    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }

    public function getDocument($date, $sales, $company)
    {
        $countDoc = Offer::whereMonth('date', date('m', strtotime($date)))
            ->whereYear('date', date('Y', strtotime($date)))
            ->where('sales_id', $sales->id)
            ->count();

        $noPenawaran = 1;
        if ($countDoc != 0) {
            $noPenawaran = $countDoc + 1;
        }

        $document = str_pad($noPenawaran, 3, '0', STR_PAD_LEFT) . "/PH/" . str_pad(($sales->no_ae == 0 ? $sales->id : $sales->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($company->short_name);

        return $document;
    }

    public function todo()
    {
    	$position = Position::where('active', 1)->get();
    	$division = Division::where('active', 1)->get();

    	return view('backend.dummy.todo');
    }

    public function createDummyTodo(Request $request)
    {
    	$message = [
            'loop.required'        => 'This field required.',
            'loop.integer'         => 'Please try again.',
            'rand_month.required'  => 'This field required.',
            'rand_month.integer'   => 'Please try again.',
            'rand_year.required'   => 'This field required.',
            'rand_year.integer'    => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'        => 'required|integer',
            'rand_month'  => 'required|integer',
            'rand_year'   => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$sales        = User::where('position', 'marketing')->get();
    	$number_sales = User::where('position', 'marketing')->count();

    	$company        = Company::where('lock', 0)->get();
    	$number_company = Company::where('lock', 0)->count();


    	$array = '';

    	for ($i=0; $i < $request->loop; $i++) {

    		$date = $request->rand_year . '-' . $request->rand_month . '-' . rand(1,28);

    		if(date('w', strtotime($date)) == 0)
    		{
    			$date = date('Y-m-d', strtotime($date) + '+1 day');
    		}


    		$data_company = Company::find($company[rand(0, $number_company-1)]->id);

	    	$brand          = Brand::where('company_id', $data_company->id)->get();
	    	$number_brand   = Brand::where('company_id', $data_company->id)->count();

	    	$data_sales = User::find($sales[rand(0, $number_sales-1)]->id);

    		$array[] = [
    			'sales_id'   => $data_sales->id,
		        'company'    => $data_company->name,
		        'company_id' => $data_company->id,
		        'brand'      => Brand::find($brand[rand(0, $number_brand-1)]->id)->brand,
		        'event'      => $faker->sentence,
		        'date_todo'  => $date . ' ' . rand(8,15) . ':00:00',
    		];
    	}

    	Todo::insert($array);

    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }


    public function supplier()
    {
    	$position = Position::where('active', 1)->get();
    	$division = Division::where('active', 1)->get();

    	return view('backend.dummy.supplier', compact('position', 'division'));
    }

    public function createDummySupplier(Request $request)
    {
    	$message = [
            'loop.required'        => 'This field required.',
            'loop.integer'         => 'Please try again.',
        ];

        $validator = Validator::make($request->all(), [
            'loop'        => 'required|integer',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    	$faker = Faker::create('ID_id');

    	$bank = ['BCA', 'BRI', 'Mandiri', 'Danamon', 'Niaga', 'BNI'];
    	$gender = ['male', 'female'];

    	$array = '';

    	for ($i=0; $i < $request->loop; $i++) {

    		$name = $faker->name($gender[rand(0,1)]);

    		$array[] = [
    			'name'          => $name,
		        'bank'          => $bank[rand(0,5)],
		        'name_rekening' => strtoupper($name),
		        'no_rekening'   => rand(10000000, 99999999),
		        'cp'            => $faker->e164PhoneNumber,
		        'phone_home'    => $faker->e164PhoneNumber,
		        'phone_mobile'  => $faker->e164PhoneNumber,
    		];
    	}

    	Supplier::insert($array);

    	return redirect()->back()->with('success', 'Data Successfully Generated');
    }

}
