<?php

/**
 * Check if a Apple account is connected to a QUIQQER user account
 *
 * @param string $idToken - Apple ID token
 * @return array|false - Details to connected Google account
 */

use QUI\Apple\Apple;

QUI::getAjax()->registerFunction(
    'package_quiqqer_authapple_ajax_isAppleAccountConnected',
    function ($idToken) {
        Apple::validateAccessToken($idToken);
        return Apple::existsQuiqqerAccount($idToken);
    },
    ['idToken']
);
