<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $table = 'overtime_requests';

    protected $fillable = ['employee_schedule_id', 'start_time', 'end_time', 'hours', 'reason', 'status', 'remarks'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'employee_schedule_id');
    }
}
