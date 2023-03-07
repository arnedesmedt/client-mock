<?php

declare(strict_types=1);

namespace ADS\ClientMock\Tests\Unit\MockPersisterData;

use EventEngine\Data\ImmutableRecord;
use EventEngine\Data\ImmutableRecordLogic;

class TestImmutable implements ImmutableRecord
{
    use ImmutableRecordLogic;

    private string $test;
}
