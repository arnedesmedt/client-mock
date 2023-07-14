<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;

interface ReturnValueTransformer
{
    /**
     * @param ImmutableRecord|array<ImmutableRecord>|bool|string $returnValue
     *
     * @return ImmutableRecord|array<ImmutableRecord>|bool|string
     */
    public function __invoke(
        ImmutableRecord|array|bool|string $returnValue,
        MockMethod|null $method = null,
    ): ImmutableRecord|array|bool|string;
}
