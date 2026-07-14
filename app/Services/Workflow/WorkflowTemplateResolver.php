<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Exceptions\Workflow\AmbiguousWorkflowTemplateException;
use App\Exceptions\Workflow\MissingWorkflowTemplateException;
use App\Models\SppbHeader;
use App\Models\WorkflowTemplate;

final class WorkflowTemplateResolver
{
    public function resolve(SppbHeader $header): WorkflowTemplate
    {
        $query = WorkflowTemplate::query()
            ->where('document_type', 'SPPB')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            });

        // Cari paling spesifik: cocok plant + department
        $candidates = (clone $query)
            ->where('plant_id', $header->plant_id)
            ->where('department_id', $header->department_id)
            ->get();

        if ($candidates->count() === 1) {
            return $candidates->first();
        }
        if ($candidates->count() > 1) {
            throw new AmbiguousWorkflowTemplateException;
        }

        // Coba: cocok plant saja (department null)
        $candidates = (clone $query)
            ->where('plant_id', $header->plant_id)
            ->whereNull('department_id')
            ->get();

        if ($candidates->count() === 1) {
            return $candidates->first();
        }
        if ($candidates->count() > 1) {
            throw new AmbiguousWorkflowTemplateException;
        }

        // Coba: global (plant null, department null)
        $candidates = (clone $query)
            ->whereNull('plant_id')
            ->whereNull('department_id')
            ->get();

        if ($candidates->count() === 1) {
            return $candidates->first();
        }
        if ($candidates->count() > 1) {
            throw new AmbiguousWorkflowTemplateException;
        }

        throw new MissingWorkflowTemplateException;
    }
}
