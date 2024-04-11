<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Surfcamp\Success\Content;

enum ContentSlideMode
{
    case None;
    case Slide;
    case Collect;
    case CollectReverse;

    public static function tryFrom(?string $slideMode): ContentSlideMode
    {
        return match ($slideMode) {
            'slide' => self::Slide,
            'collect' => self::Collect,
            'collectReverse' => self::CollectReverse,
            default => self::None,
        };
    }
}
