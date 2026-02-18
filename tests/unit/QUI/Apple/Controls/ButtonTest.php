<?php

namespace QUI\Apple\Controls;

use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    public function testGetBodyContainsAppleIconAndTextContainer(): void
    {
        $button = new Button();
        $body = $button->getBody();

        $this->assertStringContainsString('fa fa-brands fa-apple', $body);
        $this->assertStringContainsString('<span>', $body);
    }
}
