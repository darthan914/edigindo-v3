<?php

namespace App\Http\Controllers;

use App\User;
use App\Config;
use App\Models\Archive;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

use \Carbon\Carbon;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $level;

    public function filter($request, $default = null)
    {
        $return = $default;

        if ($request != '') {
            if ($request == 'all') {
                $return = '';
            } else {
                $return = $request;
            }
        }

        return $return;
    }

    public function strtocolor(string $text, int $lightness = 50)
    {
        $hex = bin2hex(substr($text, 0, 5));
        $hex = substr($hex, 0, 3);

        $hue = hexdec($hex) % 360;

        $lightness = max(0, $lightness);

        $color = 'hsl(' . $hue . ', 100%, ' . $lightness . '%)';

        return $color;
    }

    public function usergrant($user_id, string $permission = null)
    {
        $collect = User::where('parent_id', Auth::id())->get();

        foreach ($collect as $list) {
            $gatherId[] = $list->id;
        }

        $staff = $gatherId ?? [];

        if ($user_id == Auth::id() || in_array($user_id, $staff) || ($permission != null && Auth::user()->can($permission))) {
            return true;
        } else {
            return false;
        }
    }

    public function levelgrant($user_id)
    {
        $user = User::where('id', $user_id)->withDepth()->first();
        $auth = User::where('id', Auth::id())->withDepth()->first();

        if ($user->depth >= $auth->depth) {
            return true;
        } else {
            return false;
        }
    }

    public function childgrant($user_id)
    {
        $user = User::where('id', $user_id)->first();
        $auth = User::where('id', Auth::id())->withDepth()->first();

        if (($auth->_lft <= $user->_lft && $auth->_rgt >= $user->_lft) || $auth->depth == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function saveArchive($source, $action, $data)
    {
        $index = new Archive;

        $index->source = $source;
        $index->action = $action;
        $index->data = $data;
        $index->user_id = Auth::id();

        $index->save();

        return $index;
    }

    public function saveMultipleArchive($source, $action, $data)
    {
        $datas = [];
        $datetime = date('Y-m-d H:i:s');

        foreach ($data as $list) {
            $datas[] = [
                'source'     => $source,
                'action'     => $action,
                'data'       => $list,
                'user_id'    => Auth::id(),
                'created_at' => $datetime,
            ];
        }

        Archive::insert($datas);

        return 1;
    }

    public function config()
    {
        $config = Config::all();
        $data   = '';
        foreach ($config as $list) {
            eval("\$" . $list->for . " = App\Config::find(" . $list->id . ");");
            $data[] = [$list->for];
        }

        return compact('data');
    }

    public function divine()
    {
        return 1000000;
    }

    public function whatsappPhone($phone)
    {
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace('+', '', $phone);
        $phone = str_replace(' ', '', $phone);

        if ($phone[0] == '0') {
            $phone = substr($phone, 1);
            return '62' . $phone;
        } else {
            return $phone;
        }

    }

    public function getDateDiff($time1, $time2, $precision = 2)
    {
        // If not numeric then convert timestamps
        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }
        // If time1 > time2 then swap the 2 values
        if ($time1 > $time2) {
            list($time1, $time2) = array($time2, $time1);
        }
        // Set up intervals and diffs arrays
        $intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
        $diffs     = array();
        foreach ($intervals as $interval) {
            // Create temp time from time1 and interval
            $ttime = strtotime('+1 ' . $interval, $time1);
            // Set initial values
            $add    = 1;
            $looped = 0;
            // Loop until temp time is smaller than time2
            while ($time2 >= $ttime) {
                // Create new temp time from time1 and interval
                $add++;
                $ttime = strtotime("+" . $add . " " . $interval, $time1);
                $looped++;
            }
            $time1            = strtotime("+" . $looped . " " . $interval, $time1);
            $diffs[$interval] = $looped;
        }
        $count = 0;
        $times = array();
        foreach ($diffs as $interval => $value) {
            // Break if we have needed precission
            if ($count >= $precision) {
                break;
            }
            // Add value and interval if value is bigger than 0
            if ($value > 0) {
                if ($value != 1) {
                    $interval .= "s";
                }
                // Add value and interval to times array
                $times[] = $value . " " . $interval;
                $count++;
            }
        }
        // Return string with times
        return implode(", ", $times);
    }

    public function arrayToJson($value)
    {
        $value = json_encode($value);

        return json_decode($value);
    }

    public function city()
    {
        return [
            "Jakarta Barat",
            "Jakarta Pusat",
            "Jakarta Selatan",
            "Jakarta Timur",
            "Jakarta Utara",
            "Bogor",
            "Depok",
            "Tangerang",
            "Tangerang Selatan",
            "Bekasi",
            "Luar Jabodetabek",
        ];
    }
}
