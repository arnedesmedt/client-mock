<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use RuntimeException;

use function assert;

class MockPersister
{
    private MockMethod|null $lastCall = null;
    /** @var array<MockMethod> */
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

    /** @return array<MockMethod> */
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
}
