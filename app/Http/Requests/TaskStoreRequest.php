<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
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
        $project = request()->route()->parameter('project');

        $this->merge([
            'project_id' => $project->id,
        ]);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'done' => ['boolean'],
            'project_id' => ['required', 'exists:projects,id'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }
}
