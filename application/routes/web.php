<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/






Route::get('/', 'Frontend\HomeController@index')->name('frontend.index');


Route::get('/home', 'Frontend\HomeController@index')->name('frontend.index');
Route::get('/portofolio', 'Frontend\HomeController@portofolio')->name('frontend.portofolio');
Route::get('/3d', 'Frontend\HomeController@triD')->name('frontend.triD');
Route::get('/finance', 'Frontend\HomeController@finance')->name('frontend.finance');
Route::get('/graphic', 'Frontend\HomeController@graphic')->name('frontend.graphic');
Route::get('/account', 'Frontend\HomeController@account')->name('frontend.account');
Route::get('/marketing', 'Frontend\HomeController@marketing')->name('frontend.marketing');
Route::get('/quotes', 'Frontend\HomeController@quotes')->name('frontend.quotes');


Route::get('/intern', function(){
	return '';
})->name('frontend.intern');

Route::group(['prefix' => 'notification'], function()
{
    Route::get('index', 'Backend\NotificationController@index')->name('backend.notification.index');
	Route::post('datatables', 'Backend\NotificationController@datatables')->name('backend.notification.datatables');
	Route::post('get', 'Backend\NotificationController@get')->name('backend.notification.get');
	Route::post('ajaxNavbar', 'Backend\NotificationController@ajaxNavbar')->name('backend.notification.ajaxNavbar');
	Route::get('{id}/view', 'Backend\NotificationController@view')->name('backend.notification.view');
	Route::post('delete', 'Backend\NotificationController@delete')->name('backend.notification.delete');
	Route::post('action', 'Backend\NotificationController@action')->name('backend.notification.action');
});


