<?php

namespace App\Http\Requests\Api\V1\Sppb;

use Illuminate\Foundation\Http\FormRequest;

class CreateSppbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sppb.create');
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
            'purpose' => 'required|string|min:10|max:10000',
            'is_urgent' => 'boolean',
            'remarks' => 'nullable|string|max:5000',
        ];
    }
}
