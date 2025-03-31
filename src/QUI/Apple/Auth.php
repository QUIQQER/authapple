<?php

/**
 * This file contains QUI\Apple
 */

namespace QUI\Apple;

use QUI;
use QUI\Exception;
use QUI\Interfaces\Users\User;
use QUI\Users\AbstractAuthenticator;
use QUI\Locale;
use Firebase\JWT\JWT;

/**
 * Class Auth
 *
 * Authentication handler for Apple authentication
 *
 * @package QUI\Apple\Auth
 */
class Auth extends AbstractAuthenticator
{
    protected QUI\Interfaces\Users\User | null $User = null;

    public function __construct(array | int | string $user = '')
    {
        if (!empty($user) && is_string($user)) {
            try {
                $this->User = QUI::getUsers()->getUserByName($user);
            } catch (\Exception) {
                $this->User = QUI::getUsers()->getNobody();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function auth(array | int | string $authParams): void
    {
        if (!is_array($authParams) || !isset($authParams['token'])) {
            throw new QUI\Exception([
                'quiqqer/authgoogle',
                'exception.auth.wrong.data'
            ], 401);
        }

        $token = $authParams['token'];
        $code = $authParams['code'];

        $clientId = Apple::getClientId();
        $teamId = Apple::getTeamId(); // Apple Developer Team-ID
        $keyId = Apple::getKeyId(); // Key-ID aus deinem Apple-Key (.p8)
        $privateKey = Apple::getPrivateKeyId(); // Key-ID aus deinem Apple-Key (.p8)


        $claims = [
            'iss' => $teamId,
            'iat' => time(),
            'exp' => time() + 86400 * 180,
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId
        ];

        $clientSecret = JWT::encode($claims, $privateKey, 'ES256', $keyId);
        $tokenUrl = 'https://appleid.apple.com/auth/token';

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => '', // @todo
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
    }

    public function getUser(): User
    {
        return $this->User;
    }

    public function getTitle(?Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'authapple.title');
    }

    public function getDescription(?Locale $Locale = null): string
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authapple', 'authapple.description');
    }

    public static function getLoginControl(): ?QUI\Control
    {
        return new QUI\Apple\Controls\Button();
    }
}
