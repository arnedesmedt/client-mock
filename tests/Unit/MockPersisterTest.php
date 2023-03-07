<?php

declare(strict_types=1);

namespace ADS\ClientMock\Tests;

use ADS\ClientMock\MockMethod;
use ADS\ClientMock\MockPersister;
use ADS\ClientMock\Tests\Unit\MockPersisterData\TestImmutable;
use ADS\ClientMock\Tests\Unit\MockPersisterData\TestReturnValueTransformer;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function assert;

class MockPersisterTest extends TestCase
{
    private MockPersister $mockPersister;

    protected function setUp(): void
    {
        $this->mockPersister = new MockPersister(new TestReturnValueTransformer());
    }

    public function testInvoke(): void
    {
        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test']));

        $calls = $this->mockPersister->calls();
        $this->assertCount(1, $calls);

        $call = array_shift($calls);
        assert($call instanceof MockMethod);
        $this->assertEquals('foo', $call->method());
        $this->assertCount(1, $call->parametersPerCall());
    }

    public function testDoubleInvoke(): void
    {
        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test']));

        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test2']));

        $calls = $this->mockPersister->calls();
        $call = array_shift($calls);
        assert($call instanceof MockMethod);
        $this->assertCount(2, $call->parametersPerCall());
    }

    public function testInvokeWithoutReturn(): void
    {
        $this->expectExceptionMessageMatches('/You must call withReturnValue\(\) after calling a method\./');
        ($this->mockPersister)('foo', 'bar', 'baz');
        ($this->mockPersister)('foo', 'bar', 'baz');
    }
}
