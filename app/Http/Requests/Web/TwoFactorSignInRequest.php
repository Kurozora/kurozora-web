<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use App\Providers\TwoFactorAuthenticationProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorSignInRequest extends FormRequest
{
    /**
     * The user attempting the two-factor challenge.
     *
     * @var User|null
     */
    protected ?User $challengedUser = null;

    /**
     * Indicates if the user wished to be remembered after sign in.
     *
     * @var bool
     */
    protected bool $remember = false;

    /**
     * Indicates if the user has a local library.
     *
     * @var bool
     */
    protected bool $hasLocalLibrary = false;

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
     * Determine if the request has a valid two-factor code.
     *
     * @return bool
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws InvalidArgumentException
     */
    public function hasValidCode(): bool
    {
        return $this->code && tap(app(TwoFactorAuthenticationProvider::class)->verify(
            decrypt($this->challengedUser()->two_factor_secret), $this->code
        ), function ($result) {
            if ($result) {
                $this->session()->forget('sign-in.id');
            }
        });
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

        return tap(collect($this->challengedUser()->recoveryCodes())->first(function ($code) {
            return hash_equals($code, $this->recovery_code) ? $code : null;
        }), function ($code) {
            if ($code) {
                $this->session()->forget('sign-in.id');
            }
        });
    }

    /**
     * Determine if there is a challenged user in the current session.
     *
     * @return bool
     */
    public function hasChallengedUser(): bool
    {
        if ($this->challengedUser) {
            return true;
        }

        return $this->session()->has('sign-in.id') &&
            User::find($this->session()->get('sign-in.id'));
    }

    /**
     * Get the user that is attempting the two-factor challenge.
     *
     * @return User|null
     */
    public function challengedUser(): ?User
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        if (!$this->session()->has('sign-in.id') ||
            !$user = User::find($this->session()->get('sign-in.id'))) {
            throw new HttpResponseException($this->failedTwoFactorSignInResponse());
        }

        return $this->challengedUser = $user;
    }

    /**
     * Create an HTTP response for the failed two-factor authentication.
     *
     * @return Response
     */
    public function failedTwoFactorSignInResponse(): Response
    {
        return redirect()->route('two-factor.sign-in')
            ->withErrors(['code' => __('The provided two-factor authentication code was invalid.')]);
    }

    /**
     * Determine if the user wanted to be remembered after sign in.
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

    /**
     * Determine if the user has a local library.
     *
     * @return bool
     */
    public function hasLocalLibrary(): bool
    {
        if (!$this->hasLocalLibrary) {
            $this->hasLocalLibrary = $this->session()->pull('sign-in.has-local-library', false);
        }

        return $this->hasLocalLibrary;
    }
}
