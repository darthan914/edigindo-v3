<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Analytics\Period;
use Analytics;
use DateTime;

class DashboardController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index() {
		//$f1 = Analytics::performQuery(Period::days(7), "ga:percentNewSessions", array("dimensions" => "ga:date"));
	    //return response()->json($f1->rows);
	    return view('cms.dashboard.index', ['active' => 1]);
	}
	
	public function googleAnalitic() {
		$f1 = Analytics::fetchMostVisitedPages(Period::days(30));
		$f2 = Analytics::fetchTopBrowsers(Period::days(30));
		$f3 = Analytics::performQuery(Period::days(30), "ga:users", array("dimensions" => "ga:city"))->rows;
		$f4 = Analytics::performQuery(Period::days(30), "ga:bounceRate")->rows;
		$f5 = Analytics::performQuery(Period::days(30), "ga:avgSessionDuration")->rows;
		$f6 = Analytics::performQuery(Period::days(30), "ga:organicSearches", array("dimensions" => "ga:source"))->rows;
		$f7 = Analytics::performQuery(Period::days(30), "ga:newUsers", array("dimensions" => "ga:date"))->rows;
		$f8 = Analytics::performQuery(Period::days(30), "ga:users", array("dimensions" => "ga:date"))->rows;
	    return response()->json(compact("f1","f2","f3","f4","f5","f6","f7","f8"));
	}

	public function googleAnaliticPeriod($start, $end) {

		$startDate	= new DateTime($start." 00:00:00");
		$endDate	= new DateTime($end." 00:00:00");

        $f1 = Analytics::fetchMostVisitedPages(Period::create($startDate, $endDate));
        $f2 = Analytics::fetchTopBrowsers(Period::create($startDate, $endDate));
        $f3 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:users", array("dimensions" => "ga:city"))->rows;
        $f4 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:bounceRate")->rows;
        $f5 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:avgSessionDuration")->rows;
        $f6 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:organicSearches", array("dimensions" => "ga:source"))->rows;
        $f7 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:newUsers", array("dimensions" => "ga:date"))->rows;
        $f8 = Analytics::performQuery(Period::create($startDate, $endDate), "ga:users", array("dimensions" => "ga:date"))->rows;
        return response()->json(compact("f1","f2","f3","f4","f5","f6","f7","f8"));
    }
}
