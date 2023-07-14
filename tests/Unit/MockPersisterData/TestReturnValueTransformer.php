<?php

declare(strict_types=1);

namespace ADS\ClientMock\Tests\Unit\MockPersisterData;

use ADS\ClientMock\MockMethod;
use ADS\ClientMock\ReturnValueTransformer;
use EventEngine\Data\ImmutableRecord;

class TestReturnValueTransformer implements ReturnValueTransformer
{
    public function __invoke(
        ImmutableRecord|array|bool|string $returnValue,
        MockMethod|bool|null $method = null,
    ): ImmutableRecord|array|bool|string {
        return $returnValue;
    }
}
