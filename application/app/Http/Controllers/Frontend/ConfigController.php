<?php

namespace App\Http\Controllers;

use App\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use Session;

class ConfigController extends Controller
{
    public function index()
    {
        $index = Config::first();

        return view('cms.config.index', ['index' => $index]);
    }

    public function store(Request $request)
    {
        $check = Config::first();

        $result = explode(' ,', strrev($request->formatted_address));

        $geo_region = strrev($result[0]);

        $geo_placename = strrev(substr(strrev($request->formatted_address), strpos(strrev($request->formatted_address), ",") + 1));

        if (isset($check)) {
            $update = Config::find($check->id);

            $update->website_name    = $request->website_name;
            $update->company_name    = $request->company_name;
            $update->company_brief   = $request->company_brief;
            $update->address         = $request->address;
            $update->phone           = $request->phone;
            $update->email           = $request->email;
            $update->geo_position    = $request->geo_position;
            if($request->formatted_address != '')
            {
                $update->geo_placename   = $geo_placename;
                $update->geo_region      = $geo_region;
            }
            $update->keywords        = $request->keywords;
            $update->represent_email = $request->represent_email;

            $update->save();

        } else {
            $update = new Config;

            $update->website_name    = $request->website_name;
            $update->company_name    = $request->company_name;
            $update->company_brief   = $request->company_brief;
            $update->address         = $request->address;
            $update->phone           = $request->phone;
            $update->email           = $request->email;
            $update->geo_position    = $request->geo_position;
            if($request->formatted_address != '')
            {
                $update->geo_placename   = $geo_placename;
                $update->geo_region      = $geo_region;
            }
            $update->keywords        = $request->keywords;
            $update->represent_email = $request->represent_email;

            $update->save();
        }

        Session::flash('success', 'Data has been Updated');
        return redirect::back();
    }
}
