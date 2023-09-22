<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

use function array_slice;
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
            $parametersPerCall = $call->parametersPerCall();
            $returnValuesOrExceptions = $call->returnValuesOrExceptions();

            $matcher = $testCase->exactly(count($parametersPerCall));
            $mock
                ->expects($matcher)
                ->method($call->method())
                ->willReturnCallback(
                    static function (...$parameters) use (
                        $testCase,
                        $matcher,
                        $parametersPerCall,
                        $returnValuesOrExceptions,
                    ) {
                        $invocation = $matcher->getInvocationCount();
                        $expectedParameters = $parametersPerCall[$invocation - 1];
                        $returnOrException = $returnValuesOrExceptions[$invocation - 1];

                        if (count($parameters) > count($expectedParameters)) {
                            $parameters = array_slice($parameters, 0, count($expectedParameters));
                        }

                        $testCase->assertEquals($expectedParameters, $parameters);

                        if ($returnOrException['type'] === 'exception') {
                            throw $returnOrException['value'];
                        }

                        return $returnOrException['value'];
                    },
                );
        }
    }
}
