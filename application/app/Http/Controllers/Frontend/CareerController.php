<?php

namespace App\Http\Controllers;

use App\Career;
use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Session;
use Mail;

class CareerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store']]);
    }

    public function index(Request $request)
    {
        $index = Career::paginate(15);

        return view('cms.career.index', ['index' => $index]);
    }

    public function store(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'fullname'             => 'required|max:255',
            'email'                => 'required|email',
            'phone'                => 'required',
            'position'             => 'required',
            'attachment'           => 'required|mimes:zip|max:5120',
            'g-recaptcha-response' => 'required',

        ]);

        if ($validator->fails()) {
            return redirect('/#career')
                ->withErrors($validator)
                ->withInput();
        }

        $store = new Career;

        $store->fullname = $request->fullname;
        $store->email    = $request->email;
        $store->phone    = $request->phone;
        $store->position = $request->position;
        $store->message  = $request->message;

        
        Mail::send('cms.emailCareer', compact('store'), function ($message) use ($request) {

            $message->to('jonathan.digindo@gmail.com')->subject($request->subject);

        });

        Session::flash('success', 'Data has been Submited');
        

        $store->save();

        Session::flash('success', 'Data has been Submited');
        return redirect('/#career');
    }

    public function delete(Request $request)
    {
        $delete = Career::destroy($request->delete);
        Session::flash('success', 'Data has been Deleted');
        return redirect::back();
    }
}
