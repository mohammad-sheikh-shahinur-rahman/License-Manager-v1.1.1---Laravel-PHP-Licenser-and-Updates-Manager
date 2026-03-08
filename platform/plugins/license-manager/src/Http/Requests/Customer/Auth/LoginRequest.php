<?php

namespace Botble\LicenseManager\Http\Requests\Customer\Auth;

use Botble\LicenseManager\Http\Requests\Customer\Auth\Concerns\HasCaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use HasCaptcha;

    public function rules(): array
    {
        $rules =  [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];

        return $this->mergeCaptchaRulesIfEnabled($rules);
    }

    public function attributes(): array
    {
        return $this->mergeCaptchaAttributeIfEnabled([]);
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::guard('lm_customer')->attempt(
            $this->credentials(),
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function credentials(): array
    {
        return $this->only('email', 'password');
    }
}
