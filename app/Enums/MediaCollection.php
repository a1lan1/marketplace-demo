<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCollection: string
{
    case ProductCoverImage = 'product.cover-image';

    case ProductCoverImageThumb = 'product.cover-image-thumb';

    case UserAvatar = 'user.avatar';

    case UserAvatarThumb = 'user.avatar-thumb';
}
