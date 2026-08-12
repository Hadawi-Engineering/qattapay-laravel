<?php

namespace QattaPay\Laravel\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use QattaPay\Laravel\Support\Hosts;

class HostsTest extends TestCase
{
    public function test_resolve_api_hosts_dev(): void
    {
        $this->assertSame(
            [
                'https://dev.qatta.sa/api',
                'https://dev.hadawi.sa/api',
            ],
            Hosts::resolveApiHosts('dev')
        );
    }

    public function test_resolve_api_hosts_live(): void
    {
        $this->assertSame(
            [
                'https://qatta.sa/api',
                'https://beta.hadawi.sa/api',
            ],
            Hosts::resolveApiHosts('live')
        );
    }

    public function test_resolve_checkout_hosts(): void
    {
        $this->assertSame(
            [
                'https://qatta.sa',
                'https://beta.hadawi.sa',
            ],
            Hosts::resolveCheckoutHosts('live')
        );
    }

    public function test_resolve_api_base_url(): void
    {
        $this->assertSame('https://dev.qatta.sa/api', Hosts::resolveApiBaseUrl('dev'));
    }

    public function test_invalid_mode_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Hosts::resolveApiHosts('staging');
    }
}
