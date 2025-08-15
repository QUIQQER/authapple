<?php

namespace QUI\Apple\Controls;

use QUI;

class Button extends QUI\Control
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setAttribute('nodeName', 'button');
        $this->setAttribute('disabled', true);
        $this->setJavaScriptControl('package/quiqqer/authapple/bin/controls/Button');
        $this->addCSSClass('quiqqer-auth-apple-registration');
        $this->addCSSClass('quiqqer-frontend-social-button');

        $cssFile = OPT_DIR . 'quiqqer/authapple/bin/controls/Button.css';

        if (file_exists($cssFile)) {
            $this->addCSSFile($cssFile);
        }
    }

    public function getBody(): string
    {
        return '
            <span class="fa fa-brands fa-apple"></span>
            <span>' . QUI::getLocale()->get('quiqqer/authapple', 'button.title') . '</span>
        ';
    }
}
