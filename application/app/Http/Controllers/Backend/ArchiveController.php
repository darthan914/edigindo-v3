<?php

namespace App\Http\Controllers\Backend;

use App\Models\Archive;
use App\OfferList;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;

class ArchiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        return view('backend.archive.index')->with(compact('request'));
    }

    public function datatables(Request $request)
    {
        $index = Archive::leftJoin('users', 'archives.user_id', 'users.id')
            ->select('archives.*', 'users.fullname')
            ->orderBy('created_at', 'DESC')
            ->get();

        $datatables = Datatables::of($index);

        $datatables->editColumn('data', function ($index) {
            $json = json_decode($index->data);
			return json_encode($json, JSON_PRETTY_PRINT);
        });

        $datatables->addColumn('action2', function ($index) {
            $html = '';

            if($index->source != "AUTH")
            {
                $html .= '
                    <button class="btn btn-xs btn-success recover-archive" data-toggle="modal" data-target="#recover-archive" data-id="'.$index->id.'"><i class="fa fa-recycle"></i></button>
                ';
            }
            
                
            return $html;
        });

        // $datatables->addColumn('check', function ($index) {
        //     $html = '';
        //     $html .= '
        //         <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
        //     ';
        //     return $html;
        // });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function recover(Request $request)
    {
        $index = Archive::find($request->id);
        
        DB::transaction(function () use ($request){
            $index = Archive::find($request->id);

            $json = json_decode($index->data);
            $array = json_decode($index->data, true);

            eval('$index->source::updateOrCreate([\'id\' => $json->id], $array);');

        });

        return redirect()->back()->with('success', 'Data Has Been Recovered');
    }

}
