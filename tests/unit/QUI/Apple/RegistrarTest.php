<?php

namespace QUI\Apple;

use PHPUnit\Framework\TestCase;
use QUI\Apple\Controls\Button;
use QUI\Interfaces\Users\User as UserInterface;

class RegistrarTest extends TestCase
{
    public function testGetInvalidFieldsReturnsEmptyArray(): void
    {
        $registrar = new Registrar();
        $this->assertSame([], $registrar->getInvalidFields());
    }

    public function testGetControlReturnsAppleButton(): void
    {
        $registrar = new Registrar();
        $this->assertInstanceOf(Button::class, $registrar->getControl());
    }

    public function testGetIconReturnsAppleIconClass(): void
    {
        $registrar = new Registrar();
        $this->assertSame('fa fa-brands fa-apple', $registrar->getIcon());
    }

    public function testCanSendPasswordReturnsFalse(): void
    {
        $registrar = new Registrar();
        $this->assertFalse($registrar->canSendPassword());
    }

    public function testOnRegisteredDoesNotThrow(): void
    {
        $registrar = new Registrar();
        $user = $this->createMock(UserInterface::class);

        $registrar->onRegistered($user);
        $this->assertTrue(true);
    }

    public function testGetUsernameUsesCachedProfileData(): void
    {
        $registrar = new Registrar();

        $reflection = new \ReflectionProperty($registrar, 'profileData');
        $reflection->setAccessible(true);
        $reflection->setValue($registrar, ['email' => 'cached@example.com']);

        $this->assertSame('cached@example.com', $registrar->getUsername());
    }
}
