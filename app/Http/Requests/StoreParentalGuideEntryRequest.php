<?php

namespace App\Http\Requests;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreParentalGuideEntryRequest extends FormRequest
{
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
        $hasSeverity = (int) $this->input('rating') !== ParentalGuideRating::None;

        return [
            'category' => ['bail', 'required', 'integer', new EnumValue(ParentalGuideCategory::class, false)],
            'rating' => ['bail', 'required', 'integer', new EnumValue(ParentalGuideRating::class, false)],
            'frequency' => ['bail', $hasSeverity ? 'required' : 'prohibited', 'integer', new EnumValue(ParentalGuideFrequency::class, false)],
            'depiction' => ['bail', 'nullable', 'integer'],
            'reason' => ['bail', 'nullable', 'string', 'max:500'],
            'is_spoiler' => ['bail', 'required', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rating = $this->input('rating');
            $category = $this->input('category');
            $depiction = $this->input('depiction');
            $hasSeverity = (int) $rating !== ParentalGuideRating::None;
            $supportsDepiction = $hasSeverity && $this->categorySupportsDepiction($category);

            if (!$supportsDepiction && $depiction !== null) {
                $validator->errors()->add('depiction', __('The depiction field is prohibited for this rating or category.'));
                return;
            }

            if ($supportsDepiction && $depiction !== null) {
                $allowed = ParentalGuideDepiction::getValues();

                if (!in_array((int) $depiction, $allowed, true)) {
                    $validator->errors()->add('depiction', __('The selected depiction is invalid.'));
                }
            }
        });
    }

    /**
     * Whether the given category supports a depiction value.
     *
     * @param mixed $category
     *
     * @return bool
     */
    private function categorySupportsDepiction(mixed $category): bool
    {
        return match ((int) $category) {
            ParentalGuideCategory::SexAndNudity,
            ParentalGuideCategory::ViolenceAndGore,
            ParentalGuideCategory::FrighteningAndIntenseScenes => true,
            default => false,
        };
    }
}
