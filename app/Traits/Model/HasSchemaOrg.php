<?php

namespace App\Traits\Model;

use App\Enums\MediaCollection;
use App\Models\Studio;
use App\Support\BreadcrumbNode;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

trait HasSchemaOrg
{
    /**
     * The Schema.org type for this entity.
     *
     * @return string
     */
    abstract public function schemaType(): string;

    /**
     * The canonical URL for this entity.
     *
     * @return string
     */
    abstract public function schemaUrl(): string;

    /**
     * The prefix for the Schema.org keywords field.
     *
     * @return string
     */
    abstract public function schemaKeywordsPrefix(): string;

    /**
     * The label for this entity in a breadcrumb chain.
     *
     * @return string
     */
    abstract public function schemaBreadcrumbLabel(): string;

    /**
     * The parent node in the breadcrumb chain.
     *
     * @return ?BreadcrumbNode
     */
    abstract public function schemaBreadcrumbParent(): ?BreadcrumbNode;

    /**
     * The Schema.org JSON-LD payload for this entity.
     *
     * @return array
     */
    public function toSchemaOrg(): array
    {
        return [
            '@graph' => [
                $this->schemaPrimary(),
                $this->schemaBreadcrumb(),
            ],
        ];
    }

    /**
     * The full breadcrumb chain from root to current entity.
     *
     * @return BreadcrumbNode[]
     */
    public function schemaBreadcrumbChain(): array
    {
        $chain = [];
        $node = $this->schemaBreadcrumbParent();

        while ($node !== null) {
            array_unshift($chain, $node);
            $node = $node->parent;
        }

        $chain[] = new BreadcrumbNode($this->schemaBreadcrumbLabel(), $this->schemaUrl());

        return $chain;
    }

    /**
     * The Schema.org BreadcrumbList for this entity.
     *
     * @return array
     */
    protected function schemaBreadcrumb(): array
    {
        $chain = $this->schemaBreadcrumbChain();

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (BreadcrumbNode $node, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $node->label,
                    'item' => $node->url,
                ],
                $chain,
                array_keys($chain),
            ),
        ];
    }

    /**
     * The primary Schema.org entity payload.
     *
     * @return array
     */
    protected function schemaPrimary(): array
    {
        $subject = $this->schemaSubject();
        $image = $this->schemaImage();

        $schema = [
            '@type' => $this->schemaType(),
            'url' => $this->schemaUrl(),
            'name' => $this->title,
            'alternateName' => $subject->original_title ?? null,
            'image' => $image,
            'contentRating' => $subject->tvRating?->name,
            'genre' => $subject->genres?->pluck('name')->values()->all() ?? [],
            'datePublished' => $this->schemaDatePublished()?->format('Y-m-d'),
            'keywords' => trim($this->schemaKeywordsPrefix() . ',' . ($subject->keywords ?? ''), ','),
        ];

        if ($description = $this->schemaDescription()) {
            $schema['description'] = $description;
        }

        if ($this->mediaStat?->rating_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingCount' => $this->mediaStat->rating_count,
                'bestRating' => 5,
                'worstRating' => 0,
                'ratingValue' => $this->mediaStat->rating_average,
            ];
        }

        if (!empty($subject->countryOfOrigin)) {
            $schema['countryOfOrigin'] = [
                '@type' => 'Country',
                'name' => $subject->countryOfOrigin->name,
                'alternateName' => $subject->countryOfOrigin->code,
            ];
        }

        if ($studio = $this->schemaPrimaryStudio()) {
            $schema['creator'] = [[
                '@type' => 'Organization',
                'url' => route('studios.details', $studio),
            ]];
        }

        if ($trailerUrl = $this->schemaTrailerUrl()) {
            $schema['trailer'] = [
                '@type' => 'VideoObject',
                'name' => $this->title,
                'description' => 'Official Trailer',
                'embedUrl' => $trailerUrl,
                'thumbnailUrl' => $image,
                'uploadDate' => $this->schemaDatePublished()?->toIso8601String(),
            ];
        }

        return $schema;
    }

    /**
     * The model whose attributes feed genre, contentRating, studios, and keywords.
     *
     * @return Model
     */
    protected function schemaSubject(): Model
    {
        return $this;
    }

    /**
     * The cleaned description text.
     *
     * @return ?string
     */
    protected function schemaDescription(): ?string
    {
        $text = $this->synopsis;

        if (empty($text)) {
            return null;
        }

        $text = strip_tags($text);
        $text = str_replace(['\r\n', '\r', '\n', '\t'], ' ', $text);
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        return $text === '' ? null : $text;
    }

    /**
     * The hero image URL.
     *
     * @return string
     */
    protected function schemaImage(): string
    {
        return $this->getFirstMediaFullUrl(MediaCollection::Banner())
            ?? $this->getFirstMediaFullUrl(MediaCollection::Poster())
            ?? asset('images/static/promotional/social_preview_icon_only.webp');
    }

    /**
     * The release date.
     *
     * @return ?CarbonInterface
     */
    protected function schemaDatePublished(): ?CarbonInterface
    {
        return $this->started_at ?? null;
    }

    /**
     * The trailer embed URL.
     *
     * @return ?string
     */
    protected function schemaTrailerUrl(): ?string
    {
        return $this->video_url ?? null;
    }

    /**
     * The primary studio for the creator field.
     *
     * @return ?Studio
     */
    protected function schemaPrimaryStudio(): ?Studio
    {
        $studios = $this->schemaSubject()->studios ?? null;

        if (empty($studios)) {
            return null;
        }

        return $studios->firstWhere('is_studio', '=', true) ?? $studios->first();
    }
}
