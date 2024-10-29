<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Services\Basic;
use Yuhzel\Xaseco\Core\Xml\XmlParser;
use Yuhzel\Xaseco\Core\Xml\XmlArrayObject;

/**
 * Style plugin (TMF).
 * Selects ManiaLink window style templates.
 * Created by Xymph
 * Update by Yuhzel
 */
class Style
{
    public string $windowStyle = 'DarkBlur';
    public ?XmlArrayObject $style = null;

    public function __construct(
        private XmlParser $xmlParser
    ) {}

    public function onStartup(): void
    {
        $styleFile = "styles/{$this->windowStyle}.xml";
        Basic::console('Load default style [{1}]', $styleFile);
        $this->style = $this->xmlParser->parseXml($styleFile);
    }
}
