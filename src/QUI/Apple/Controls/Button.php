<?php

namespace QUI\Apple\Controls;

use QUI;

class Button extends QUI\Control
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setAttribute('nodeName', 'button');
        $this->setJavaScriptControl('package/quiqqer/authapple/bin/controls/Button');
        $this->addCSSClass('quiqqer-auth-apple-registration quiqqer-frontend-social-button');
    }

    public function getBody(): string
    {
        return '
            <span class="fa fa-brands fa-apple"></span>
            <span>'. QUI::getLocale()->get('quiqqer/authapple', 'button.title') . '</span>
        ';
    }
}
