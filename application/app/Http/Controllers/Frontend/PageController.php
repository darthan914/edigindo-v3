<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Config;
use App\Models\License;
use App\Models\Product;
use App\Models\Ecommerce;
use App\Models\Distribution;
use App\Models\News;
use App\Models\Inbox;

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

use Datatables;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
    	$license = License::where('flag_publish', 1)->get();
    	$product = Product::where('flag_publish', 1)->take(4)->get();
    	$ecommerce = Ecommerce::where('flag_publish', 1)->get();

    	return view('frontend.home', compact('license', 'product', 'ecommerce'));
    }

    public function about()
    {
    	return view('frontend.about');
    }

    public function product()
    {
    	$product = Product::where('flag_publish', 1)->take(4)->get();

    	return view('frontend.product', compact('product'));
    }

    public function distribution()
    {
    	$distribution = Distribution::where('flag_publish', 1)->get();
        $ecommerce = Ecommerce::where('flag_publish', 1)->get();

    	return view('frontend.distribution', compact('distribution', 'ecommerce'));
    }

    public function service()
    {
    	return view('frontend.service');
    }

    public function news()
    {
    	$news    = News::where('flag_publish', 1)->paginate(3);
    	$latest  = News::where('flag_publish', 1)->orderBy('created_at', 'DESC')->take(10)->get();
    	$archive = News::where('flag_publish', 1)->select(DB::raw('DATE(created_at) as created_at'))->groupBy(DB::raw('DATE(created_at)'))->take(10)->get();

    	return view('frontend.news', compact('news', 'latest', 'archive'));
    }

    public function inNews($slug)
    {
    	$news    = News::where('flag_publish', 1)->where('slug_url', $slug)->first();
    	$latest  = News::where('flag_publish', 1)->orderBy('created_at', 'DESC')->take(10)->get();
    	$archive = News::where('flag_publish', 1)->select(DB::raw('DATE(created_at) as created_at'))->groupBy(DB::raw('DATE(created_at)'))->take(10)->get();

    	if($news == '')
    	{
    		return redirect()->route('frontend.news');
    	}

    	return view('frontend.in-news', compact('news', 'latest', 'archive'));
    }

    public function contact()
    {
    	$ecommerce = Ecommerce::where('flag_publish', 1)->get();

    	return view('frontend.contact', compact('ecommerce'));
    }

    public function send(Request $request)
    {
    	$message = [
            'name.required'     => 'This Field Required.',
            'email.required'    => 'This Field Required.',
            'email.email'       => 'Email Format Only.',
            'subject.required'  => 'This Field Required.',
            'messages.required' => 'This Field Required.',
        ];

        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'email'        => 'email|required',
            'subject'      => 'required',
            'messages'     => 'required'
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

    	$index = new Inbox;

        $index->name     = $request->name;
        $index->email    = $request->email;
        $index->subject  = $request->subject;
        $index->messages = $request->messages;

        $index->save();

    	return redirect()->back()->with('messages', 'Thank you');
    }
}
