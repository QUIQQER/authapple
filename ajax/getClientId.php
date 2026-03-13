<?php

/**
 * Get Apple API Client-ID
 *
 * @return string - Client-ID
 */

use QUI\Apple\Apple;

QUI::getAjax()->registerFunction(
    'package_quiqqer_authapple_ajax_getClientId',
    function () {
        return Apple::getClientId();
    }
);
