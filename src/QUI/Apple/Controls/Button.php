<?php

namespace QUI\Apple\Controls;

use QUI;

class Button extends QUI\Control
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setJavaScriptControl('package/quiqqer/authapple/bin/controls/Button');
    }
}
