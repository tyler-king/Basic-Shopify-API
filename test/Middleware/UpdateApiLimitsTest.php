<?php

namespace Osiset\BasicShopifyAPI\Test\Middleware;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Psr\Http\Message\RequestInterface;
use Osiset\BasicShopifyAPI\Test\BaseTest;
use Osiset\BasicShopifyAPI\Middleware\UpdateApiLimits;

class UpdateApiLimitsTest extends BaseTest
{
    public function testRestRuns(): void
    {
        // Create the client
        $api = $this->buildClient([]);

        // Create the middleware instance
        $mw = new UpdateApiLimits($api);

        // Ensure its empty
        $previous = $api->getRestClient()->getLimitStore()->get();

        // Run a request
        $promise = $mw(
            function (RequestInterface $request, array $options) {
                $promise = new Promise();
                $promise->resolve(
                    new Response(
                        200,
                        [
                            BasicShopifyAPI::HEADER_REST_API_LIMITS => '79/80'
                        ]
                    )
                );

                return $promise;
            }
        )(
            new Request('GET', '/admin/shop.json'),
            []
        );
        $promise->wait();

        // Check we have timestamp now
        $this->assertNotEquals(
            $previous,
            $api->getRestClient()->getLimitStore()->get()
        );
    }

    public function testGraphRuns(): void
    {
        // Create the client
        $api = $this->buildClient([]);

        // Create the middleware instance
        $mw = new UpdateApiLimits($api);

        // Ensure its empty
        $previous = $api->getGraphClient()->getLimitStore()->get();

        // Run a request
        $promise = $mw(
            function (RequestInterface $request, array $options) {
                $promise = new Promise();
                $promise->resolve(
                    new Response(
                        200,
                        [],
                        file_get_contents(__DIR__.'/../fixtures/graphql/shop_products.json')
                    ));

                return $promise;
            }
        )(
            new Request('GET', '/admin/api/graphql.json'),
            []
        );
        $promise->wait();

        // Check we have timestamp now
        $this->assertNotEquals(
            $previous,
            $api->getGraphClient()->getLimitStore()->get()
        );
    }
}