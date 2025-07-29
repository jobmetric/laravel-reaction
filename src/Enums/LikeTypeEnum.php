<?php

namespace JobMetric\Like\Enums;

use JobMetric\PackageCore\Enums\EnumMacros;

/**
 * @method static LIKE()
 * @method static DISLIKE()
 */
enum LikeTypeEnum : string {
    use EnumMacros;

    case LIKE = "like";
    case DISLIKE = "dislike";
}
