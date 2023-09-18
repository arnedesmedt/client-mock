<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

interface Mock
{
    /** @return array<MockMethod> */
    public function calls(): array;

    /** @return class-string */
    public function mockInterface(): string;

    public function withReturnValue(ImmutableRecord $immutableRecord): self;

    public function build(TestCase $testCase, MockObject $mock): void;
}
