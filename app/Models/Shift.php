<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Shift
 *
 * @property int $id
 * @property string $code
 * @property string $start_time
 * @property string $end_time
 */
class Shift extends Model
{
    use HasFactory;

    protected $table = 'shift_codes';

    protected $fillable = ['code', 'start_time', 'end_time'];
}