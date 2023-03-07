<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;

interface ReturnValueTransformer
{
    /**
     * @param ImmutableRecord|array<ImmutableRecord> $returnValue
     *
     * @return ImmutableRecord|array<ImmutableRecord>
     */
    public function __invoke(ImmutableRecord|array $returnValue, MockMethod|null $method = null): ImmutableRecord|array;
}
