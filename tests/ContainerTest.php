<?php

declare(strict_types=1);

namespace UMA\DIC\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

class ContainerTest extends TestCase
{
    public function testSetGet(): void
    {
        $sut = new Container();
        $sut->set('foo', 'bar');
        self::assertTrue($sut->has('foo'));
        self::assertSame('bar', $sut->get('foo'));

        $sut = new Container(['baz' => 'qux']);
        self::assertTrue($sut->has('baz'));
        self::assertSame('qux', $sut->get('baz'));
    }

    public function testProvider(): void
    {
        $provider = new class() implements ServiceProvider {
            public function provide(Container $c): void
            {
                $c->set('foo', 'bar');
            }
        };

        $sut = new Container();
        $sut->register($provider);

        self::assertTrue($sut->has('foo'));
        self::assertSame('bar', $sut->get('foo'));
    }

    public function testResolved(): void
    {
        $sut = new Container([
            'foo' => 'bar',
            'baz' => static function (Container $c): string {
                return 'qux'.$c->get('foo');
            },
        ]);

        self::assertTrue($sut->resolved('foo'));
        self::assertFalse($sut->resolved('baz'));

        self::assertSame('quxbar', $sut->get('baz'));
        self::assertTrue($sut->resolved('baz'));
    }

    public function testGetException(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $sut = new Container();
        $sut->get('foo');
    }

    public function testResolvedException(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $sut = new Container();
        $sut->resolved('foo');
    }

    public function testInvokableObjectIsNotRunAtRetrieval(): void
    {
        $sut = new Container();
        $sut->set('foo', $invokable = new class() {
            public function __invoke(): string
            {
                return 'bar';
            }
        });

        self::assertTrue($sut->resolved('foo'));
        self::assertNotSame('bar', $retrieved = $sut->get('foo'));
        self::assertSame($invokable, $retrieved);
    }
}
