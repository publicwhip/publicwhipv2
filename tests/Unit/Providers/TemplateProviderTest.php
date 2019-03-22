<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use PublicWhip\Providers\TemplateProvider;
use ReflectionException;
use Slim\Views\Twig;

/**
 * WikiParserProviderTest.
 *
 * @coversDefaultClass \PublicWhip\Providers\TemplateProvider
 * @covers \PublicWhip\Providers\TemplateProvider::<!public>
 */
final class TemplateProviderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::render
     * @throws ReflectionException
     */
    public function testRender(): void
    {
        $twig = $this->createMock(Twig::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $outputResponse = $this->createMock(ResponseInterface::class);
        $twig->expects(self::once())->method('render')->with(
            $mockResponse,
            'test.twig',
            ['hello' => 'here']
        )->willReturn($outputResponse);
        $sut = new TemplateProvider($twig);
        self::assertSame($outputResponse, $sut->render($mockResponse, 'test.twig', ['hello' => 'here']));
    }

    /**
     * @covers ::__construct
     * @covers ::render
     * @throws ReflectionException
     */
    public function testRenderNoData(): void
    {
        $twig = $this->createMock(Twig::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $outputResponse = $this->createMock(ResponseInterface::class);
        $twig->expects(self::once())->method('render')->with(
            $mockResponse,
            'test.twig',
            []
        )->willReturn($outputResponse);
        $sut = new TemplateProvider($twig);
        self::assertSame($outputResponse, $sut->render($mockResponse, 'test.twig'));
    }
}
