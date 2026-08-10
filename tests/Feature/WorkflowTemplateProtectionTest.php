<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTemplateProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_workflow_template_with_associated_instances(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $department->id]);

        $template = WorkflowTemplate::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'requester_id' => $user->id,
        ]);

        WorkflowInstance::factory()->create([
            'workflow_template_id' => $template->id,
            'sppb_header_id' => $sppb->id,
        ]);

        $this->assertTrue($template->hasDependentRecords());

        $this->expectException(\DomainException::class);
        $template->delete();
    }

    public function test_can_delete_workflow_template_with_steps_when_no_instances_exist(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $template = WorkflowTemplate::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $step = WorkflowStep::factory()->create([
            'workflow_template_id' => $template->id,
            'sequence' => 1,
            'code' => 'STEP1',
            'name' => 'Approval Step 1',
        ]);

        $this->assertFalse($template->hasDependentRecords());

        $template->delete();

        $this->assertDatabaseMissing('workflow_templates', ['id' => $template->id]);
        $this->assertDatabaseMissing('workflow_steps', ['id' => $step->id]);
    }
}
