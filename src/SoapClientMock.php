<?php

declare(strict_types=1);

namespace ADS\ClientMock;

use Mockery;

use function is_array;
use function sprintf;

abstract class SoapClientMock extends ClientMock
{
    protected static function describeFactoryMock(
        Mockery\MockInterface $expectation,
    ): Mockery\ExpectationInterface|Mockery\HigherOrderMessage {
        return $expectation->shouldReceive('factory');
    }

    protected static function buildResponse(string $method, mixed $response): mixed
    {
        $addition = is_array($response) ? '->toArray' : '';
        $mockResponse = Mockery::mock('response');
        /** @var Mockery\Expectation $responseExpectation */
        $responseExpectation = $mockResponse->shouldReceive(sprintf('%sResult%s', $method, $addition));
        $responseExpectation->andReturn($response);

        return $mockResponse;
    }
}
