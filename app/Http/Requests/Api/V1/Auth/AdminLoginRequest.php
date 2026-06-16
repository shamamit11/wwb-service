<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Modules\Auth\Data\AdminLoginData;
use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): AdminLoginData
    {
        /** @var array{email:string,password:string,device_name?:string|null} $validated */
        $validated = $this->validated();

        return new AdminLoginData(
            email: strtolower($validated['email']),
            password: $validated['password'],
            deviceName: $validated['device_name'] ?: 'admin-api',
        );
    }
}