Route::group(['prefix' => 'edigindo'], function()
{
	Route::get('/blank', function(){ return ''; })->name('blank');
	Route::get('/first', 'Backend\AuthController@first');
	// Route::get('/test/{campaign_detail}', 'Backend\CampaignController@datatablesDashboard')->name('test');

	Route::get('/toggleSidebar', 'Backend\ConfigController@toggleSidebar')->name('toggleSidebar');
	Route::get('/checkSidebar', 'Backend\ConfigController@checkSidebar')->name('checkSidebar');

	Route::get('/', 'Backend\AuthController@showLogin')->name('backend');
	Route::get('/login', 'Backend\AuthController@showLogin')->name('login');
	Route::post('/login', 'Backend\AuthController@login')->name('backend.login');
	Route::get('/logout', 'Backend\AuthController@logout')->name('backend.logout');
	Route::get('/register/{token}', 'Backend\AuthController@register')->name('backend.register');
	Route::post('/updateRegister', 'Backend\AuthController@updateRegister')->name('backend.updateRegister');
	Route::get('/forgotPassword', 'Backend\AuthController@forgotPassword')->name('backend.forgotPassword');
	Route::post('/sendForgotPassword', 'Backend\AuthController@sendForgotPassword')->name('backend.sendForgotPassword');
	Route::get('/resetPassword/{token}', 'Backend\AuthController@resetPassword')->name('backend.resetPassword');
	Route::post('/updatePassword', 'Backend\AuthController@updatePassword')->name('backend.updatePassword');

	Route::group(['prefix' => 'jobApply'], function()
	{
		Route::get('/', 'Backend\JobApplyController@index')->name('backend.jobApply')->middleware('can:list-jobApply');
		Route::get('index', 'Backend\JobApplyController@index')->name('backend.jobApply.index')->middleware('can:list-jobApply');
		Route::post('datatables', 'Backend\JobApplyController@datatables')->name('backend.jobApply.datatables')->middleware('can:list-jobApply');

		Route::post('store', 'Backend\JobApplyController@store')->name('backend.jobApply.store');
		Route::post('delete', 'Backend\JobApplyController@delete')->name('backend.jobApply.delete')->middleware('can:delete-jobApply');
		Route::post('action', 'Backend\JobApplyController@action')->name('backend.jobApply.action');
	});

	Route::group(['prefix' => 'quote'], function()
	{
		Route::get('/', 'Backend\QuoteController@index')->name('backend.quote')->middleware('can:list-quote');
		Route::get('index', 'Backend\QuoteController@index')->name('backend.quote.index')->middleware('can:list-quote');
		Route::post('datatables', 'Backend\QuoteController@datatables')->name('backend.quote.datatables')->middleware('can:list-quote');

		Route::post('store', 'Backend\QuoteController@store')->name('backend.quote.store');
		Route::post('delete', 'Backend\QuoteController@delete')->name('backend.quote.delete')->middleware('can:delete-quote');
		Route::post('action', 'Backend\QuoteController@action')->name('backend.quote.action');
	});


	Route::group(['prefix' => 'home'], function()
	{
	    Route::get('/', 'Backend\DashboardController@index')->name('backend.home');
		Route::get('index', 'Backend\DashboardController@index')->name('backend.home.index');

		Route::post('ajaxChartMonthly', 'Backend\DashboardController@ajaxChartMonthly')->name('backend.home.ajaxChartMonthly')->middleware('can:sales-dashboard');
		Route::post('ajaxChartTarget', 'Backend\DashboardController@ajaxChartTarget')->name('backend.home.ajaxChartTarget')->middleware('can:sales-dashboard');
		Route::post('ajaxChartInOut', 'Backend\DashboardController@ajaxChartInOut')->name('backend.home.ajaxChartInOut')->middleware('can:incomeOutcome-dashboard');
		Route::post('ajaxChartCampaign', 'Backend\DashboardController@ajaxChartCampaign')->name('backend.home.ajaxChartCampaign')->middleware('can:sales-dashboard');
		Route::post('ajaxChartCampaignDetail/{campaign_detail}', 'Backend\DashboardController@ajaxChartCampaignDetail')->name('backend.home.ajaxChartCampaignDetail')->middleware('can:sales-dashboard');
	});


	// Migration
	// Route::get('/migration', 'Backend\MigrationController@index')->name('migration')->middleware('can:migration');


	// Config
	Route::get('/config', 'Backend\ConfigController@index')->name('backend.config')->middleware('can:configuration');
	Route::get('/config/edit/{index}', 'Backend\ConfigController@edit')->name('backend.config.edit')->middleware('can:configuration');
	Route::post('/config/update/{index}', 'Backend\ConfigController@update')->name('backend.config.update')->middleware('can:configuration');

	Route::get('/sql', 'Backend\ConfigController@sql')->name('backend.sql')->middleware('can:sql');
	Route::post('/sql/runSql', 'Backend\ConfigController@runSql')->name('backend.runSql')->middleware('can:sql');


	// Admin User
	Route::group(['prefix' => 'user'], function()
	{
		Route::get('profile', 'Backend\UserController@profile')->name('backend.user.profile');
		Route::post('updateProfile', 'Backend\UserController@updateProfile')->name('backend.user.updateProfile');


		Route::get('/', 'Backend\UserController@index')->name('backend.user')->middleware('can:list-user');
		Route::get('index', 'Backend\UserController@index')->name('backend.user.index')->middleware('can:list-user');
		Route::post('datatables', 'Backend\UserController@datatables')->name('backend.user.datatables')->middleware('can:list-user');

		Route::get('create', 'Backend\UserController@create')->name('backend.user.create')->middleware('can:create-user,index');
		Route::post('store', 'Backend\UserController@store')->name('backend.user.store')->middleware('can:create-user,index');
		
		Route::get('edit/{index}', 'Backend\UserController@edit')->name('backend.user.edit')->middleware('can:update-user,index');
		Route::post('update/{index}', 'Backend\UserController@update')->name('backend.user.update')->middleware('can:update-user,index');
		Route::get('access/{index}', 'Backend\UserController@access')->name('backend.user.access')->middleware('can:access-user,index');
		Route::post('accessUpdate/{index}', 'Backend\UserController@accessUpdate')->name('backend.user.accessUpdate')->middleware('can:access-user,index');
		
		Route::post('delete', 'Backend\UserController@delete')->name('backend.user.delete');
		Route::post('action', 'Backend\UserController@action')->name('backend.user.action');
		
		Route::post('active', 'Backend\UserController@active')->name('backend.user.active')->middleware('can:update-user,index');
		
		Route::post('impersonate', 'Backend\UserController@impersonate')->name('backend.user.impersonate');
		Route::get('leave', 'Backend\UserController@leave')->name('backend.user.leave');
		
		Route::get('fixTree', 'Backend\UserController@fixTree')->name('backend.user.fixTree');
		Route::get('resend/{index}', 'Backend\UserController@resend')->name('backend.user.resend');
	});


	// Admin Position
	Route::group(['prefix' => 'position'], function()
	{
		Route::get('/', 'Backend\PositionController@index')->name('backend.position')->middleware('can:list-position');
		Route::get('index', 'Backend\PositionController@index')->name('backend.position.index')->middleware('can:list-position');
		Route::post('datatables', 'Backend\PositionController@datatables')->name('backend.position.datatables')->middleware('can:list-position');

		Route::get('create', 'Backend\PositionController@create')->name('backend.position.create')->middleware('can:create-position');
		Route::post('store', 'Backend\PositionController@store')->name('backend.position.store')->middleware('can:create-position');
		Route::get('edit/{index}', 'Backend\PositionController@edit')->name('backend.position.edit')->middleware('can:update-position,index');
		Route::post('update/{index}', 'Backend\PositionController@update')->name('backend.position.update')->middleware('can:update-position,index');
		Route::post('delete', 'Backend\PositionController@delete')->name('backend.position.delete');
		Route::post('action', 'Backend\PositionController@action')->name('backend.position.action');
		Route::post('active', 'Backend\PositionController@active')->name('backend.position.active');
	});


	// Admin Division
	Route::group(['prefix' => 'division'], function()
	{
		Route::get('/', 'Backend\DivisionController@index')->name('backend.division')->middleware('can:list-division');
		Route::get('index', 'Backend\DivisionController@index')->name('backend.division.index')->middleware('can:list-division');
		Route::post('datatables', 'Backend\DivisionController@datatables')->name('backend.division.datatables')->middleware('can:list-division');

		Route::get('create', 'Backend\DivisionController@create')->name('backend.division.create')->middleware('can:create-division');
		Route::post('store', 'Backend\DivisionController@store')->name('backend.division.store')->middleware('can:create-division');

		Route::get('edit/{index}', 'Backend\DivisionController@edit')->name('backend.division.edit')->middleware('can:update-division');
		Route::post('update/{index}', 'Backend\DivisionController@update')->name('backend.division.update')->middleware('can:update-division');
		Route::post('active', 'Backend\DivisionController@active')->name('backend.division.active')->middleware('can:update-division');
		Route::post('activeClient', 'Backend\DivisionController@activeClient')->name('backend.division.activeClient')->middleware('can:update-division');
		
		Route::post('delete', 'Backend\DivisionController@delete')->name('backend.division.delete');

		Route::post('action', 'Backend\DivisionController@action')->name('backend.division.action');
	});


	// Admin File
	Route::group(['prefix' => 'file'], function()
	{
		Route::get('/', 'Backend\FileController@index')->name('backend.file')->middleware('can:list-file');
		Route::get('index', 'Backend\FileController@index')->name('backend.file.index')->middleware('can:list-file');
		Route::post('datatables', 'Backend\FileController@datatables')->name('backend.file.datatables')->middleware('can:list-file');

		Route::get('create', 'Backend\FileController@create')->name('backend.file.create')->middleware('can:create-file');
		Route::post('store', 'Backend\FileController@store')->name('backend.file.store')->middleware('can:create-file');
		Route::get('{id}/edit', 'Backend\FileController@edit')->name('backend.file.edit')->middleware('can:edit-file');
		Route::post('{id}/update', 'Backend\FileController@update')->name('backend.file.update')->middleware('can:edit-file');
		Route::post('delete', 'Backend\FileController@delete')->name('backend.file.delete')->middleware('can:delete-file');
		Route::post('action', 'Backend\FileController@action')->name('backend.file.action');
	});

	// Admin File
	Route::group(['prefix' => 'tool'], function()
	{
		Route::get('labelPackage', 'Backend\ToolController@labelPackage')->name('backend.tool.labelPackage')->middleware('can:labelPackage-tool');
		Route::get('downloadLabelPackageTemplate', 'Backend\ToolController@downloadLabelPackageTemplate')->name('backend.tool.downloadLabelPackageTemplate')->middleware('can:labelPackage-tool');
		Route::post('generateLabelPackage', 'Backend\ToolController@generateLabelPackage')->name('backend.tool.generateLabelPackage')->middleware('can:labelPackage-tool');
	});


	// Admin Company
	Route::group(['prefix' => 'company'], function()
	{
		Route::get('/', 'Backend\CompanyController@index')->name('backend.company')->middleware('can:list-company');
		Route::get('index', 'Backend\CompanyController@index')->name('backend.company.index')->middleware('can:list-company');
		Route::post('datatables', 'Backend\CompanyController@datatables')->name('backend.company.datatables')->middleware('can:list-company');

		Route::get('create', 'Backend\CompanyController@create')->name('backend.company.create')->middleware('can:create-company');
		Route::post('store', 'Backend\CompanyController@store')->name('backend.company.store')->middleware('can:create-company');
		Route::get('edit/{index}', 'Backend\CompanyController@edit')->name('backend.company.edit')->middleware('can:update-company');
		Route::post('update/{index}', 'Backend\CompanyController@update')->name('backend.company.update')->middleware('can:update-company');
		Route::post('delete', 'Backend\CompanyController@delete')->name('backend.company.delete')->middleware('can:delete-company');
		Route::post('action', 'Backend\CompanyController@action')->name('backend.company.action');

		Route::get('dashboard', 'Backend\CompanyController@dashboard')->name('backend.company.dashboard')->middleware('can:dashboard-company');
		Route::post('datatablesClientDashboard', 'Backend\CompanyController@datatablesClientDashboard')->name('backend.company.datatablesClientDashboard')->middleware('can:dashboard-company');
		Route::post('datatablesMonthlyDashboard', 'Backend\CompanyController@datatablesMonthlyDashboard')->name('backend.company.datatablesMonthlyDashboard')->middleware('can:dashboard-company');
		Route::post('datatablesYearlyDashboard', 'Backend\CompanyController@datatablesYearlyDashboard')->name('backend.company.datatablesYearlyDashboard')->middleware('can:dashboard-company');
		Route::post('datatablesDetailDashboard', 'Backend\CompanyController@datatablesDetailDashboard')->name('backend.company.datatablesDetailDashboard')->middleware('can:dashboard-company');
		Route::post('datatablesDataYearlyDetail', 'Backend\CompanyController@datatablesDataYearlyDetail')->name('backend.company.datatablesDataYearlyDetail')->middleware('can:dashboard-company');

		Route::post('datatablesPic/{index}', 'Backend\CompanyController@datatablesPic')->name('backend.company.datatablesPic')->middleware('can:update-company');
		Route::post('storePic', 'Backend\CompanyController@storePic')->name('backend.company.storePic')->middleware('can:update-company');
		Route::post('updatePic', 'Backend\CompanyController@updatePic')->name('backend.company.updatePic')->middleware('can:update-company');
		Route::post('deletePic', 'Backend\CompanyController@deletePic')->name('backend.company.deletePic')->middleware('can:update-company');
		Route::post('whatsappPic', 'Backend\CompanyController@whatsappPic')->name('backend.company.whatsappPic')->middleware('can:send-company');
		Route::post('emailPic', 'Backend\CompanyController@emailPic')->name('backend.company.emailPic')->middleware('can:send-company');
		Route::post('actionPic', 'Backend\CompanyController@actionPic')->name('backend.company.actionPic')->middleware('can:update-company');

		Route::post('datatablesAddress/{index}', 'Backend\CompanyController@datatablesAddress')->name('backend.company.datatablesAddress')->middleware('can:update-company');
		Route::post('storeAddress', 'Backend\CompanyController@storeAddress')->name('backend.company.storeAddress')->middleware('can:update-company');
		Route::post('updateAddress', 'Backend\CompanyController@updateAddress')->name('backend.company.updateAddress')->middleware('can:update-company');
		Route::post('deleteAddress', 'Backend\CompanyController@deleteAddress')->name('backend.company.deleteAddress')->middleware('can:update-company');
		Route::post('actionAddress', 'Backend\CompanyController@actionAddress')->name('backend.company.actionAddress')->middleware('can:update-company');

		Route::post('datatablesBrand/{index}', 'Backend\CompanyController@datatablesBrand')->name('backend.company.datatablesBrand')->middleware('can:update-company');
		Route::post('storeBrand', 'Backend\CompanyController@storeBrand')->name('backend.company.storeBrand')->middleware('can:update-company');
		Route::post('updateBrand', 'Backend\CompanyController@updateBrand')->name('backend.company.updateBrand')->middleware('can:update-company');
		Route::post('deleteBrand', 'Backend\CompanyController@deleteBrand')->name('backend.company.deleteBrand')->middleware('can:update-company');
		Route::post('actionBrand', 'Backend\CompanyController@actionBrand')->name('backend.company.actionBrand')->middleware('can:update-company');

		Route::post('lock', 'Backend\CompanyController@lock')->name('backend.company.lock')->middleware('can:lock-company');
		Route::post('confirm', 'Backend\CompanyController@confirm')->name('backend.company.confirm')->middleware('can:confirm-company');
		Route::post('getDetail', 'Backend\CompanyController@getDetail')->name('backend.company.getDetail');
	});


	// Admin target
	Route::group(['prefix' => 'target'], function()
	{
		Route::get('/', 'Backend\TargetController@index')->name('backend.target')->middleware('can:list-target');
		Route::get('/index', 'Backend\TargetController@index')->name('backend.target.index')->middleware('can:list-target');
		Route::post('/datatables', 'Backend\TargetController@datatables')->name('backend.target.datatables')->middleware('can:list-target');

		Route::get('create', 'Backend\TargetController@create')->name('backend.target.create')->middleware('can:create-target');
		Route::post('store', 'Backend\TargetController@store')->name('backend.target.store')->middleware('can:create-target');
		Route::get('edit/{index}', 'Backend\TargetController@edit')->name('backend.target.edit')->middleware('can:update-target');
		Route::post('update/{index}', 'Backend\TargetController@update')->name('backend.target.update')->middleware('can:update-target');
		Route::post('delete', 'Backend\TargetController@delete')->name('backend.target.delete')->middleware('can:delete-target');
		Route::post('action', 'Backend\TargetController@action')->name('backend.target.action');

		Route::get('dashboard/{index}', 'Backend\TargetController@dashboard')->name('backend.target.dashboard');
		Route::post('datatablesDashboard/{index}', 'Backend\TargetController@datatablesDashboard')->name('backend.target.datatablesDashboard');

		Route::post('datatablesDetail/{index}', 'Backend\TargetController@datatablesDetail')->name('backend.target.datatablesDetail');
		Route::post('storeDetail', 'Backend\TargetController@storeDetail')->name('backend.target.storeDetail');
		Route::post('updateDetail', 'Backend\TargetController@updateDetail')->name('backend.target.updateDetail');
		Route::post('deleteDetail', 'Backend\TargetController@deleteDetail')->name('backend.target.deleteDetail');
		Route::post('actionDetail', 'Backend\TargetController@actionDetail')->name('backend.target.actionDetail');
		Route::post('repairDetail', 'Backend\TargetController@repairDetail')->name('backend.target.repairDetail');
	});


	// Admin campaign
	Route::group(['prefix' => 'campaign'], function()
	{
		Route::get('/', 'Backend\CampaignController@index')->name('backend.campaign')->middleware('can:list-campaign');
		Route::get('/index', 'Backend\CampaignController@index')->name('backend.campaign.index')->middleware('can:list-campaign');
		Route::post('/datatables', 'Backend\CampaignController@datatables')->name('backend.campaign.datatables')->middleware('can:list-campaign');

		Route::get('create', 'Backend\CampaignController@create')->name('backend.campaign.create')->middleware('can:create-campaign');
		Route::post('store', 'Backend\CampaignController@store')->name('backend.campaign.store')->middleware('can:create-campaign');
		Route::get('update/{index}', 'Backend\CampaignController@update')->name('backend.campaign.edit')->middleware('can:update-campaign');
		Route::post('update/{index}', 'Backend\CampaignController@update')->name('backend.campaign.update')->middleware('can:update-campaign');
		Route::post('delete', 'Backend\CampaignController@delete')->name('backend.campaign.delete')->middleware('can:delete-campaign');
		Route::post('action', 'Backend\CampaignController@action')->name('backend.campaign.action');

		Route::get('dashboard/{index}', 'Backend\CampaignController@dashboard')->name('backend.campaign.dashboard');
		Route::post('datatablesDashboard/{campaign_detail}', 'Backend\CampaignController@datatablesDashboard')->name('backend.campaign.datatablesDashboard');



		Route::post('datatablesCampaignDetail/{index}', 'Backend\CampaignController@datatablesCampaignDetail')->name('backend.campaign.datatablesCampaignDetail')->middleware('can:edit-campaign');
		Route::get('createCampaignDetail/{index}', 'Backend\CampaignController@createCampaignDetail')->name('backend.campaign.createCampaignDetail')->middleware('can:create-campaign');
		Route::post('storeCampaignDetail', 'Backend\CampaignController@storeCampaignDetail')->name('backend.campaign.storeCampaignDetail')->middleware('can:create-campaign');
		Route::get('editCampaignDetail/{index}', 'Backend\CampaignController@editCampaignDetail')->name('backend.campaign.editCampaignDetail')->middleware('can:edit-campaign');
		Route::post('updateCampaignDetail/{index}', 'Backend\CampaignController@updateCampaignDetail')->name('backend.campaign.updateCampaignDetail')->middleware('can:edit-campaign');
		Route::post('deleteCampaignDetail', 'Backend\CampaignController@deleteCampaignDetail')->name('backend.campaign.deleteCampaignDetail')->middleware('can:delete-campaign');
	});


	// Admin SPK
	Route::group(['prefix' => 'spk'], function()
	{
		Route::get('/', 'Backend\SpkController@index')->name('backend.spk')->middleware('can:list-spk');
		Route::get('index', 'Backend\SpkController@index')->name('backend.spk.index')->middleware('can:list-spk');
		Route::post('datatables', 'Backend\SpkController@datatables')->name('backend.spk.datatables')->middleware('can:list-spk');

		Route::get('dashboard', 'Backend\SpkController@dashboard')->name('backend.spk.dashboard')->middleware('can:dashboard-spk');
		Route::post('datatablesSalesDashboard', 'Backend\SpkController@datatablesSalesDashboard')->name('backend.spk.datatablesSalesDashboard')->middleware('can:dashboard-spk');
		Route::post('datatablesMonthlyDashboard', 'Backend\SpkController@datatablesMonthlyDashboard')->name('backend.spk.datatablesMonthlyDashboard')->middleware('can:dashboard-spk');
		Route::post('datatablesDetailDashboard', 'Backend\SpkController@datatablesDetailDashboard')->name('backend.spk.datatablesDetailDashboard')->middleware('can:dashboard-spk');

		Route::get('create', 'Backend\SpkController@create')->name('backend.spk.create')->middleware('can:create-spk');
		Route::post('store', 'Backend\SpkController@store')->name('backend.spk.store')->middleware('can:create-spk');
		Route::get('edit/{index}', 'Backend\SpkController@edit')->name('backend.spk.edit');
		Route::post('update/{index}', 'Backend\SpkController@update')->name('backend.spk.update')->middleware('can:update-spk,index');
		Route::post('delete', 'Backend\SpkController@delete')->name('backend.spk.delete');

		Route::post('confirm', 'Backend\SpkController@confirm')->name('backend.spk.confirm');
		Route::post('unconfirm', 'Backend\SpkController@unconfirm')->name('backend.spk.unconfirm');
		
		Route::post('action', 'Backend\SpkController@action')->name('backend.spk.action');
		Route::post('pdf', 'Backend\SpkController@pdf')->name('backend.spk.pdf');
		Route::post('excel', 'Backend\SpkController@excel')->name('backend.spk.excel');

		Route::post('finish', 'Backend\SpkController@finish')->name('backend.spk.finish');
		Route::post('undoFinish', 'Backend\SpkController@undoFinish')->name('backend.spk.undoFinish');

		Route::post('datatablesDetail/{index}', 'Backend\SpkController@datatablesDetail')->name('backend.spk.datatablesDetail');
		Route::post('storeDetail', 'Backend\SpkController@storeDetail')->name('backend.spk.storeDetail');
		Route::post('updateDetail', 'Backend\SpkController@updateDetail')->name('backend.spk.updateDetail');
		Route::post('deleteDetail', 'Backend\SpkController@deleteDetail')->name('backend.spk.deleteDetail');
		Route::post('actionDetail', 'Backend\SpkController@actionDetail')->name('backend.spk.actionDetail');
		Route::post('repairDetail', 'Backend\SpkController@repairDetail')->name('backend.spk.repairDetail');

		Route::post('getSpk', 'Backend\SpkController@getSpk')->name('backend.spk.getSpk');
		Route::post('getDetail', 'Backend\SpkController@getDetail')->name('backend.spk.getDetail');
	});

	// Admin estimator
	Route::group(['prefix' => 'estimator'], function()
	{
		Route::get('/', 'Backend\EstimatorController@index')->name('backend.estimator')->middleware('can:list-estimator');
		Route::get('index', 'Backend\EstimatorController@index')->name('backend.estimator.index')->middleware('can:list-estimator');
		Route::post('datatables', 'Backend\EstimatorController@datatables')->name('backend.estimator.datatables')->middleware('can:list-estimator');

		Route::get('create', 'Backend\EstimatorController@create')->name('backend.estimator.create')->middleware('can:create-estimator');
		Route::post('store', 'Backend\EstimatorController@store')->name('backend.estimator.store')->middleware('can:create-estimator');
		Route::get('edit/{index}', 'Backend\EstimatorController@edit')->name('backend.estimator.edit')->middleware('can:update-estimator,index');
		Route::post('update/{index}', 'Backend\EstimatorController@update')->name('backend.estimator.update')->middleware('can:update-estimator,index');
		Route::post('delete', 'Backend\EstimatorController@delete')->name('backend.estimator.delete');
		Route::post('action', 'Backend\EstimatorController@action')->name('backend.estimator.action');

		Route::get('price/{index}', 'Backend\EstimatorController@price')->name('backend.estimator.price')->middleware('can:createPrice-estimator,index');
		Route::post('datatablesPrice/{index}', 'Backend\EstimatorController@datatablesPrice')->name('backend.estimator.datatablesPrice')->middleware('can:createPrice-estimator,index');
		
		Route::post('storePrice/{index}', 'Backend\EstimatorController@storePrice')->name('backend.estimator.storePrice')->middleware('can:createPrice-estimator,index');
		Route::post('updatePrice', 'Backend\EstimatorController@updatePrice')->name('backend.estimator.updatePrice');
		Route::post('deletePrice', 'Backend\EstimatorController@deletePrice')->name('backend.estimator.deletePrice');
		Route::post('actionPrice', 'Backend\EstimatorController@actionPrice')->name('backend.estimator.actionPrice');

		Route::get('dashboard', 'Backend\EstimatorController@dashboard')->name('backend.estimator.dashboard')->middleware('can:dashboard-estimator');
		Route::post('datatablesDashboard', 'Backend\EstimatorController@datatablesDashboard')->name('backend.estimator.datatablesDashboard')->middleware('can:dashboard-estimator');
		Route::post('datatablesDetailEstimator', 'Backend\EstimatorController@datatablesDetailEstimator')->name('backend.estimator.datatablesDetailEstimator')->middleware('can:dashboard-estimator');

		Route::post('getEstimator', 'Backend\EstimatorController@getEstimator')->name('backend.estimator.getEstimator')->middleware('can:create-estimator');
		Route::post('getDetail', 'Backend\EstimatorController@getDetail')->name('backend.estimator.getDetail');
	});

	// Admin Production
	Route::group(['prefix' => 'production'], function()
	{
		Route::get('/', 'Backend\ProductionController@index')->name('backend.production')->middleware('can:list-production');
		Route::get('index', 'Backend\ProductionController@index')->name('backend.production.index')->middleware('can:list-production');
		Route::post('datatables', 'Backend\ProductionController@datatables')->name('backend.production.datatables')->middleware('can:list-production');

		Route::post('action', 'Backend\ProductionController@action')->name('backend.production.action');

		Route::post('pdf', 'Backend\ProductionController@pdf')->name('backend.production.pdf');

		Route::get('calendar', 'Backend\ProductionController@calendar')->name('backend.production.calendar')->middleware('can:list-production');
		Route::post('ajaxCalendar', 'Backend\ProductionController@ajaxCalendar')->name('backend.production.ajaxCalendar')->middleware('can:list-production');

		Route::post('complete', 'Backend\ProductionController@complete')->name('backend.production.complete');

		Route::post('history', 'Backend\ProductionController@history')->name('backend.production.history');
	});


	// Admin Offer
	Route::group(['prefix' => 'offer'], function()
	{
		Route::get('/', 'Backend\OfferController@index')->name('backend.offer')->middleware('can:list-offer');
		Route::get('index', 'Backend\OfferController@index')->name('backend.offer.index')->middleware('can:list-offer');
		Route::post('datatables', 'Backend\OfferController@datatables')->name('backend.offer.datatables')->middleware('can:list-offer');

		Route::get('dashboard', 'Backend\OfferController@dashboard')->name('backend.offer.dashboard')->middleware('can:dashboard-offer');
		Route::post('datatablesDashboardSales', 'Backend\OfferController@datatablesDashboardSales')->name('backend.offer.datatablesDashboardSales')->middleware('can:dashboard-offer');
		Route::post('datatablesDashboardClient', 'Backend\OfferController@datatablesDashboardClient')->name('backend.offer.datatablesDashboardClient')->middleware('can:dashboard-offer');


		Route::post('getData', 'Backend\OfferController@getData')->name('backend.offer.getData')->middleware('can:dashboard-offer');

		Route::get('create', 'Backend\OfferController@create')->name('backend.offer.create')->middleware('can:create-offer');
		Route::post('store', 'Backend\OfferController@store')->name('backend.offer.store')->middleware('can:create-offer');
		Route::get('edit/{index}', 'Backend\OfferController@edit')->name('backend.offer.edit');
		Route::post('update/{index}', 'Backend\OfferController@update')->name('backend.offer.update')->middleware('can:update-offer,index');
		Route::post('delete', 'Backend\OfferController@delete')->name('backend.offer.delete');
		Route::post('action', 'Backend\OfferController@action')->name('backend.offer.action');
		Route::post('pdf', 'Backend\OfferController@pdf')->name('backend.offer.pdf');

		Route::get('history/{index}', 'Backend\OfferController@history')->name('backend.offer.history');

		Route::post('datatablesDetail/{index}', 'Backend\OfferController@datatablesDetail')->name('backend.offer.datatablesDetail')->middleware('can:update-offer,index');
		Route::post('storeDetail', 'Backend\OfferController@storeDetail')->name('backend.offer.storeDetail');
		Route::post('updateDetail', 'Backend\OfferController@updateDetail')->name('backend.offer.updateDetail');
		Route::post('deleteDetail', 'Backend\OfferController@deleteDetail')->name('backend.offer.deleteDetail');
		Route::post('actionDetail', 'Backend\OfferController@actionDetail')->name('backend.offer.actionDetail');
		Route::post('statusDetail', 'Backend\OfferController@statusDetail')->name('backend.offer.statusDetail');
		Route::post('undoDetail', 'Backend\OfferController@undoDetail')->name('backend.offer.undoDetail');

		Route::post('getDocument', 'Backend\OfferController@getDocument')->name('backend.offer.getDocument');
		Route::post('getDetail', 'Backend\OfferController@getDetail')->name('backend.offer.getDetail');
	});


	


	// Admin Invoice
	Route::group(['prefix' => 'invoice'], function()
	{
		Route::get('/', 'Backend\InvoiceController@index')->name('backend.invoice')->middleware('can:list-invoice');
		Route::get('index', 'Backend\InvoiceController@index')->name('backend.invoice.index')->middleware('can:list-invoice');
		Route::post('datatables', 'Backend\InvoiceController@datatables')->name('backend.invoice.datatables')->middleware('can:list-invoice');
		Route::post('getStatus', 'Backend\InvoiceController@getStatus')->name('backend.invoice.getStatus')->middleware('can:list-invoice');

		Route::get('dashboard', 'Backend\InvoiceController@dashboard')->name('backend.invoice.dashboard')->middleware('can:dashboard-invoice');
		Route::post('datatablesDashboard', 'Backend\InvoiceController@datatablesDashboard')->name('backend.invoice.datatablesDashboard')->middleware('can:dashboard-invoice');
		Route::post('datatablesDetailDashboard', 'Backend\InvoiceController@datatablesDetailDashboard')->name('backend.invoice.datatablesDetailDashboard')->middleware('can:dashboard-invoice');
		Route::post('ajaxDashboardPrice', 'Backend\InvoiceController@ajaxDashboardPrice')->name('backend.invoice.ajaxDashboardPrice')->middleware('can:dashboard-invoice');

		Route::post('noAdmin', 'Backend\InvoiceController@noAdmin')->name('backend.invoice.noAdmin');
		Route::post('addDocument', 'Backend\InvoiceController@addDocument')->name('backend.invoice.addDocument');
		Route::post('redoDocument', 'Backend\InvoiceController@redoDocument')->name('backend.invoice.redoDocument');
		Route::post('undoDocument', 'Backend\InvoiceController@undoDocument')->name('backend.invoice.undoDocument');
		Route::post('delete', 'Backend\InvoiceController@delete')->name('backend.invoice.delete');
		Route::post('addInvoice', 'Backend\InvoiceController@addInvoice')->name('backend.invoice.addInvoice');
		Route::post('undoInvoice', 'Backend\InvoiceController@undoInvoice')->name('backend.invoice.undoInvoice');
		Route::post('addReceived', 'Backend\InvoiceController@addReceived')->name('backend.invoice.addReceived');
		Route::post('undoReceived', 'Backend\InvoiceController@undoReceived')->name('backend.invoice.undoReceived');
		Route::post('addSend', 'Backend\InvoiceController@addSend')->name('backend.invoice.addSend');
		Route::post('undoSend', 'Backend\InvoiceController@undoSend')->name('backend.invoice.undoSend');
		Route::post('checkFinance', 'Backend\InvoiceController@checkFinance')->name('backend.invoice.checkFinance');
		Route::post('noteInvoice', 'Backend\InvoiceController@noteInvoice')->name('backend.invoice.noteInvoice');
		Route::post('checkMaster', 'Backend\InvoiceController@checkMaster')->name('backend.invoice.checkMaster')->middleware('can:checkMaster-invoice');

		Route::post('excel', 'Backend\InvoiceController@excel')->name('backend.invoice.excel')->middleware('can:excel-invoice');
	});


	// Admin Delivery
	Route::group(['prefix' => 'delivery'], function()
	{
		Route::get('/', 'Backend\DeliveryController@index')->name('backend.delivery')->middleware('can:list-delivery');
		Route::get('index', 'Backend\DeliveryController@index')->name('backend.delivery.index')->middleware('can:list-delivery');
		Route::post('datatables', 'Backend\DeliveryController@datatables')->name('backend.delivery.datatables')->middleware('can:list-delivery');

		Route::get('create', 'Backend\DeliveryController@create')->name('backend.delivery.create')->middleware('can:create-delivery');
		Route::post('store', 'Backend\DeliveryController@store')->name('backend.delivery.store')->middleware('can:create-delivery');
		Route::get('{id}/edit', 'Backend\DeliveryController@edit')->name('backend.delivery.edit')->middleware('can:edit-delivery');
		Route::post('{id}/update', 'Backend\DeliveryController@update')->name('backend.delivery.update')->middleware('can:edit-delivery');
		Route::post('delete', 'Backend\DeliveryController@delete')->name('backend.delivery.delete')->middleware('can:delete-delivery');
		
		Route::post('change', 'Backend\DeliveryController@change')->name('backend.delivery.change')->middleware('can:change-delivery');
		
		Route::post('send', 'Backend\DeliveryController@send')->name('backend.delivery.send')->middleware('can:send-delivery');
		Route::post('undoSend', 'Backend\DeliveryController@undoSend')->name('backend.delivery.undoSend')->middleware('can:undoSend-delivery');
		

		Route::post('take', 'Backend\DeliveryController@take')->name('backend.delivery.take')->middleware('can:take-delivery');
		Route::post('undoTake', 'Backend\DeliveryController@undoTake')->name('backend.delivery.undoTake')->middleware('can:undoTake-delivery');

		Route::post('confirm', 'Backend\DeliveryController@confirm')->name('backend.delivery.confirm')->middleware('can:confirm-delivery');
		Route::post('undoConfirm', 'Backend\DeliveryController@undoConfirm')->name('backend.delivery.undoConfirm')->middleware('can:undoConfirm-delivery');

		Route::get('viewDistance', 'Backend\DeliveryController@viewDistance')->name('backend.delivery.viewDistance')->middleware('can:viewDist-delivery');
		Route::post('datatablesViewDistance', 'Backend\DeliveryController@datatablesViewDistance')->name('backend.delivery.datatablesViewDistance')->middleware('can:viewDist-delivery');

		Route::get('courier', 'Backend\DeliveryController@courier')->name('backend.delivery.courier')->middleware('can:courier-delivery');
		Route::post('datatablesCourier', 'Backend\DeliveryController@datatablesCourier')->name('backend.delivery.datatablesCourier')->middleware('can:courier-delivery');

		Route::post('startSend', 'Backend\DeliveryController@startSend')->name('backend.delivery.startSend');
		Route::post('undoStartSend', 'Backend\DeliveryController@undoStartSend')->name('backend.delivery.undoStartSend');

		Route::post('finish', 'Backend\DeliveryController@finish')->name('backend.delivery.finish');
		Route::post('undoFinish', 'Backend\DeliveryController@undoFinish')->name('backend.delivery.undoFinish');
	});


	// Admin Designer
	Route::group(['prefix' => 'designer'], function()
	{
		Route::get('/', 'Backend\DesignerController@index')->name('backend.designer')->middleware('can:list-designer');
		Route::get('index', 'Backend\DesignerController@index')->name('backend.designer.index')->middleware('can:list-designer');
		Route::post('datatables', 'Backend\DesignerController@datatables')->name('backend.designer.datatables')->middleware('can:list-designer');

		Route::get('calendar', 'Backend\DesignerController@calendar')->name('backend.designer.calendar')->middleware('can:list-designer');
		Route::post('ajaxCalendar', 'Backend\DesignerController@ajaxCalendar')->name('backend.designer.ajaxCalendar')->middleware('can:list-designer');

		Route::get('dashboard', 'Backend\DesignerController@dashboard')->name('backend.designer.dashboard')->middleware('can:dashboard-designer');
		Route::post('ajaxDashboard', 'Backend\DesignerController@ajaxDashboard')->name('backend.designer.ajaxDashboard')->middleware('can:dashboard-designer');
		Route::post('getData', 'Backend\DesignerController@getData')->name('backend.designer.getData')->middleware('can:dashboard-designer');

		Route::get('create', 'Backend\DesignerController@create')->name('backend.designer.create')->middleware('can:create-designer');
		Route::post('store', 'Backend\DesignerController@store')->name('backend.designer.store')->middleware('can:create-designer');
		Route::get('{id}/edit', 'Backend\DesignerController@edit')->name('backend.designer.edit')->middleware('can:edit-designer');
		Route::post('{id}/update', 'Backend\DesignerController@update')->name('backend.designer.update')->middleware('can:edit-designer');
		Route::post('delete', 'Backend\DesignerController@delete')->name('backend.designer.delete')->middleware('can:delete-designer');
		Route::post('action', 'Backend\DesignerController@action')->name('backend.designer.action');

		Route::post('take', 'Backend\DesignerController@take')->name('backend.designer.take')->middleware('can:take-designer');
		Route::post('finish', 'Backend\DesignerController@finish')->name('backend.designer.finish')->middleware('can:finish-designer');
		Route::post('approve', 'Backend\DesignerController@approve')->name('backend.designer.approve')->middleware('can:approved-designer');
		Route::post('reject', 'Backend\DesignerController@reject')->name('backend.designer.reject')->middleware('can:approved-designer');
		Route::post('success', 'Backend\DesignerController@success')->name('backend.designer.success')->middleware('can:project-designer');
		Route::post('failed', 'Backend\DesignerController@failed')->name('backend.designer.failed')->middleware('can:project-designer');
		Route::post('revision', 'Backend\DesignerController@revision')->name('backend.designer.revision')->middleware('can:revision-designer');

		Route::get('designCandidate', 'Backend\DesignerController@designCandidate')->name('backend.designer.designCandidate')->middleware('can:designCandidate-designer');
		Route::post('datatablesDesignCandidate', 'Backend\DesignerController@datatablesDesignCandidate')->name('backend.designer.datatablesDesignCandidate')->middleware('can:designCandidate-designer');

		
		Route::get('{id}/createDesignCandidate', 'Backend\DesignerController@createDesignCandidate')->name('backend.designer.createDesignCandidate')->middleware('can:createDesignCandidate-designer');
		Route::post('{id}/storeDesignCandidate', 'Backend\DesignerController@storeDesignCandidate')->name('backend.designer.storeDesignCandidate')->middleware('can:createDesignCandidate-designer');
		
		
		Route::get('{id}/editDesignCandidate', 'Backend\DesignerController@editDesignCandidate')->name('backend.designer.editDesignCandidate')->middleware('can:editDesignCandidate-designer');
		Route::post('datatablesEditDesignCandidate', 'Backend\DesignerController@datatablesEditDesignCandidate')->name('backend.designer.datatablesEditDesignCandidate')->middleware('can:editDesignCandidate-designer');
		
		Route::post('{id}/updateDesignCandidate', 'Backend\DesignerController@updateDesignCandidate')->name('backend.designer.updateDesignCandidate')->middleware('can:editDesignCandidate-designer');

		Route::post('actionDesignCandidatePreview', 'Backend\DesignerController@actionDesignCandidatePreview')->name('backend.designer.actionDesignCandidatePreview');
		
		
		Route::post('deleteDesignCandidate', 'Backend\DesignerController@deleteDesignCandidate')->name('backend.designer.deleteDesignCandidate')->middleware('can:deleteDesignCandidate-designer');
	});


	// Admin PR
	Route::group(['prefix' => 'pr'], function()
	{
		Route::get('/', 'Backend\PrController@index')->name('backend.pr')->middleware('can:list-pr');
		Route::get('index', 'Backend\PrController@index')->name('backend.pr.index')->middleware('can:list-pr');
		Route::post('datatables', 'Backend\PrController@datatables')->name('backend.pr.datatables')->middleware('can:list-pr');

		Route::post('storeProjectPr', 'Backend\PrController@storeProjectPr')->name('backend.pr.storeProjectPr')->middleware('can:create-pr');
		Route::post('storeOfficePr', 'Backend\PrController@storeOfficePr')->name('backend.pr.storeOfficePr')->middleware('can:create-pr');
		Route::post('storePaymentPr', 'Backend\PrController@storePaymentPr')->name('backend.pr.storePaymentPr')->middleware('can:create-pr');

		Route::get('edit/{index}', 'Backend\PrController@edit')->name('backend.pr.edit');
		Route::post('update/{index}', 'Backend\PrController@update')->name('backend.pr.update');
		Route::post('delete', 'Backend\PrController@delete')->name('backend.pr.delete');
		Route::post('action', 'Backend\PrController@action')->name('backend.pr.action');

		Route::post('datatablesDetail/{index}', 'Backend\PrController@datatablesPrDetail')->name('backend.pr.datatablesDetail');
		Route::post('storeDetail', 'Backend\PrController@storePrDetail')->name('backend.pr.storeDetail');
		Route::post('updateDetail', 'Backend\PrController@updatePrDetail')->name('backend.pr.updateDetail');
		Route::post('deleteDetail', 'Backend\PrController@deletePrDetail')->name('backend.pr.deleteDetail');
		Route::post('actionDetail', 'Backend\PrController@actionPrDetail')->name('backend.pr.actionDetail');


		Route::get('unconfirm', 'Backend\PrController@unconfirm')->name('backend.pr.unconfirm');
		Route::post('datatablesUnconfirm', 'Backend\PrController@datatablesUnconfirm')->name('backend.pr.datatablesUnconfirm');
		Route::post('updateConfirm', 'Backend\PrController@updateConfirm')->name('backend.pr.updateConfirm')->middleware('can:confirm-pr');

		Route::get('confirm', 'Backend\PrController@confirm')->name('backend.pr.confirm');
		Route::post('revision', 'Backend\PrController@revision')->name('backend.pr.revision');

	    Route::get('datatablesConfirmGet', 'Backend\PrController@datatablesConfirm')->name('backend.pr.datatablesConfirm');
	    Route::post('datatablesConfirm', 'Backend\PrController@datatablesConfirm')->name('backend.pr.datatablesConfirm');
		Route::post('getStatusConfirmProject', 'Backend\PrController@getStatusConfirmProject')->name('backend.pr.getStatusConfirmProject');

	    Route::post('getStatusConfirmPayment', 'Backend\PrController@getStatusConfirmPayment')->name('backend.pr.getStatusConfirmPayment');
	    Route::post('datatablesConfirmPayment', 'Backend\PrController@datatablesConfirmPayment')->name('backend.pr.datatablesConfirmPayment');

		Route::get('dashboard', 'Backend\PrController@dashboard')->name('backend.pr.dashboard')->middleware('can:dashboard-pr');
		Route::post('ajaxDashboard', 'Backend\PrController@ajaxDashboard')->name('backend.pr.ajaxDashboard')->middleware('can:dashboard-pr');
		Route::post('datatablesDetailDashboard', 'Backend\PrController@datatablesDetailDashboard')->name('backend.pr.datatablesDetailDashboard')->middleware('can:dashboard-pr');

		Route::post('excel', 'Backend\PrController@excel')->name('backend.pr.excel')->middleware('can:excel-pr');

		

		

		Route::post('changePurchasing', 'Backend\PrController@changePurchasing')->name('backend.pr.changePurchasing')->middleware('can:changePurchasing-pr');
		Route::post('changeStatus', 'Backend\PrController@changeStatus')->name('backend.pr.changeStatus')->middleware('can:changePurchasing-pr');

		Route::post('checkAudit', 'Backend\PrController@checkAudit')->name('backend.pr.checkAudit')->middleware('can:checkAudit-pr');
		Route::post('checkFinance', 'Backend\PrController@checkFinance')->name('backend.pr.checkFinance')->middleware('can:checkFinance-pr');
		Route::post('noteAudit', 'Backend\PrController@noteAudit')->name('backend.pr.noteAudit')->middleware('can:checkAudit-pr');

		Route::post('storePoProject', 'Backend\PrController@storePoProject')->name('backend.pr.storePoProject')->middleware('can:create-po');
		Route::post('storePoPayment', 'Backend\PrController@storePoPayment')->name('backend.pr.storePoPayment')->middleware('can:create-po');

		Route::post('updatePoProject', 'Backend\PrController@updatePoProject')->name('backend.pr.updatePoProject');
		Route::post('updatePoPayment', 'Backend\PrController@updatePoPayment')->name('backend.pr.updatePoPayment');

		Route::post('deletePo', 'Backend\PrController@deletePo')->name('backend.pr.deletePo');
		Route::post('undoPo', 'Backend\PrController@undoPo')->name('backend.pr.undoPo');

		Route::post('pdf', 'Backend\PrController@pdf')->name('backend.pr.pdf')->middleware('can:pdf-pr');
		Route::post('getSpkItem', 'Backend\PrController@getSpkItem')->name('backend.pr.getSpkItem');

		Route::get('item', 'Backend\PrController@item')->name('backend.pr.item')->middleware('can:list-pr');
		Route::post('datatablesItem', 'Backend\PrController@datatablesItem')->name('backend.pr.datatablesItem')->middleware('can:list-pr');
		Route::post('receivedItem', 'Backend\PrController@receivedItem')->name('backend.pr.receivedItem');
		Route::post('complainItem', 'Backend\PrController@complainItem')->name('backend.pr.complainItem');
	});


	// Admin Supplier
	Route::group(['prefix' => 'supplier'], function()
	{
		Route::get('/', 'Backend\SupplierController@index')->name('backend.supplier')->middleware('can:list-supplier');
		Route::get('index', 'Backend\SupplierController@index')->name('backend.supplier.index')->middleware('can:list-supplier');
		Route::post('datatables', 'Backend\SupplierController@datatables')->name('backend.supplier.datatables')->middleware('can:list-supplier');

		Route::get('create', 'Backend\SupplierController@create')->name('backend.supplier.create')->middleware('can:create-supplier');
		Route::post('store', 'Backend\SupplierController@store')->name('backend.supplier.store')->middleware('can:create-supplier');
		Route::get('{id}/edit', 'Backend\SupplierController@edit')->name('backend.supplier.edit')->middleware('can:edit-supplier');
		Route::post('{id}/update', 'Backend\SupplierController@update')->name('backend.supplier.update')->middleware('can:edit-supplier');
		Route::post('delete', 'Backend\SupplierController@delete')->name('backend.supplier.delete')->middleware('can:delete-supplier');
		Route::post('action', 'Backend\SupplierController@action')->name('backend.supplier.action');
	});


	// Admin Todo
	Route::group(['prefix' => 'todo'], function()
	{
		Route::get('/', 'Backend\TodoController@index')->name('backend.todo')->middleware('can:list-todo');
		Route::get('index', 'Backend\TodoController@index')->name('backend.todo.index')->middleware('can:list-todo');
		Route::post('datatables', 'Backend\TodoController@datatables')->name('backend.todo.datatables')->middleware('can:list-todo');

		Route::get('calendar', 'Backend\TodoController@calendar')->name('backend.todo.calendar')->middleware('can:list-todo');
		Route::post('ajaxCalendar', 'Backend\TodoController@ajaxCalendar')->name('backend.todo.ajaxCalendar')->middleware('can:list-todo');

		Route::get('create', 'Backend\TodoController@create')->name('backend.todo.create')->middleware('can:create-todo');
		Route::post('store', 'Backend\TodoController@store')->name('backend.todo.store')->middleware('can:create-todo');
		Route::get('{id}/edit', 'Backend\TodoController@edit')->name('backend.todo.edit')->middleware('can:edit-todo');
		Route::post('{id}/update', 'Backend\TodoController@update')->name('backend.todo.update')->middleware('can:edit-todo');
		Route::post('delete', 'Backend\TodoController@delete')->name('backend.todo.delete')->middleware('can:delete-todo');
		Route::post('action', 'Backend\TodoController@action')->name('backend.todo.action');

		Route::post('status', 'Backend\TodoController@status')->name('backend.todo.status')->middleware('can:status-todo');
		Route::post('undo', 'Backend\TodoController@undo')->name('backend.todo.undo')->middleware('can:undo-todo');

		Route::get('dashboard', 'Backend\TodoController@dashboard')->name('backend.todo.dashboard')->middleware('can:dashboard-todo');
		Route::post('ajaxSales', 'Backend\TodoController@ajaxSales')->name('backend.todo.ajaxSales')->middleware('can:dashboard-todo');
		Route::post('datatablesDetailSales', 'Backend\TodoController@datatablesDetailSales')->name('backend.todo.datatablesDetailSales')->middleware('can:dashboard-todo');
	});


	// Admin Car
	Route::group(['prefix' => 'car'], function()
	{
		Route::get('/', 'Backend\CarController@index')->name('backend.car')->middleware('can:list-car');
		Route::get('index', 'Backend\CarController@index')->name('backend.car.index')->middleware('can:list-car');
		Route::post('datatables', 'Backend\CarController@datatables')->name('backend.car.datatables')->middleware('can:list-car');

		Route::get('create', 'Backend\CarController@create')->name('backend.car.create')->middleware('can:create-car');
		Route::post('store', 'Backend\CarController@store')->name('backend.car.store')->middleware('can:create-car');
		Route::get('{id}/edit', 'Backend\CarController@edit')->name('backend.car.edit')->middleware('can:edit-car');
		Route::post('{id}/update', 'Backend\CarController@update')->name('backend.car.update')->middleware('can:edit-car');
		Route::post('delete', 'Backend\CarController@delete')->name('backend.car.delete')->middleware('can:delete-car');
		Route::post('action', 'Backend\CarController@action')->name('backend.car.action');
	});


	// Admin Advertisment
	Route::group(['prefix' => 'advertisment'], function()
	{
		Route::get('/', 'Backend\AdController@index')->name('backend.advertisment')->middleware('can:list-advertisment');
		Route::get('index', 'Backend\AdController@index')->name('backend.advertisment.index')->middleware('can:list-advertisment');
		Route::post('datatables', 'Backend\AdController@datatables')->name('backend.advertisment.datatables')->middleware('can:list-advertisment');

		Route::post('store', 'Backend\AdController@store')->name('backend.advertisment.store')->middleware('can:create-advertisment');
		Route::post('update', 'Backend\AdController@update')->name('backend.advertisment.update')->middleware('can:edit-advertisment');
		Route::post('delete', 'Backend\AdController@delete')->name('backend.advertisment.delete')->middleware('can:delete-advertisment');
		Route::post('action', 'Backend\AdController@action')->name('backend.advertisment.action');

		Route::post('storeDetail', 'Backend\AdController@storeDetail')->name('backend.advertisment.storeDetail')->middleware('can:create-advertisment');
		Route::post('updateDetail', 'Backend\AdController@updateDetail')->name('backend.advertisment.updateDetail')->middleware('can:edit-advertisment');
		Route::post('deleteDetail', 'Backend\AdController@deleteDetail')->name('backend.advertisment.deleteDetail')->middleware('can:delete-advertisment');
	});


	


	

	// Admin Trash
	Route::group(['prefix' => 'archive'], function()
	{
		Route::get('/', 'Backend\ArchiveController@index')->name('backend.archive')->middleware('can:list-archive');
		Route::get('index', 'Backend\ArchiveController@index')->name('backend.archive.index')->middleware('can:list-archive');
		Route::post('datatables', 'Backend\ArchiveController@datatables')->name('backend.archive.datatables')->middleware('can:list-archive');

		Route::post('recover', 'Backend\ArchiveController@recover')->name('backend.archive.recover')->middleware('can:list-archive');
	});



	// Admin Trash
	Route::group(['prefix' => 'trash'], function()
	{
		Route::get('company', 'Backend\TrashController@company')->name('backend.trash.company')->middleware('can:list-trash');
		Route::post('datatablesCompany', 'Backend\TrashController@datatablesCompany')->name('backend.trash.datatablesCompany')->middleware('can:list-trash');

		Route::get('brand', 'Backend\TrashController@brand')->name('backend.trash.brand')->middleware('can:list-trash');
		Route::post('datatablesBrand', 'Backend\TrashController@datatablesBrand')->name('backend.trash.datatablesBrand')->middleware('can:list-trash');

		Route::get('address', 'Backend\TrashController@address')->name('backend.trash.address')->middleware('can:list-trash');
		Route::post('datatablesAddress', 'Backend\TrashController@datatablesAddress')->name('backend.trash.datatablesAddress')->middleware('can:list-trash');

		Route::get('pic', 'Backend\TrashController@pic')->name('backend.trash.pic')->middleware('can:list-trash');
		Route::post('datatablesPic', 'Backend\TrashController@datatablesPic')->name('backend.trash.datatablesPic')->middleware('can:list-trash');

		Route::get('spk', 'Backend\TrashController@spk')->name('backend.trash.spk')->middleware('can:list-trash');
		Route::post('datatablesSpk', 'Backend\TrashController@datatablesSpk')->name('backend.trash.datatablesSpk')->middleware('can:list-trash');

		Route::get('production', 'Backend\TrashController@production')->name('backend.trash.production')->middleware('can:list-trash');
		Route::post('datatablesProduction', 'Backend\TrashController@datatablesProduction')->name('backend.trash.datatablesProduction')->middleware('can:list-trash');

		Route::get('invoice', 'Backend\TrashController@invoice')->name('backend.trash.invoice')->middleware('can:list-trash');
		Route::post('datatablesInvoice', 'Backend\TrashController@datatablesInvoice')->name('backend.trash.datatablesInvoice')->middleware('can:list-trash');

		Route::get('offer', 'Backend\TrashController@offer')->name('backend.trash.offer')->middleware('can:list-trash');
		Route::post('datatablesOffer', 'Backend\TrashController@datatablesOffer')->name('backend.trash.datatablesOffer')->middleware('can:list-trash');

		Route::get('offerList', 'Backend\TrashController@offerList')->name('backend.trash.offerList')->middleware('can:list-trash');
		Route::post('datatablesOfferList', 'Backend\TrashController@datatablesOfferList')->name('backend.trash.datatablesOfferList')->middleware('can:list-trash');

		Route::get('pr', 'Backend\TrashController@pr')->name('backend.trash.pr')->middleware('can:list-trash');
		Route::post('datatablesPr', 'Backend\TrashController@datatablesPr')->name('backend.trash.datatablesPr')->middleware('can:list-trash');

		Route::get('prDetail', 'Backend\TrashController@prDetail')->name('backend.trash.prDetail')->middleware('can:list-trash');
		Route::post('datatablesPrDetail', 'Backend\TrashController@datatablesPrDetail')->name('backend.trash.datatablesPrDetail')->middleware('can:list-trash');


		Route::post('delete', 'Backend\TrashController@delete')->name('backend.trash.delete')->middleware('can:delete-trash');
		Route::post('restore', 'Backend\TrashController@restore')->name('backend.trash.restore')->middleware('can:restore-trash');
		Route::post('action', 'Backend\TrashController@action')->name('backend.trash.action');
	});


	// Admin Design Request
	Route::group(['prefix' => 'designRequest'], function()
	{
		Route::get('/', 'Backend\DesignRequestController@index')->name('backend.designRequest')->middleware('can:list-designRequest');
		Route::get('index', 'Backend\DesignRequestController@index')->name('backend.designRequest.index')->middleware('can:list-designRequest');
		Route::post('datatables', 'Backend\DesignRequestController@datatables')->name('backend.designRequest.datatables')->middleware('can:list-designRequest');

		Route::get('create', 'Backend\DesignRequestController@create')->name('backend.designRequest.create')->middleware('can:create-designRequest');
		Route::post('store', 'Backend\DesignRequestController@store')->name('backend.designRequest.store')->middleware('can:create-designRequest');

		Route::get('{id}/edit', 'Backend\DesignRequestController@edit')->name('backend.designRequest.edit')->middleware('can:edit-designRequest');
		Route::post('datatablesDesignCandidate', 'Backend\DesignRequestController@datatablesDesignCandidate')->name('backend.designRequest.datatablesDesignCandidate')->middleware('can:edit-designRequest');

		Route::post('{id}/update', 'Backend\DesignRequestController@update')->name('backend.designRequest.update')->middleware('can:edit-designRequest');

		Route::post('{id}/setStatus', 'Backend\DesignRequestController@setStatus')->name('backend.designRequest.setStatus')->middleware('can:setStatus-designRequest');

		Route::post('delete', 'Backend\DesignRequestController@delete')->name('backend.designRequest.delete')->middleware('can:delete-designRequest');
		Route::post('action', 'Backend\DesignRequestController@action')->name('backend.designRequest.action');
	});


	// Admin Contract
	Route::group(['prefix' => 'contract'], function()
	{
		Route::get('/', 'Backend\ContractController@index')->name('backend.contract')->middleware('can:list-contract');
		Route::get('index', 'Backend\ContractController@index')->name('backend.contract.index')->middleware('can:list-contract');
		Route::post('datatables', 'Backend\ContractController@datatables')->name('backend.contract.datatables')->middleware('can:list-contract');

		Route::get('create', 'Backend\ContractController@create')->name('backend.contract.create')->middleware('can:create-contract');
		Route::post('store', 'Backend\ContractController@store')->name('backend.contract.store')->middleware('can:create-contract');

		Route::get('{id}/edit', 'Backend\ContractController@edit')->name('backend.contract.edit')->middleware('can:edit-contract');
		Route::post('{id}/update', 'Backend\ContractController@update')->name('backend.contract.update')->middleware('can:edit-contract');

		Route::post('delete', 'Backend\ContractController@delete')->name('backend.contract.delete')->middleware('can:delete-contract');
		Route::post('action', 'Backend\ContractController@action')->name('backend.contract.action');

		Route::post('getContract', 'Backend\ContractController@getContract')->name('backend.contract.getContract');
		Route::post('getOffer', 'Backend\ContractController@getOffer')->name('backend.contract.getOffer');
		Route::post('pdf', 'Backend\ContractController@pdf')->name('backend.contract.pdf')->middleware('can:pdf-contract');


		Route::post('{id}/generateSpk', 'Backend\ContractController@generateSpk')->name('backend.contract.generateSpk')->middleware('can:create-spk');
	});

	// Admin List Request
	Route::group(['prefix' => 'listRequest'], function()
	{
		Route::get('/', 'Backend\ListRequestController@index')->name('backend.listRequest')->middleware('can:list-listRequest');
		Route::get('index', 'Backend\ListRequestController@index')->name('backend.listRequest.index')->middleware('can:list-listRequest');
		Route::post('datatables', 'Backend\ListRequestController@datatables')->name('backend.listRequest.datatables')->middleware('can:list-listRequest');
		
		Route::post('getStatus', 'Backend\ListRequestController@getStatus')->name('backend.listRequest.getStatus')->middleware('can:status-listRequest');

		Route::post('store', 'Backend\ListRequestController@store')->name('backend.listRequest.store')->middleware('can:create-listRequest');
		Route::post('update', 'Backend\ListRequestController@update')->name('backend.listRequest.update')->middleware('can:edit-listRequest');
		Route::post('delete', 'Backend\ListRequestController@delete')->name('backend.listRequest.delete')->middleware('can:delete-listRequest');
		Route::post('action', 'Backend\ListRequestController@action')->name('backend.listRequest.action');
		Route::post('feedback', 'Backend\ListRequestController@feedback')->name('backend.listRequest.feedback')->middleware('can:feedback-listRequest');
		Route::post('undoFeedback', 'Backend\ListRequestController@undoFeedback')->name('backend.listRequest.undoFeedback')->middleware('can:undoFeedback-listRequest');
		Route::post('confirm', 'Backend\ListRequestController@confirm')->name('backend.listRequest.confirm')->middleware('can:confirm-listRequest');
		Route::post('undoConfirm', 'Backend\ListRequestController@undoConfirm')->name('backend.listRequest.undoConfirm')->middleware('can:undoConfirm-listRequest');

	});


	// Admin CRM
	Route::group(['prefix' => 'crm'], function()
	{
		Route::get('/', 'Backend\CrmController@index')->name('backend.crm')->middleware('can:list-crm');
		Route::get('index', 'Backend\CrmController@index')->name('backend.crm.index')->middleware('can:list-crm');
		Route::post('datatables', 'Backend\CrmController@datatables')->name('backend.crm.datatables')->middleware('can:list-crm');

		Route::post('store', 'Backend\CrmController@store')->name('backend.crm.store')->middleware('can:create-crm');
		Route::get('createProspec', 'Backend\CrmController@createProspec')->name('backend.crm.createProspec')->middleware('can:create-crm');
		Route::post('storeProspec', 'Backend\CrmController@storeProspec')->name('backend.crm.storeProspec')->middleware('can:create-crm');

		Route::post('update', 'Backend\CrmController@update')->name('backend.crm.update')->middleware('can:edit-crm');
		Route::get('editProspec/{id}', 'Backend\CrmController@editProspec')->name('backend.crm.editProspec')->middleware('can:edit-crm');
		Route::post('updateProspec/{id}', 'Backend\CrmController@updateProspec')->name('backend.crm.updateProspec')->middleware('can:edit-crm');

		Route::post('delete', 'Backend\CrmController@delete')->name('backend.crm.delete')->middleware('can:delete-crm');
		Route::post('action', 'Backend\CrmController@action')->name('backend.crm.action');
		Route::post('next', 'Backend\CrmController@next')->name('backend.crm.next')->middleware('can:next-crm');
		Route::post('reschedule', 'Backend\CrmController@reschedule')->name('backend.crm.reschedule')->middleware('can:reschedule-crm');
		
		Route::get('calendar/{id}', 'Backend\CrmController@calendar')->name('backend.crm.calendar');
		Route::post('ajaxCalendar', 'Backend\CrmController@ajaxCalendar')->name('backend.crm.ajaxCalendar');

		Route::post('checkIn', 'Backend\CrmController@checkIn')->name('backend.crm.checkIn');
		Route::post('checkOut', 'Backend\CrmController@checkOut')->name('backend.crm.checkOut');

		Route::post('sendFeedbackByEmail', 'Backend\CrmController@sendFeedbackByEmail')->name('backend.crm.sendFeedbackByEmail');
		Route::post('sendFeedbackByWhatsapp', 'Backend\CrmController@sendFeedbackByWhatsapp')->name('backend.crm.sendFeedbackByWhatsapp');

		Route::get('feedback', 'Backend\CrmController@feedback')->name('backend.crm.feedback');
		Route::post('storeFeedback', 'Backend\CrmController@storeFeedback')->name('backend.crm.storeFeedback');
	});


	// Admin Account
	Route::group(['prefix' => 'account'], function()
	{
		Route::get('/', 'Backend\AccountController@accountList')->name('backend.account')->middleware('can:accountList-account');
		Route::get('accountList', 'Backend\AccountController@accountList')->name('backend.account.accountList')->middleware('can:accountList-account');
		Route::post('datatablesAccountList', 'Backend\AccountController@datatablesAccountList')->name('backend.account.datatablesAccountList')->middleware('can:accountList-account');

		Route::post('storeAccountList', 'Backend\AccountController@storeAccountList')->name('backend.account.storeAccountList')->middleware('can:createAccountList-account');
		Route::post('updateAccountList', 'Backend\AccountController@updateAccountList')->name('backend.account.updateAccountList')->middleware('can:editAccountList-account');
		Route::post('deleteAccountList', 'Backend\AccountController@deleteAccountList')->name('backend.account.deleteAccountList')->middleware('can:deleteAccountList-account');
		Route::post('actionAccountList', 'Backend\AccountController@actionAccountList')->name('backend.account.actionAccountList');
		Route::post('activeAccountList', 'Backend\AccountController@activeAccountList')->name('backend.account.activeAccountList')->middleware('can:activeAccountList-account');
		Route::post('setChildAccountList', 'Backend\AccountController@setChildAccountList')->name('backend.account.setChildAccountList')->middleware('can:relationAccountList-account');
		Route::post('setParentAccountList', 'Backend\AccountController@setParentAccountList')->name('backend.account.setParentAccountList')->middleware('can:relationAccountList-account');
		Route::post('mergeAccountList', 'Backend\AccountController@mergeAccountList')->name('backend.account.mergeAccountList')->middleware('can:mergeAccountList-account');


		Route::get('accountClass', 'Backend\AccountController@accountClass')->name('backend.account.accountClass')->middleware('can:accountClass-account');
		Route::post('datatablesAccountClass', 'Backend\AccountController@datatablesAccountClass')->name('backend.account.datatablesAccountClass')->middleware('can:accountClass-account');

		Route::post('storeAccountClass', 'Backend\AccountController@storeAccountClass')->name('backend.account.storeAccountClass')->middleware('can:createAccountClass-account');
		Route::post('updateAccountClass', 'Backend\AccountController@updateAccountClass')->name('backend.account.updateAccountClass')->middleware('can:editAccountClass-account');
		Route::post('deleteAccountClass', 'Backend\AccountController@deleteAccountClass')->name('backend.account.deleteAccountClass')->middleware('can:deleteAccountClass-account');
		Route::post('actionAccountClass', 'Backend\AccountController@actionAccountClass')->name('backend.account.actionAccountClass');


		Route::get('accountType', 'Backend\AccountController@accountType')->name('backend.account.accountType')->middleware('can:accountType-account');
		Route::post('datatablesAccountType', 'Backend\AccountController@datatablesAccountType')->name('backend.account.datatablesAccountType')->middleware('can:accountType-account');

		Route::post('storeAccountType', 'Backend\AccountController@storeAccountType')->name('backend.account.storeAccountType')->middleware('can:createAccountType-account');
		Route::post('updateAccountType', 'Backend\AccountController@updateAccountType')->name('backend.account.updateAccountType')->middleware('can:editAccountType-account');
		Route::post('deleteAccountType', 'Backend\AccountController@deleteAccountType')->name('backend.account.deleteAccountType')->middleware('can:deleteAccountType-account');
		Route::post('actionAccountType', 'Backend\AccountController@actionAccountType')->name('backend.account.actionAccountType');

		Route::get('accountJournal', 'Backend\AccountController@accountJournal')->name('backend.account.accountJournal')->middleware('can:accountJournal-account');
		Route::post('datatablesAccountGeneral', 'Backend\AccountController@datatablesAccountGeneral')->name('backend.account.datatablesAccountGeneral')->middleware('can:accountJournal-account');

		Route::get('createAccountGeneral', 'Backend\AccountController@createAccountGeneral')->name('backend.account.createAccountGeneral')->middleware('can:createAccountGeneral-account');
		Route::post('storeAccountGeneral', 'Backend\AccountController@storeAccountGeneral')->name('backend.account.storeAccountGeneral')->middleware('can:createAccountGeneral-account');
		Route::get('{id}/editAccountGeneral', 'Backend\AccountController@editAccountGeneral')->name('backend.account.editAccountGeneral')->middleware('can:editAccountGeneral-account');
		Route::post('{id}/updateAccountGeneral', 'Backend\AccountController@updateAccountGeneral')->name('backend.account.updateAccountGeneral')->middleware('can:editAccountGeneral-account');
		Route::post('deleteAccountGeneral', 'Backend\AccountController@deleteAccountGeneral')->name('backend.account.deleteAccountGeneral')->middleware('can:deleteAccountGeneral-account');
		Route::post('actionAccountGeneral', 'Backend\AccountController@actionAccountGeneral')->name('backend.account.actionAccountGeneral');

		Route::post('storeAccountGeneralDetail', 'Backend\AccountController@storeAccountGeneralDetail')->name('backend.account.storeAccountGeneralDetail')->middleware('can:editAccountGeneral-account');
		Route::post('updateAccountGeneralDetail', 'Backend\AccountController@updateAccountGeneralDetail')->name('backend.account.updateAccountGeneralDetail')->middleware('can:editAccountGeneral-account');
		Route::post('deleteAccountGeneralDetail', 'Backend\AccountController@deleteAccountGeneralDetail')->name('backend.account.deleteAccountGeneralDetail')->middleware('can:editAccountGeneral-account');



		Route::get('accountSales', 'Backend\AccountController@accountSales')->name('backend.account.accountSales')->middleware('can:accountSales-account');
		Route::post('datatablesAccountSales', 'Backend\AccountController@datatablesAccountSales')->name('backend.account.datatablesAccountSales')->middleware('can:accountSales-account');

		Route::get('createAccountSales', 'Backend\AccountController@createAccountSales')->name('backend.account.createAccountSales')->middleware('can:createAccountSales-account');
		Route::post('storeAccountSales', 'Backend\AccountController@storeAccountSales')->name('backend.account.storeAccountSales')->middleware('can:createAccountSales-account');
		Route::get('{id}/editAccountSales', 'Backend\AccountController@editAccountSales')->name('backend.account.editAccountSales')->middleware('can:editAccountSales-account');
		Route::post('{id}/updateAccountSales', 'Backend\AccountController@updateAccountSales')->name('backend.account.updateAccountSales')->middleware('can:editAccountSales-account');
		Route::post('deleteAccountSales', 'Backend\AccountController@deleteAccountSales')->name('backend.account.deleteAccountSales')->middleware('can:deleteAccountSales-account');
		Route::post('actionAccountSales', 'Backend\AccountController@actionAccountSales')->name('backend.account.actionAccountSales');
		Route::post('statusAccountSales', 'Backend\AccountController@statusAccountSales')->name('backend.account.statusAccountSales');
		Route::post('pdfAccountSales', 'Backend\AccountController@pdfAccountSales')->name('backend.account.pdfAccountSales');

		Route::post('storeAccountSalesDetail', 'Backend\AccountController@storeAccountSalesDetail')->name('backend.account.storeAccountSalesDetail')->middleware('can:editAccountSales-account');
		Route::post('updateAccountSalesDetail', 'Backend\AccountController@updateAccountSalesDetail')->name('backend.account.updateAccountSalesDetail')->middleware('can:editAccountSales-account');
		Route::post('deleteAccountSalesDetail', 'Backend\AccountController@deleteAccountSalesDetail')->name('backend.account.deleteAccountSalesDetail')->middleware('can:editAccountSales-account');



		Route::get('accountBanking', 'Backend\AccountController@accountBanking')->name('backend.account.accountBanking')->middleware('can:accountBanking-account');
		Route::post('datatablesAccountBanking', 'Backend\AccountController@datatablesAccountBanking')->name('backend.account.datatablesAccountBanking')->middleware('can:accountBanking-account');

		Route::get('createAccountBanking', 'Backend\AccountController@createAccountBanking')->name('backend.account.createAccountBanking')->middleware('can:createAccountBanking-account');
		Route::post('storeAccountBanking', 'Backend\AccountController@storeAccountBanking')->name('backend.account.storeAccountBanking')->middleware('can:createAccountBanking-account');
		Route::get('{id}/editAccountBanking', 'Backend\AccountController@editAccountBanking')->name('backend.account.editAccountBanking')->middleware('can:editAccountBanking-account');
		Route::post('{id}/updateAccountBanking', 'Backend\AccountController@updateAccountBanking')->name('backend.account.updateAccountBanking')->middleware('can:editAccountBanking-account');
		Route::post('deleteAccountBanking', 'Backend\AccountController@deleteAccountBanking')->name('backend.account.deleteAccountBanking')->middleware('can:deleteAccountBanking-account');
		Route::post('actionAccountBanking', 'Backend\AccountController@actionAccountBanking')->name('backend.account.actionAccountBanking');
		Route::post('pdfAccountBanking', 'Backend\AccountController@pdfAccountBanking')->name('backend.account.pdfAccountBanking');

		Route::post('storeAccountBankingDetail', 'Backend\AccountController@storeAccountBankingDetail')->name('backend.account.storeAccountBankingDetail')->middleware('can:editAccountBanking-account');
		Route::post('updateAccountBankingDetail', 'Backend\AccountController@updateAccountBankingDetail')->name('backend.account.updateAccountBankingDetail')->middleware('can:editAccountBanking-account');
		Route::post('deleteAccountBankingDetail', 'Backend\AccountController@deleteAccountBankingDetail')->name('backend.account.deleteAccountBankingDetail')->middleware('can:editAccountBanking-account');



		Route::get('accountPurchasing', 'Backend\AccountController@accountPurchasing')->name('backend.account.accountPurchasing')->middleware('can:accountPurchasing-account');
		Route::post('datatablesAccountPurchasing', 'Backend\AccountController@datatablesAccountPurchasing')->name('backend.account.datatablesAccountPurchasing')->middleware('can:accountPurchasing-account');

		Route::get('createAccountPurchasing', 'Backend\AccountController@createAccountPurchasing')->name('backend.account.createAccountPurchasing')->middleware('can:createAccountPurchasing-account');
		Route::post('storeAccountPurchasing', 'Backend\AccountController@storeAccountPurchasing')->name('backend.account.storeAccountPurchasing')->middleware('can:createAccountPurchasing-account');
		Route::get('{id}/editAccountPurchasing', 'Backend\AccountController@editAccountPurchasing')->name('backend.account.editAccountPurchasing')->middleware('can:editAccountPurchasing-account');
		Route::post('{id}/updateAccountPurchasing', 'Backend\AccountController@updateAccountPurchasing')->name('backend.account.updateAccountPurchasing')->middleware('can:editAccountPurchasing-account');
		Route::post('deleteAccountPurchasing', 'Backend\AccountController@deleteAccountPurchasing')->name('backend.account.deleteAccountPurchasing')->middleware('can:deleteAccountPurchasing-account');
		Route::post('actionAccountPurchasing', 'Backend\AccountController@actionAccountPurchasing')->name('backend.account.actionAccountPurchasing');
		Route::post('statusAccountPurchasing', 'Backend\AccountController@statusAccountPurchasing')->name('backend.account.statusAccountPurchasing');
		Route::post('pdfAccountPurchasing', 'Backend\AccountController@pdfAccountPurchasing')->name('backend.account.pdfAccountPurchasing');

		Route::post('storeAccountPurchasingDetail', 'Backend\AccountController@storeAccountPurchasingDetail')->name('backend.account.storeAccountPurchasingDetail')->middleware('can:editAccountPurchasing-account');
		Route::post('updateAccountPurchasingDetail', 'Backend\AccountController@updateAccountPurchasingDetail')->name('backend.account.updateAccountPurchasingDetail')->middleware('can:editAccountPurchasing-account');
		Route::post('deleteAccountPurchasingDetail', 'Backend\AccountController@deleteAccountPurchasingDetail')->name('backend.account.deleteAccountPurchasingDetail')->middleware('can:editAccountPurchasing-account');

	});


	// Admin Activity
	Route::group(['prefix' => 'activity'], function()
	{
		Route::get('/', 'Backend\ActivityController@index')->name('backend.activity')->middleware('can:list-activity');
		Route::get('index', 'Backend\ActivityController@index')->name('backend.activity.index')->middleware('can:list-activity');
		Route::post('datatables', 'Backend\ActivityController@datatables')->name('backend.activity.datatables')->middleware('can:list-activity');

		Route::get('create', 'Backend\ActivityController@create')->name('backend.activity.create')->middleware('can:create-activity');
		Route::post('store', 'Backend\ActivityController@store')->name('backend.activity.store')->middleware('can:create-activity');
		Route::get('{id}/edit', 'Backend\ActivityController@edit')->name('backend.activity.edit')->middleware('can:edit-activity');
		Route::post('{id}/update', 'Backend\ActivityController@update')->name('backend.activity.update')->middleware('can:edit-activity');
		Route::post('delete', 'Backend\ActivityController@delete')->name('backend.activity.delete')->middleware('can:delete-activity');
		Route::post('action', 'Backend\ActivityController@action')->name('backend.activity.action');
		Route::post('confirm', 'Backend\ActivityController@confirm')->name('backend.activity.confirm')->middleware('can:confirm-activity');

		Route::post('checkHRD', 'Backend\ActivityController@checkHRD')->name('backend.activity.checkHRD')->middleware('can:checkHRD-activity');

	});

	// Admin Dayoff
	Route::group(['prefix' => 'dayoff'], function()
	{
		Route::get('/', 'Backend\DayoffController@index')->name('backend.dayoff')->middleware('can:list-dayoff');
		Route::get('index', 'Backend\DayoffController@index')->name('backend.dayoff.index')->middleware('can:list-dayoff');
		Route::post('datatables', 'Backend\DayoffController@datatables')->name('backend.dayoff.datatables')->middleware('can:list-dayoff');

		Route::post('store', 'Backend\DayoffController@store')->name('backend.dayoff.store')->middleware('can:create-dayoff');
		Route::post('update', 'Backend\DayoffController@update')->name('backend.dayoff.update')->middleware('can:edit-dayoff');
		Route::post('delete', 'Backend\DayoffController@delete')->name('backend.dayoff.delete')->middleware('can:delete-dayoff');
		Route::post('action', 'Backend\DayoffController@action')->name('backend.dayoff.action');
		Route::post('confirm', 'Backend\DayoffController@confirm')->name('backend.dayoff.confirm')->middleware('can:confirm-dayoff');

		Route::post('checkHRD', 'Backend\DayoffController@checkHRD')->name('backend.dayoff.checkHRD')->middleware('can:checkHRD-dayoff');


		Route::get('setting', 'Backend\DayoffController@setting')->name('backend.dayoff.setting')->middleware('can:setting-dayoff');
		Route::post('datatablesSetting', 'Backend\DayoffController@datatablesSetting')->name('backend.dayoff.datatablesSetting')->middleware('can:setting-dayoff');
		Route::post('updateSetting', 'Backend\DayoffController@updateSetting')->name('backend.dayoff.updateSetting')->middleware('can:setting-dayoff');

	});

	// Admin Absence
	Route::group(['prefix' => 'absence'], function()
	{
		Route::get('/', 'Backend\AbsenceController@index')->name('backend.absence')->middleware('can:list-absence');
		Route::get('index', 'Backend\AbsenceController@index')->name('backend.absence.index')->middleware('can:list-absence');
		Route::post('datatables', 'Backend\AbsenceController@datatables')->name('backend.absence.datatables')->middleware('can:list-absence');

		Route::post('store', 'Backend\AbsenceController@store')->name('backend.absence.store')->middleware('can:create-absence');
		Route::post('update', 'Backend\AbsenceController@update')->name('backend.absence.update')->middleware('can:edit-absence');
		Route::post('delete', 'Backend\AbsenceController@delete')->name('backend.absence.delete')->middleware('can:delete-absence');
		Route::post('action', 'Backend\AbsenceController@action')->name('backend.absence.action');
		Route::post('confirm', 'Backend\AbsenceController@confirm')->name('backend.absence.confirm')->middleware('can:confirm-absence');

		Route::post('checkHRD', 'Backend\AbsenceController@checkHRD')->name('backend.absence.checkHRD')->middleware('can:checkHRD-absence');

	});

	Route::get('/attandance', 'Backend\DummyController@view')->name('backend.dummy.attandance');

	// Admin Ar Model
	Route::group(['prefix' => 'arModel'], function()
	{
		Route::get('/', 'Backend\ArModelController@index')->name('backend.arModel')->middleware('can:list-arModel');
		Route::get('index', 'Backend\ArModelController@index')->name('backend.arModel.index')->middleware('can:list-arModel');
		Route::post('datatables', 'Backend\ArModelController@datatables')->name('backend.arModel.datatables')->middleware('can:list-arModel');

		Route::get('create', 'Backend\ArModelController@create')->name('backend.arModel.create')->middleware('can:create-arModel');
		Route::post('store', 'Backend\ArModelController@store')->name('backend.arModel.store')->middleware('can:create-arModel');
		Route::get('{id}/edit', 'Backend\ArModelController@edit')->name('backend.arModel.edit')->middleware('can:edit-arModel');
		Route::post('{id}/update', 'Backend\ArModelController@update')->name('backend.arModel.update')->middleware('can:edit-arModel');
		Route::post('delete', 'Backend\ArModelController@delete')->name('backend.arModel.delete')->middleware('can:delete-arModel');
		Route::post('action', 'Backend\ArModelController@action')->name('backend.arModel.action');
		Route::post('active', 'Backend\ArModelController@active')->name('backend.arModel.active')->middleware('can:active-arModel');

		Route::post('pdf', 'Backend\ArModelController@pdf')->name('backend.arModel.pdf');

		Route::get('qrCode/{id}', 'Backend\ArModelController@qrCode')->name('backend.arModel.qrCode');

	});

	// Admin Stock
	Route::group(['prefix' => 'stock'], function()
	{
		Route::get('/', 'Backend\StockController@index')->name('backend.stock')->middleware('can:list-stock');
		Route::get('index', 'Backend\StockController@index')->name('backend.stock.index')->middleware('can:list-stock');
		Route::post('datatables', 'Backend\StockController@datatables')->name('backend.stock.datatables')->middleware('can:list-stock');

		Route::get('create', 'Backend\StockController@create')->name('backend.stock.create')->middleware('can:create-stock');
		Route::post('store', 'Backend\StockController@store')->name('backend.stock.store')->middleware('can:create-stock');
		Route::get('{id}/edit', 'Backend\StockController@edit')->name('backend.stock.edit')->middleware('can:edit-stock');
		Route::post('{id}/update', 'Backend\StockController@update')->name('backend.stock.update')->middleware('can:edit-stock');
		Route::post('delete', 'Backend\StockController@delete')->name('backend.stock.delete')->middleware('can:delete-stock');
		Route::post('action', 'Backend\StockController@action')->name('backend.stock.action');
		Route::post('active', 'Backend\StockController@active')->name('backend.stock.active')->middleware('can:active-stock');

		Route::get('stockBook', 'Backend\StockController@stockBook')->name('backend.stock.stockBook')->middleware('can:list-stock');
		Route::post('datatablesStockBook', 'Backend\StockController@datatablesStockBook')->name('backend.stock.datatablesStockBook')->middleware('can:list-stock');

		Route::get('createStockBook', 'Backend\StockController@createStockBook')->name('backend.stock.createStockBook')->middleware('can:createStockBook-stock');
		Route::post('storeStockBook', 'Backend\StockController@storeStockBook')->name('backend.stock.storeStockBook')->middleware('can:createStockBook-stock');
		Route::get('{id}/editStockBook', 'Backend\StockController@editStockBook')->name('backend.stock.editStockBook')->middleware('can:editStockBook-stock');
		Route::post('{id}/updateStockBook', 'Backend\StockController@updateStockBook')->name('backend.stock.updateStockBook')->middleware('can:editStockBook-stock');
		Route::post('deleteStockBook', 'Backend\StockController@deleteStockBook')->name('backend.stock.deleteStockBook')->middleware('can:deleteStockBook-stock');
		Route::post('actionStockBook', 'Backend\StockController@actionStockBook')->name('backend.stock.actionStockBook');
		Route::post('statusStockBook', 'Backend\StockController@statusStockBook')->name('backend.stock.statusStockBook')->middleware('can:statusStockBook-stock');


		Route::get('stockPlace', 'Backend\StockController@stockPlace')->name('backend.stock.stockPlace')->middleware('can:listStockPlace-stock');
		Route::post('datatablesStockPlace', 'Backend\StockController@datatablesStockPlace')->name('backend.stock.datatablesStockPlace')->middleware('can:listStockPlace-stock');

		Route::post('storeStockPlace', 'Backend\StockController@storeStockPlace')->name('backend.stock.storeStockPlace')->middleware('can:createStockPlace-stock');
		Route::post('updateStockPlace', 'Backend\StockController@updateStockPlace')->name('backend.stock.updateStockPlace')->middleware('can:editStockPlace-stock');
		Route::post('deleteStockPlace', 'Backend\StockController@deleteStockPlace')->name('backend.stock.deleteStockPlace')->middleware('can:deleteStockPlace-stock');
	});

	// Route::get('/dummy/users', 'Backend\DummyController@users')->name('backend.dummy.users');
	// Route::post('/dummy/createDummyUsers', 'Backend\DummyController@createDummyUsers')->name('backend.dummy.createDummyUsers');
	// Route::get('/dummy/company', 'Backend\DummyController@company')->name('backend.dummy.company');
	// Route::post('/dummy/createDummyCompany', 'Backend\DummyController@createDummyCompany')->name('backend.dummy.createDummyCompany');
	// Route::get('/dummy/spk', 'Backend\DummyController@spk')->name('backend.dummy.spk');
	// Route::post('/dummy/createDummySpk', 'Backend\DummyController@createDummySpk')->name('backend.dummy.createDummySpk');
	// Route::get('/dummy/offer', 'Backend\DummyController@offer')->name('backend.dummy.offer');
	// Route::post('/dummy/createDummyOffer', 'Backend\DummyController@createDummyOffer')->name('backend.dummy.createDummyOffer');
	// Route::get('/dummy/todo', 'Backend\DummyController@todo')->name('backend.dummy.todo');
	// Route::post('/dummy/createDummyTodo', 'Backend\DummyController@createDummyTodo')->name('backend.dummy.createDummyTodo');
	// Route::get('/dummy/supplier', 'Backend\DummyController@supplier')->name('backend.dummy.supplier');
	// Route::post('/dummy/createDummySupplier', 'Backend\DummyController@createDummySupplier')->name('backend.dummy.createDummySupplier');
});