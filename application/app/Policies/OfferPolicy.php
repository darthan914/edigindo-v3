<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\Offer;
use App\Models\OfferDetail;

class OfferPolicy
{
    use HandlesAuthorization;

    public function update(User $auth, Offer $offer)
    {
        return (($auth->_lft <= $offer->sales->_lft && $offer->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('update-offer');
    }

    public function delete(User $auth, Offer $offer)
    {
        return (($auth->_lft <= $offer->sales->_lft && $offer->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('delete-offer');
    }

    public function check(User $auth, Offer $offer)
    {
        return (($auth->_lft <= $offer->sales->_lft && $offer->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user'));
    }

    public function status(User $auth, OfferDetail $offer_detail)
    {
        return (($auth->_lft <= $offer_detail->offers->sales->_lft && $offer_detail->offers->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $offer_detail->status == 'WAITING';
    }

    public function undo(User $auth, Offer $offer)
    {
        return (($auth->_lft <= $offer->sales->_lft && $offer->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('undo-offer');
    }

    public function pdf(User $auth, Offer $offer)
    {
        return (($auth->_lft <= $offer->sales->_lft && $offer->sales->_lft <= $auth->_rgt) || $auth->hasAccess('full-user')) && $auth->hasAccess('pdf-offer');
    }
}
