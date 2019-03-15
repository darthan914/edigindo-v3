<?php

namespace App\Http\Controllers\Backend;

use App\User;
use App\Models\Position;
use App\Models\Division;
use App\Models\Archive;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Session;
use File;
use Datatables;
use Validator;

use Mail;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $position = Position::where('active', 1);

        if (!Auth::user()->can('full-user')) {
            $position->whereBetween('_lft', [Auth::user()->positions->_lft, Auth::user()->positions->_rgt]);
        }

        $position = $position->get();

        return view('backend.user.index', compact('request', 'position'));
    }

    public function datatables(Request $request)
    {
        // $this->fixTree();
        
        $f_user     = $this->filter($request->f_user);
        $f_position = $this->filter($request->f_position);
        $f_active   = $this->filter($request->f_active);
        $search     = $this->filter($request->search);

        $index = User::select('*')->withDepth();

        if($search != '')
        {
            $index->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%');
            });
        }
        else
        {
            if ($f_user == 'child') {
                $index->where('id', Auth::id())->orWhere('parent_id', Auth::id());
            } elseif ($f_user == 'new') {
                $index->whereNotNull('verification');
            }

            if ($f_position != '') {
                $index->where('position_id', $f_position);
            }

            if ($f_active != '' && $f_active == 1) {
                $index->where('active', 1);
            } else if ($f_active === '0') {
                $index->where('active', '<>', 1);
            }
        }

        

        $index = $index->orderBy('_lft', 'ASC')->get();

        $datatables = Datatables::of($index);

        $datatables->addColumn('action', function ($index) {
            $html = '';

            if (Auth::user()->can('update-user',$index)) {
                $html .= '
                    <a href="' . route('backend.user.edit', ['id' => $index->id]) . '" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                ';

                if ($index->active) {
                    $html .= '
                        <button class="btn btn-xs btn-dark inactive-user" data-toggle="modal" data-target="#inactive-user" data-id="' . $index->id . '"><i class="fa fa-eye-slash"></i></button>
                    ';
                } else {
                    $html .= '
                        <button class="btn btn-xs btn-success active-user" data-toggle="modal" data-target="#active-user" data-id="' . $index->id . '"><i class="fa fa-eye"></i></button>
                    ';
                }
            }

            if (Auth::user()->can('access-user',$index)) {
                $html .= '
                    <a href="' . route('backend.user.access', ['id' => $index->id]) . '" class="btn btn-xs btn-default"><i class="fa fa-key"></i></a>
                ';
            }

            if (Auth::user()->can('delete-user',$index)) {
                $html .= '
                    <button class="btn btn-xs btn-danger delete-user" data-toggle="modal" data-target="#delete-user" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
                ';
            }

            if (Auth::user()->can('impersonate-user',$index)) {
                $html .= '
                    <button class="btn btn-xs btn-info impersonate-user" data-toggle="modal" data-target="#impersonate-user" data-id="' . $index->id . '"><i class="fa fa-sign-in"></i></button>
                ';
            }

            return $html;
        });


        $datatables->addColumn('check', function ($index) {
            $html = '';

            if (Auth::user()->can('check-user',$index)) {
                $html .= '
                    <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
                ';
            }
            return $html;
        });

        $datatables->editColumn('_lft', function ($index) {
            $html = str_repeat ( '-&nbsp;&nbsp;&nbsp;' , max($index->depth, 0)) . $index->fullname;
            return $html;
        });

        $datatables->editColumn('information', function ($index) {
            $html = '<b>' . $index->username . '</b><br/>' . $index->email;

            if($index->verification)
            {
                $html .= ' <a href="'.route('backend.user.resend', $index->id).'">Resend</a>';
            }

            $html .= '<br/>'. $index->positions->name . ' - ' . ($index->divisions->name ?? 'Any') . '<br/>NO. ' . $index->no_ae ?? 'error';

            $html .= '<br/>';

            if ($index->active == 1) {
                $html .= '
                    <span class="label label-success">Active</span>
                ';
            } else if ($index->active == -1) {
                $html .= '
                    <span class="label label-warning">Impersonatable</span>
                ';
            } else {
                $html .= '
                    <span class="label label-default">Inactive</span>
                ';
            }
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
    }

    public function create()
    {
        if(Auth::user()->can('full-user'))
        {
            $position = Position::where('active', 1)->get();
            $parent   = User::where('active', 1)->get();
        }
        else
        {
            $position = Position::whereBetween('_lft', [Auth::user()->positions->_lft, Auth::user()->positions->_rgt])
                ->where('active', 1)
                ->get();

            $parent   = User::where('active', 1)
                ->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt])
                ->get();
        }

        $division    = Division::all();

        return view('backend.user.create', compact('position', 'division', 'parent'));
    }

    public function store(Request $request)
    {

        if (Auth::user()->can('position-user')) {

            $validator = Validator::make($request->all(), [
                'email'         => 'required|unique:users,email|email',
                'position_id'   => 'required|integer',
                'password_user' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'email'         => 'required|unique:users,email|email',
                'password_user' => 'required',
            ]);
        }

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password_user, Auth::user()->password)) {
                $validator->errors()->add('password_user', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $verification = str_random(30);

        $index = new User;

        $index->username   = str_random(10);
        $index->email      = $request->email;
        $index->password   = bcrypt(str_random(10));
        $index->first_name = 'New';
        $index->last_name  = 'User';

        $index->position_id = $request->position_id;
        $index->division_id = $request->division_id;

        $index->phone        = '00000000000';
        $index->no_ae        = $this->getNoAE($index->position);
        $index->active       = 0;
        $index->parent_id    = $request->parent_id;
        $index->verification = $verification;

        $index->save();

        saveArchives($index, Auth::id(), "Create User", $request->except(['password', 'password_confirmation', 'password_user', '_token']));

        Mail::send('email.verification', compact('index'), function ($message) use ($index) {
            $message->to($index->email)->subject('Register for Edigindo');
        });

        return redirect()->route('backend.user')->with('success', 'Data Has Been Added');
    }

    public function edit(User $index)
    {
        if(Auth::user()->can('full-user'))
        {
            $position = Position::where('active', 1)->get();
            $parent   = User::where('active', 1)->get();
        }
        else
        {
            $position = Position::whereBetween('_lft', [Auth::user()->positions->_lft, Auth::user()->positions->_rgt])
                ->where('active', 1)
                ->get();

            $parent   = User::where('active', 1)
                ->whereBetween('_lft', [Auth::user()->_lft, Auth::user()->_rgt])
                ->get();
        }
            
        $division    = Division::all();

        return view('backend.user.edit', compact('index', 'position', 'division', 'parent'));
    }

    public function update(User $index, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username'      => 'required|unique:users,username,' . $index->id,
            'email'         => 'required|email|unique:users,email,' . $index->id,
            'password'      => 'nullable|confirmed',
            'position_id'   => 'required|integer',
            'first_name'    => 'required',
            'password_user' => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password_user, Auth::user()->password)) {
                $validator->errors()->add('password_user', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request, $index){

            saveArchives($index, Auth::id(), "Update User", $request->except(['password', 'password_confirmation', 'password_user', '_token']));

            $index->username      = $request->username;
            $index->email         = $request->email;
            $index->password      = $request->password != '' ? bcrypt($request->password) : $index->password;
            $index->first_name    = $request->first_name;
            $index->last_name     = $request->last_name;

            $index->position_id = $request->position_id;
            $index->division_id = $request->division_id;

            $index->phone = $request->phone;
            $index->no_ae = $request->no_ae ?? $this->getNoAE($index->position_id);

            if (isset($request->remove_signature)) {
                if ($index->signature != '') {
                    File::delete($index->signature);
                    $index->signature = null;
                }
            } else if ($request->hasFile('signature')) {
                $pathSource = 'upload/user/signature/';
                $file       = $request->file('signature');
                $filename   = time() . '.' . $file->getClientOriginalExtension();

                if ($file->move($pathSource, $filename)) {
                    if ($index->signature != '') {
                        File::delete($index->signature);
                        $index->signature = null;
                    }
                    $index->signature = $pathSource . $filename;
                }
            }

            if (isset($request->remove_photo)) {
                if ($index->photo != '') {
                    File::delete($index->photo);
                    $index->photo = null;
                }
            } else if ($request->hasFile('photo')) {
                $pathSource = 'upload/user/photo/';
                $file       = $request->file('photo');
                $filename   = time() . '.' . $file->getClientOriginalExtension();

                if ($file->move($pathSource, $filename)) {
                    if ($index->photo != '') {
                        File::delete($index->photo);
                        $index->photo = null;
                    }
                    $index->photo = $pathSource . $filename;
                }
            }

            $index->active = $request->active ?? 0;
            $index->parent_id = $request->parent_id;

            $index->save();

            if($index->verification)
            {
                Mail::send('email.verification', compact('index'), function ($message) use ($index) {
                    $message->to($index->email)->subject('Register for Edigindo');
                });
            }

        });

        return redirect()->route('backend.user.index')->with('success', 'Data Has Been Updated');
    }

    public function profile()
    {
        $index = User::find(Auth::id());

        return view('backend.user.profile', compact('index'));
    }

    public function updateProfile(Request $request)
    {
        

        $validator = Validator::make($request->all(), [
                'username'      => 'required|unique:users,username,' . Auth::id(),
                'email'         => 'required|email|unique:users,email,' . Auth::id(),
                'password'      => 'nullable|confirmed',
                'first_name'      => 'required',
                'password_user' => 'required',
            ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password_user, Auth::user()->password)) {
                $validator->errors()->add('password_user', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request){

            $index = User::find(Auth::id());
            saveArchives($index, Auth::id(), "Update User Profile", $request->except(['password', 'password_confirmation', 'password_user', '_token']));

            $index->username   = $request->username;
            $index->email      = $request->email;
            $index->password   = $request->password != '' ? bcrypt($request->password) : $index->password;
            $index->first_name = $request->first_name;
            $index->last_name  = $request->last_name;
            $index->phone      = $request->phone;

            if (isset($request->remove_signature)) {
                if ($index->signature != '') {
                    File::delete($index->signature);
                    $index->signature = null;
                }
            } else if ($request->hasFile('signature')) {
                $pathSource = 'upload/user/signature/';
                $file       = $request->file('signature');
                $filename   = time() . '.' . $file->getClientOriginalExtension();

                if ($file->move($pathSource, $filename)) {
                    if ($index->signature != '') {
                        File::delete($index->signature);
                        $index->signature = null;
                    }
                    $index->signature = $pathSource . $filename;
                }
            }

            if (isset($request->remove_photo)) {
                if ($index->photo != '') {
                    File::delete($index->photo);
                    $index->photo = null;
                }
            } else if ($request->hasFile('photo')) {
                $pathSource = 'upload/user/photo/';
                $file       = $request->file('photo');
                $filename   = time() . '.' . $file->getClientOriginalExtension();

                if ($file->move($pathSource, $filename)) {
                    if ($index->photo != '') {
                        File::delete($index->photo);
                        $index->photo = null;
                    }
                    $index->photo = $pathSource . $filename;
                }
            }

            $index->save();

        });

        return redirect()->route('backend.home')->with('success', 'Profile Has Been Updated');
    }

    public function delete(Request $request)
    {
        $index = User::find($request->id);

        if(!Auth::user()->can('delete-user', $index))
        {
            return redirect()->route('backend.user')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('delete-user-error', '');
        }

        saveArchives($index, Auth::id(), "Delete User");

        User::destroy($request->id);

        return redirect()->route('backend.user')->with('success', 'Data Has Been Deleted');
    }

    public function action(Request $request)
    {
        if (!Hash::check($request->password, Auth::user()->password))
        {
            return redirect()->back()->with('failed', 'Your password user invalid');
        }
        else if (is_array($request->id))
        {
            if ($request->action == 'delete') {

                foreach ($request->id as $list){
                    if (Auth::user()->can('delete-user', User::find($list)))
                    {
                        $id[] = $list;
                    }
                }
                
                $index = User::whereIn('id', $id)->get();

                saveMultipleArchives(User::class, $index, Auth::id(), "Delete User");

                User::destroy($id);

                return redirect()->back()->with('success', 'Data Has Been Deleted');

            } else if ($request->action == 'enable') {

                foreach ($request->id as $list) {
                    if (Auth::user()->can('update-user', User::find($list)))
                    {
                        $id[] = $list;
                    }
                }

                $index = User::whereIn('id', $id)->get();

                saveMultipleArchives(User::class, $index, Auth::id(), "Active User");

                $index = User::whereIn('id', $id)->update(['active' => 1]);

                return redirect()->back()->with('success', 'Data Has Been Enabled');

            } else if ($request->action == 'disable') {

                foreach ($request->id as $list) {
                    if (Auth::user()->can('update-user', User::find($list)))
                    {
                        $id[] = $list;
                    }
                }

                $index = User::whereIn('id', $id)->get();

                saveMultipleArchives(User::class, $index, Auth::id(), "Inactive User");


                $index = User::whereIn('id', $id)->update(['active' => -1]);
                return redirect()->back()->with('success', 'Data Has Been Disabled');
            }
        }

        return redirect()->back()->with('info', 'No data change');
    }

    public function active(Request $request)
    {
        $index = User::find($request->id);

        if(!Auth::user()->can('update-user',$index))
        {
            return redirect()->route('backend.user')->with('failed', 'Access Denied');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            if ($index->active <= 0) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('active-user-error', '');
            } else if ($index->active == 1) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('active-user-error', '');
            }

        }

        if ($index->active <= 0) {

            saveArchives($index, Auth::id(), "Active User");
            $index->active = 1;
            $index->save();

            return redirect()->back()->with('success', 'Data Has Been Enabled');
        } 
        else if ($index->active == 1) {

            saveArchives($index, Auth::id(), "Inactive User");

            $index->active = -1;
            $index->save();

            return redirect()->back()->with('success', 'Data Has Been Disabled');
        }
    }

    public function access(User $index)
    {
        $key   = User::keypermission();

        return view('backend.user.access')->with(compact('index', 'key'));
    }

    public function accessUpdate(User $index, Request $request)
    {
        $message = [
            'password.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        saveArchives($index, Auth::id(), "Access User", $request->except(['_token']));

        $grant  = $request->grant ? implode($request->grant, ', ') : '';
        $denied = $request->denied ? implode($request->denied, ', ') : '';

        $index->grant  = $grant;
        $index->denied = $denied;

        $index->save();

        return redirect()->route('backend.user')->with('success', 'Data Has Been Updated');
    }

    public function impersonate(Request $request)
    {
        $index = User::find($request->id);
        if(!Auth::user()->can('impersonate-user',$index))
        {
            return redirect()->route('backend.user')->with('failed', 'Access Denied');
        }

        $message = [
            'password.required' => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ], $message);

        $validator->after(function ($validator) use ($request) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                $validator->errors()->add('password', 'Your password user invalid');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('impersonate-user-error', '');
        }

        saveArchives($index, Auth::id(), "Impersonate user");

        Auth::user()->setImpersonating($index->id);

        return redirect()->route('backend.home')->with('success', 'Login as ' . $index->fullname);
    }

    public function leave()
    {
        Auth::user()->stopImpersonating();

        return redirect()->route('backend.user')->with('success', 'Back as original user');
    }

    public function getNoAE($position)
    {

        // return $sales_id;
        $noAeCollection = User::select(DB::raw('GROUP_CONCAT(DISTINCT `no_ae`) as list_no_ae'))
            ->where('position_id', $position)
            ->where('active', 1)
            ->first();

        $no_ae      = 1;
        $list_no_ae = explode(',', $noAeCollection->list_no_ae);

        foreach ($list_no_ae as $list) {
            if (in_array($no_ae, $list_no_ae)) {
                $no_ae++;
            } else {
                break;
            }
        }

        return $no_ae;
    }

    public function fixTree()
    {
        User::where('parent_id', 0)->update(['parent_id' => null]);
        User::fixTree();

        // return redirect()->back()->with('success', 'The Hierarchy User Has Been Fixed');
    }

    public function resend(User $index)
    {
        if($index->verification)
        {
            Mail::send('email.verification', compact('index'), function ($message) use ($index) {
                $message->to($index->email)->subject('Register for Edigindo');
            });

            return redirect()->back()->with('success', 'Email has been sended');
        }
        else
        {
            return redirect()->back()->with('failed', 'Not found!');
        }
    }
}
