<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkflowTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkflowTemplate::query()->delete();
        WorkflowStep::query()->delete();

        $plants = Plant::all();
        $batPosition = Position::where('code', 'BAT')->first();

        if (! $batPosition) {
            return;
        }

        foreach ($plants as $plant) {
            $departments = Department::where('plant_id', $plant->id)->get();

            foreach ($departments as $dept) {
                // Buat workflow template untuk dokumen SPPB di plant & department ini
                $template = WorkflowTemplate::create([
                    'uuid' => (string) Str::uuid(),
                    'code' => 'WF-SPPB-'.str_replace(' ', '', $plant->code).'-'.$dept->code,
                    'name' => 'Workflow SPPB '.$plant->name.' - '.$dept->name,
                    'version' => 1,
                    'plant_id' => $plant->id,
                    'department_id' => $dept->id,
                    'document_type' => 'SPPB',
                    'description' => 'Alur persetujuan dokumen SPPB departemen '.$dept->name.' di '.$plant->name,
                    'is_active' => true,
                ]);

                // Step 1: Persetujuan Manager (REQUESTER_MANAGER)
                WorkflowStep::create([
                    'workflow_template_id' => $template->id,
                    'sequence' => 1,
                    'code' => 'MGR_APPROVE',
                    'name' => 'Persetujuan Manager',
                    'approver_type' => 'REQUESTER_MANAGER',
                    'approver_user_ids' => [],
                    'approver_position_ids' => [],
                    'approval_mode' => 'ANY',
                    'minimum_approvals' => 1,
                    'sla_hours' => 24,
                    'allow_self_approval' => false,
                    'is_final' => false,
                    'configuration' => [],
                ]);

                // Step 2: Verifikasi BAT (POSITION: BAT)
                WorkflowStep::create([
                    'workflow_template_id' => $template->id,
                    'sequence' => 2,
                    'code' => 'BAT_VERIFY',
                    'name' => 'Verifikasi BAT',
                    'approver_type' => 'POSITION',
                    'approver_user_ids' => [],
                    'approver_position_ids' => [$batPosition->id],
                    'approval_mode' => 'ANY',
                    'minimum_approvals' => 1,
                    'sla_hours' => 24,
                    'allow_self_approval' => true,
                    'is_final' => true,
                    'configuration' => [],
                ]);
            }
        }
    }
}
