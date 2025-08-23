<?php

namespace App\Livewire\Profile;

use App\Contracts\UpdatesUserAccountInformation;
use App\Models\User;
use App\Traits\Livewire\WithRateLimiting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class UpdateAccountInformationForm extends Component
{
    use WithRateLimiting;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-component' => '$refresh'
    ];

    /**
     * The rate limit decay in seconds.
     *
     * @var int $rateLimitDecay
     */
    protected int $rateLimitDecay = 90;

    /**
     * Prepare the component.
     *
     * @param $user
     *
     * @return void
     */
    public function mount($user): void
    {
        $state = $user->only(['slug', 'email']);

        $this->user = $user;
        $this->state = [
            'username' => $state['slug'],
            'email' => $state['email']
        ];
    }

    /**
     * Update the user's account information.
     *
     * @param UpdatesUserAccountInformation $updater
     *
     * @return void
     */
    public function updateAccountInformation(UpdatesUserAccountInformation $updater): void
    {
        $this->resetErrorBag();

        $attributes = $this->state;

        $updater->update($this->user, $attributes);

        $this->dispatch('saved');

        if (!$this->user->hasVerifiedEmail()) {
            $this->rateLimit(1, $this->rateLimitDecay);

            $this->user->sendEmailVerificationNotification();

            $this->dispatch('verification-link-sent', countdown: $this->rateLimitDecay);
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $hasVerifiedEmail = $this->user->hasVerifiedEmail();

        if ($hasVerifiedEmail) {
            $rateLimitDecay = 0;
        } else {
            $errors = $this->getErrorBag();
            $rateLimitDecay = $errors->has('seconds_until_available') ? (int) $errors->get('seconds_until_available')[0] : $this->rateLimitAvailableIn('updateAccountInformation');
        }

        return view('livewire.profile.update-account-information-form', [
            'hasVerifiedEmail' => $hasVerifiedEmail,
            'rateLimitDecay' => $rateLimitDecay,
        ]);
    }
}
