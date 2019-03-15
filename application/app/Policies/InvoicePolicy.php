<?php

namespace App\Policies;

use App\User;
use App\Models\Spk;
use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(User $auth, Spk $spk)
    {
        return $auth->hasAccess('create-invoice') && $spk->check_master == 0;
    }

    public function update(User $auth, Invoice $invoice)
    {
        return $auth->hasAccess('update-invoice') && $invoice->spk->check_master == 0 && $invoice->check_finance == 0;
    }

    public function delete(User $auth, Invoice $invoice)
    {
        return $auth->hasAccess('delete-invoice') && $invoice->spk->check_master == 0 && $invoice->check_finance == 0;
    }

    public function undo(User $auth, Invoice $invoice)
    {
        return $auth->hasAccess('undo-invoice') && $invoice->spk->check_master == 0 && $invoice->check_finance == 0;
    }

    public function checkFinance(User $auth, Invoice $invoice)
    {
        return $auth->hasAccess('checkFinance-invoice') && $invoice->spk->check_master == 0;
    }

    public function admin(User $auth, Spk $spk)
    {
        return $auth->hasAccess('admin-invoice') && $spk->check_master == 0;
    }
}
