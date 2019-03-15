<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\Estimator;

class EstimatorPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, Estimator $estimator)
    {
        return (($auth->_lft <= $estimator->sales->_lft && $estimator->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-estimator');
    }

    public function delete(User $auth, Estimator $estimator)
    {
        return (($auth->_lft <= $estimator->sales->_lft && $estimator->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('delete-estimator');
    }

    public function check(User $auth, Estimator $estimator)
    {
        return (($auth->_lft <= $estimator->sales->_lft && $estimator->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user'));
    }

    public function createPrice(User $auth, Estimator $estimator)
    {
        return ($estimator->user_estimator_id == null || ($auth->_lft <= ($estimator->user_estimator->_lft ?? 0) && ($estimator->user_estimator->_lft ?? 0) <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('createPrice-estimator');
    }

    public function updatePrice(User $auth, Estimator $estimator)
    {
        return (($auth->_lft <= $estimator->user_estimator->_lft && $estimator->user_estimator->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('updatePrice-estimator');
    }

    public function deletePrice(User $auth, Estimator $estimator)
    {
        return (($auth->_lft <= $estimator->user_estimator->_lft && $estimator->user_estimator->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('deletePrice-estimator');
    }
}
