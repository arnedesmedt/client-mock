<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use RuntimeException;

use function sprintf;

class MockMethod
{
    /** @var array<array<mixed>> */
    private array $parametersPerCall = [];
    /** @var array<mixed> */
    private array $returnValues = [];

    public function __construct(
        private readonly string $method,
    ) {
    }

    /** @param array<mixed> $parameters */
    public function addParameters(array $parameters): self
    {
        $this->parametersPerCall[] = $parameters;

        return $this;
    }

    public function merge(MockMethod $lastCall): self
    {
        if ($lastCall->method() !== $this->method) {
            throw new RuntimeException('Cannot merge calls with different methods');
        }

        $this->parametersPerCall = [...$this->parametersPerCall, ...$lastCall->parametersPerCall()];
        $this->returnValues = [...$this->returnValues, ...$lastCall->returnValues()];

        return $this;
    }

    /** @param ImmutableRecord|array<mixed>|bool $returnValue */
    public function addReturnValue(ImmutableRecord|array|bool|string $returnValue): self
    {
        $this->returnValues[] = $returnValue;

        return $this;
    }

    public function method(): string
    {
        return $this->method;
    }

    /** @return array<array<mixed>> */
    public function parametersPerCall(): array
    {
        return $this->parametersPerCall;
    }

    /** @return array<mixed> */
    public function returnValues(): array
    {
        return $this->returnValues;
    }

    public function removeIndex(int $index): void
    {
        if (! isset($this->parametersPerCall[$index]) || ! isset($this->returnValues[$index])) {
            throw new RuntimeException(
                sprintf(
                    'No parameters and return value index \'%d\' found for method %s',
                    $index,
                    $this->method,
                ),
            );
        }

        unset($this->parametersPerCall[$index]);
        unset($this->returnValues[$index]);
    }
}
