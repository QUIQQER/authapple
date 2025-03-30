<?php

namespace QUI\Apple;

use QUI;
use QUI\FrontendUsers;

/**
 * Class Email\Registrar
 *
 * Registration via e-mail address
 *
 * @package QUI\Registration\Google
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
        // TODO: Implement onRegistered() method.
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
