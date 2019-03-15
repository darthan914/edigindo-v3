<?php

namespace App\Http\Controllers\Api;

use App\Delivery;
use App\User;
use App\Config;

use App\Http\Controllers\Controller;
use App\Notifications\Notif;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public $auth = '';
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function collection()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $user = Delivery::join('users', 'users.id', '=', 'delivery.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if(!Auth::user()->can('allUser-delivery'))
        {
            $user->whereIn('user_id', Auth::user()->staff());
        }

        $user = $user->get();

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

        $via = $this->arrayToJson([
            ['value' => 'Kurir', 'name' => 'Kurir'],
            ['value' => 'Supir', 'name' => 'Supir']
        ]);

        $statusf = $this->arrayToJson([
            ['value' => 'WAITING', 'name' => 'Waiting'],
            ['value' => 'TAKEN', 'name' => 'Take'],
            ['value' => 'SENDING', 'name' => 'Sending'],
            ['value' => 'FINISH', 'name' => 'Finish'],
            ['value' => 'SUCCESS', 'name' => 'Success'],
            ['value' => 'FAILED', 'name' => 'Falied']
        ]);

        $when = $this->arrayToJson([
            ['value' => 'today', 'name' => 'Today'],
            ['value' => 'tomorrow', 'name' => 'Tomorrow'],
            ['value' => 'future', 'name' => 'Next Day'],
            ['value' => 'yesterday', 'name' => 'Last Day']
        ]);

        $sort = $this->arrayToJson([
            ['value' => 'delivery.id', 'name' => 'Created'],
            ['value' => 'delivery.project', 'name' => 'Project'],
            ['value' => 'delivery.spk', 'name' => 'SPK'],
            ['value' => 'delivery.datetime_send', 'name' => 'Date Send'],
            ['value' => 'delivery.city', 'name' => 'City'],
        ]);

        $data = compact('user', 'courier', 'via', 'city', 'statusf', 'when', 'sort');

        return response()->json(compact('status', 'message', 'data'));
    }

    // Decrepted
    public function user()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $data = Delivery::join('users', 'users.id', '=', 'delivery.user_id')
            ->select('users.fullname', 'users.id')
            ->orderBy('users.fullname', 'ASC')->distinct();

        if (!Auth::user()->can('allUser-delivery')) {
            $data->whereIn('user_id', Auth::user()->staff());
        }

        $data = $data->get();

        return response()->json(compact('status', 'message', 'data'));
    }

    // Decrepted
    public function userCourier()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';
        
        $data = Delivery::select('delivery.courier_id as id', 'users.fullname')
            ->join('users', 'delivery.courier_id', 'users.id')
            ->where('delivery.courier_id', '<>', 0)
            ->orderBy('users.fullname', 'ASC')
            ->distinct();

        if (!Auth::user()->can('allCourier-delivery')) {
            $data->whereIn('courier_id', Auth::user()->staff());
        }

        $data = $data->get();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function index(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $limit  = $this->filter($request->limit, 20);

        $f_via     = $this->filter($request->f_via);
        $f_range   = $this->filter($request->f_range);
        $f_when    = $this->filter($request->f_when);
        $f_status  = $this->filter($request->f_status);
        $f_user    = $this->filter($request->f_user);
        $f_id      = $this->filter($request->f_id);
        $f_city    = $this->filter($request->f_city);
        $s_spk     = $this->filter($request->s_spk);
        $s_project = $this->filter($request->s_project);

        $sort  = $this->filter($request->sort, 'id');
        $order = $this->filter($request->order, 'DESC');

        $index = Delivery::join('users', 'delivery.user_id', 'users.id')
            ->select('delivery.*', 'users.fullname');

        $group = Delivery::select('city', DB::raw('COUNT(id) as count_city'));
        
        if($s_spk != '' || $s_project != '')
        {
            if($s_spk != '')
            {
                $index->where('delivery.spk', 'like' , '%'.$s_spk.'%');
                $group->where('delivery.spk', 'like' , '%'.$s_spk.'%');
            }

            if($s_project != '')
            {
                $index->where('delivery.project', 'like' , '%'.$s_project.'%');
                $group->where('delivery.project', 'like' , '%'.$s_project.'%');
            }
        }
        else if ($f_id != '') {
            $index->where('delivery.id', $f_id);
            $group->where('delivery.id', $f_id);
        } 
        else {
            if ($f_via != '') {
                $index->where('via', $f_via);
                $group->where('via', $f_via);
            }

            if ($f_range != '') {
                $date    = explode(' - ', $f_range);
                $date[0] = date('Y-m-d H:i:s', strtotime($date[0]));
                $date[1] = date('Y-m-d H:i:s', strtotime($date[1] . '+1 day'));

                $index->where('datetime_send', '>=', $date[0])
                    ->where('datetime_send', '<=', $date[1]);
                $group->where('datetime_send', '>=', $date[0])
                    ->where('datetime_send', '<=', $date[1]);
            }

            if ($f_when != '' && $f_when == 'yesterday') 
            {
                $index->where('datetime_send', '<', date('Y-m-d'));
                $group->where('datetime_send', '<', date('Y-m-d'));
            }
            else if ($f_when != '' && $f_when == 'today') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d').'%');
                $group->where('datetime_send', 'LIKE', date('Y-m-d').'%');
            }
            else if ($f_when != '' && $f_when == 'tomorrow') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
                $group->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
            }
            else if ($f_when != '' && $f_when == 'future') 
            {
                $index->where('datetime_send', '>', date('Y-m-d'));
                $group->where('datetime_send', '>', date('Y-m-d'));
            }

            if ($f_status != '') {
                $index->where('status', $f_status);
                $group->where('status', $f_status);
            }

            if ($f_city != '') {
                $index->where('city', $f_city);
                $group->where('city', $f_city);
            }

            if ($f_user == 'staff') {
                $index->whereIn('user_id', Auth::user()->staff());
                $group->whereIn('user_id', Auth::user()->staff());
            } else if ($f_user != '') {
                $index->where('user_id', $f_user);
                $group->where('user_id', $f_user);
            }
        }

        $index = $index->orderBy($sort, $order)->paginate($limit);
        $group = $group->groupBy('city')->get();

        $data = compact('index', 'group');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function view(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $data = Delivery::join('users', 'delivery.user_id', 'users.id')
            ->select('delivery.*', 'users.fullname')
            ->where('delivery.id', $request->id)
            ->first();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function courier(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->hasAccess('courier-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $limit  = $this->filter($request->limit, 20);

        $f_when    = $this->filter($request->f_when);
        $f_status  = $this->filter($request->f_status);
        $f_user    = $this->filter($request->f_user);
        $f_courier = $this->filter($request->f_courier,Auth::id());
        $f_id      = $this->filter($request->f_id);

        $sort  = $this->filter($request->sort, 'delivery.id');
        $order = $this->filter($request->order, 'DESC');

        $index = Delivery::join('users as user_request', 'delivery.user_id', 'user_request.id')
            ->join('users as courier', 'delivery.courier_id', 'courier.id')
            ->select('delivery.*', 'user_request.fullname as user_name' , 'user_request.phone as user_phone', 'courier.fullname as courier_name');

        $group = Delivery::select('city', DB::raw('COUNT(id) as count_city'));

        if ($f_id != '') {
            $index->where('delivery.id', $f_id);
            $group->where('delivery.id', $f_id);
        } else {

            if ($f_when != '' && $f_when == 'yesterday') 
            {
                $index->where('datetime_send', '<', date('Y-m-d'));
                $group->where('datetime_send', '<', date('Y-m-d'));
            }
            else if ($f_when != '' && $f_when == 'today') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d').'%');
                $group->where('datetime_send', 'LIKE', date('Y-m-d').'%');
            }
            else if ($f_when != '' && $f_when == 'tomorrow') 
            {
                $index->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
                $group->where('datetime_send', 'LIKE', date('Y-m-d', strtotime('+1 day')).'%');
            }
            else if ($f_when != '' && $f_when == 'future') 
            {
                $index->where('datetime_send', '>', date('Y-m-d'));
                $group->where('datetime_send', '>', date('Y-m-d'));
            }

            if ($f_status == 'FINISH') {
                $index->whereIn('status', ['FINISH', 'SUCCESS', 'FAILED']);
                $group->whereIn('status', ['FINISH', 'SUCCESS', 'FAILED']);
            } else if ($f_status != '') {
                $index->where('status', $f_status);
                $group->where('status', $f_status);
            }

            if ($f_user != '') {
                $index->where('user_id', $f_user);
                $group->where('user_id', $f_user);
            }

            if ($f_courier == 'staff') {
                $index->whereIn('courier_id', Auth::user()->staff());
                $group->whereIn('courier_id', Auth::user()->staff());
            } else if ($f_courier != '') {
                $index->where('courier_id', $f_courier);
                $group->where('courier_id', $f_courier);
            }
        }

        $index = $index->orderBy($sort, $order)->paginate($limit);
        $group = $group->groupBy('city')->get();

        $data = compact('index', 'group');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function take(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        if (!Auth::user()->hasAccess('take-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index = Delivery::find($request->id);

        if ($index->status != "WAITING") {
            $status  = 'ERROR';
            $message = 'Data already Taken';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index->courier_id   = Auth::id();
        $index->name_courier = Auth::user()->fullname;
        $index->status       = 'TAKEN';

        $html = '
			Your Request Delivery has been taken By : ' . $index->name_courier . '
		';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        $index->save();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function undoTake(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        if (!Auth::user()->hasAccess('undoTake-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index = Delivery::find($request->id);

        if (in_array($index->status, ['SUCCESS', 'FAILED'])) {
            $status  = 'ERROR';
            $message = 'Data cannot to undo';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index->courier_id   = 0;
        $index->name_courier = null;
        $index->status       = 'WAITING';

        $index->save();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function startSend(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = Delivery::find($request->id);

        if ($index->courier_id != Auth::user()->id) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $check = Delivery::where('status', 'SENDING')->where('courier_id', Auth::user()->id)->first();

        if($check)
        {
            $status  = 'ERROR';
            $message = 'Complete your current task first';

            return response()->json(compact('status', 'message', 'data'));
        }

        // $geocode = file_get_contents('https://maps.google.com/maps/api/distancematrix/json?origins=' . (urlencode($index->latitude . ',' . $index->longitude)) . '&destinations=' . (urlencode($request->start_latitude . ',' . $request->start_longitude)) . '&key=AIzaSyB7nvRGWqvtGGXyrdyfcG-xJsMfWaoGVY8');
        $geocode = file_get_contents('https://maps.google.com/maps/api/distancematrix/json?origins=' . (urlencode($index->latitude . ',' . $index->longitude)) . '&destinations=' . (urlencode($request->start_latitude . ',' . $request->start_longitude)) . '&key='.env('GOOGLE_MAPS_API'));
        $output  = json_decode($geocode);

        $index->start_latitude  = $request->start_latitude;
        $index->start_longitude = $request->start_longitude;
        $index->distance        = $output->rows[0]->elements[0]->distance->value ?? 0;
        $index->date_sended     = date('Y-m-d H:i:s');
        $index->status          = 'SENDING';

        $index->save();

        $html = '
			Your Request Delivery is on the way by : ' . $index->name_courier . '
		';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }

    public function undoStartSend(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = Delivery::find($request->id);

        if (in_array($index->status, ['SUCCESS', 'FAILED'])) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index->start_latitude  = null;
        $index->start_longitude = null;
        $index->distance        = null;
        $index->date_sended     = null;
        $index->status          = 'TAKEN';

        $index->save();

        $html = '
			Your Request Delivery current on the way is revert by : ' . $index->name_courier . '
		';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }

    public function finish(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = Delivery::find($request->id);

        if ($index->courier_id != Auth::user()->id) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index->status        = 'FINISH';
        $index->date_arrived  = date('Y-m-d H:i:s');
        $index->end_latitude  = $request->end_latitude;
        $index->end_longitude = $request->end_longitude;
        $index->received_by   = $request->received_by;
        $index->save();

        $html = '
			Your Request is Delivered by : ' . $index->name_courier . '
		';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }

    public function undoFinish(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = Delivery::find($request->id);

        if (in_array($index->status, ['SUCCESS', 'FAILED'])) {
            $status  = 'ERROR';
            $message = 'Data cannot to undo';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index->status        = 'SENDING';
        $index->date_arrived  = null;
        $index->end_latitude  = null;
        $index->end_longitude = null;
        $index->received_by   = null;
        $index->save();

        $html = '
			Your Request Delivered is revert by : ' . $index->name_courier . '
		';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }

    public function confirm(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        if (!Auth::user()->hasAccess('confirm-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index = Delivery::find($request->id);

        if (in_array($index->status, ['SUCCESS', 'FAILED'])) {
            $status  = 'ERROR';
            $message = 'Data cannot to update';

            return response()->json(compact('status', 'message', 'data'));
        }

        if ($request->status == 'SUCCESS') {
            $index->status = 'SUCCESS';
        } else if ($request->status == 'FAILED') {
            $index->reason = $request->reason;
            $index->status = 'FAILED';
        }

        $index->save();

        $html = '
			Your Delivery has been rate
		';

        User::find($index->courier_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery.courier', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }

    public function undoConfirm(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        if (!Auth::user()->hasAccess('undoConfirm-delivery')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $index = Delivery::find($request->id);

        $index->date_arrived  = null;
        $index->received_by   = null;
        $index->end_latitude  = null;
        $index->end_longitude = null;
        $index->reason        = null;
        $index->status        = 'SENDING';

        $index->save();

        $html = '
			Your Delivery has been revert confirm
		';

        User::find($index->courier_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.delivery.courier', ['f_id' => $index->id])));

        return response()->json(compact('status', 'message', 'data'));
    }
}
