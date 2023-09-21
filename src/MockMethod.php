<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use RuntimeException;
use Throwable;

use function sprintf;

class MockMethod
{
    /** @var array<array<mixed>> */
    private array $parametersPerCall = [];
    /** @var array<array{type: string, value: mixed}> */
    private array $returnValuesOrExceptions = [];

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
        $this->returnValuesOrExceptions = [
            ...$this->returnValuesOrExceptions,
            ...$lastCall->returnValuesOrExceptions(),
        ];

        return $this;
    }

    /** @param ImmutableRecord|array<mixed>|bool $returnValue */
    public function addReturnValue(ImmutableRecord|array|bool|string $returnValue): self
    {
        $this->returnValuesOrExceptions[] = [
            'type' => 'return',
            'value' => $returnValue,
        ];

        return $this;
    }

    public function addException(Throwable $exception): self
    {
        $this->returnValuesOrExceptions[] = [
            'type' => 'exception',
            'value' => $exception,
        ];

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

    /** @return array<array{type: string, value: mixed}> */
    public function returnValuesOrExceptions(): array
    {
        return $this->returnValuesOrExceptions;
    }

    public function removeIndex(int $index): void
    {
        if (! isset($this->parametersPerCall[$index]) || ! isset($this->returnValuesOrExceptions[$index])) {
            throw new RuntimeException(
                sprintf(
                    'No parameters and return value index \'%d\' found for method %s',
                    $index,
                    $this->method,
                ),
            );
        }

        unset($this->parametersPerCall[$index]);
        unset($this->returnValuesOrExceptions[$index]);
    }
}
