<?php

namespace App\Http\Controllers\Backend;

use App\User;
use App\Models\Position;
use App\Models\Archive;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Session;
use Mail;
use Validator;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() || Auth::viaRemember()) {
            return redirect()->route('backend.home');
        }
        return view('backend.auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password, 'active' => 1], $request->remember_me)) {


            saveArchives(User::find(Auth::id()), Auth::id(), "Login");

            return redirect()->route('backend.home');
        } else {
            $query = User::where('username', $request->username);
            $find  = $query->first();
            $check = $query->count();

            if ($check) {
                if ($find->active != 1) {
                    Session::flash('failed', 'Your username not active please inform to team leader');
                    return redirect::back();
                } else {
                    Session::flash('failed', 'Invalid password');
                    return redirect::back();
                }
            } else {
                Session::flash('failed', 'Invalid Username');
                return redirect::back();
            }
        }
    }

    public function logout()
    {
        saveArchives(User::find(Auth::id()), Auth::id(), "Logout");

        Auth::logout();
        return redirect()->route('backend');
    }

    public function register($token)
    {
        $index = User::where('verification', $token)->first();

        return view('backend.auth.register', compact('index', 'token'));
    }

    public function updateRegister(Request $request)
    {
        $index = User::where('verification', $request->token)->first();

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'password' => 'nullable|confirmed',
            'first_name' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        saveArchives($index, $index->id, "REGISTER & LOGIN", $request->except(['password', 'password_confirmation', 'password_user', '_token', 'token']));

        $index->username   = $request->username;
        $index->password   = bcrypt($request->password);
        $index->first_name = $request->first_name;
        $index->last_name  = $request->last_name;
        $index->phone      = $request->phone ?? $index->phone;

        if ($request->hasFile('signature')) {
            $pathSource = 'upload/user/signature/';
            $file       = $request->file('signature');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            if ($file->move($pathSource, $filename)) {
                $index->signature = $pathSource . $filename;
            }
        }

        if ($request->hasFile('photo')) {
            $pathSource = 'upload/user/photo/';
            $file       = $request->file('photo');
            $filename   = time() . '.' . $file->getClientOriginalExtension();

            if ($file->move($pathSource, $filename)) {
                $index->photo = $pathSource . $filename;
            }
        }

        $index->active       = 1;
        $index->verification = NULL;

        $index->save();

        Auth::loginUsingId($index->id);

        return redirect()->route('backend.home')->with('success', 'Welcome to Edigindo');
    }

    public function forgotPassword()
    {
        return view('backend.auth.forgotPassword');
    }

    public function sendForgotPassword(Request $request)
    {
        $index = User::where('email', $request->email)->where('active', 1)->first();

        if($index)
        {
            $token = str_random(30);

            $index->forgot_password         = $token;
            $index->expired_forgot_password = date('Y-m-d H:i:s', strtotime('+7 days'));

            $index->save();

            Mail::send('email.resetPassword', compact('index'), function ($message) use ($request) {
                $message->to($request->email)->subject('Reset Username and Password for Edigindo');
            });

            return redirect()->back()->with('success', 'Your Reset Username and Password has been sent');
        }
        else
        {
            return redirect()->back()->with('failed', 'Email Not Found');
        }
    }

    public function resetPassword($token)
    {
        $index = User::where('forgot_password', $token)
            ->where('expired_forgot_password', '>=', date('Y-m-d H:i:s'))
            ->first();

        return view('backend.auth.resetPassword', compact('index', 'token'));
    }

    public function updatePassword(Request $request)
    {
        $index = User::where('forgot_password', $request->token)
            ->where('expired_forgot_password', '>=', date('Y-m-d H:i:s'))
            ->first();

        if($index)
        {

            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:users,username,'.$index->id,
                'password' => 'nullable|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            saveArchives($index, $index->id, "RESET PASSWORD", $request->except(['password', 'password_confirmation', 'password_user', '_token', 'token']));

            $index->username = $request->username;
            $index->password = bcrypt($request->password);

            $index->forgot_password         = NULL;
            $index->expired_forgot_password = NULL;

            $index->save();

            return redirect()->route('login')->with('success', 'Your Password has been reseted');
        }
        else
        {
            return redirect()->route('login')->with('failed', 'Token Expired');
        }
    }

    public function first()
    {
        $position = Position::first();
        $user = User::first();

        if(!$position)
        {
            $position = new Position;

            $position->name = "Master";
            $position->active = 1;
            $position->permission = 'configuration, list-user, create-user, update-user, delete-user, access-user, impersonate-user, list-position, create-position, update-position, delete-position, list-division, create-division, update-division, delete-division';

            $position->save();
        }

        if(!$user)
        {
            $user = new User;

            $user->username = "darthan914";
            $user->email = "darthan.sevenz@gmail.com";
            $user->password = bcrypt("digindo123");
            $user->position_id = $position->id;
            $user->no_ae = 1;
            $user->first_name = 'Jonathan';
            $user->last_name = 'Dharmawan';
            $user->active = 1;

            $user->save();
        }

        return redirect()->route('backend.login');
    }
}
