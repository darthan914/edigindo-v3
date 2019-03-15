<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Auth;
use Datatables;

class NotificationController extends Controller
{
    public function get()
    {
    	$index = Auth::user()->notifications()->limit(5)->get();
    	$unreadCount = Auth::user()->unreadNotifications()->count();

        $unreadList = '';

        foreach ($index as $list) {
            $unreadList[] = [
                "from"     => $list->data['from'] . ' ' . ($list->read_at == null ? '(Unread)' : ''),
                "id"       => $list->id,
                "messages" => $list->data['messages'],
                "slug"     => $list->data['slug'],
                "type"     => $list->type,
            ];
        }

    	return compact('unreadList', 'unreadCount');
    }

    public function index(Request $request)
    {
    	return view('backend.notification.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Auth::user()->notifications()->orderBy('created_at', 'DESC')->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('data', function ($index) {
            $html = '
            	<p>From : <b>'. $index->data['from'] .'</b></p>
            	<p>'. $index->data['messages'] .'</p>

            ';
                
            return $html;
        });

        $datatables->editColumn('created_at', function ($index) {
            $html = date('d/m/Y H:i:s', strtotime($index->created_at));
                
            return $html;
        });

        $datatables->editColumn('read_at', function ($index) {

        	if($index->read_at != null)
        	{
        		$html = date('d/m/Y H:i:s', strtotime($index->read_at));
        	}
        	else
        	{
        		$html = 'Unread';
        	}
            
                
            return $html;
        });

        $datatables->addColumn('action', function ($index) {
            $html = '';


	        $html .= '
	            <a href="' . route('backend.notification.view', ['id' => $index->id]) . '" class="btn btn-xs btn-info"><i class="fa fa-link"></i></a>
	        ';

	        $html .= '
	            <button class="btn btn-xs btn-danger delete-notification" data-toggle="modal" data-target="#delete-notification" data-id="'.$index->id.'"><i class="fa fa-trash"></i></button>
	        ';
                
            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function ajaxNavbar()
    {
        $index = Auth::user()->unreadNotifications()->limit(5)->get();

        return view('backend.notification.navbar')->with(compact('index'));
    }

    public function view($id)
    {
    	$index = Auth::user()->notifications()->find($id);

    	if($index->read_at == null)
    	{
    		Auth::user()->unreadNotifications()->find($id)->markAsRead();
    	}

    	return redirect($index->data['slug']);
    }

    public function delete(Request $request)
    {
    	Auth::user()->notifications()->find($request->id)->delete();

    	return redirect()->back();
    }

    public function action(Request $request)
    {
        if ($request->action == 'delete') {
            Notification::destroy($request->id);
            return redirect()->back()->with('success', 'Data Has Been Deleted');
        }
    }

    public function push($id)
    {
        return Notification::find($id);
    }
}
