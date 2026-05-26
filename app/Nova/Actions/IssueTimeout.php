<?php

namespace App\Nova\Actions;

use App\Enums\TimeoutDuration;
use App\Enums\TimeoutReason;
use App\Models\Timeout;
use App\Models\User;
use App\Notifications\UserTimedOut;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class IssueTimeout extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Issue Timeout';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Issue Timeout';

    /**
     * Apply the chosen timeout to every selected user.
     *
     * @param ActionFields $fields
     * @param Collection   $models
     *
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $issuer = request()->user();
        $duration = (int) $fields->get('duration');
        $reasonKey = (int) $fields->get('reason_key');
        $note = $fields->get('note');
        $isPermanent = $duration === TimeoutDuration::Permanent;
        $newExpiresAt = TimeoutDuration::expiresAtFor($duration);

        $issuedCount = 0;
        $skippedCount = 0;

        foreach ($models as $user) {
            /** @var User $user */
            if (($issuer !== null && $user->id === $issuer->id) || $user->hasAnyRole(['superAdmin', 'admin', 'mod'])) {
                $skippedCount++;
                continue;
            }

            $timeout = DB::transaction(function () use ($user, $issuer, $reasonKey, $note, $isPermanent, $newExpiresAt) {
                $previousTimeout = $user->timeouts()
                    ->active()
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $effectiveIsPermanent = $isPermanent;
                $effectiveExpiresAt = $newExpiresAt;

                if ($previousTimeout !== null) {
                    if ($previousTimeout->is_permanent) {
                        $effectiveIsPermanent = true;
                        $effectiveExpiresAt = null;
                    } else if (!$effectiveIsPermanent && $previousTimeout->expires_at !== null && $effectiveExpiresAt !== null && $previousTimeout->expires_at->greaterThan($effectiveExpiresAt)) {
                        $effectiveExpiresAt = $previousTimeout->expires_at->toImmutable();
                    }

                    $previousTimeout->update([
                        'revoked_at' => Carbon::now(),
                        'revoked_by_id' => $issuer?->id,
                    ]);
                }

                /** @var Timeout $created */
                $created = $user->timeouts()->create([
                    'issued_by_id' => $issuer?->id,
                    'reason_key' => $reasonKey,
                    'note' => $note,
                    'is_permanent' => $effectiveIsPermanent,
                    'expires_at' => $effectiveIsPermanent ? null : $effectiveExpiresAt,
                ]);

                return $created;
            });

            $user->notify(new UserTimedOut($timeout));
            $issuedCount++;
        }

        $message = 'Issued ' . $issuedCount . ' timeout(s).';

        if ($skippedCount > 0) {
            $message .= ' Skipped ' . $skippedCount . ' (self or staff).';
        }

        return Action::message($message);
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
        return [
            Select::make('Duration', 'duration')
                ->options(TimeoutDuration::asSelectArray())
                ->displayUsingLabels()
                ->rules('required'),

            Select::make('Reason', 'reason_key')
                ->options(TimeoutReason::asSelectArray())
                ->displayUsingLabels()
                ->rules('required'),

            Textarea::make('Note', 'note')
                ->rules('required', 'max:2000')
                ->help('Internal note shown to staff and to the suspended user.'),
        ];
    }
}
