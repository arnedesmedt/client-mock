<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;

interface Mock
{
    /** @return array<MockMethod> */
    public function calls(): array;

    public function mockInterface(): string;

    public function withReturnValue(ImmutableRecord $immutableRecord): self;
}