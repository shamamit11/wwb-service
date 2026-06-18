<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Auth\Data\ChangePasswordData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangeAdminPasswordRequest extends FormRequest
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
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::defaults()],
        ];
    }

    public function toData(): ChangePasswordData
    {
        /** @var array{current_password:string,password:string} $validated */
        $validated = $this->validated();

        return new ChangePasswordData(
            currentPassword: $validated['current_password'],
            password: $validated['password'],
        );
    }
}
