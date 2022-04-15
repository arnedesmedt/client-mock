<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use ADS\Util\ArrayUtil;
use ADS\ValueObjects\ValueObject;
use EventEngine\Data\ImmutableRecord;
use Mockery;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function array_map;
use function gettype;
use function is_array;
use function is_scalar;
use function json_encode;
use function method_exists;
use function preg_match;
use function preg_quote;
use function sprintf;
use function str_replace;

abstract class ClientMock
{
    public const COULD_BE_ANYTHING = '__**__';
    public const MULTIPLE_RETURNS = '__multiple_returns__';

    protected static Mockery\MockInterface|Mockery\LegacyMockInterface|null $client = null;

    protected static function client(): Mockery\LegacyMockInterface|Mockery\MockInterface
    {
        if (static::$client) {
            return static::$client;
        }

        static::$client = Mockery::mock('fakeClient');

        Mockery::getConfiguration()->setObjectFormatter(
            ImmutableRecord::class,
            static fn ($object) => ['properties' => $object->toArray()]
        );

        /** @var Mockery\ExpectationInterface  $clientFactory */
        $clientFactory = static::clientFactory();
        $clientFactory->andReturn(static::$client);

        return static::$client;
    }

    protected static function clientFactory(): Mockery\ExpectationInterface|Mockery\HigherOrderMessage
    {
        /** @var Mockery\MockInterface $expectation */
        $expectation = Mockery::mock(
            sprintf(
                'overload:%s',
                static::factoryClass()
            )
        );

        return static::describeFactoryMock($expectation);
    }

    public static function clearClientMock(): void
    {
        static::$client = null;
    }

    abstract protected static function describeFactoryMock(
        Mockery\MockInterface $expectation
    ): Mockery\ExpectationInterface|Mockery\HigherOrderMessage;

    public static function mockCall(string $method, mixed $response = null, mixed ...$requestParameters): void
    {
        $client = self::client();
        $clientClass = static::clientClass();

        if (! method_exists($clientClass, $method)) {
            throw new RuntimeException(
                sprintf(
                    'Can\'t mock method \'%s\' for class \'%s\' because it doesn\'t exists.',
                    $method,
                    $clientClass
                )
            );
        }

        /** @var Mockery\Expectation $requestExpectation */
        $requestExpectation = $client->shouldReceive($method);

        if (! empty($requestParameters)) {
            $arguments = array_map(
                static fn ($requestParameter) => Mockery::on(
                    static function ($requestParameterOfCall) use ($requestParameter) {
                        return static::compareRequest($requestParameterOfCall, $requestParameter);
                    }
                ),
                $requestParameters
            );

            $requestExpectation->with(...$arguments);
        }

        $requestExpectation->andReturnValues(static::buildResponse($method, $response));
    }

    protected static function compareRequest(mixed $requestParameterOfCall, mixed $requestParameterOfMock): bool
    {
        if ($requestParameterOfCall instanceof ImmutableRecord) {
            $requestParameterOfCall = $requestParameterOfCall->toArray();
        }

        if ($requestParameterOfMock instanceof ImmutableRecord) {
            $requestParameterOfMock = $requestParameterOfMock->toArray();
        }

        if ($requestParameterOfCall instanceof ValueObject) {
            $requestParameterOfCall = $requestParameterOfCall->toValue();
        }

        if ($requestParameterOfMock instanceof ValueObject) {
            $requestParameterOfMock = $requestParameterOfMock->toValue();
        }

        if (gettype($requestParameterOfCall) !== gettype($requestParameterOfMock)) {
            return false;
        }

        if (is_array($requestParameterOfCall) && is_array($requestParameterOfMock)) {
            return self::sameArrays($requestParameterOfCall, $requestParameterOfMock);
        }

        if (is_scalar($requestParameterOfCall)) {
            return $requestParameterOfCall === $requestParameterOfMock;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $first
     * @param array<string, mixed> $second
     */
    private static function sameArrays(array $first, array $second): bool
    {
        ArrayUtil::ksortRecursive($first);
        ArrayUtil::ksortRecursive($second);

        $encodedFirst = json_encode($first);
        $encodedSecond = json_encode($second);

        if ($encodedFirst === false || $encodedSecond === false) {
            return false;
        }

        $quotedSecond = str_replace(['"__\*\*__"', '__\*\*__'], '.*', preg_quote($encodedSecond, '/'));

        return (bool) preg_match('/' . $quotedSecond . '/', $encodedFirst);
    }

    /**
     * @return array<mixed>
     */
    protected static function buildResponse(string $method, mixed $response): array
    {
        if (
            is_array($response)
            && isset($response[self::MULTIPLE_RETURNS])
            && ! ArrayUtil::isAssociative($response[self::MULTIPLE_RETURNS])
        ) {
            return array_map(
                static fn ($oneResponse) => static::buildOneResponse($method, $oneResponse),
                $response[self::MULTIPLE_RETURNS]
            );
        }

        return [static::buildOneResponse($method, $response)];
    }

    abstract protected static function buildOneResponse(string $method, mixed $response): mixed;

    /**
     * @return class-string
     */
    abstract protected static function factoryClass(): string;

    /**
     * @return class-string
     */
    abstract protected static function clientClass(): string;

    /**
     * @return ReflectionClass<object>|null
     */
    protected static function clientReflectionClass(): ?ReflectionClass
    {
        return new ReflectionClass(static::clientClass());
    }

    protected static function reflectionMethod(string $method): ReflectionMethod
    {
        /** @var ReflectionClass<object> $clientReflectionClass */
        $clientReflectionClass = static::clientReflectionClass();

        if (! $clientReflectionClass->hasMethod($method)) {
            throw new RuntimeException(
                sprintf(
                    'Method \'%s\' not found in class \'%s\'.',
                    $method,
                    $clientReflectionClass->getName()
                )
            );
        }

        return $clientReflectionClass->getMethod($method);
    }

    /**
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        self::mockCall($name, ...$arguments);
    }
}
