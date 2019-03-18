<?php

namespace App\Policies;

use App\User;
use App\Models\Pr;
use App\Models\PrDetail;
use App\Models\Po;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, Pr $pr)
    {
        return (($auth->_lft <= $pr->users->_lft && $pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-pr');
    }

    public function updateDetail(User $auth, PrDetail $pr_detail)
    {
        return (($auth->_lft <= $pr_detail->pr->users->_lft && $pr_detail->pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-pr') && ($pr_detail->status != 'CONFIRMED' || $pr_detail->status == 'REVISION');
    }

    public function delete(User $auth, Pr $pr)
    {
        $check_confirm = $pr->pr_details()->where('status', 'CONFIRMED')->count();

        return (($auth->_lft <= $pr->users->_lft && $pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('delete-pr') && !isset($pr->finish_pr_at) && $check_confirm == 0;
    }

    public function deleteDetail(User $auth, PrDetail $pr_detail)
    {
        $check_order = $pr_detail->po()->count();

        return (($auth->_lft <= $pr_detail->pr->users->_lft && $pr_detail->pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-pr') && $pr_detail->status != 'CONFIRMED' && $check_order == 0;
    }

    public function check(User $auth, Pr $pr)
    {
        return (($auth->_lft <= $pr->users->_lft && $pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user'));
    }

    public function checkDetail(User $auth, PrDetail $pr_detail)
    {
        return (($auth->_lft <= $pr_detail->pr->users->_lft && $pr_detail->pr->users->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $pr_detail->status != 'CONFIRMED';
    }

}
