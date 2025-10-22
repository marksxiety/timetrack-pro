<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationUnit extends Model
{
   protected $table = 'organization_units';

   protected $fillable = ['unit_path'];
}
