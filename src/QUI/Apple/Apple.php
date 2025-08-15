<?php

namespace QUI\Apple;

use QUI;
use QUI\ExceptionStack;
use QUI\Permissions\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class Apple
{
    public static function table(): string
    {
        return QUI::getDBTableName('quiqqer_auth_apple');
    }

    public static function getClientId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'clientId');
    }

    /**
     * Apple Developer Team-ID
     */
    public static function getTeamId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'teamId');
    }

    /**
     * Key-ID from Apple-Key (.p8)
     */
    public static function getKeyId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'keyId');
    }

    /**
     * Private Key (.p8)
     */
    public static function getPrivateKeyId(): string
    {
        return QUI::getPackage('quiqqer/authapple')->getConfig()->get('apiSettings', 'privateKeyId');
    }

    /**
     * @throws ExceptionStack
     * @throws QUI\Exception
     * @throws Exception
     * @throws QUI\Users\Exception
     * @throws QUI\Database\Exception
     */
    public static function connectQuiqqerAccount(
        int | string $uid,
        string $accessToken,
        bool $checkPermission = true
    ): void {
        if ($checkPermission !== false) {
            self::checkEditPermission($uid);
        }

        $User = QUI::getUsers()->get($uid);
        $profileData = self::getProfileData($accessToken);

        if (self::existsQuiqqerAccount($accessToken)) {
            throw new QUI\Exception([
                'quiqqer/authapple',
                'exception.apple.account_already_connected',
                ['email' => $profileData['email']]
            ]);
        }

        self::validateAccessToken($accessToken);

        QUI::getDataBase()->insert(
            self::table(),
            [
                'userId' => $User->getUUID(),
                'appleSub' => $profileData['sub'],
                'email' => $profileData['email'],
                'name' => $profileData['email']
            ]
        );

        $User->enableAuthenticator(
            Auth::class,
            QUI::getUsers()->getSystemUser()
        );
    }

    /**
     * @throws Exception
     * @throws QUI\Database\Exception
     */
    public static function getConnectedAccountByToken(string $idToken): bool | array
    {
        self::validateAccessToken($idToken);
        $profile = self::getProfileData($idToken);

        $result = QUI::getDataBase()->fetch([
            'from' => self::table(),
            'where' => [
                'appleSub' => $profile['sub']
            ]
        ]);

        if (empty($result)) {
            return false;
        }

        return current($result);
    }

    /**
     * @throws Exception
     * @throws QUI\Database\Exception
     */
    public static function disconnectAccount(
        int | string $userId,
        bool $checkPermission = true
    ): void {
        if ($checkPermission !== false) {
            self::checkEditPermission($userId);
        }

        try {
            $User = QUI::getUsers()->get($userId);
            $userUuid = $User->getUUID();
        } catch (QUI\Exception) {
            return;
        }

        QUI::getDataBase()->delete(
            self::table(),
            ['userId' => $userUuid]
        );
    }

    /**
     * @throws Exception
     */
    public static function checkEditPermission($userId): void
    {
        if (QUI::getUserBySession()->getUUID() === QUI::getUsers()->getSystemUser()->getUUID()) {
            return;
        }

        if (QUI::getSession()->get('uid') !== $userId || !$userId) {
            throw new QUI\Permissions\Exception(
                QUI::getLocale()->get(
                    'quiqqer/authgoogle',
                    'exception.operation.only.allowed.by.own.user'
                ),
                401
            );
        }
    }

    /**
     * Checks if a Google API access token is valid and if the user has provided
     * the necessary information (email)
     *
     * @return void
     * @throws Exception
     */
    public static function validateAccessToken(string $idToken): void
    {
        // verify
        $apple_keys_url = 'https://appleid.apple.com/auth/keys';
        $keys = json_decode(file_get_contents($apple_keys_url), true);
        $jwk = JWK::parseKeySet($keys);

        // Extrahiere den "kid" aus dem Token-Header, um den richtigen Key zu wählen
        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $idToken)[0])), true);
        $key = $jwk[$header['kid']] ?? null;

        if (!$key) {
            throw new Exception('Apple public key not found for this token');
        }

        try {
            $payload = JWT::decode($idToken, $key);;
        } catch (\Exception $e) {
            QUI\System\Log::addError($e->getMessage());

            throw new Exception([
                'quiqqer/authapple',
                'exception.apple.invalid.token'
            ]);
        }

        if (!isset($payload->aud) || $payload->aud != self::getClientId()) {
            throw new Exception([
                'quiqqer/authapple',
                'exception.apple.invalid.token'
            ]);
        }
    }

    public static function existsQuiqqerAccount(string $idToken): bool
    {
        $data = self::getProfileData($idToken);
        $appleSub = $data['sub'] ?? null;

        if (empty($appleSub)) {
            return false;
        }

        $result = QUI::getDataBase()->fetch([
            'from' => self::table(),
            'where' => [
                'appleSub' => $appleSub
            ],
            'limit' => 1
        ]);

        if (empty($result)) {
            return false;
        }

        $userId = $result[0]['userId'];

        if (empty($userId)) {
            return false;
        }

        try {
            $user = QUI::getUsers()->get($userId);
        } catch (QUI\Exception) {
            return false;
        }

        try {
            $user->getAuthenticator(Auth::class);
            return true;
        } catch (QUI\Exception) {
        }

        try {
            // add authenticator
            $user->enableAuthenticator(Auth::class, QUI::getUsers()->getSystemUser());
        } catch (QUI\Exception) {
            return false;
        }

        return true;
    }

    /**
     * @throws QUI\Users\Exception
     */
    public static function getUserByToken($idToken): QUI\Interfaces\Users\User
    {
        $data = self::getProfileData($idToken);
        $appleSub = $data['sub'] ?? null;

        if (empty($appleSub)) {
            throw new QUI\Users\Exception(
                QUI::getLocale()->get(
                    'quiqqer/core',
                    'exception.lib.user.wrong.uid'
                ),
                404
            );
        }

        try {
            $result = QUI::getDataBase()->fetch([
                'from' => self::table(),
                'where' => [
                    'appleSub' => $appleSub
                ],
                'limit' => 1
            ]);

            if (isset($result[0]['userId'])) {
                return QUI::getUsers()->get($result[0]['userId']);
            }
        } catch (\Exception $e) {
            QUI\System\Log::addError($e->getMessage());
        }

        throw new QUI\Users\Exception(
            QUI::getLocale()->get(
                'quiqqer/core',
                'exception.lib.user.wrong.uid'
            ),
            404
        );
    }

    public static function getProfileData($idToken)
    {
        /*
        {
          "iss": "https://appleid.apple.com",
          "aud": "deine-client-id",
          "exp": 1699999999,
          "iat": 1699999000,
          "sub": "0012345.a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6.1234",
          "email": "user@example.com",
          "email_verified": "true",
          "is_private_email": "false",
          "auth_time": 1699999000,
          "given_name": "Max",          // nur beim ersten Login!
          "family_name": "Mustermann"   // nur beim ersten Login!
        }
        */

        $parts = explode('.', $idToken);
        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload); // base64url zu base64
        $payload = base64_decode($payload);

        return json_decode($payload, true);
    }
}
