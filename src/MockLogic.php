<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;

trait MockLogic
{
    public function __construct(private MockPersister $persister)
    {
    }

    /** @return array<MockMethod> */
    public function calls(): array
    {
        return $this->persister->calls();
    }

    /** @param ImmutableRecord|array<ImmutableRecord> $returnValue */
    public function withReturnValue(ImmutableRecord|array $returnValue): self
    {
        $this->persister->withReturnValue($returnValue);

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
}
