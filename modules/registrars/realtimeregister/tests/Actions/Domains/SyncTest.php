<?php

namespace Tests\Actions\Domains;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\Actions\Domains\Sync;
use RealtimeRegisterDomains\App;

class SyncTest extends TestCase
{
    public static function dueDateProvider(): array
    {
        return [
            'disabled (empty checkbox) preserves the next due date' => ['', '25', null],
            'disabled (null) preserves the next due date' => [null, '25', null],
            'enabled subtracts the configured days' => ['on', '25', '2027-09-10'],
            'enabled without days uses the expiry date' => ['on', '', '2027-10-05'],
            'enabled with zero days uses the expiry date' => ['on', '0', '2027-10-05'],
        ];
    }

    #[DataProvider('dueDateProvider')]
    public function testSyncDueDate(?string $syncEnabled, ?string $days, ?string $expected): void
    {
        $sync = $this->makeSync([
            'DomainSyncNextDueDate' => $syncEnabled,
            'DomainSyncNextDueDateDays' => $days,
        ]);

        $this->assertSame($expected, $sync->callSyncDueDate('2027-10-05'));
    }

    public static function autoRenewProvider(): array
    {
        return [
            'already on stays untouched' => [true, true, 12, false],
            'already off stays untouched' => [false, false, 12, false],
            'gets enabled' => [false, true, 12, true],
            'gets disabled' => [true, false, 12, true],
            'short period gets enabled' => [false, true, 1, true],
            'short period is not disabled' => [true, false, 1, false],
        ];
    }

    #[DataProvider('autoRenewProvider')]
    public function testAutoRenewNeedsUpdate(bool $current, bool $wanted, int $period, bool $expected): void
    {
        $this->assertSame($expected, $this->makeSync([])->callAutoRenewNeedsUpdate($current, $wanted, $period));
    }

    private function makeSync(array $config): object
    {
        $sync = new class (App::instance()) extends Sync {
            public array $settings = [];

            public function config(string $key, $default = null)
            {
                return ($this->settings[$key] ?? null) ?: $default;
            }

            public function callAutoRenewNeedsUpdate(bool $current, bool $wanted, int $period): bool
            {
                return $this->autoRenewNeedsUpdate($current, $wanted, $period);
            }

            public function callSyncDueDate(string $date): ?string
            {
                return $this->syncDueDate($date);
            }
        };
        $sync->settings = $config;

        return $sync;
    }
}
