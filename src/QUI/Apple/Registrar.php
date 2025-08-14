<?php

namespace QUI\Apple;

use QUI;
use QUI\FrontendUsers;

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

    public function onRegistered(QUI\Interfaces\Users\User $User): void
    {
        $SystemUser = QUI::getUsers()->getSystemUser();
        $token = $this->getAttribute('token');

        // test if user is connected
        // we don't want to override a connected user
        if (Apple::existsQuiqqerAccount($token)) {
            return;
        }

        // set user data
        $profileData = Apple::getProfileData($token);

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
    }

    public function getInvalidFields(): array
    {
        return [];
    }

    // endregion

    public function getUsername(): string
    {
        // TODO: Implement getUsername() method.
        return '';
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
