<?php
namespace PHPSTORM_META
{
    override(
        \Psr\Container\ContainerInterface::get(0),
        map([
            '' => '@',
        ])
    );
    override(
        \DI\Container::get(0),
        map([
            '' => '@',
        ])
    );
    override(
        \PHPUnit\Framework\TestCase::createMock(0),
        map([
            '' => '@|\PHPUnit\Framework\MockObject\MockObject',
        ])
    );
    override(
        \PHPUnit\Framework\TestCase::getMockForAbstractClass(0),
        map([
            '' => '@|\PHPUnit\Framework\MockObject\MockObject',
        ])
    );
    override(
        \PHPUnit_Framework_TestCase::createMock(0),
        map([
            '' => '@|\PHPUnit_Framework_MockObject_MockObject',
        ])
    );
    override(
        \PHPUnit_Framework_TestCase::getMockForAbstractClass(0),
        map([
            '' => '@|\PHPUnit_Framework_MockObject_MockObject',
        ])
    );
}