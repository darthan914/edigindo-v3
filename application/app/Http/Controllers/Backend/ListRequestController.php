<?php

namespace App\Http\Controllers\Backend;

use App\Config;
use App\Division;
use App\Http\Controllers\Controller;
use App\Models\ListRequest;
use App\Notifications\Notif;
use App\User;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class ListRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $year = ListRequest::select(DB::raw('YEAR(created_at) as year'))->orderBy('created_at', 'ASC')->distinct()->get();

        $month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

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

        $division        = Division::where('active', 1)->get();
        $type            = ['ITEM' => 'Goods', 'SERVICES' => 'Service'];
        $status_feedback = ['NO_FEEDBACK' => 'No Feedback', 'FEEDBACK' => 'Feedback'];
        $status_confirm  = ['NO_CONFIRM' => 'No Confirm', 'CONFIRMED' => 'Confirm'];

        return view('backend.listRequest.index', compact('request', 'year', 'month', 'user', 'respond', 'type', 'status_feedback', 'status_confirm', 'division', 'feedback_position', 'feedback_user'));
    }

    public function datatables(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        if(in_array(Auth::user()->position, explode(', ', $feedback_position->value)) || in_array(Auth::id(), explode(', ', $feedback_user->value)))
        {
            $f_user = $this->filter($request->f_user);
        }
        else
        {
            $f_user = $this->filter($request->f_user, Auth::id());
        }


        $f_year            = $this->filter($request->f_year, date('Y'));
        $f_month           = $this->filter($request->f_month, date('n'));
        $f_respond         = $this->filter($request->f_respond);
        $f_type            = $this->filter($request->f_type);
        $f_division        = $this->filter($request->f_division);
        $f_status_feedback = $this->filter($request->f_status_feedback);
        $f_status_confirm  = $this->filter($request->f_status_confirm);

        $f_id   = $this->filter($request->f_id);
        $s_item = $this->filter($request->s_item);

        $f_when   = $this->filter($request->f_when);
        $f_result = $this->filter($request->f_result);

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

        $index = $index->orderBy('list_request.id', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('edit-listRequest') && ($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id)) && !$index->datetime_feedback) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning edit-listRequest" data-toggle="modal" data-target="#edit-listRequest"
                        data-id="' . $index->id . '"
                        data-type="' . $index->type . '"
                        data-item="' . $index->item . '"
                        data-division="' . $index->division . '"
                        data-attachment="' . asset($index->attachment) . '"
                    ><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('delete-listRequest') && ($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id)) && !$index->datetime_feedback) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger delete-listRequest" data-toggle="modal" data-target="#delete-listRequest" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('feedback-listRequest') && !$index->datetime_confirm && !$index->datetime_feedback) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-primary feedback-listRequest" data-toggle="modal" data-target="#feedback-listRequest" data-id="' . $index->id . '">Add Feedback</button>
                ';
            }

            if (Auth::user()->can('undoFeedback-listRequest') && !$index->datetime_confirm && $index->datetime_feedback) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning undoFeedback-listRequest" data-toggle="modal" data-target="#undoFeedback-listRequest" data-id="' . $index->id . '">Undo Feedback</button>
                ';
            }

            if (Auth::user()->can('confirm-listRequest') && ($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id)) && $index->datetime_feedback && !$index->datetime_confirm) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-success confirm-listRequest" data-toggle="modal" data-target="#confirm-listRequest" data-id="' . $index->id . '">Confirm Respond</button>
                ';
            }

            if (Auth::user()->can('undoConfirm-listRequest') && ($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id)) && $index->datetime_feedback && $index->datetime_confirm) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-warning undoConfirm-listRequest" data-toggle="modal" data-target="#undoConfirm-listRequest" data-id="' . $index->id . '">Undo Confirm Respond</button>
                ';
            }

            return $html;
        });

        $datatables->editColumn('item', function ($index) {
            $html = '[' . $index->type . '] ' . $index->item;

            if ($index->attachment) {
                $html .= '<br/><a class="btn btn-primary btn-xs" href="' . asset($index->attachment) . '" target="_new"><i class="fa fa-paperclip" aria-hidden="true"></i></a>';
            }

            return $html;
        });

        $datatables->editColumn('feedback', function ($index) {
            $html = 'No Feedback';

            if ($index->datetime_feedback) {
                $html = 'Date Post : ' . date('d/m/Y H:i', strtotime($index->datetime_feedback)) . '<br/>';
                $html .= 'By : ' . $index->respond_fullname . '<br/>';
                $html .= $index->feedback;
            }

            return $html;
        });

        $datatables->editColumn('datetime_confirm', function ($index) {
            $html = 'No Confirm';

            if ($index->datetime_confirm) {
                $html = date('d/m/Y H:i', strtotime($index->datetime_confirm));
            }

            return $html;
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d/m/Y H:i', strtotime($index->created_at));

            return $html;
        });

        $datatables->addColumn('respond_time', function ($index) {
            $html = 'No Feedback';

            if ($index->datetime_feedback) {
                $html = $this->getDateDiff($index->datetime_feedback, $index->created_at, 1);
            }

            return $html;
        });

        $datatables->addColumn('result', function ($index) use ($minimum_request_item_minute, $minimum_request_service_minute) {
            $html = 'No Feedback';

            if ($index->datetime_feedback) {
                switch ($index->type) {
                    case 'ITEM':
                        if ((strtotime($index->datetime_feedback) - strtotime($index->created_at)) < $minimum_request_item_minute->value) {
                            $html = 'GOOD';
                        } else {
                            $html = 'BAD';
                        }
                        break;

                    case 'SERVICES':
                        if ((strtotime($index->datetime_feedback) - strtotime($index->created_at)) < $minimum_request_service_minute->value) {
                            $html = 'GOOD';
                        } else {
                            $html = 'BAD';
                        }
                        break;

                    default:
                        $html = '-';
                        break;
                }

            }

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            if ($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id)) {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }

            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function getStatus(Request $request)
    {
        $f_month = $this->filter($request->f_month, date('n'));
        $f_year  = $this->filter($request->f_year, date('Y'));

        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $division = Division::where('active', 1)->get();

        $listRequest = ListRequest::leftJoin('users', 'list_request.user_id', 'users.id')
            ->leftJoin('users as respond', 'list_request.respond_id', 'respond.id')
            ->select(
                'list_request.*'
            );

        if ($f_month != '') {
            $listRequest->whereMonth('list_request.created_at', $f_month);
        }

        if ($f_year != '') {
            $listRequest->whereYear('list_request.created_at', $f_year);
        }

        $listRequest = $listRequest->get();

        $status = '';

        foreach ($division as $list) {
            $total_today       = 0;
            $total_no_feedback = 0;
            $total_good        = 0;
            $total_bad         = 0;

            foreach ($listRequest as $count) {
                if ($list->code == $count->division &&
                    date('Y-m-d', strtotime($count->created_at)) == date('Y-m-d') && $count->feedback == '') {
                    $total_today += 1;
                }

                if ($list->code == $count->division && $count->feedback == '') {
                    $total_no_feedback += 1;
                }

                if ($list->code == $count->division && $count->feedback &&
                    (
                        (
                            $count->type == 'ITEM' && 
                            (strtotime($count->datetime_feedback) - strtotime($count->created_at)) < $minimum_request_item_minute->value
                        ) || 
                        (
                            $count->type == 'SERVICES' &&
                            (strtotime($count->datetime_feedback) - strtotime($count->created_at)) < $minimum_request_service_minute->value
                        )
                    )
                ) {
                    $total_good += 1;
                }

                if ($list->code == $count->division && $count->feedback &&
                    (
                        (
                            $count->type == 'ITEM' && 
                            (strtotime($count->datetime_feedback) - strtotime($count->created_at)) >= $minimum_request_item_minute->value
                        ) || 
                        (
                            $count->type == 'SERVICES' &&
                            (strtotime($count->datetime_feedback) - strtotime($count->created_at)) >= $minimum_request_service_minute->value
                        )
                    )
                ) {
                    $total_bad += 1;
                }

            }

            $status[] = [
                'name'        => $list->code,
                'today'       => $total_today,
                'no_feedback' => $total_no_feedback,
                'good'        => $total_good,
                'bad'         => $total_bad,
            ];
        }

        return compact('status');
    }

    public function store(Request $request)
    {
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
            return redirect()->back()->withErrors($validator)->withInput()->with('create-listRequest-error', 'Something Errors');
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

        return redirect()->back()->with('success', 'Data Has Been Added');
    }

    public function update(Request $request)
    {
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
            return redirect()->back()->withErrors($validator)->withInput()->with('edit-listRequest-error', 'Something Errors');
        }

        $this->saveArchive('App\Models\ListRequest', 'UPDATED', $index);

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

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = ListRequest::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-listRequest') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\ListRequest', 'DELETED', $index);
        ListRequest::destroy($request->id);

        return redirect()->back()->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if (is_array($request->id)) {
            foreach ($request->id as $list) {

                $index = ListRequest::find($list);

                if (($this->usergrant($index->user_id, 'allUser-listRequest') || $this->levelgrant($index->user_id))) {
                    $id[] = $list;
                }
            }

            if ($request->action == 'delete' && Auth::user()->can('delete-ListRequest')) {
                $index = ListRequest::find($id);
                $this->saveMultipleArchive('App\Models\ListRequest', 'DELETED', $index);

                ListRequest::destroy($id);
                return redirect()->back()->with('success', 'Data Selected Has Been Deleted');
            }

        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function feedback(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $message = [
            'feedback.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'feedback' => 'required',
        ], $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('feedback-listRequest-error', 'Something Errors');
        }

        $index = ListRequest::find($request->id);

        $this->saveArchive('App\Models\ListRequest', 'FEEDBACK', $index);

        $index->respond_id        = Auth::id();
        $index->feedback          = $request->feedback;
        $index->datetime_feedback = date('Y-m-d H:i:s');

        $index->save();

        $html = '
            Your Request ' . $index->item . ' Got Information
        ';

        User::find($index->user_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.listRequest', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function undoFeedback(Request $request)
    {

        $index = ListRequest::find($request->id);

        $this->saveArchive('App\Models\ListRequest', 'UNDO_FEEDBACK', $index);

        $index->respond_id        = null;
        $index->feedback          = null;
        $index->datetime_feedback = null;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function confirm(Request $request)
    {
        $config = Config::all();
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
        }

        $index = ListRequest::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-listRequest') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\ListRequest', 'CONFIRMED', $index);

        $index->datetime_confirm = date('Y-m-d H:i:s');

        $index->save();

        $html = '
            Your Respond ' . $index->item . ' Has Been Confirmed
        ';

        User::find($index->respond_id)->notify(new Notif(Auth::user()->nickname, $html, route('backend.listRequest', ['f_id' => $index->id])));

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }

    public function undoConfirm(Request $request)
    {
        $index = ListRequest::find($request->id);

        if (!$this->usergrant($index->user_id, 'allUser-listRequest') || !$this->levelgrant($index->user_id)) {
            return redirect()->route('backend.spk')->with('failed', 'Access Denied');
        }

        $this->saveArchive('App\Models\ListRequest', 'UNDO_CONFIRM', $index);

        $index->datetime_confirm = null;

        $index->save();

        return redirect()->back()->with('success', 'Data Has Been Updated');
    }
}
