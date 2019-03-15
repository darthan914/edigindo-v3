<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Config;
use App\Spk;
use App\Company;
use App\Pr;
use App\Models\PrDetail;
use App\Division;
use App\Supplier;
use App\User;
use App\Models\ListRequest;

use Validator;
use Datatables;

class BetaController extends Controller
{
    public function createDelivery()
    {
        $spk     = Spk::all();
        $company = Company::all();
        $city    = $this->city();

        return view('backend.beta.createDelivery', compact('spk', 'company', 'city'));
    }

    public function postDelivery(Request $request)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $message = [
            'spk.required' => 'This field required.',
            'datetime_send.required' => 'This field required.',
            'datetime_send.date' => 'Invalid date format.',
            'via.required' => 'This field required.',
            'get_from.required' => 'This field required.',
            'detail.required' => 'This field required.',
            'address.required' => 'This field required.',
            'city.required' => 'This field required.',
            'task.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'spk' => 'required',
            'datetime_send' => 'required|date',
            'via' => 'required',
            'get_from' => 'required',
            'detail' => 'required',
            'address' => 'required',
            'city' => 'required',
            'task' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
        	// UNCOMMENT FOR PUBLISH
            // if ($this->checkSchedule($request)) {
            //     $validator->errors()->add('datetime_send', 'Schedule is full');
            // }
        });

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // DELETE FOR PUBLISH
        return redirect()->back()->with('info', 'Data posted ' . json_encode($request->all()) );
        // END OF DELETE

        // UNCOMMENT FOR PUBLISH
        // $index = new Delivery;

        // $index->user_id       = Auth::user()->id; 
        // $index->project       = $request->project ?? $index->task;
        // $index->name          = Auth::user()->fullname; 
        // $index->spk           = $request->spk; 
        // $index->company       = $request->company;
        // $index->brand         = $request->brand_id;
        // $index->pic_name      = $request->pic_name;
        // $index->pic_phone     = $request->pic_phone;
        // $index->get_from      = $request->get_from;
        // $index->address       = $request->address;
        // $index->city          = $request->city;
        // $index->latitude      = $request->latitude;
        // $index->longitude     = $request->longitude;
        // $index->via           = $request->via;
        // $index->datetime_send = date('Y-m-d H:i:s', strtotime($request->datetime_send));
        // $index->task          = 'OTHER';
        // $index->ppn           = $request->ppn ? $request->ppn : 0;
        // $index->detail        = $request->detail;
        // $index->note          = $request->note;

        // if ($request->hasFile('file')) {
        //     $pathSource = '/source/Upload/arriveFile/';
        //     $image      = $request->file('file');
        //     $filename   = time() . '.' . $image->getClientOriginalExtension();
        //     $image->move($pathSource, $filename);
        //     $index->file = $pathSource . $filename;
        // }

        // $delivery_notif = User::where(function ($query) use ($delivery_position, $delivery_user) {
        //         $query->whereIn('position', explode(', ', $delivery_position->value))
        //         ->orWhereIn('id', explode(', ', $delivery_user->value));
        //     })
        //     ->get();

        // $html = '
        //     New Request Delivery, Project : '.$request->project.'
        // ';

        // $index->save();

        // foreach ($delivery_notif as $list) {
        //     $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));
        // }

        // return redirect()->route('backend.delivery')->with('success', 'Data has been added');
    }

    public function back(Request $request)
    {
    	return redirect()->back()->with('info', 'Data posted ' . json_encode($request->all()) );
    }

}
