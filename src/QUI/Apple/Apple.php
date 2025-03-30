<?php

namespace QUI\Apple;

use QUI;
use QUI\Exception;

class Apple
{
    public static function table(): string
    {
        return QUI::getDBTableName('quiqqer_auth_apple');
    }

    /**
     * @throws Exception
     */
    public static function getClientId(): string
    {
        return QUI::getPackage('quiqqer/authgoogle')->getConfig()->get('apiSettings', 'clientId');
    }
}
