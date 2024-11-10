<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', 'unique:projects,name,' . $this->project->id],
            'description' => ['nullable', 'string', 'max:255'],
            'started_at' => ['date', 'date_format:Y-m-d H:i:s'],
            'ended_at' => ['date', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
