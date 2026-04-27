<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOvertimeRequestStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $approver;
    private OrganizationUnit $orgUnit;
    private Shift $shift;
    private Schedule $schedule;
    private OvertimeRequest $overtimeRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->employee = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->approver = User::factory()->create([
            'role' => 'approver',
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $this->shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->schedule = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);

        $this->overtimeRequest = OvertimeRequest::create([
            'employee_schedule_id' => $this->schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '2.00',
            'reason' => 'Original reason',
            'status' => 'APPROVED',
        ]);
    }

    // -- APPROVED path (via approver route) --

    public function test_approve_pending_request()
    {
        $pending = OvertimeRequest::create([
            'employee_schedule_id' => $this->schedule->id,
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'hours' => '2.00',
            'reason' => 'Approve me',
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $pending->id,
            'update_status' => 'APPROVED',
            'current_status' => 'PENDING',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('overtime_requests', ['id' => $pending->id, 'status' => 'APPROVED']);
    }

    public function test_approve_requires_current_status()
    {
        $pending = OvertimeRequest::create([
            'employee_schedule_id' => $this->schedule->id,
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'hours' => '2.00',
            'reason' => 'Approve me',
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $pending->id,
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHasErrors(['current_status']);
    }

    public function test_approve_current_status_must_be_valid()
    {
        $pending = OvertimeRequest::create([
            'employee_schedule_id' => $this->schedule->id,
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'hours' => '2.00',
            'reason' => 'Approve me',
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $pending->id,
            'update_status' => 'APPROVED',
            'current_status' => 'DECLINED',
        ]);

        $response->assertSessionHasErrors(['current_status']);
    }

    // -- DISAPPROVED path (via approver route) --

    public function test_disapprove_with_remarks()
    {
        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DISAPPROVED',
            'remarks' => 'Not justified, exceeds the allowed overtime limit for this period.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $this->overtimeRequest->id,
            'status' => 'DISAPPROVED',
            'remarks' => 'Not justified, exceeds the allowed overtime limit for this period.',
        ]);
    }

    public function test_disapprove_requires_remarks()
    {
        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DISAPPROVED',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }

    public function test_disapprove_remarks_min_length()
    {
        $response = $this->actingAs($this->approver)->post('/overtime/update/approver', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DISAPPROVED',
            'remarks' => 'Too short',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }

    // -- DECLINED path (via employee route) --

    public function test_decline_with_remarks()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DECLINED',
            'remarks' => 'I am declining this overtime request because my schedule has changed.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $this->overtimeRequest->id,
            'status' => 'DECLINED',
        ]);
    }

    public function test_decline_requires_remarks()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DECLINED',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }

    public function test_decline_remarks_min_length()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'DECLINED',
            'remarks' => 'Short',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }

    // -- PENDING path (re-file via employee route) --

    public function test_pending_updates_reason_and_times()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'PENDING',
            'current_status' => 'APPROVED',
            'employee_schedule_id' => $this->schedule->id,
            'date' => '2026-01-05',
            'reason' => 'Updated reason',
            'start_time' => '06:00',
            'end_time' => '08:00',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $this->overtimeRequest->id,
            'status' => 'PENDING',
            'reason' => 'Updated reason',
        ]);
    }

    public function test_pending_requires_current_status()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'PENDING',
            'employee_schedule_id' => $this->schedule->id,
            'date' => '2026-01-05',
            'reason' => 'Updated reason',
            'start_time' => '06:00',
            'end_time' => '08:00',
        ]);

        $response->assertSessionHasErrors(['current_status']);
    }

    public function test_pending_requires_reason()
    {
        $response = $this->actingAs($this->employee)->post('/overtime/update/employee', [
            'id' => $this->overtimeRequest->id,
            'update_status' => 'PENDING',
            'current_status' => 'APPROVED',
            'employee_schedule_id' => $this->schedule->id,
            'date' => '2026-01-05',
            'start_time' => '06:00',
            'end_time' => '08:00',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }
}
