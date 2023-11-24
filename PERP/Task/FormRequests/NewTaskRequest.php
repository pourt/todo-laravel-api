<?php

namespace PERP\Task\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

class NewTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *`
     * @return void
     */
    protected function prepareForValidation()
    {
    }

    public function rules()
    {
        return [
            'title' => [
                'required',
            ],
            'description' => [
                'required',
            ],
            'due_date' => [
                'required',
                'date',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'Title is required.',
            'description.required' => 'Description is required.',
            'due_date.required' =>  'Due date is required.',
        ];
    }
}
