<?php

namespace App\Policies;

use App\User;
use App\Models\Spk;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpkPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-spk') && !isset($spk->finish_spk_at);
    }

    public function delete(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('delete-spk') && !isset($spk->finish_spk_at);
    }

    public function undo(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('undo-spk');
    }

    public function confirm(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('confirm-spk');
    }

    public function finish(User $auth, Spk $spk)
    {
        $index = Spk::withStatisticProduction()->where('id', $spk->id)->first();

        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && ($index->sum_quantity_production <= $index->sum_quantity_production_finish || $index->count_production == 0) && $auth->hasAccess('update-spk') && !isset($spk->finish_spk_at);
    }

    public function pdf(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('pdf-spk');
    }

    public function check(User $auth, Spk $spk)
    {
        return (($auth->_lft <= $spk->sales->_lft && $spk->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user'));
    }
}
