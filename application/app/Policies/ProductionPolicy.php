<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\Production;


class ProductionPolicy
{
    use HandlesAuthorization;

    public function complete(User $auth, Production $production)
    {
        return ($auth->division_id == $production->division_id || $auth->division_id == $production->spk->main_division_id || $auth->hasAccess('full-user')) && $auth->hasAccess('complete-production') && $production->quantity > $production->count_finish;
    }

    public function pdf(User $auth, Production $production)
    {
        return ($auth->division_id == $production->division_id || $auth->division_id == $production->spk->main_division_id || $auth->hasAccess('full-user')) && $auth->hasAccess('pdf-production');
    }

    public function check(User $auth, Production $production)
    {
        return ($auth->division_id == $production->division_id || $auth->division_id == $production->spk->main_division_id || $auth->hasAccess('full-user')) && $auth->hasAccess('complete-production');
    }
}
