<?php

/**
 * This file contains QUI\Apple
 */

namespace QUI\Apple;

use QUI;
use QUI\Exception;
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

    public function __construct(array | int | string | User | null $user = '')
    {
        if (!empty($user) && is_string($user)) {
            try {
                $this->User = QUI::getUsers()->getUserByName($user);
            } catch (\Exception) {
                $this->User = QUI::getUsers()->getNobody();
            }
        }
    }

    public function isSecondaryAuthentication(): bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    public function auth(array | int | string $authParams): void
    {
        if (!is_array($authParams) || !isset($authParams['token'])) {
            throw new QUI\Exception([
                'quiqqer/authapple',
                'exception.auth.wrong.data'
            ], 401);
        }

        $token = $authParams['token'];
        Apple::validateAccessToken($token);

        if (!Apple::existsQuiqqerAccount($token)) {
            throw new Exception('Apple user does not exist in QUIQQER', 401);
        }

        $userData = Apple::getProfileData($token);
        $appleSub = $userData['sub'] ?? null;
        $Users = QUI::getUsers();

        if (empty($appleSub)) {
            throw new Exception('Apple user does not exist in QUIQQER', 401);
        }

        $connectionProfile = Apple::getConnectedAccountByToken($token);

        try {
            $User = $Users->get($connectionProfile['userId']);
            // Apple::connectQuiqqerAccount($User->getUUID(), $token, false);

            if (is_null($this->User)) {
                $this->User = $User;
            }
        } catch (QUI\Exception) {
            throw new Exception('Apple user does not exist in QUIQQER', 401);
        }
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

    public function getIcon(): string
    {
        return 'fa fa-brands fa-apple';
    }
}
