<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Schedule extends Model
{
    use HasFactory;
    protected $table = 'schedules';

    protected $fillable = ['user_id', 'shift_id', 'week', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class, 'employee_schedule_id');
    }
}
