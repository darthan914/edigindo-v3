<?php

if (!function_exists('getDateDiff'))
{
	function getDateDiff($time1, $time2, $precision = 2)
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
}

if (!function_exists('penyebut') && !function_exists('terbilang'))
{

    function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = penyebut($nilai/1000000000) . " milyar" . penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = penyebut($nilai/1000000000000) . " trilyun" . penyebut(fmod($nilai,1000000000000));
        }     
        return $temp;
    }
 
    function terbilang($nilai) {
        if($nilai<0) {
            $hasil = "minus ". trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }           
        return $hasil;
    }
}

if (!function_exists('getConfigValue'))
{
    function getConfigValue($attr, $array = false)
    {
        $config = App\Models\Configuration::where('for', $attr)->first();

        if($config)
        {
            if($config->type == "MULTIPLE")
            {
                return explode(', ', $config->value);
            }
            else
            {
                return $config->value;
            }
        }
        else
        {
            if($array)
            {
                return [];
            }
            else
            {
                return '';
            }
        }
    }
}

if (!function_exists('phone_number_format'))
{
    function phone_number_format($number, $code = '62')
    {
        if($number == '') return '';
        
        $number = preg_replace("/[^\d]/","",$number);

        if ($number[0] == '0') {
            $number = substr($number, 1);
            return $code . $number;
        } else {
            return $number;
        }

    }
}

if (!function_exists('saveArchives'))
{
    function saveArchives($class, $user_id, $name, $insert_data = NULL)
    {
        $archive = new App\Models\Archive;

        $archive->user_id     = $user_id;
        $archive->action_data = $name;
        $archive->insert_data = $insert_data;
        $archive->old_data    = $class;

        $archive->save();

        $class->archives()->save($archive);

    }
}

if (!function_exists('saveMultipleArchives'))
{
    function saveMultipleArchives($class, $index, $user_id, $name, $insert_data = NULL)
    {
        $data = [];
        $date = date('Y-m-d H:i:s');
        foreach ($index as $list)
        {
            $data[] = [
                'archivable_id'   => $list->id,
                'archivable_type' => $class,
                'user_id'         => $user_id,
                'action_data'     => strtoupper($name),
                'insert_data'     => json_encode($insert_data),
                'old_data'        => json_encode($list),
                'created_at'      => $date,
            ];
        }
        App\Models\Archive::insert($data);

    }
}