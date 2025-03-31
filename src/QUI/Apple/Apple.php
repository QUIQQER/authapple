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

    public static function getClientId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'clientId');
    }

    /**
     * Apple Developer Team-ID
     *
     * @return string
     */
    public static function getTeamId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'teamId');
    }

    /**
     * Key-ID from Apple-Key (.p8)
     *
     * @return string
     */
    public static function getKeyId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'keyId');
    }

    /**
     * Private Key (.p8)
     *
     * @return string
     */
    public static function getPrivateKeyId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'privateKeyId');
    }
}
