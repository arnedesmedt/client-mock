<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;

interface ReturnValueTransformer
{
    /**
     * @param ImmutableRecord|array<ImmutableRecord>|bool $returnValue
     *
     * @return ImmutableRecord|array<ImmutableRecord>|bool
     */
    public function __invoke(
        ImmutableRecord|array|bool $returnValue,
        MockMethod|null $method = null,
    ): ImmutableRecord|array|bool;
}
