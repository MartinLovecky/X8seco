<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Core\Xml\XmlParser;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

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
