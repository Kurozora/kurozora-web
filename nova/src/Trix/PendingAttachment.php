<?php

namespace Laravel\Nova\Trix;

use Laravel\Nova\Fields\Trix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PendingAttachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nova_pending_trix_attachments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Persist the given draft's pending attachments.
     *
     * @param  string  $draftId
     * @param  \Laravel\Nova\Fields\Trix  $field
     * @param  mixed  $model
     * @return void
     */
    public static function persistDraft($draftId, Trix $field, $model)
    {
        static::where('draft_id', $draftId)->get()->each->persist($field, $model);
    }

    /**
     * Persist the pending attachment.
     *
     * @param  \Laravel\Nova\Fields\Trix  $field
     * @param  mixed  $model
     * @return void
     */
    public function persist(Trix $field, $model)
    {
        Attachment::create([
            'attachable_type' => get_class($model),
            'attachable_id' => $model->getKey(),
            'attachment' => $this->attachment,
            'disk' => $field->disk,
            'url' => Storage::disk($field->disk)->url($this->attachment),
        ]);

        $this->delete();
    }

    /**
     * Purge the attachment.
     *
     * @return void
     */
    public function purge()
    {
        Storage::disk($this->disk)->delete($this->attachment);

        $this->delete();
    }
}
