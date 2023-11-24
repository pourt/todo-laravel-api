<?php

namespace PERP\Task\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
        $this->merge([

        ]);
    }

    public function rules()
    {
         return [
            'title' => [
                'required',
            ],
            'Task_date' => [
                'required',
            ],
            'prize' => [
                'required',
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
            'Task_date.required' => 'Task date is required.',
            'prize.required' =>  'Prize is required.',
        ];
    }
}
