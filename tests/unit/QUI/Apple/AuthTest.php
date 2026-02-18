<?php

namespace QUI\Apple;

use PHPUnit\Framework\TestCase;
use QUI\Apple\Controls\Button;

class AuthTest extends TestCase
{
    public function testIsSecondaryAuthenticationReturnsFalse(): void
    {
        $auth = new Auth();
        $this->assertFalse($auth->isSecondaryAuthentication());
    }

    public function testGetIconReturnsAppleIconClass(): void
    {
        $auth = new Auth();
        $this->assertSame('fa fa-brands fa-apple', $auth->getIcon());
    }

    public function testGetLoginControlReturnsAppleButton(): void
    {
        $control = Auth::getLoginControl();
        $this->assertInstanceOf(Button::class, $control);
    }
}
