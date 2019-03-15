<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Mail;
use Session;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store']]);
    }

    public function index()
    {
        $index = Contact::orderBy('id', 'desc')->paginate(15);

        return view('cms.contact.index', ['index' => $index]);
    }

    public function store(Request $request)
    {
        $store = new Contact;

        $store->name     = $request->name;
        $store->phone    = $request->phone;
        $store->email    = $request->email;
        $store->subject  = $request->subject;
        $store->messages = $request->messages;

        $store->save();

        try
        {
        	Mail::send('cms.email', $store, function ($message) use ($request) {

	            $message->to('jonathan.digindo@gmail.com')->subject($request->subject);

	        });

	        Session::flash('success', 'Data has been Submited');
        }
        catch(Exception $e)
        {
        	Session::flash('info', 'Data has been Submited');
        }
        
        return redirect::back();
    }

    public function delete(Request $request)
    {
        $delete = Contact::destroy($request->check_id);
        Session::flash('success', 'Data has been Deleted');
        return redirect::back();
    }
}
