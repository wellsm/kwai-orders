<?php

namespace App\Policies;

use App\Enums\Plan;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return Filament::getTenant()->isPlan(Plan::Basic);
    }
}
