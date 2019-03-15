<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\User;
use App\Models\Position;

class AuthController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth:api', ['except' => ['login']]);
	}

	public function login(Request $request)
	{
		// return $request->all();
		$status  = 'OK';
		$message = '';
		$data    = '';

		if (Auth::attempt(['username' => $request->username, 'password' => $request->password, 'active' => 1])) {

			if ($request->isMethod('post')) {
	            $user     = Auth::user();
	            $position = Position::where('code', $user->position)->first();
	            $token    = $user->createToken('EdigindoApp')->accessToken;
	        }

            $data = compact('token', 'user', 'position');
        } else {
            $query = User::where('username', $request->username);
            $find  = $query->first();
            $check = $query->count();

            if ($check) {
                if ($find->active != 1) {
                    $status  = 'ERROR';
					$message = 'Your username not active please inform to team leader';
                } else {
                    $status = 'ERROR';
					$message = 'Invalid password';
                }
            } else {
                $status = 'ERROR';
				$message = 'Invalid Username';
            }
        }

		return response()->json(compact('status', 'message', 'data'));
	}

	public function logout(Request $request)
	{
		$status = 'OK';
		$message = '';
		$data = [];

		if (Auth::check() && $request->isMethod('post')) {
	       Auth::user()->AauthAcessToken()->delete();
	    }

		return response()->json(compact('status', 'message', 'data'));
	}

	public function auth(Request $request)
	{
		$status = 'OK';
		$message = '';

		$user     = Auth::user();
		$position = Position::where('code', $user->position)->first();

		$data = compact('user', 'position');

		return response()->json(compact('status', 'message', 'data'));
	}


	public function pusher(Request $request)
	{
		$auth = Auth::user()->AauthAcessToken()->first()->id;

		return response()->json(compact('auth'));
	}


	public function hasAccess(Request $request)
	{
		$user = Auth::user();

		$permission_arr = explode(', ', $user->getRole->permission);
		$grant_arr      = explode(', ', $user->grant);
		$denied_arr     = explode(', ', $user->denied);

		if((in_array($request->access, $permission_arr) || in_array($request->access, $grant_arr)) && !in_array($request->access, $denied_arr)) {
			return true;
		}

		return false;
	}
}
