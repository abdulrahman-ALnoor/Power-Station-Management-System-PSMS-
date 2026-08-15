<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * ملاحظة: admin يتجاوز كل شي تلقائياً عبر Gate::before بـ AppServiceProvider.
     * حسب المستند: المحاسب له صلاحية كاملة على كل الفواتير بدون قيود
     * (بعكس service-requests اللي فيها قيد "بس اللي أنشأها هو").
     */

    public function viewAny(User $user): bool
    {
        return $user->hasRole('accountant');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('accountant');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('accountant');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('accountant');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('accountant');
    }
}
