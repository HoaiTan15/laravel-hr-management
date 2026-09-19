<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Request as RequestModel;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    public function test_employee_without_today_check_in_sees_a_mandatory_check_in_modal(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)
            ->get(route('employee.home'))
            ->assertOk()
            ->assertSee('Chấm công đầu ngày')
            ->assertSee('Vui lòng Check-in trước khi bắt đầu làm việc.')
            ->assertSee('Check-in ngay');
    }

    public function test_checked_in_dashboard_renders_real_stitch_summary_data_and_laravel_links(): void
    {
        $employee = $this->createEmployee([], [
            'phone' => '0901234567',
            'address' => 'HUIT campus',
            'avatar' => 'avatar.png',
        ]);
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);
        Task::create(['assigned_to' => $employee->id, 'created_by' => $employee->user_id, 'title' => 'Own task', 'status' => 'in_progress', 'due_at' => today()->addDay()]);
        RequestModel::create(['created_by' => $employee->user_id, 'type' => 'hardware', 'payload' => ['title' => 'Own request', 'content' => 'Need equipment'], 'status' => 'pending']);

        $response = $this->actingAs($employee->user)->get(route('employee.home'))
            ->assertOk()
            ->assertSee('Đang làm việc')
            ->assertSee('>Check-out</button>', false)
            ->assertSee('Own task')
            ->assertSee('Own request')
            ->assertSee(route('employee.tasks.index'))
            ->assertSee(route('employee.requests.index'))
            ->assertSee(route('employee.profile'))
            ->assertSee(route('logout'))
            ->assertDontSee('Xin chào,')
            ->assertDontSee('Chúc bạn một ngày làm việc hiệu quả.');

        $this->assertStringNotContainsString('href="#"', $response->getContent());
    }

    public function test_mobile_employee_layout_keeps_logout_control_visible(): void
    {
        $css = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('@media(max-width:700px){.sidebar-bottom{display:block;margin-top:0;padding-top:8px}}', $css);
        $this->assertStringContainsString('Đăng xuất', view('hrms-layout', [
            'role' => 'employee',
            'active' => 'dashboard',
        ])->render());
    }

    public function test_checked_out_dashboard_disables_second_checkout(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now()->subHours(2),
            'check_out_at' => now()->subHour(),
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.home'))
            ->assertOk()
            ->assertSee('Đã Check-out')
            ->assertSee('disabled', false)
            ->assertSee('is-complete', false)
            ->assertDontSee('action="'.route('attendance.check-out').'"', false);
    }
}
