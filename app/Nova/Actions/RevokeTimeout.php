<?php

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RevokeTimeout extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Revoke Active Timeout';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Revoke Timeout';

    /**
     * Lifts the currently active timeout for each selected user.
     *
     * @param ActionFields $fields
     * @param Collection   $models
     *
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $issuer = request()->user();
        $revokedCount = 0;

        foreach ($models as $user) {
            /** @var User $user */
            $wasRevoked = DB::transaction(function () use ($user, $issuer) {
                $activeTimeout = $user->timeouts()
                    ->active()
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                if ($activeTimeout === null) {
                    return false;
                }

                $activeTimeout->update([
                    'revoked_at' => Carbon::now(),
                    'revoked_by_id' => $issuer?->id,
                ]);

                return true;
            });

            if ($wasRevoked) {
                $revokedCount++;
            }
        }

        return Action::message('Revoked ' . $revokedCount . ' active timeout(s).');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
