<?php

declare(strict_types=1);
namespace Sloth\Model\Meta;

/**
 * User meta model with unserialize-safe value access.
 *
 * Extends the Corcel implementation to guard the `value` accessor against
 * PHP 8 `unserialize()` warnings for plain-text meta values.
 */
class UserMeta extends \Corcel\Model\Meta\UserMeta
{
    use HasSafeMetaValue;
}
