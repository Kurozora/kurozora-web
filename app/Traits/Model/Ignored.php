<?php

namespace App\Traits\Model;

use App\Scopes\IgnoreListScope;
use Illuminate\Database\Query\Builder;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Builder withIgnoreList(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder|Builder withoutIgnoreList()
 */
trait Ignored
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootIgnored(): void
    {
        if ((new CrawlerDetect)->isCrawler()) {
            return;
        }

        static::addGlobalScope(new IgnoreListScope());
    }

    /**
     * Get the name of the "tv rating" column.
     *
     * @return string
     */
    public function getIgnoreListColumn(): string
    {
        return defined(static::class.'::IGNORE_LIST_ID') ? static::IGNORE_LIST_ID : 'id';
    }

    /**
     * Get the fully qualified "tv rating" column.
     *
     * @return string
     */
    public function getQualifiedIgnoreListColumn(): string
    {
        return $this->qualifyColumn($this->getIgnoreListColumn());
    }
}
