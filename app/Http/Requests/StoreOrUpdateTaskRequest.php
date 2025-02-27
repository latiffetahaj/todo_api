<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,urgent',
            'is_completed' => 'boolean',
            'completed_at' => 'date'
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            // Make fields optional for update
            $rules['name'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string';
            $rules['priority'] = 'sometimes|in:low,medium,urgent';
            // 'is_completed' can remain as is since it's already optional by nature
        }

        return $rules;
    }
} 