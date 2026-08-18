<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Public endpoint (QR scan from mobile) allows unauthenticated access.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('signature') && ! $this->has('recipient_signature')) {
            $merge['recipient_signature'] = $this->input('signature');
        }
        if ($this->has('notes') && ! $this->has('receiving_notes')) {
            $merge['receiving_notes'] = $this->input('notes');
        }
        if ($this->has('received_by_name') && ! $this->has('recipient_name')) {
            $merge['recipient_name'] = $this->input('received_by_name');
        }
        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_signature' => ['nullable', 'string'],   // base64 data:image/png;base64,...
            'receiving_notes' => ['nullable', 'string', 'max:1000'],
            'received_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_name.max' => 'Nama penerima maksimal 255 karakter.',
            'receiving_notes.max' => 'Catatan penerimaan maksimal 1000 karakter.',
            'received_at.date' => 'Format tanggal penerimaan tidak valid.',
        ];
    }
}
