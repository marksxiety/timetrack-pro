<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasScopedQueries
{
    protected function getOrgUnitId(): ?int
    {
        $user = Auth::user();
        return $user->role === 'admin' ? null : $user->organization_unit_id;
    }
}
