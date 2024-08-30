<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use RealtimeRegister\App;
use RealtimeRegister\Enums\ScriptLocationType;
use RealtimeRegister\Services\Assets;

class AssetsTest extends TestCase
{
    public function testAddFileToHeader()
    {
        $asset = new Assets();
        $asset->addScript('Hello.js');

        $this->assertEquals(
            '<script src="/modules/registrars/' . App::NAME . '/src/Assets/Js/Hello.js?' . App::VERSION . '"></script>',
            $asset->renderHead()
        );
        $this->assertEquals('', $asset->renderFooter());
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
        $this->assertEquals('', $asset->renderFooter());
    }

    public function testAddInlineToHeader()
    {
        $asset = new Assets();
        $asset->addToHeader('<script>alert(\'Hello\');</script>');
        $this->assertEquals('<script>alert(\'Hello\');</script>', $asset->renderHead());

        $this->assertEquals('', $asset->renderFooter());
    }

    public function testCssAndJavascriptHeader()
    {
        $asset = new Assets();
        $asset->addStyle('Hello.css');
        $asset->addScript('Hello.js');
        $this->assertEquals(
            '<link href="/modules/registrars/' . App::NAME . '/src/Assets/Css/Hello.css?'
            . App::VERSION . '" rel="stylesheet"><script src="/modules/registrars/' . App::NAME
            . '/src/Assets/Js/Hello.js?'
            . App::VERSION . '"></script>',
            $asset->renderHead()
        );

        $this->assertEquals('', $asset->renderFooter());
    }

    public function testAddFileToFooter()
    {
        $asset = new Assets();
        $asset->addScript('Hello.js', ScriptLocationType::Footer);

        $this->assertEquals(
            '<script src="/modules/registrars/' . App::NAME . '/src/Assets/Js/Hello.js?' . App::VERSION . '"></script>',
            $asset->renderFooter()
        );

        $this->assertEquals('', $asset->renderHead());
    }

    public function testAddFileToHeaderAndFooter()
    {
        $asset = new Assets();
        $asset->addScript('HelloFooter.js', ScriptLocationType::Footer);
        $asset->addScript('HelloHeader.js');

        $this->assertEquals(
            '<script src="/modules/registrars/' . App::NAME . '/src/Assets/Js/HelloFooter.js?'
            . App::VERSION . '"></script>',
            $asset->renderFooter()
        );

        $this->assertEquals(
            '<script src="/modules/registrars/' . App::NAME . '/src/Assets/Js/HelloHeader.js?'
            . App::VERSION . '"></script>',
            $asset->renderHead()
        );
    }
}
