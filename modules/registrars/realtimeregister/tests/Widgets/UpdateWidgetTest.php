<?php

namespace Widgets;

use PHPUnit\Framework\TestCase;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Widget\UpdateWidget;

class UpdateWidgetTest extends TestCase
{
    public function setUp(): void
    {
        $this->widget = $this->createPartialMock(UpdateWidget::class, ['deleteCurrentReference']);
    }

    public function testNewRelease()
    {
        $this->widget->expects($this->never())
            ->method('deleteCurrentReference');

        $this->assertNotNull(
            $this->widget->generateOutput(
                [
                    'updates_available' => [
                        (object)[
                            'version' => '1337.0.1',
                            'prerelease' => false,
                            'description' => 'Test Updates',
                            'link' => 'https://example.com/updates',
                        ]
                    ]
                ]
            )
        );
    }

    public function testSameRelease()
    {
        $this->widget->expects($this->once())
            ->method('deleteCurrentReference');

        $this->assertNull(
            $this->widget->generateOutput(
                [
                    'updates_available' => [
                        (object)[
                            'version' => App::VERSION,
                            'prerelease' => false,
                            'description' => 'Test Updates',
                            'link' => 'https://example.com/updates',
                        ]
                    ]
                ]
            )
        );
    }

    public function testNoUpdates()
    {
        $this->widget->expects($this->once())
            ->method('deleteCurrentReference');

        $this->assertNull(
            $this->widget->generateOutput(
                [
                    'updates_available' => [
                        (object)[
                            'version' => 'v1.0.0',
                            'prerelease' => false,
                            'description' => 'Test Updates',
                            'link' => 'https://example.com/updates',
                        ]
                    ]
                ]
            )
        );
    }
}
