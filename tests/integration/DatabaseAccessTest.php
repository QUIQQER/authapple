<?php

namespace QUITests\Apple\Integration;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Apple\Apple;
use Throwable;

class DatabaseAccessTest extends TestCase
{
    private ?string $userUuid = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!QUI::getSchemaManager()->tablesExist([Apple::table()])) {
            self::markTestSkipped('The Apple authentication database table is not installed.');
        }
    }

    protected function tearDown(): void
    {
        if ($this->userUuid !== null) {
            try {
                QUI::getDataBaseConnection()->delete(
                    QUI\Utils\Doctrine::quoteIdentifier(Apple::table()),
                    [QUI\Utils\Doctrine::quoteIdentifier('userId') => $this->userUuid]
                );

                QUI::getUsers()->deleteUser($this->userUuid);
            } catch (Throwable) {
            }
        }

        parent::tearDown();
    }

    public function testDisconnectAccountDeletesAppleConnectionThroughDbal(): void
    {
        $Users = QUI::getUsers();
        $SystemUser = $Users->getSystemUser();
        $suffix = bin2hex(random_bytes(8));
        $username = 'phpunit-authapple-' . $suffix;

        try {
            $User = $Users->createChildWithAttributes([
                'username' => $username,
                'email' => $username . '@example.invalid',
                'firstname' => 'Apple',
                'lastname' => 'DBAL'
            ], $SystemUser);
        } catch (Throwable $Exception) {
            self::markTestSkipped('No usable super-user fixture is available: ' . $Exception->getMessage());
        }

        $this->userUuid = $User->getUUID();
        $appleSub = 'phpunit-apple-sub-' . $suffix;
        $Connection = QUI::getDataBaseConnection();

        $Connection->insert(
            QUI\Utils\Doctrine::quoteIdentifier(Apple::table()),
            [
                QUI\Utils\Doctrine::quoteIdentifier('userId') => $this->userUuid,
                QUI\Utils\Doctrine::quoteIdentifier('appleSub') => $appleSub,
                QUI\Utils\Doctrine::quoteIdentifier('email') => $username . '@example.invalid',
                QUI\Utils\Doctrine::quoteIdentifier('name') => 'Apple DBAL'
            ]
        );

        $QueryBuilder = QUI::getQueryBuilder();
        $storedUserUuid = $QueryBuilder
            ->select('userId')
            ->from(QUI\Utils\Doctrine::quoteIdentifier(Apple::table()))
            ->where($QueryBuilder->expr()->eq('appleSub', ':appleSub'))
            ->setParameter('appleSub', $appleSub)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        self::assertSame($this->userUuid, $storedUserUuid);

        Apple::disconnectAccount($this->userUuid, false);

        $QueryBuilder = QUI::getQueryBuilder();
        $storedUserUuid = $QueryBuilder
            ->select('userId')
            ->from(QUI\Utils\Doctrine::quoteIdentifier(Apple::table()))
            ->where($QueryBuilder->expr()->eq('appleSub', ':appleSub'))
            ->setParameter('appleSub', $appleSub)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        self::assertFalse($storedUserUuid);
    }
}
