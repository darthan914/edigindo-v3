<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Config;
use App\Division;
use App\Models\ListRequest;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Notifications\Notif;

use Datatables;
use Validator;

class ListRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function collection()
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $year = ListRequest::select(DB::raw('YEAR(created_at) as year'))->orderBy('created_at', 'ASC')->distinct()->get();

        $month = $this->arrayToJson([
            ['value' => '1', 'name' => 'Januari'],
            ['value' => '2', 'name' => 'Febuari'],
            ['value' => '3', 'name' => 'Maret'],
            ['value' => '4', 'name' => 'April'],
            ['value' => '5', 'name' => 'Mei'],
            ['value' => '6', 'name' => 'Juni'],
            ['value' => '7', 'name' => 'Juli'],
            ['value' => '8', 'name' => 'Agustus'],
            ['value' => '9', 'name' => 'September'],
            ['value' => '10', 'name' => 'Oktober'],
            ['value' => '11', 'name' => 'November'],
            ['value' => '12', 'name' => 'Desember'],
        ]);

        $user = ListRequest::join('users', 'list_request.user_id', 'users.id')
            ->select('users.id', 'users.fullname')
            ->orderBy('users.fullname', 'ASC')
            ->distinct();

        if(!(in_array(Auth::user()->position, explode(', ', $feedback_position->value)) || in_array(Auth::id(), explode(', ', $feedback_user->value))))
        {
            if (!Auth::user()->can('allUser-listRequest')) {
                $user->whereIn('list_request.user_id', Auth::user()->staff());
            }
        }

        $user = $user->get();

        $respond = ListRequest::join('users', 'list_request.respond_id', 'users.id')
            ->select('users.id', 'users.fullname')
            ->orderBy('users.fullname', 'ASC')
            ->distinct()->get();

        $division = Division::where('active', 1)->get();

        $type = $this->arrayToJson([
            ['value' => 'ITEM', 'name' => 'Barang'],
            ['value' => 'SERVICES', 'name' => 'Jasa']
        ]);

        $status_feedback = $this->arrayToJson([
            ['value' => 'NO_FEEDBACK', 'name' => 'Belum Ada Feedback'],
            ['value' => 'FEEDBACK', 'name' => 'Sudah difeedback']
        ]);

        $status_confirm = $this->arrayToJson([
            ['value' => 'NO_CONFIRM', 'name' => 'Belum Konfirmasi'],
            ['value' => 'CONFIRMED', 'name' => 'Sudah Konfirmasi']
        ]);

        $data = compact('year', 'month', 'user', 'respond', 'type', 'status_feedback', 'status_confirm', 'division', 'feedback_position');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function index(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-listRequest')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $f_user            = $this->filter($request->f_user, Auth::id());
        $f_year            = $this->filter($request->f_year, date('Y'));
        $f_month           = $this->filter($request->f_month);
        $f_respond         = $this->filter($request->f_respond);
        $f_type            = $this->filter($request->f_type);
        $f_division        = $this->filter($request->f_division);
        $f_status_feedback = $this->filter($request->f_status_feedback);
        $f_status_confirm  = $this->filter($request->f_status_confirm);

        $f_id   = $this->filter($request->f_id);
        $s_item = $this->filter($request->s_item);

        $f_when   = $this->filter($request->f_when);
        $f_result = $this->filter($request->f_result);

        $limit  = $this->filter($request->limit, 20);
        $sort  = $this->filter($request->sort, 'list_request.id');
        $order = $this->filter($request->order, 'DESC');


        $index = ListRequest::leftJoin('users', 'list_request.user_id', 'users.id')
            ->leftJoin('users as respond', 'list_request.respond_id', 'respond.id')
            ->select(
                'list_request.*',
                'users.fullname as user_fullname',
                'respond.fullname as respond_fullname'
            );

        if ($s_item != '' || $f_id != '') {
            if ($s_item != '') {
                $index->where('list_request.item', 'like', '%' . $s_item . '%');
            }

            if ($f_id != '') {
                $index->where('list_request.id', $f_id);
            }

        } else {
            if ($f_month != '') {
                $index->whereMonth('list_request.created_at', $f_month);
            }

            if ($f_year != '') {
                $index->whereYear('list_request.created_at', $f_year);
            }

            if ($f_user == 'staff') {
                $index->whereIn('user_id', Auth::user()->staff());
            } else if ($f_user != '') {
                $index->where('user_id', $f_user);
            }

            if ($f_respond != '') {
                $index->where('respond_id', $f_respond);
            }

            if ($f_type != '') {
                $index->where('type', $f_type);
            }

            if ($f_division != '') {
                $index->where('list_request.division', $f_division);
            }

            if ($f_status_feedback == 'NO_FEEDBACK') {
                $index->whereNull('datetime_feedback');
            } else if ($f_status_feedback == 'FEEDBACK') {
                $index->whereNotNull('datetime_feedback');
            }

            if ($f_status_confirm == 'NO_CONFIRM') {
                $index->whereNull('datetime_confirm');
            } else if ($f_status_confirm == 'CONFIRMED') {
                $index->whereNotNull('datetime_confirm');
            }

            switch ($f_when) {
                case 'TODAY':
                    $index->whereDate('list_request.created_at', date('Y-m-d'));
                    break;

                default:
                    //
                    break;
            }

            switch ($f_result) {
                case 'GOOD':

                    $index->where(function ($query) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                        $query->where(function ($query2) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                            $query2->where(DB::raw("TIMESTAMPDIFF(SECOND,'list_request.datetime_feedback','list_request.created_at') < " . $minimum_request_item_minute->value))->where('type', 'ITEM');

                            
                        })
                        ->orWhere(function ($query2) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                            $query2->where(DB::raw("TIMESTAMPDIFF(SECOND,'list_request.datetime_feedback','list_request.created_at') < " . $minimum_request_service_minute->value))->where('type', 'SERVICES');

                            
                        });
                    });

                    break;

                case 'BAD':
                    
                    $index->where(function ($query) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                        $query->where(function ($query2) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                            $query2->where(DB::raw("TIMESTAMPDIFF(SECOND,'list_request.datetime_feedback','list_request.created_at') >= " . $minimum_request_item_minute->value))->where('type', 'ITEM');

                            
                        })
                        ->orWhere(function ($query2) use ($minimum_request_item_minute, $minimum_request_service_minute, $f_division){
                            $query2->where(DB::raw("TIMESTAMPDIFF(SECOND,'list_request.datetime_feedback','list_request.created_at') >= " . $minimum_request_service_minute->value))->where('type', 'SERVICES');


                        });
                    });

                    break;

                default:
                    //
                    break;
            }
        }

        $index = $index->orderBy($sort, $order)->paginate($limit);

        $data = compact('index');

        return response()->json(compact('status', 'message', 'data'));
    }

    public function view(Request $request)
    {
        $status  = 'OK';
        $message = '';
        $data    = '';

        if (!Auth::user()->can('list-listRequest')) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $data = ListRequest::leftJoin('users', 'list_request.user_id', 'users.id')
            ->leftJoin('users as respond', 'list_request.respond_id', 'respond.id')
            ->select(
                'list_request.*',
                'users.fullname as user_fullname',
                'respond.fullname as respond_fullname'
            )
            ->where('listRequest.id', $request->id)
            ->first();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function store(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been added';
        $data    = '';

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'type.required'     => 'This field required.',
            'item.required'     => 'This field required.',
            'division.required' => 'This field required.',
            'image.image'       => 'Image Only.',
        ];

        $validator = Validator::make($request->all(), [
            'type'     => 'required',
            'item'     => 'required',
            'division' => 'required',
            'image'    => 'image|nullable',
        ], $message);

        if ($validator->fails()) {
            $status  = 'ERROR';
            $message = 'Invalid Input';

            return response()->json(compact('status', 'message', 'data'))->withErrors($validator);
        }

        $index = new ListRequest;

        $index->user_id  = Auth::id();
        $index->type     = $request->type;
        $index->division = $request->division;
        $index->item     = $request->item;

        if ($request->hasFile('attachment')) {
            $pathSource = 'upload/listRequest/';
            $file       = $request->file('attachment');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            $file->move($pathSource, $filename);
            $index->attachment = $pathSource . $filename;
        }

        $index->save();

        $feedback_notif = User::where(function ($query) use ($feedback_position, $feedback_user) {
            $query->whereIn('position', explode(', ', $feedback_position->value))
                ->orWhereIn('id', explode(', ', $feedback_user->value));
        })
            ->get();

        $html = '
            New Request List From' . Auth::user()->fullname . '
        ';

        foreach ($feedback_notif as $list) {
            $list->notify(new Notif(Auth::user()->nickname, $html, route('backend.listRequest', ['f_id' => $index->id]) ) );
        }

        return response()->json(compact('status', 'message', 'data'));
    }

    public function update(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been updated';
        $data    = '';

        $index = ListRequest::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-listRequest') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $message = [
            'type.required'     => 'This field required.',
            'item.required'     => 'This field required.',
            'division.required' => 'This field required.',
            'image.image'       => 'Image Only.',
        ];

        $validator = Validator::make($request->all(), [
            'type'     => 'required',
            'item'     => 'required',
            'division' => 'required',
            'image'    => 'image|nullable',
        ], $message);

        if ($validator->fails()) {
            $status  = 'ERROR';
            $message = 'Invalid Input';

            return response()->json(compact('status', 'message', 'data'))->withErrors($validator);
        }

        $this->saveArchive('LIST_REQUEST', 'UPDATED', $index);

        $index->type     = $request->type;
        $index->division = $request->division;
        $index->item     = $request->item;

        if (isset($request->remove_attachment)) {
            File::delete($index->attachment);
            $index->attachment = null;
        } else if ($request->hasFile('attachment')) {
            if ($index->attachment) {
                File::delete($index->attachment);
            }
            $pathSource = 'upload/listRequest/';
            $fileData   = $request->file('attachment');
            $filename   = time() . '.' . $fileData->getClientOriginalExtension();
            $fileData->move($pathSource, $filename);

            $index->attachment = $pathSource . $filename;
        }

        $index->save();

        return response()->json(compact('status', 'message', 'data'));
    }

    public function delete(Request $request)
    {
        $status  = 'OK';
        $message = 'Data has been deleted';
        $data    = '';

        $index = ListRequest::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-listRequest') || !$this->levelgrant($index->user_id)) {
            $status  = 'ERROR';
            $message = 'Access Denied';

            return response()->json(compact('status', 'message', 'data'));
        }

        $this->saveArchive('LIST_REQUEST', 'DELETED', $index);
        ListRequest::destroy($request->id);

        return response()->json(compact('status', 'message', 'data'));
    }
}
