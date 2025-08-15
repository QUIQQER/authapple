<?php

namespace QUI\Apple;

use QUI;
use QUI\Permissions\Exception;
use QUI\Users\User;

class Events
{
    /**
     * @throws Exception
     * @throws QUI\Database\Exception
     */
    public static function onUserDelete(User $User): void
    {
        Apple::disconnectAccount($User->getUUID(), false);
    }
}
