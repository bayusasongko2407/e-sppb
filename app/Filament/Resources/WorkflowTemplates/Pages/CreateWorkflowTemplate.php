<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplates\Pages;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use App\Models\WorkflowTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateWorkflowTemplate extends CreateRecord
{
    protected static string $resource = WorkflowTemplateResource::class;

    public function mount(): void
    {
        parent::mount();

        $sourceId = request()->query('source');

        if ($sourceId) {
            $source = WorkflowTemplate::with('workflowSteps')->find($sourceId);

            if ($source) {
                $steps = $source->workflowSteps
                    ->sortBy('sequence')
                    ->map(fn ($step) => [
                        'sequence' => $step->sequence,
                        'code' => $step->code,
                        'name' => $step->name,
                        'approver_type' => $step->approver_type,
                        'approver_user_ids' => $step->approver_user_ids ?? [],
                        'approver_position_ids' => $step->approver_position_ids ?? [],
                        'approval_mode' => $step->approval_mode,
                        'minimum_approvals' => $step->minimum_approvals,
                        'sla_hours' => $step->sla_hours,
                        'allow_self_approval' => $step->allow_self_approval,
                    ])
                    ->values()
                    ->toArray();

                $this->form->fill([
                    'uuid' => Str::uuid()->toString(),
                    'code' => $source->code.'-COPY',
                    'name' => $source->name.' (Salinan)',
                    'version' => 1,
                    'plant_id' => $source->plant_id,
                    'department_id' => $source->department_id,
                    'document_type' => $source->document_type,
                    'description' => $source->description,
                    'is_active' => $source->is_active,
                    'effective_from' => $source->effective_from,
                    'effective_until' => $source->effective_until,
                    'workflowSteps' => $steps,
                ]);
            }
        }
    }
}
