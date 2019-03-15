<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, User $user)
    {
        return (($auth->_lft <= $user->_lft && $user->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-user');
    }

    public function delete(User $auth, User $user)
    {
        return (($auth->_lft <= $user->_lft && $user->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('delete-user') && $auth->id != $user->id;
    }

    public function access(User $auth, User $user)
    {
        return (($auth->_lft <= $user->_lft && $user->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('access-user');
    }

    public function impersonate(User $auth, User $user)
    {
        return (($auth->_lft <= $user->_lft && $user->_lft <= $auth->_rgt)  || $auth->hasAccess('full-user') || $user->active == -1) && $auth->hasAccess('impersonate-user') && $auth->id != $user->id;
    }

    public function check(User $auth, User $user)
    {
        return (($auth->_lft <= $user->_lft && $auth->_rgt >= $user->_lft) || $auth->hasAccess('full-user')) && $auth->id != $user->id;
    }
}
