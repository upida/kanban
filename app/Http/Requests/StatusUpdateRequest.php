<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatusUpdateRequest extends FormRequest
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
        $project_id = $this->get('project_id');
        $name = $this->get('name');

        return [
            'project_id' => ['exists:projects,id,' . $this->status->project_id],
            'name' => ['string', 'max:255', Rule::unique('statuses')->where(
                function ($query) use ($project_id, $name) {
                    $query->where('project_id', $project_id)
                        ->where('name', $name)
                        ->where('id', '!=', $this->status->id);
                }
            )],
        ];
    }
}
