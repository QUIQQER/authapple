<?php

namespace QUI\Apple;

use PHPUnit\Framework\TestCase;
use QUI\Users\User;

class EventsTest extends TestCase
{
    public function testOnUserDeleteDoesNotThrowForUnknownUserUuid(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUUID')->willReturn('unknown-test-uuid');

        Events::onUserDelete($user);
        $this->assertTrue(true);
    }
}
