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
}
