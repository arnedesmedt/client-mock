<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use RuntimeException;

use function array_combine;
use function array_filter;
use function array_keys;
use function array_values;
use function end;
use function sprintf;

class MockPersister
{
    private MockMethod|null $lastCall = null;
    /** @var array<int, MockMethod> */
    private array $calls = [];

    public function __construct(private readonly ReturnValueTransformer|null $returnValueTransformer = null)
    {
    }

    public function __invoke(string $method, mixed ...$parameters): self
    {
        if ($this->lastCall !== null) {
            throw new RuntimeException('You must call withReturnValue() after calling a method.');
        }

        $this->lastCall = (new MockMethod($method))->addParameters($parameters);

        return $this;
    }

    /** @param ImmutableRecord|array<ImmutableRecord>|bool $returnValue */
    public function withReturnValue(ImmutableRecord|array|bool $returnValue): void
    {
        if (! $this->lastCall instanceof MockMethod) {
            throw new RuntimeException('You must call a method before calling withReturnValue().');
        }

        $returnValue = $this->transformReturnValue($returnValue);

        $this->calls[] = $this->lastCall->addReturnValue($returnValue);

        $this->lastCall = null;
    }

    /** @return array<string, MockMethod> */
    public function calls(): array
    {
        $callsPerMethod = [];
        foreach ($this->calls as $call) {
            if (isset($callsPerMethod[$call->method()])) {
                $call = $callsPerMethod[$call->method()]->merge($call);
            }

            $callsPerMethod[$call->method()] = $call;
        }

        return $callsPerMethod;
    }

    /**
     * @param ImmutableRecord|array<ImmutableRecord>|bool $returnValue
     *
     * @return ImmutableRecord|array<ImmutableRecord>|bool
     */
    private function transformReturnValue(ImmutableRecord|array|bool $returnValue): ImmutableRecord|array|bool
    {
        if ($this->returnValueTransformer === null) {
            return $returnValue;
        }

        return ($this->returnValueTransformer)($returnValue, $this->lastCall);
    }

    public function removeMockMethods(string ...$methods): void
    {
        foreach ($methods as $method) {
            foreach ($this->calls as $index => $call) {
                if ($call->method() !== $method) {
                    continue;
                }

                unset($this->calls[$index]);
            }
        }
    }

    /** @return array<MockMethod> */
    private function needMockMethods(string $method): array
    {
        $methodCalls = array_filter(
            $this->calls,
            static fn (MockMethod $call) => $call->method() === $method,
        );

        if (empty($methodCalls)) {
            throw new RuntimeException(
                sprintf(
                    'Mock method %s does not exist.',
                    $method,
                ),
            );
        }

        return $methodCalls;
    }

    public function removeMockMethodForIndex(string $method, int $index): void
    {
        $mockMethods = $this->needMockMethods($method);
        $originalKeys = array_keys($mockMethods);
        $newKeys = array_keys(array_values($mockMethods));
        $mapping = array_combine($newKeys, $originalKeys);

        unset($this->calls[$mapping[$index]]);
    }

    public function removeAllMocks(): void
    {
        $this->calls = [];
        $this->lastCall = null;
    }

    public function repeat(int $times = 1): void
    {
        if ($this->lastCall !== null || empty($this->calls)) {
            throw new RuntimeException('Can\'t call repeat() after calling a method or if no calling has done.');
        }

        $call = end($this->calls);

        for ($i = 0; $i < $times; $i++) {
            $this->calls[] = clone $call;
        }
    }
}
