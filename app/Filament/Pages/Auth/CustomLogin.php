<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Services\Auth\AuthService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    /**
     * Override the credentials method to handle both email and NIK.
     *
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    /**
     * Override getEmailFormComponent to return a text input for Email or NIK.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('Email atau NIK'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * Authenticate the user using the custom AuthService.
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        try {
            $authService = app(AuthService::class);
            $authService->attemptLogin(
                $data['email'],
                $data['password'],
                $data['remember'] ?? false
            );
        } catch (ValidationException $e) {
            // Map the validation error key to 'data.email' so it renders correctly on the form input
            $message = $e->validator->errors()->first('email') ?: $e->getMessage();
            throw ValidationException::withMessages([
                'data.email' => $message,
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
