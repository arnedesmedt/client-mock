<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

use function array_map;
use function count;

trait MockLogic
{
    public function __construct(private MockPersister $persister)
    {
    }

    /** @return array<string, MockMethod> */
    public function calls(): array
    {
        return $this->persister->calls();
    }

    /** @param ImmutableRecord|array<ImmutableRecord>|bool|string $returnValue */
    public function withReturnValue(ImmutableRecord|array|bool|string $returnValue): self
    {
        $this->persister->withReturnValue($returnValue);

        return $this;
    }

    public function withException(Throwable $exception): self
    {
        $this->persister->withException($exception);

        return $this;
    }

    public function repeat(int $times = 1): self
    {
        $this->persister->repeat($times);

        return $this;
    }

    public function removeMockMethods(string ...$methods): self
    {
        $this->persister->removeMockMethods(...$methods);

        return $this;
    }

    public function removeAllMocks(): self
    {
        $this->persister->removeAllMocks();

        return $this;
    }

    public function removeMockMethodForIndex(string $method, int $index): self
    {
        $this->persister->removeMockMethodForIndex($method, $index);

        return $this;
    }

    public function build(TestCase $testCase, MockObject $mock): void
    {
        foreach ($this->calls() as $call) {
            $parameters = $call->parametersPerCall();
            $matcher = $testCase->exactly(count($parameters));
            $returnValuesOrExceptions = $call->returnValuesOrExceptions();

            $invocationMocker = $mock
                ->expects($matcher)
                ->method($call->method());

            foreach ($returnValuesOrExceptions as $index => $returnValueOrException) {
                $isReturnValue = $returnValueOrException['type'] === 'return';

                $invocationMocker
                    ->with(...array_map(
                        static fn ($parameter) => $testCase->equalTo($parameter),
                        $parameters[$index],
                    ));

                $willMethod = $isReturnValue
                    ? 'willReturn'
                    : 'willThrowException';

                $invocationMocker->{$willMethod}($returnValueOrException['value']);
            }
        }
    }
}
