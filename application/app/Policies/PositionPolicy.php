<?php

namespace App\Policies;

use App\User;
use App\Models\Position;
use Illuminate\Auth\Access\HandlesAuthorization;

class PositionPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, Position $position)
    {
        return (($auth->positions->_lft <= $position->_lft && $position->_lft <= $auth->positions->_rgt) || $auth->hasAccess('full-position')) && $auth->hasAccess('update-position');
    }

    public function delete(User $auth, Position $position)
    {
        return (($auth->positions->_lft <= $position->_lft && $position->_lft <= $auth->positions->_rgt) || $auth->hasAccess('full-position')) && $auth->hasAccess('delete-position') && $auth->positions->id != $position->id;
    }

    public function check(User $auth, Position $position)
    {
        return (($auth->positions->_lft <= $position->_lft && $position->_lft <= $auth->positions->_rgt) || $auth->hasAccess('full-position')) && $auth->positions->id != $position->id;
    }
}
