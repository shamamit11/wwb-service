<?php

namespace App\Http\Requests\Api\V1;

use App\Modules\Users\Data\CreateUserData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function toData(): CreateUserData
    {
        /** @var array{name:string,email:string,password:string} $validated */
        $validated = $this->validated();

        return new CreateUserData(
            name: $validated['name'],
            email: strtolower($validated['email']),
            password: $validated['password'],
        );
    }
}
