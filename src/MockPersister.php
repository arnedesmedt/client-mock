<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use RuntimeException;

use function assert;
use function sprintf;

class MockPersister
{
    private MockMethod|null $lastCall = null;
    /** @var array<string, MockMethod> */
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

    /** @param ImmutableRecord|array<ImmutableRecord> $returnValue */
    public function withReturnValue(ImmutableRecord|array $returnValue): void
    {
        assert($this->lastCall instanceof MockMethod);

        $returnValue = $this->transformReturnValue($returnValue);

        $lastCall = $this->lastCall->addReturnValue($returnValue);

        if (isset($this->calls[$lastCall->method()])) {
            $lastCall = $this->calls[$lastCall->method()]->merge($lastCall);
        }

        $this->calls[$lastCall->method()] = $lastCall;

        $this->lastCall = null;
    }

    /** @return array<string, MockMethod> */
    public function calls(): array
    {
        return $this->calls;
    }

    /**
     * @param ImmutableRecord|array<ImmutableRecord> $returnValue
     *
     * @return ImmutableRecord|array<ImmutableRecord>
     */
    private function transformReturnValue(ImmutableRecord|array $returnValue): ImmutableRecord|array
    {
        if ($this->returnValueTransformer === null) {
            return $returnValue;
        }

        return ($this->returnValueTransformer)($returnValue, $this->lastCall);
    }

    public function removeMockMethods(string ...$methods): void
    {
        foreach ($methods as $method) {
            $this->needMockMethod($method);

            unset($this->calls[$method]);
        }
    }

    private function needMockMethod(string $method): MockMethod
    {
        if (! isset($this->calls[$method])) {
            throw new RuntimeException(
                sprintf(
                    'Mock method %s does not exist.',
                    $method,
                ),
            );
        }

        return $this->calls[$method];
    }

    public function removeMockMethodForIndex(string $method, int $index): void
    {
        $mockMethod = $this->needMockMethod($method);
        $mockMethod->removeIndex($index);
    }
}
