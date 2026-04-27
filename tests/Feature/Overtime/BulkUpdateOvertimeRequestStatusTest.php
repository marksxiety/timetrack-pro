<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class BulkUpdateOvertimeRequestStatusTest extends TestCase
{
    private User $approver;
    private User $employee;
    private OrganizationUnit $orgUnit;
    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->approver = User::factory()->create([
            'role' => 'approver',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->employee = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->actingAs($this->approver);

        $this->shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    private function createPendingOvertime(): OvertimeRequest
    {
        $schedule = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);

        return OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '2.00',
            'reason' => 'Test reason',
            'status' => 'PENDING',
        ]);
    }

    private function createApprovedOvertime(): OvertimeRequest
    {
        $schedule = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);

        return OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'hours' => '2.00',
            'reason' => 'Test reason',
            'status' => 'APPROVED',
        ]);
    }

    public function test_bulk_approve_pending_requests()
    {
        $ot1 = $this->createPendingOvertime();
        $ot2 = $this->createPendingOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$ot1->id, $ot2->id],
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHas('message');
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot1->id, 'status' => 'APPROVED']);
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot2->id, 'status' => 'APPROVED']);
    }

    public function test_bulk_file_approved_requests()
    {
        $ot1 = $this->createApprovedOvertime();
        $ot2 = $this->createApprovedOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$ot1->id, $ot2->id],
            'update_status' => 'FILED',
        ]);

        $response->assertSessionHas('message');
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot1->id, 'status' => 'FILED']);
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot2->id, 'status' => 'FILED']);
    }

    public function test_validation_requires_ids()
    {
        $response = $this->post('/overtime/update/bulk', [
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_validation_requires_valid_status()
    {
        $response = $this->post('/overtime/update/bulk', [
            'ids' => [1],
            'update_status' => 'INVALID',
        ]);

        $response->assertSessionHasErrors(['update_status']);
    }

    public function test_status_transition_enforced_for_approve()
    {
        $ot = $this->createApprovedOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$ot->id],
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot->id, 'status' => 'APPROVED']);
    }

    public function test_status_transition_enforced_for_file()
    {
        $ot = $this->createPendingOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$ot->id],
            'update_status' => 'FILED',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('overtime_requests', ['id' => $ot->id, 'status' => 'PENDING']);
    }

    public function test_mixed_status_transition_fails()
    {
        $pending = $this->createPendingOvertime();
        $approved = $this->createApprovedOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$pending->id, $approved->id],
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('overtime_requests', ['id' => $pending->id, 'status' => 'PENDING']);
        $this->assertDatabaseHas('overtime_requests', ['id' => $approved->id, 'status' => 'APPROVED']);
    }

    public function test_success_message_includes_count()
    {
        $ot1 = $this->createPendingOvertime();
        $ot2 = $this->createPendingOvertime();
        $ot3 = $this->createPendingOvertime();

        $response = $this->post('/overtime/update/bulk', [
            'ids' => [$ot1->id, $ot2->id, $ot3->id],
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHas('message', '3 request(s) have been approved.');
    }

    public function test_empty_ids_array_fails()
    {
        $response = $this->post('/overtime/update/bulk', [
            'ids' => [],
            'update_status' => 'APPROVED',
        ]);

        $response->assertSessionHasErrors(['ids']);
    }
}
