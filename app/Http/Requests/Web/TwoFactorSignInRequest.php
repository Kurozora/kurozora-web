<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use App\Providers\TwoFactorAuthenticationProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorSignInRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var mixed
     */
    protected mixed $challengedUser = null;

    /**
     * Indicates if the user wished to be remembered after login.
     *
     * @var bool
     */
    protected bool $remember = false;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'code'          => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ];
    }

    /**
     * Determine if the request has a valid two factor code.
     *
     * @return bool
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function hasValidCode(): bool
    {
        return $this->code && app(TwoFactorAuthenticationProvider::class)->verify(
                decrypt($this->challengedUser()->two_factor_secret), $this->code
            );
    }

    /**
     * Get the valid recovery code if one exists on the request.
     *
     * @return string|null
     */
    public function validRecoveryCode(): ?string
    {
        if (!$this->recovery_code) {
            return null;
        }

        return collect($this->challengedUser()->recoveryCodes())->first(function ($code) {
            return hash_equals($this->recovery_code, $code) ? $code : null;
        });
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return mixed
     */
    public function challengedUser(): mixed
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        if (!$this->session()->has('sign-in.id') ||
            !$user = User::find($this->session()->pull('sign-in.id'))) {
            throw new HttpResponseException($this->failedTwoFactorSignInResponse());
        }

        return $this->challengedUser = $user;
    }

    /**
     * Create an HTTP response for the failed two factor authentication.
     *
     * @return Response
     */
    public function failedTwoFactorSignInResponse(): Response
    {
        return redirect()->route('sign-in')->withErrors(['email' => __('The provided two factor authentication code was invalid.')]);
    }

    /**
     * Determine if the user wanted to be remembered after login.
     *
     * @return bool
     */
    public function remember(): bool
    {
        if (!$this->remember) {
            $this->remember = $this->session()->pull('sign-in.remember', false);
        }

        return $this->remember;
    }
}
