<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Web\Controllers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PublicWhip\Services\HansardServiceInterface;
use PublicWhip\Web\Controllers\PingController;
use ReflectionException;
use Slim\Http\Response;

/**
 * PingControllerTest.
 *
 * @coversDefaultClass \PublicWhip\Web\Controllers\PingController
 */
final class PingControllerTest extends TestCase
{

    /**
     * @covers ::indexAction
     */
    public function testIndexAction(): void
    {
        $sut = new PingController();
        $mockResponse = new Response(200);
        $response = $sut->indexAction($mockResponse);
        self::assertSame($response, $mockResponse);
        $body = $response->getBody();
        $body->rewind();
        self::assertSame('ready', $body->getContents());
    }

    /**
     * @covers ::lastDivisionParsedAction
     * @throws ReflectionException
     */
    public function testLastDivisionParseAction(): void
    {
        $sut = new PingController();
        $mockResponse = new Response(200);

        /** @var HansardServiceInterface|MockObject $mockDivisionService */
        $mockDivisionService = $this->createMock(HansardServiceInterface::class);
        $mockDivisionService->method('getNewestDivisionDate')->willReturn('2018-01-23');

        $response = $sut->lastDivisionParsedAction($mockDivisionService, $mockResponse);
        self::assertSame($response, $mockResponse);
        $body = $response->getBody();
        $body->rewind();
        self::assertSame('2018-01-23', $body->getContents());
    }
}