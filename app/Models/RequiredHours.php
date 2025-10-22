<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequiredHours extends Model
{
    use HasFactory;

    protected $table = 'required_hours';

    protected $fillable = ['year', 'week', 'required_hours', 'organization_unit_id'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
}
