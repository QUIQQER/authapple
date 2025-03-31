<?php

/**
 * This file contains QUI\Apple
 */

namespace QUI\Apple;

use QUI;
use QUI\Interfaces\Users\User;
use QUI\Users\AbstractAuthenticator;
use QUI\Locale;

/**
 * Class Auth
 *
 * Authentication handler for Apple authentication
 *
 * @package QUI\Apple\Auth
 */
class Auth extends AbstractAuthenticator
{
    protected QUI\Interfaces\Users\User | null $User = null;

    public function __construct(array | int | string $user = '')
    {
        if (!empty($user) && is_string($user)) {
            try {
                $this->User = QUI::getUsers()->getUserByName($user);
            } catch (\Exception) {
                $this->User = QUI::getUsers()->getNobody();
            }
        }
    }

    public function auth(array | int | string $authParams)
    {
        // TODO: Implement auth() method.
    }

    public function getUser(): User
    {
        return $this->User;
    }

    public function getTitle(?Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'authapple.title');
    }

    public function getDescription(?Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'authapple.description');
    }

    public static function getLoginControl(): ?QUI\Control
    {
        return new QUI\Apple\Controls\Button();
    }
}
