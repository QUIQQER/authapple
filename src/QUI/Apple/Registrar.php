<?php

namespace QUI\Apple;

use QUI;
use QUI\ExceptionStack;
use QUI\FrontendUsers;
use QUI\Permissions\Exception;

/**
 * Class Registrar
 *
 * Registration via apple address
 */
class Registrar extends FrontendUsers\AbstractRegistrar
{
    // region auth stuff
    public function validate(): array
    {
        // TODO: Implement validate() method.
        return [];
    }

    public function createUser(): QUI\Interfaces\Users\User
    {
        $token = $this->getAttribute('token');

        if (Apple::existsQuiqqerAccount($token)) {
            return Apple::getUserByToken($token);
        }

        $User =  parent::createUser();
        $profileData = Apple::getProfileData($token);
        $SystemUser = QUI::getUsers()->getSystemUser();

        $User->setAttributes([
            'email' => $profileData['email'],
            'firstname' => empty($profileData['given_name']) ? null : $profileData['given_name'],
            'lastname' => empty($profileData['family_name']) ? null : $profileData['family_name'],
        ]);


        $User->setAttribute(FrontendUsers\Handler::USER_ATTR_EMAIL_VERIFIED, boolval($profileData['email_verified']));

        $User->setPassword(QUI\Security\Password::generateRandom(), $SystemUser);
        $User->save($SystemUser);

        // connect Google account with QUIQQER account
        Apple::connectQuiqqerAccount($User->getUUID(), $token, false);

        return $User;
    }

    public function onRegistered(QUI\Interfaces\Users\User $User): void
    {
    }

    public function getInvalidFields(): array
    {
        return [];
    }

    // endregion

    public function getUsername(): string
    {
        $token = $this->getAttribute('token');
        $profileData = Apple::getProfileData($token);

        return $profileData['email'];
    }

    public function getControl(): QUI\Control
    {
        return new QUI\Apple\Controls\Button();
    }

    public function getTitle(?QUI\Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'registrar.title');
    }

    public function getDescription(?QUI\Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'registrar.description');
    }

    public function getIcon(): string
    {
        return 'fa fa-brands fa-apple';
    }

    public function canSendPassword(): bool
    {
        return false;
    }
}
