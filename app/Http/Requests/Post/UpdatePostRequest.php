<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'sometimes|image',
            'pinned' => 'required|integer|in:0,1',
            'tags' => 'sometimes|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
    public function messages()
    {
        return [
            'pinned.integer' => 'The pinned field must be an integer.',
            'pinned.in' => 'The pinned field must be either 0 or 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
