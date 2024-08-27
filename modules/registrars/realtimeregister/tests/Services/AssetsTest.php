<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use RealtimeRegister\App;
use RealtimeRegister\Services\Assets;

class AssetsTest extends TestCase
{
    public function testAddToHeader()
    {
        $asset = new Assets();
        $asset->addToHeader('Hello');
        $this->assertEquals('Hello', $asset->renderHead());
    }

    public function testAddFileToHeader()
    {
        $asset = new Assets();
        $asset->addScript('Hello.js');

        $this->assertEquals(
            '<script src="/modules/registrars/' . App::NAME . '/src/Assets/Js/Hello.js?' . App::VERSION . '"></script>',
            $asset->renderHead()
        );
    }

    public function testAddCssFileToHeader()
    {
        $asset = new Assets();
        $asset->addStyle('Hello.css');
        $this->assertEquals(
            '<link href="/modules/registrars/' . App::NAME . '/src/Assets/Css/Hello.css?'
            . App::VERSION . '" rel="stylesheet">',
            $asset->renderHead()
        );
    }

    public function testAddToFooter()
    {
        $asset = new Assets();
        $asset->addToFooter('Hello');
        $this->assertEquals('Hello', $asset->renderFooter());
    }
}
