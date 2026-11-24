<?php

namespace Tests\Actions\Contacts;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegister\RealtimeRegister;
use RealtimeRegister\Support\AuthorizedClient;
use RealtimeRegisterDomains\Actions\Contacts\ResendValidation;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Request;

class ResendValidationTest extends TestCase
{
    private array $history = [];

    protected function tearDown(): void
    {
        Cache::forget('resend-validation:example.nl');
        (new \ReflectionProperty(App::class, 'client'))->setValue(null, null);
    }

    public function testClientIsRateLimited(): void
    {
        $this->mockApi([$this->domainResponse(), new Response(202)]);

        $this->assertTrue($this->invoke(['cooldown' => true])['success']);
        $this->assertArrayHasKey('error', $this->invoke(['cooldown' => true]));
        $this->assertCount(1, $this->validateCalls());
    }

    public function testAdminIsNotRateLimited(): void
    {
        $this->mockApi([$this->domainResponse(), new Response(202), $this->domainResponse(), new Response(202)]);

        $this->assertTrue($this->invoke()['success']);
        $this->assertTrue($this->invoke()['success']);
        $this->assertCount(2, $this->validateCalls());
    }

    private function invoke(array $extra = []): array
    {
        $request = new Request(
            ['sld' => 'example', 'tld' => 'nl', 'id' => 7, 'customer_handle' => 'customer', ...$extra]
        );
        App::registrarConfig()->setRequest($request);

        $action = new class (App::instance()) extends ResendValidation {
            protected function metadata(Request $request): TLDMetaData
            {
                $metadata = (new \ReflectionClass(TLDMetaData::class))->newInstanceWithoutConstructor();
                $metadata->validationCategory = 'General';
                return $metadata;
            }
        };

        return $action($request);
    }

    private function validateCalls(): array
    {
        return array_filter(
            $this->history,
            fn($t) => str_ends_with($t['request']->getUri()->getPath(), '/contacts/registrant-1/validate')
        );
    }

    private function mockApi(array $responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));
        $authorized = new AuthorizedClient('https://api.test/', 'key');
        $authorized->setClient(new Client(['handler' => $stack, 'base_uri' => 'https://api.test/']));
        $client = new RealtimeRegister('key');
        $client->setClient($authorized);
        (new \ReflectionProperty(App::class, 'client'))->setValue(null, $client);
    }

    private function domainResponse(): Response
    {
        return new Response(200, [], json_encode([
            'domainName' => 'example.nl', 'registry' => 'sidn', 'customer' => 'customer',
            'registrant' => 'registrant-1', 'privacyProtect' => false, 'status' => ['PENDING_VALIDATION'],
            'autoRenew' => true, 'autoRenewPeriod' => 12, 'createdDate' => '2026-01-01T00:00:00Z',
            'expiryDate' => '2027-01-01T00:00:00Z', 'premium' => false,
        ]));
    }
}
