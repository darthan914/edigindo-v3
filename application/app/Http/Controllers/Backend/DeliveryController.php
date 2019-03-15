<?php

namespace App\Http\Controllers\Backend;

use App\Delivery;
use App\User;
use App\Spk;
use App\Company;
use App\Brand;
use App\Pic;
use App\Address;
use App\Config;

use App\Notifications\Notif;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Session;
use File;
use Hash;
use Validator;
use PDF;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class DeliveryController extends Controller
{
    public function twoDigit($value)
    {
        return str_pad($value, 2, '0', STR_PAD_LEFT);
    }

    public function checkSchedule($request, $id = NULL): bool
    {
        $config       = Config::all();
        $data = '';
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
            $data[] = [$list->for];
        }

        $startHour = $shift_day_hour->value; // 6
        $endHour   = $shift_night_hour->value; // 17

        $maxDay   = $max_shift_day->value; // 10
        $maxNight = $max_shift_night->value; // 4

        $date = date('Y-m-d', strtotime($request->datetime_send));
        $time = date('H', strtotime($request->datetime_send));

        $startDay = $date . " ". $this->twoDigit($startHour) .":00:00";
        $endDay   = $date . " ". $this->twoDigit($endHour - 1) .":59:59";

        $startMorning = $date . " 00:00:00";
        $endMorning   = $date . " ". $this->twoDigit($startHour - 1) .":59:59";
        $startNight   = $date . " ". $this->twoDigit($endHour) .":00:00";
        $endNight     = $date . " 23:59:59";

        if ($time >= $startHour && $time <= $endHour)
        {
            $count = Delivery::whereBetween('datetime_send', 
                    [
                        date('Y-m-d H:i:s', strtotime($startDay)),
                        date('Y-m-d H:i:s', strtotime($endDay))
                    ]
                );

            if($id)
            {
                $count->where('id', '<>', $id);
            }

            $count = $count->where('via', 'Supir')->count();

            if ($count >= $maxDay && $request->via == 'Supir')
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            $count = Delivery::where(function ($query) use ($startMorning, $endMorning, $startNight, $endNight) {
                    $query->whereBetween('datetime_send', 
                        [
                            date('Y-m-d H:i:s', strtotime($startMorning)),
                            date('Y-m-d H:i:s', strtotime($endMorning))
                        ]
                    )
                    ->orWhereBetween('datetime_send', 
                        [
                            date('Y-m-d H:i:s', strtotime($startNight)),
                            date('Y-m-d H:i:s', strtotime($endNight))
                        ]
                    );
                });

            if($id)
            {
                $count->where('id', '<>', $id);
            }

            $count = $count->where('via', 'Supir')->count();

            if ($count >= $maxNight && $request->via == 'Supir')
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
    }

    public function index(Request $request)
	{
        $user = Delivery::join('users', 'users.id', '=', 'delivery.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-delivery'))
        {
            $user->whereIn('user_id', Auth::user()->staff());
        }

        $user = $user->get();

        $city = $this->city();

        $status = [
            'WAITING' => 'Waiting',
            'TAKEN'   => 'Taken',
            'SENDING' => 'Sending',
            'FINISH'  => 'Finish',
            'SUCCESS' => 'Success',
            'FAILED'  => 'Failed'
        ];

		return view('backend.delivery.index')->with(compact('request', 'status', 'user', 'city'));

        // UPDATE `delivery` SET `reason` = NULL WHERE reason = "NULL";
        // UPDATE `delivery` SET `reason` = NULL WHERE reason = "";
        // UPDATE `delivery` SET `received_by` = NULL WHERE `received_by` = "NULL";
        // UPDATE `delivery` SET `received_by` = NULL WHERE `received_by` = "";
	}

    public function datatables(Request $request)
    {
    	$f_via    = $this->filter($request->f_via);
    	$f_range  = $this->filter($request->f_range);
        $f_when   = $this->filter($request->f_when, 'today');
        $f_status = $this->filter($request->f_status);
    	$f_city  = $this->filter($request->f_city);
        $f_user   = $this->filter($request->f_user);
        $f_id     = $this->filter($request->f_id);

    	$index = Delivery::join('users', 'delivery.user_id', 'users.id')
            ->select('delivery.*', 'users.fullname');

        if($f_id != '')
        {
            $index->where('delivery.id', $f_id);
        }
        else
        {
            if ($f_via != '')
            {
                $index->where('via', $f_via);
            }

            if($f_range != '')
            {
                $date = explode(' - ', $f_range);
                $date[0] = date('Y-m-d H:i:s', strtotime($date[0]));
                $date[1] = date('Y-m-d H:i:s', strtotime($date[1] . '+1 day'));

                $index->where('datetime_send', '>=', $date[0])
                    ->where('datetime_send', '<=', $date[1]);
            }

            if ($f_when != '' && $f_when == 'yesterday') 
            {
                $index->where('datetime_send', '<', date('Y-m-d'));
            }
            else if ($f_when != '' && $f_when == 'today') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d').'%');
            }
            else if ($f_when != '' && $f_when == 'tomorrow') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
            }
            else if ($f_when != '' && $f_when == 'future') 
            {
                $index->where('datetime_send', '>', date('Y-m-d'));
            }


            if ($f_status != '') 
            {
                $index->where('status', $f_status);
            }

            if ($f_city != '') 
            {
                $index->where('city', $f_city);
            }

            if($f_user == 'staff')
            {
                $index->whereIn('user_id', Auth::user()->staff());
            }
            else if($f_user != '')
            {
                $index->where('user_id', $f_user);
            }
        }

    	

        $index = $index->orderBy('delivery.id', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('fullname', function ($index) {
            return $index->project . '<br/>' . $index->fullname;
        });


        $datatables->addColumn('is_ppn', function ($index) {

            if($index->ppn)
            {
                return 'Yes';
            }
            else
            {
                return 'No';
            }
        });

        $datatables->editColumn('datetime_send', function ($index) {

            $html = date('d M Y H:i', strtotime($index->datetime_send));

            return $html;
        });

        $datatables->editColumn('file', function ($index) {

        	if($index->file)
        	{
        		$html = '<a href="'.asset($index->file).'" class="btn btn-primary btn-xs"><i class="fa fa-download" aria-hidden="true"></i></a>';
        	}
        	else
        	{
        		$html = 'No Attachment';
        	}
        	
            return $html;
        });

        $datatables->addColumn('status', function ($index) {

            $html = '';

            if($index->status == 'WAITING')
            {
                $html .= '<strong>Waiting</strong>';
            }
            else if($index->status == 'SENDING')
            {
                $html .= '<strong>Sending</strong>';

                $html .= '<br/>'. date('d/m/Y H:i', strtotime($index->date_sended)) . '<br/>('.$index->name_courier.')';
            }
            else if($index->status == 'FINISH')
            {
                $html .= '<strong>Item Delivered</strong>';
                if($index->received_by){
                    $html .= '<br/> Received By : ' . $index->received_by;
                }
            }
            else if($index->status == 'SUCCESS')
            {
                $html .= '<strong>Success</strong>';

                $html .= '<br/>'. date('d/m/Y H:i', strtotime($index->date_arrived));
                if($index->received_by){
                    $html .= '<br/> Received By : ' . $index->received_by;
                }
            }
            else if($index->status == 'FAILED')
            {
                $html .= '<strong>Failed</strong>';
                
                $html .= '<br/>'. $index->reason;
            }

            return $html;
        });

        $datatables->addColumn('action_2', function ($index) {
            $html = '';

            if($index->status == 'WAITING')
            {
                // Class change
                if(Auth::user()->can('change-delivery'))
                {
                    $html .= '
                        <button class="btn btn-xs btn-warning change-delivery" data-toggle="modal" data-target="#change-delivery" data-id="'.$index->id.'"
                            data-via="'.$index->via.'"  
                            data-datetime_send="'.date('Y-m-d\TH:i', strtotime($index->datetime_send)).'">Change</button>
                    ';
                }

                // Class send
                if(Auth::user()->can('send-delivery'))
                {
                    $html .= '
                        <button class="btn btn-xs btn-success send-delivery" data-toggle="modal" data-target="#send-delivery" data-id="'.$index->id.'"
                        >Send</button>
                    ';
                }

                // Class take
                if(Auth::user()->can('take-delivery'))
                {
                    $html .= '
                        <button class="btn btn-xs btn-success take-delivery" data-toggle="modal" data-target="#take-delivery" data-id="'.$index->id.'"
                        >Take</button>
                    ';
                }
            }

            // if($index->status == 'TAKEN')
            // {
            //     // Class change
            //     if(Auth::user()->can('undoTake-delivery'))
            //     {
            //         $html .= '
            //             <button class="btn btn-xs btn-default undoTake-delivery" data-toggle="modal" data-target="#undoTake-delivery" data-id="'.$index->id.'">Undo Take</button>
            //         ';
            //     }
            // }


            if($index->status == 'FINISH')
            {
                // Class confirm
                if(Auth::user()->can('confirm-delivery') && ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <button class="btn btn-xs btn-primary confirm-delivery" data-toggle="modal" data-target="#confirm-delivery" data-id="'.$index->id.'">Confirm</button>
                    ';
                }

                // Class undoSend
                if(Auth::user()->can('undoSend-delivery'))
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-default undoSend-delivery" data-toggle="modal" data-target="#undoSend-delivery" data-id="'.$index->id.'">Undo Send</i></button>
                    ';
                }
            }

            if(in_array($index->status, ['SUCCESS', 'FAILED']))
            {
                // Class undoConfirm
                if(Auth::user()->can('undoConfirm-delivery'))
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-default undoConfirm-delivery" data-toggle="modal" data-target="#undoConfirm-delivery" data-id="'.$index->id.'">Undo Confirm</button>
                    ';
                }
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if($index->status == 'WAITING')
            {
                if(Auth::user()->can('edit-delivery') && ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <a href="'.route('backend.delivery.edit', ['id' => $index->id]).'" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                    ';
                }

                if(Auth::user()->can('delete-delivery') && ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-danger delete-delivery" data-toggle="modal" data-target="#delete-delivery" data-id="'.$index->id.'"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    ';
                }
            }
            return $html;
            
        });

        $datatables->editColumn('detail', function ($index) {
            return view('backend.delivery.detail.index', compact('index'));
        });
            

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        $spk     = Spk::all();
        $company = Company::all();
        $city    = $this->city();

        return view('backend.delivery.create', compact('spk', 'company', 'city'));
    }

    public function store(Request $request)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $message = [
            'spk.required' => 'This field required.',
            'project.required' => 'This field required.',
            'name.required' => 'This field required.',
            'datetime_send.required' => 'This field required.',
            'datetime_send.date' => 'Invalid date format.',
            'via.required' => 'This field required.',
            'get_from.required' => 'This field required.',
            'detail.required' => 'This field required.',
            'address.required' => 'This field required.',
            'city.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'spk' => 'required',
            'project' => 'required',
            'name' => 'required',
            'datetime_send' => 'required|date',
            'via' => 'required',
            'get_from' => 'required',
            'detail' => 'required',
            'address' => 'required',
            'city' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if ($this->checkSchedule($request)) {
                $validator->errors()->add('datetime_send', 'Schedule is full');
            }
        });

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $index = new Delivery;

        $index->user_id       = Auth::user()->id; 
        $index->project       = $request->project;
        $index->name          = $request->name; 
        $index->spk           = $request->spk; 
        $index->company       = $request->company;
        $index->brand         = $request->brand_id;
        $index->pic_name      = $request->pic_name;
        $index->pic_phone     = $request->pic_phone;
        $index->get_from      = $request->get_from;
        $index->address       = $request->address;
        $index->city          = $request->city;
        $index->latitude      = $request->latitude;
        $index->longitude     = $request->longitude;
        $index->via           = $request->via;
        $index->datetime_send = date('Y-m-d H:i:s', strtotime($request->datetime_send));
        $index->task          = 'OTHER';
        $index->ppn           = $request->ppn ? $request->ppn : 0;
        $index->detail        = $request->detail;
        $index->note          = $request->note;

        if ($request->hasFile('file')) {
            $pathSource = '/source/Upload/arriveFile/';
            $image      = $request->file('file');
            $filename   = time() . '.' . $image->getClientOriginalExtension();
            $image->move($pathSource, $filename);
            $index->file = $pathSource . $filename;
        }

        $delivery_notif = User::where(function ($query) use ($delivery_position, $delivery_user) {
                $query->whereIn('position', explode(', ', $delivery_position->value))
                ->orWhereIn('id', explode(', ', $delivery_user->value));
            })
            ->get();

        $html = '
            New Request Delivery, Project : '.$request->project.'
        ';

        $index->save();

        foreach ($delivery_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));
        }

        return redirect()->route('backend.delivery')->with('success', 'Data has been added');
    }

    public function edit($id)
    {
        $index   = Delivery::find($id);
        $spk     = Spk::all();
        $company = Company::all();
        $city    = $this->city();


        return view('backend.delivery.edit', compact('index', 'spk', 'company', 'city'));
    }

    public function update(Request $request, $id)
    {
        $config       = Config::all();
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $index = Delivery::find($id);

        if(!$this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id))
        {
            return redirect()->route('backend.delivery')->with('failed', 'Access Denied');
        }

        $message = [
            'spk.required' => 'This field required.',
            'project.required' => 'This field required.',
            'name.required' => 'This field required.',
            'datetime_send.required' => 'This field required.',
            'datetime_send.date' => 'Invalid date format.',
            'via.required' => 'This field required.',
            'get_from.required' => 'This field required.',
            'detail.required' => 'This field required.',
            'address.required' => 'This field required.',
            'city.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'spk' => 'required',
            'project' => 'required',
            'name' => 'required',
            'datetime_send' => 'required|date',
            'via' => 'required',
            'get_from' => 'required',
            'detail' => 'required',
            'address' => 'required',
            'city' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request, $id) {
            if ($this->checkSchedule($request, $id)) {
                $validator->errors()->add('datetime_send', 'Schedule is full');
            }
        });

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        if($index->status == 'SENDING')
        {
            return redirect()->back()->with('failed', 'Data cannot to update');
        }

        $this->saveArchive('App\Delivery', 'UPDATED', $index);

        $index->project       = $request->project;
        $index->name          = $request->name;
        $index->spk           = $request->spk;
        $index->company       = $request->company;
        $index->brand         = $request->brand_id;
        $index->pic_name      = $request->pic_name;
        $index->pic_phone     = $request->pic_phone;
        $index->get_from      = $request->get_from;
        $index->address       = $request->address;
        $index->city          = $request->city;
        $index->latitude      = $request->latitude;
        $index->longitude     = $request->longitude;
        $index->via           = $request->via;
        $index->datetime_send = date('Y-m-d H:i:s', strtotime($request->datetime_send));
        $index->ppn           = $request->ppn;
        $index->detail        = $request->detail;
        $index->note          = $request->note;

        if ($request->hasFile('file')) {
            if($index->file)
            {
                File::delete($index->file);
            }
            $pathSource = 'upload/spk/detail/';
            $fileData       = $request->file('file');
            $filename   = time() . '.' . $fileData->getClientOriginalExtension();
            $fileData->move($pathSource, $filename);
            
            $index->file = $pathSource . $filename;
        }
        else if (isset($request->remove))
        {
            File::delete($index->file);
            $index->file = null;
        }

        $delivery_notif = User::where(function ($query) use ($delivery_position, $delivery_user) {
                $query->whereIn('position', explode(', ', $delivery_position->value))
                ->orWhereIn('id', explode(', ', $delivery_user->value));
            })
            ->get();

        $html = '
            Request Delivery Updated, Project : '.$request->project.'
        ';

        foreach ($delivery_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));
        }

        $index->save();

        return redirect()->route('backend.delivery')->with('success', 'Data has been updated');;
    }

    public function delete(Request $request)
    {
        $index = Delivery::find($request->id);

        if($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id))
        {
            $this->saveArchive('App\Delivery', 'DELETED', $index);

            Delivery::destroy($request->id);
        }
        else
        {
            return redirect()->route('backend.delivery')->with('failed', 'Access Denied');
        }

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function change(Request $request)
    {
        $id = $request->id;

        $message = [
            'datetime_send.required' => 'This field required.',
            'datetime_send.date' => 'Invalid date format.',
            'via.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'datetime_send' => 'required|date',
            'via' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request, $id) {
            if ($this->checkSchedule($request, $id)) {
                $validator->errors()->add('datetime_send', 'Schedule is full');
            }
        });

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput()->with('change-delivery-error', '');
        }

        $index = Delivery::find($id);

        if($index->date_sended)
        {
            return redirect()->back()->with('failed', 'Data cannot to update');
        }

        $this->saveArchive('App\Delivery', 'CHANGED', $index);

        $index->via = $request->via;
        $index->datetime_send = date('Y-m-d H:i:s', strtotime($request->datetime_send));

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function send(Request $request)
    {
        $message = [
            'name_courier.required' => 'This field required.',
            'date_sended.date' => 'Date format only.',
        ];

        $validator = Validator::make($request->all(), [
            'name_courier' => 'required',
            'date_sended' => 'date|nullable',
        ], $message);

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput()->with('send-delivery-error', '');
        }

        $index = Delivery::find($request->id);

        if($index->date_sended)
        {
            return redirect()->back()->with('failed', 'Data cannot to update');
        }

        $this->saveArchive('App\Delivery', 'SENDED', $index);

        $index->name_courier = $request->name_courier;
        $index->date_sended    = $request->date_sended ? date('Y-m-d H:i:s', strtotime($request->date_sended)) : date('Y-m-d H:i:s');
        $index->status         = 'SENDING';

        $html = '
            Your Request Delivery has been sended by : '.$request->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function undoSend(Request $request)
    {
        $index = Delivery::find($request->id);

        if(in_array($index->status, ['SUCCESS', 'FAILED', 'TAKEN']))
        {
            return redirect()->back()->with('failed', 'Data cannot to undo');
        }

        $this->saveArchive('App\Delivery', 'UNDO_SEND', $index);

        $index->courier_id      = 0;
        $index->name_courier = NULL;
        $index->date_sended    = NULL;
        $index->status         = 'WAITING';


        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function take(Request $request)
    {
        $index = Delivery::find($request->id);

        if($index->date_sended)
        {
            return redirect()->back()->with('failed', 'Data already Taken');
        }

        $this->saveArchive('App\Delivery', 'TAKE', $index);

        $index->courier_id   = Auth::id();
        $index->name_courier = Auth::user()->fullname;
        $index->status       = 'TAKEN';

        $html = '
            Your Request Delivery has been taken by : '.$index->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function undoTake(Request $request)
    {
        $index = Delivery::find($request->id);

        if(in_array($index->status, ['SUCCESS', 'FAILED']))
        {
            return redirect()->back()->with('failed', 'Data cannot to undo');
        }

        $this->saveArchive('App\Delivery', 'UNDO_TAKE', $index);

        $index->courier_id   = 0;
        $index->name_courier = NULL;
        $index->status       = 'WAITING';

        $index->save();

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function startSend(Request $request)
    {
        $index = Delivery::find($request->id);

        if($index->courier_id != Auth::id())
        {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Delivery', 'START_SEND', $index);

        $geocode = file_get_contents('https://maps.google.com/maps/api/distancematrix/json?origins='.(urlencode($index->latitude.','.$index->longitude)).'&destinations='.(urlencode($request->start_latitude.','.$request->start_longitude)).'&key='.env('GOOGLE_MAPS_API'));
        $output= json_decode($geocode);

        $index->start_latitude  = $request->start_latitude;
        $index->start_longitude = $request->start_longitude;
        $index->distance        = $output->rows[0]->elements[0]->distance->value ?? 0;
        $index->date_sended     = date('Y-m-d H:i:s');
        $index->status          = 'SENDING';

        $index->save();

        $html = '
            Your Request Delivery is on the way by : '.$index->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function undoStartSend(Request $request)
    {
        $index = Delivery::find($request->id);

        if(in_array($index->status, ['SUCCESS', 'FAILED']))
        {
            return redirect()->back()->with('failed', 'Data cannot to undo');
        }

        $this->saveArchive('App\Delivery', 'UNDO_START_SEND', $index);

        $index->start_latitude  = NULL;
        $index->start_longitude = NULL;
        $index->distance        = NULL;
        $index->date_sended     = NULL;
        $index->status          = 'TAKEN';

        $index->save();

        $html = '
            Your Request Delivery current on the way is revert by : '.$index->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function confirm(Request $request)
    {
        $message = [
            'status.required' => 'This field required.',
            'date_arrived.required_if' => 'This field required if success.',
            'date_arrived.date' => 'Date format only.',
            'reason.required_if' => 'This field required if failed.',
        ];

        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'date_arrived' => 'required_if:status,SUCCESS|date|nullable',
            'reason' => 'required_if:status,FAILED',
        ], $message);

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput()->with('confirm-delivery-error', '');
        }

        $index = Delivery::find($request->id);

        if(in_array($index->status, ['SUCCESS', 'FAILED']))
        {
            return redirect()->back()->with('failed', 'Data cannot to update');
        }

        $this->saveArchive('App\Delivery', 'CONFIRM', $index);

        if($request->status == 'SUCCESS')
        {
            $index->date_arrived = $index->date_arrived ?? ($request->date_arrived ? date('Y-m-d H:i:s', strtotime($request->date_arrived)) : date('Y-m-d H:i:s'));
            $index->received_by  = $index->received_by ?? $request->received_by;
            $index->status       = 'SUCCESS';
        }
        else if($request->status == 'FAILED')
        {
            $index->reason       = $request->reason;
            $index->status       = 'FAILED';
        }

        $index->save();

        $html = '
            Your Delivery has been rate
        ';

        User::find($index->courier_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery.courier', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function undoConfirm(Request $request)
    {

        $index = Delivery::find($request->id);

        $this->saveArchive('App\Delivery', 'UNDO_CONFIRM', $index);

        $index->date_arrived  = NULL;
        $index->received_by   = NULL;
        $index->end_latitude  = null;
        $index->end_longitude = null;
        $index->reason        = NULL;
        $index->status        = 'SENDING';

        $index->save();

        $html = '
            Your Delivery has been revert confirm
        ';

        User::find($index->courier_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery.courier', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function finish(Request $request)
    {

        $index = Delivery::find($request->id);

        if($index->courier_id != Auth::id())
        {
            return redirect()->back()->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Delivery', 'FINISH', $index);

        $index->status        = 'FINISH';
        $index->date_arrived  = date('Y-m-d H:i:s');
        $index->end_latitude  = $request->end_latitude;
        $index->end_longitude = $request->end_longitude;
        $index->received_by   = $request->received_by;
        $index->save();

        $html = '
            Your Request is Delivered by : '.$index->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function undoFinish(Request $request)
    {
        $index = Delivery::find($request->id);

        if(in_array($index->status, ['SUCCESS', 'FAILED']))
        {
            return redirect()->back()->with('failed', 'Data cannot to undo');
        }

        $this->saveArchive('App\Delivery', 'UNDO_FINISH', $index);

        $index->status        = 'SENDING';
        $index->date_arrived  = NULL;
        $index->end_latitude  = null;
        $index->end_longitude = null;
        $index->received_by   = NULL;
        $index->save();

        $html = '
            Your Request Delivered is revert by : '.$index->name_courier.'
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data has been updated');
    }

    public function viewDistance(Request $request)
    {
        return view('backend.delivery.viewDistance')->with(compact('request'));
    }

    public function datatablesViewDistance(Request $request)
    {
        $f_max_distance = $this->filter($request->f_max_distance);

        $config    = Config::all();
        $data = '';
        foreach ($config as $list) {
            eval("\$".$list->for." = App\Config::find(".$list->id.");");
        }

        $index = Address::join('company', 'address.company_id', 'company.id')
            ->select('company.name','address.address', DB::raw(
                "
                    (6371 * ACOS(COS(RADIANS(".$current_latitude->value."))
                           * COS(RADIANS(latitude))
                           * COS(RADIANS(".$current_longitude->value.") - RADIANS(longitude))
                           + SIN(RADIANS(".$current_latitude->value."))
                           * SIN(RADIANS(latitude)))) AS distance
                "

            ));

        if($f_max_distance != '')
        {
            $index->having('distance', '<', $f_max_distance);
        }

        $index = $index->orderBy('address.id', 'ASC')->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('distance', function ($index) {
            return number_format($index->distance) . ' km';
        });


        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function courier(Request $request)
    {
        $courier = Delivery::select('delivery.courier_id as id', 'users.fullname')
            ->join('users', 'delivery.courier_id', 'users.id')
            ->where('delivery.courier_id', '<>', 0)
            ->orderBy('users.fullname', 'ASC')
            ->distinct();

        if(!Auth::user()->can('allCourier-delivery'))
        {
            $courier->whereIn('courier_id', Auth::user()->staff());
        }

        $city = $this->city();

        $user = Delivery::join('users', 'users.id', '=', 'delivery.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        $status = ['TAKEN' => 'Taken', 'SENDING' => 'Sending', 'FINISH' => 'Finish'];

        return view('backend.delivery.courier')->with(compact('courier', 'user', 'request', 'city'));
    }

    public function datatablesCourier(Request $request)
    {
        $f_when   = $this->filter($request->f_when);
        $f_status = $this->filter($request->f_status);
        $f_city   = $this->filter($request->f_city);
        $f_user   = $this->filter($request->f_user);
        $f_courier = $this->filter($request->f_courier, Auth::id());
        $f_id     = $this->filter($request->f_id);

        $index = Delivery::join('users as user_request', 'delivery.user_id', 'user_request.id')
            ->join('users as courier', 'delivery.courier_id', 'courier.id')
            ->select('delivery.*', 'user_request.fullname as user_name', 'courier.fullname as courier_name');

        if($f_id != '')
        {
            $index->where('delivery.id', $f_id);
        }
        else
        {

            if ($f_when != '' && $f_when == 'yesterday') 
            {
                $index->where('datetime_send', '<', date('Y-m-d'));
            }
            else if ($f_when != '' && $f_when == 'today') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d').'%');
            }
            else if ($f_when != '' && $f_when == 'tomorrow') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
            }
            else if ($f_when != '' && $f_when == 'future') 
            {
                $index->where('datetime_send', '>', date('Y-m-d'));
            }


            if($f_status == 'FINISH')
            {
                $index->whereIn('status', ['FINISH', 'SUCCESS', 'FAILED']);
            }
            else if ($f_status != '') 
            {
                $index->where('status', $f_status);
            }

            if($f_user != '')
            {
                $index->where('user_id', $f_user);
            }

            if ($f_city != '') 
            {
                $index->where('city', $f_city);
            }

            if($f_courier == 'staff')
            {
                $index->whereIn('courier_id', Auth::user()->staff());
            }
            else if($f_courier != '')
            {
                $index->where('courier_id', $f_courier);
            }
        }


        $index = $index->orderBy('delivery.id', 'DESC')->get();

        $datatables = Datatables::of($index);


        $datatables->editColumn('user_name', function ($index) {
            return $index->project . '<br/>' . $index->user_name;
        });

        $datatables->addColumn('is_ppn', function ($index) {

            if($index->ppn)
            {
                return 'Yes';
            }
            else
            {
                return 'No';
            }
        });

        $datatables->editColumn('datetime_send', function ($index) {

            $html = date('d M Y H:i', strtotime($index->datetime_send));

            return $html;
        });


        $datatables->addColumn('status', function ($index) {

            $html = '';

            if($index->status == 'TAKEN')
            {
                $html .= '<strong>Ready to deliver</strong>';
            }
            else if($index->status == 'SENDING')
            {
                $html .= '<strong>Sending</strong>';

                // Class undoSend
                if(Auth::user()->can('undoSend-delivery'))
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-default undoStartSend-delivery" data-toggle="modal" data-target="#undoStartSend-delivery" data-id="'.$index->id.'"><i class="fa fa-undo" aria-hidden="true"></i></button>
                    ';
                }

                $html .= '<br/>'. date('d/m/Y H:i', strtotime($index->date_sended)) . '<br/>('.$index->name_courier.')';
            }
            else if($index->status == 'FINISH')
            {
                $html .= '<strong>Item Delivered</strong>';
            }
            else if($index->status == 'SUCCESS')
            {
                $html .= '<strong>Success</strong>';

                // Class undoConfirm
                if(Auth::user()->can('undoConfirm-delivery'))
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-default undoConfirm-delivery" data-toggle="modal" data-target="#undoConfirm-delivery" data-id="'.$index->id.'"><i class="fa fa-undo" aria-hidden="true"></i></button>
                    ';
                }

                $html .= '<br/>'. date('d/m/Y H:i', strtotime($index->date_arrived));
                if($index->received_by){
                    $html .= '<br/> Received By : ' . $index->received_by;
                }
            }
            else if($index->status == 'FAILED')
            {
                $html .= '<strong>Failed</strong>';

                // Class undoConfirm
                if(Auth::user()->can('undoConfirm-delivery'))
                {
                    $html .= '
                        <button type="button" class="btn btn-xs btn-default undoConfirm-delivery" data-toggle="modal" data-target="#undoConfirm-delivery" data-id="'.$index->id.'"><i class="fa fa-undo" aria-hidden="true"></i></button>
                    ';
                }
                
                $html .= '<br/>'. $index->reason;
            }

            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if($index->status == 'TAKEN')
            {
                // Class startSend
                if( ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <button class="btn btn-xs btn-success startSend-delivery" data-toggle="modal" data-target="#startSend-delivery" data-id="'.$index->id.'">Start Send</button>
                    ';
                }
            }

            
            if($index->status == 'SENDING')
            {
                // Class finish
                if( ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <button class="btn btn-xs btn-success undoStartSend-delivery" data-toggle="modal" data-target="#undoStartSend-delivery" data-id="'.$index->id.'">Undo Send</button>
                    ';

                    $html .= '
                        <button class="btn btn-xs btn-success finish-delivery" data-toggle="modal" data-target="#finish-delivery" data-id="'.$index->id.'">Finish</button>
                    ';
                }
            }

            if($index->status == 'FINISH')
            {
                // Class finish
                if( ($this->usergrant($index->user_id, 'allUser-delivery') || !$this->levelgrant($index->user_id)) )
                {
                    $html .= '
                        <button class="btn btn-xs btn-default undoFinish-delivery" data-toggle="modal" data-target="#undoFinish-delivery" data-id="'.$index->id.'">Undo Finish</button>
                    ';
                }
            }
            return $html;
        });

        $datatables->editColumn('detail', function ($index) {
            return view('backend.delivery.detail.index', compact('index'));
        });
            

        $datatables = $datatables->make(true);
        return $datatables;
    }
}
