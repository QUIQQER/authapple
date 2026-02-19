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
    private ?array $profileData = null;

    private function getProfileDataFromToken(): array
    {
        if (!is_null($this->profileData)) {
            return $this->profileData;
        }

        $token = $this->getAttribute('token');
        Apple::validateAccessToken($token);
        $this->profileData = Apple::getProfileData($token);

        return $this->profileData;
    }

    // region auth stuff
    /**
     * @throws FrontendUsers\Exception
     * @throws QUI\Exception
     */
    public function validate(): array
    {
        $lg = 'quiqqer/authapple';
        $lgPrefix = 'exception.registrar.';

        $token = $this->getAttribute('token');

        if (empty($token)) {
            throw new FrontendUsers\Exception([
                $lg,
                $lgPrefix . 'token_invalid'
            ]);
        }

        try {
            $profileData = $this->getProfileDataFromToken();
        } catch (\Exception) {
            throw new FrontendUsers\Exception([
                $lg,
                $lgPrefix . 'token_invalid'
            ]);
        }

        $email = $profileData['email'] ?? '';

        if (empty($email)) {
            throw new FrontendUsers\Exception([
                $lg,
                $lgPrefix . 'email_address_empty'
            ]);
        }

        if (QUI::getUsers()->usernameExists($email)) {
            // If the account is already connected, allow re-registration flow
            if (!Apple::existsQuiqqerAccount($token)) {
                throw new FrontendUsers\Exception([
                    $lg,
                    $lgPrefix . 'email_already_exists'
                ]);
            }
        }

        $Handler = FrontendUsers\Handler::getInstance();
        $settings = $Handler->getRegistrationSettings();
        $allowUnverifiedEmailAddresses = (int)($settings['allowUnverifiedEmailAddresses'] ?? 0);
        $emailVerified = filter_var(
            $profileData['email_verified'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if (
            !$allowUnverifiedEmailAddresses
            && !$emailVerified
        ) {
            throw new FrontendUsers\Exception([
                $lg,
                $lgPrefix . 'email_not_verified'
            ]);
        }

        return $profileData;
    }

    public function createUser(): QUI\Interfaces\Users\User
    {
        $token = $this->getAttribute('token');
        $profileData = $this->getProfileDataFromToken();

        if (Apple::existsQuiqqerAccount($token)) {
            return Apple::getUserByToken($token);
        }

        $User =  parent::createUser();
        $SystemUser = QUI::getUsers()->getSystemUser();

        $User->setAttributes([
            'email' => $profileData['email'],
            'firstname' => empty($profileData['given_name']) ? null : $profileData['given_name'],
            'lastname' => empty($profileData['family_name']) ? null : $profileData['family_name'],
        ]);


        $emailVerified = filter_var(
            $profileData['email_verified'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        $User->setAttribute(FrontendUsers\Handler::USER_ATTR_EMAIL_VERIFIED, $emailVerified);

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

    /**
     * @throws Exception
     */
    public function getUsername(): string
    {
        $profileData = $this->getProfileDataFromToken();

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
