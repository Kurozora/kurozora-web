<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AppThemeDownloadKind Plist()
 * @method static AppThemeDownloadKind CSS()
 */
final class AppThemeDownloadKind extends Enum
{
    const int Plist = 0;
    const int CSS   = 1;

    /**
     * Get the content type for an enum value
     *
     * @return string
     */
    public function getContentType(): string
    {
        return match($this->value) {
            self::Plist => 'application/x-plist',
            self::CSS => 'text/css; charset=UTF-8',
        };
    }

    /**
     * Get the content type for an enum value
     *
     * @return string
     */
    public function getExtension(): string
    {
        return match($this->value) {
            self::Plist => 'plist',
            self::CSS => 'css',
        };
    }
}
