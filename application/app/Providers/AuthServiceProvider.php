<?php

namespace App\Providers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Laravel\Passport\Passport;
use Carbon\Carbon;

use App\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addDays(15));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

        Gate::define('migration', function($user) {
            return $user->hasAccess('migration');
        });

        Gate::define('configuration', function($user) {
            return $user->hasAccess('configuration');
        });

        Gate::define('sql', function($user) {
            return $user->hasAccess('sql');
        });

        Gate::define('beta', function($user) {
            return $user->hasAccess('beta');
        });

        $data = User::keypermission();

        foreach ($data as $list) {
            foreach ($list['data'] as $list2) {
                Gate::define( $list2['value'] , function($user) use ($list2) {
                    return $user->hasAccess( $list2['value'] );
                });
            }
        }

        // policy user
        Gate::define('update-user', 'App\Policies\UserPolicy@update');
        Gate::define('delete-user', 'App\Policies\UserPolicy@delete');
        Gate::define('access-user', 'App\Policies\UserPolicy@access');
        Gate::define('impersonate-user', 'App\Policies\UserPolicy@impersonate');
        Gate::define('check-user', 'App\Policies\UserPolicy@check');

        // policy position
        Gate::define('update-position', 'App\Policies\PositionPolicy@update');
        Gate::define('delete-position', 'App\Policies\PositionPolicy@delete');
        Gate::define('check-position', 'App\Policies\PositionPolicy@check');

        // policy division
        Gate::define('delete-division', 'App\Policies\DivisionPolicy@delete');
        Gate::define('check-division', 'App\Policies\DivisionPolicy@check');

        // policy spk
        Gate::define('update-spk', 'App\Policies\SpkPolicy@update');
        Gate::define('delete-spk', 'App\Policies\SpkPolicy@delete');
        Gate::define('undo-spk', 'App\Policies\SpkPolicy@undo');
        Gate::define('confirm-spk', 'App\Policies\SpkPolicy@confirm');
        Gate::define('finish-spk', 'App\Policies\SpkPolicy@finish');
        Gate::define('pdf-spk', 'App\Policies\SpkPolicy@pdf');
        Gate::define('check-spk', 'App\Policies\SpkPolicy@check');

        // policy estimator
        Gate::define('update-estimator', 'App\Policies\EstimatorPolicy@update');
        Gate::define('delete-estimator', 'App\Policies\EstimatorPolicy@delete');
        Gate::define('check-estimator', 'App\Policies\EstimatorPolicy@check');
        Gate::define('createPrice-estimator', 'App\Policies\EstimatorPolicy@createPrice');
        Gate::define('updatePrice-estimator', 'App\Policies\EstimatorPolicy@updatePrice');
        Gate::define('deletePrice-estimator', 'App\Policies\EstimatorPolicy@deletePrice');

        // policy production
        Gate::define('complete-production', 'App\Policies\ProductionPolicy@complete');
        Gate::define('pdf-production', 'App\Policies\ProductionPolicy@pdf');
        Gate::define('check-production', 'App\Policies\ProductionPolicy@check');

        // policy offer
        Gate::define('update-offer', 'App\Policies\OfferPolicy@update');
        Gate::define('delete-offer', 'App\Policies\OfferPolicy@delete');
        Gate::define('status-offer', 'App\Policies\OfferPolicy@status');
        Gate::define('check-offer', 'App\Policies\OfferPolicy@check');
        Gate::define('undo-offer', 'App\Policies\OfferPolicy@undo');
        Gate::define('pdf-offer', 'App\Policies\OfferPolicy@pdf');

        // policy invoice
        Gate::define('create-invoice', 'App\Policies\InvoicePolicy@create');
        Gate::define('update-invoice', 'App\Policies\InvoicePolicy@update');
        Gate::define('delete-invoice', 'App\Policies\InvoicePolicy@delete');
        Gate::define('undo-invoice', 'App\Policies\InvoicePolicy@undo');
        Gate::define('admin-invoice', 'App\Policies\InvoicePolicy@admin');
        Gate::define('checkFinance-invoice', 'App\Policies\InvoicePolicy@checkFinance');
    }
}
