<?php

namespace App\Rules;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateModelIsTracked implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param string                                       $attribute
     * @param mixed                                        $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $library = (int) ($this->data['library'] ?? UserLibraryKind::Anime);
        $model = match($library) {
            UserLibraryKind::Game => Game::withoutGlobalScopes()
                ->withTranslation()
                ->firstWhere(Game::TABLE_NAME . '.id', '=', $value),
            UserLibraryKind::Manga => Manga::withoutGlobalScopes()
                ->withTranslation()
                ->firstWhere(Manga::TABLE_NAME . '.id', '=', $value),
            default => Anime::withoutGlobalScopes()
                ->withTranslation()
                ->firstWhere(Anime::TABLE_NAME . '.id', '=', $value)
        };

        if ($model == null) {
            $fail(__('The specified title cannot be found. Please try again.'));
        } else if (!auth()->user()->hasTracked($model)) {
            $fail($this->message($model->title));
        }
    }

    /**
     * Set the data under validation.
     *
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the validation error message.
     *
     * @param string $title
     *
     * @return string
     */
    public function message(string $title): string
    {
        return __('Please add ":x" to your library first.', ['x' => $title]);
    }
}
