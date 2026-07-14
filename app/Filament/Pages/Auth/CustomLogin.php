<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Services\Auth\AuthService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;

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
     * Get the form schema for the login page.
     *
     * @return array<int, Component>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getLoginFormComponent()
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

        $authService = app(AuthService::class);
        $authService->attemptLogin(
            $data['email'],
            $data['password'],
            $data['remember'] ?? false
        );

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
