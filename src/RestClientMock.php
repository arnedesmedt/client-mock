<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use EventEngine\Data\ImmutableRecord;
use Mockery;
use ReflectionClass;
use ReflectionNamedType;

use function class_exists;

abstract class RestClientMock extends ClientMock
{
    protected static function describeFactoryMock(
        Mockery\MockInterface $expectation
    ): Mockery\ExpectationInterface|Mockery\HigherOrderMessage {
        /** @var Mockery\Expectation $withShouldReceive */
        $withShouldReceive = $expectation->shouldReceive('setClient');
        /** @var Mockery\MockInterface $withReturn */
        $withReturn = $withShouldReceive->andReturnSelf();

        return $withReturn->shouldReceive('build');
    }

    protected static function buildOneResponse(string $method, mixed $response): mixed
    {
        /** @var ReflectionNamedType|null $returnType */
        $returnType = static::reflectionMethod($method)->getReturnType();

        if ($returnType === null) {
            return $response;
        }

        $returnTypeClass = $returnType->getName();

        if (class_exists($returnTypeClass) && ! $response instanceof $returnTypeClass) {
            $returnTypeReflection = new ReflectionClass($returnTypeClass);

            if ($returnTypeReflection->implementsInterface(ImmutableRecord::class)) {
                $response = $returnTypeClass::fromArray($response);
            }
        }

        return $response;
    }
}
