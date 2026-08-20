<?php

namespace App\Traits\Model;

use App\Models\UserNote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Noter
{
    /**
     * The user's notes.
     *
     * @return HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class);
    }

    /**
     * Returns the user's note on the given model.
     *
     * @param Model $model
     *
     * @return null|UserNote
     */
    public function noteFor(Model $model): ?UserNote
    {
        if ($this->relationLoaded('notes')) {
            return $this->notes
                ->where('noteable_type', '=', $model->getMorphClass())
                ->firstWhere('noteable_id', '=', $model->getKey());
        }

        return $this->notes()
            ->where('noteable_type', '=', $model->getMorphClass())
            ->where('noteable_id', '=', $model->getKey())
            ->first();
    }

    /**
     * Whether the user has a note on the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function hasNoteFor(Model $model): bool
    {
        return $this->noteFor($model) !== null;
    }

    /**
     * Writes the user's note on the given model, clearing it when the body is empty.
     *
     * @param Model       $model
     * @param null|string $body
     *
     * @return null|UserNote
     */
    public function setNote(Model $model, ?string $body): ?UserNote
    {
        $body = trim(strip_tags((string) $body));

        if ($body === '') {
            $this->clearNote($model);
            return null;
        }

        if ($this->relationLoaded('notes')) {
            $this->unsetRelation('notes');
        }

        return $this->notes()
            ->updateOrCreate([
                'noteable_type' => $model->getMorphClass(),
                'noteable_id' => $model->getKey(),
            ], [
                'body' => $body,
            ]);
    }

    /**
     * Removes the user's note on the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function clearNote(Model $model): bool
    {
        if ($this->relationLoaded('notes')) {
            $this->unsetRelation('notes');
        }

        // Deleted through the model so the state observer fires.
        return (bool) $this->notes()
            ->where('noteable_type', '=', $model->getMorphClass())
            ->where('noteable_id', '=', $model->getKey())
            ->first()
            ?->delete();
    }

    /**
     * Removes every note of the given type.
     *
     * @param null|string $type
     *
     * @return bool
     */
    public function clearNotes(?string $type = null): bool
    {
        if ($this->relationLoaded('notes')) {
            $this->unsetRelation('notes');
        }

        // Bulk delete bypasses model events, so the state version is bumped by hand.
        $affected = (bool) $this->notes()
            ->when($type !== null, function ($query) use ($type) {
                $query->where('noteable_type', '=', $type);
            })
            ->delete();

        $this->bumpStateVersion();

        return $affected;
    }
}
