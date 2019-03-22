<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Providers;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PublicWhip\Providers\MailerTransportProvider;
use Swift_NullTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

/**
 * MailerTransportProviderTest.
 *
 * @coversDefaultClass \PublicWhip\Providers\MailerTransportProvider
 * @covers \PublicWhip\Providers\MailerTransportProvider::<!public>
 */
final class MailerTransportProviderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getTransport
     */
    public function testForNull(): void
    {
        $sut = new MailerTransportProvider(['transport' => 'null']);
        self::assertInstanceOf(Swift_NullTransport::class, $sut->getTransport());
    }

    /**
     * @covers ::getTransport
     */
    public function testForNullMultipleCallsShouldReturnSame(): void
    {
        $sut = new MailerTransportProvider(['transport' => 'null']);
        $first = $sut->getTransport();
        $second = $sut->getTransport();
        self::assertSame($first, $second);
    }

    /**
     * @covers ::__construct
     */
    public function testForUnrecognised(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unrecognised mail transport');
        new MailerTransportProvider([]);
    }

    /**
     * @covers ::__construct
     */
    public function testForSmtpNoHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing host for smtp transport');
        new MailerTransportProvider(['transport' => 'smtp', 'port' => '25', 'username' => 'test', 'password' => 'abc']);
    }

    /**
     * @covers ::__construct
     */
    public function testForSmtpNoPort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing port for smtp transport');
        new MailerTransportProvider([
            'transport' => 'smtp',
            'host' => 'example.com',
            'username' => 'test',
            'password' => 'abc'
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testForSmtpNoUsername(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing username for smtp transport');
        new MailerTransportProvider([
            'transport' => 'smtp',
            'host' => 'example.com',
            'port' => '25',
            'password' => 'abc'
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testForSmtpNoPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing password for smtp transport');
        new MailerTransportProvider([
            'transport' => 'smtp',
            'host' => 'example.com',
            'port' => '25',
            'username' => 'abc'
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::getTransport
     */
    public function testForSmtp(): void
    {
        $sut = new MailerTransportProvider([
            'transport' => 'smtp',
            'host' => 'example.com',
            'port' => '242',
            'username' => 'abc',
            'password' => 'test'
        ]);
        $transport = $sut->getTransport();
        self::assertInstanceOf(Swift_SmtpTransport::class, $transport);
        /** @var Swift_SmtpTransport $transport */
        self::assertSame('example.com', $transport->getHost());
        self::assertSame(242, $transport->getPort());
        self::assertSame('abc', $transport->getUsername());
        self::assertSame('test', $transport->getPassword());
    }

    /**
     * @covers ::__construct
     */
    public function testForSendNoCommand(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing command for sendmail transport');
        new MailerTransportProvider([
            'transport' => 'sendmail'
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::getTransport
     */
    public function testForSendmail(): void
    {
        $sut = new MailerTransportProvider([
            'transport' => 'sendmail',
            'command' => 'hello'
        ]);
        $transport = $sut->getTransport();
        self::assertInstanceOf(Swift_SendmailTransport::class, $transport);
        /** @var Swift_SendmailTransport $transport */
        self::assertSame('hello', $transport->getCommand());
    }
}
