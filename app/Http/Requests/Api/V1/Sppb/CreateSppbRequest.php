<?php

namespace App\Http\Requests\Api\V1\Sppb;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class CreateSppbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sppb.create');
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        $firstLocationId = Location::value('id') ?? 1;
        $secondLocationId = Location::where('id', '!=', $firstLocationId)->value('id') ?? ($firstLocationId + 1);

        $plantId = $this->input('plant_id') ?: ($user?->plant_id ?: 1);
        $departmentId = $this->input('department_id') ?: ($user?->department_id ?: 1);

        $this->merge([
            'plant_id' => (int) $plantId,
            'department_id' => (int) $departmentId,
            'origin_location_id' => (int) ($this->input('origin_location_id') ?: $firstLocationId),
            'destination_location_id' => (int) ($this->input('destination_location_id') ?: $secondLocationId),
            'request_date' => $this->input('request_date') ?: now()->toDateString(),
            'date_needed' => $this->input('date_needed') ?: ($this->input('request_date') ?: now()->toDateString()),
            'needed_name' => $this->input('needed_name') ?: ($this->input('purpose') ?: 'Keperluan Operasional'),
            'is_urgent' => $this->boolean('is_urgent', $this->input('priority') === 'urgent' || $this->input('priority') === 'high'),
        ]);
    }

    public function rules(): array
    {
        return [
            'plant_id' => 'required|integer|exists:plants,id',
            'department_id' => 'required|integer|exists:departments,id',
            'origin_location_id' => 'required|integer|exists:locations,id',
            'destination_location_id' => 'required|integer|exists:locations,id|different:origin_location_id',
            'needed_name' => 'nullable|string|max:255',
            'request_date' => 'required|date',
            'date_needed' => 'required|date|after_or_equal:request_date',
            'purpose' => 'required|string|min:5|max:10000',
            'is_urgent' => 'boolean',
            'remarks' => 'nullable|string|max:5000',
            'items' => 'nullable|array',
        ];
    }
}
