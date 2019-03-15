<?php

namespace App\Policies;

use App\User;
use App\Models\Division;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisionPolicy
{
    use HandlesAuthorization;

    public function delete(User $auth, Division $division)
    {
        return $auth->hasAccess('delete-division') && ($auth->divisions->id ?? 0) != $division->id;
    }

    public function check(User $auth, Division $division)
    {
        return ($auth->divisions->id ?? 0) != $division->id;
    }
}
