<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Core\Xml\{XmlArrayObject, XmlParser};

/**
 * Fufi Menu Plugin for XASECO by oorf-fuckfish
 * Version 0.36
 *
 * Updated by Yuhzel
 */
class FufiMenu
{
    /**
     * Undocumented variable
     *
     * @var XmlArrayObject|null
     * @internal This property is for internal use only.
     */
    private ?XmlArrayObject $xmlData = null;
    // @phpstan-ignore-next-line
    private array $styles = [];
    // @phpstan-ignore-next-line
    private array $blocks = [];
    // private int $manialinkID = 383;
    // private int $uniqueID = 1001;
    // private string $id = '0000';
    // private bool $firstChallenge = true;
    // private array $entries = [];
    // private float $separatorheight = 0.75;
    // private float $posx = -65.0;
    // private int $posy = 47;
    // private int $width = 8;
    // private float $height = 2.2;
    // private int $horientation = 1;
    // private int $vorientation = 1;
    // private string $caption = "   Menu";
    // private int $menutimeout = 13000;

    public function __construct(private XmlParser $xmlParser) {}

    public function onStartup()
    {
        $this->xmlData = $this->xmlParser->parseXml('fufi_menu_config.xml');

        $this->loadSettings();
        $this->loadStyles();
        $this->loadEntries();
    }

    /**
     * Loads settings from the `$xmlData` array and assigns them to the respective class attributes.
     *
     * - If `$xmlData` is not empty, it processes settings.
     *
     * @return void
     */
    private function loadSettings(): void
    {
        $filePath = "{$this->xmlParser->xmlPath}fufi_menu.xml";
        $xmlBlocks = @file_get_contents($filePath);

        $this->blocks = $this->getXMLTemplateBlocks($xmlBlocks);
    }

    /**
     * Extracts XML template blocks from a given XML string.
     *
     * This method scans the input XML string for XML template block markers (e.g., `<!--start_blockname-->`),
     * extracts the blocks, and returns them as an associative array where the key is the block name and the value
     * is the XML content of that block.
     *
     * Modernized using:
     * - `str_contains` for substring checks instead of `strstr`.
     * - Improved readability and error handling.
     *
     * @param string $xml The XML string containing the template blocks.
     * @return array An associative array where keys are block names and values are the corresponding XML content.
     */
    private function getXMLTemplateBlocks(string $xml): array
    {
        $result = [];
        $xmlCopy = $xml;

        while (str_contains($xmlCopy, '<!--start_')) {
            $startPos = strpos($xmlCopy, '<!--start_') + 10;
            $xmlCopy = substr($xmlCopy, $startPos);

            $endPos = strpos($xmlCopy, '-->');
            $title = substr($xmlCopy, 0, $endPos);

            $result[$title] = trim($this->getXMLBlock($xml, $title));

            // Move past the end comment
            $xmlCopy = substr($xmlCopy, $endPos + 3);
        }

        return $result;
    }

    /**
     * Extracts a specific XML block from a given XML string based on block markers.
     *
     * This method finds the XML content between the start and end markers for a specific block.
     * It uses string position functions to locate the start and end markers, then slices the XML content.
     *
     * Modernized using:
     * - `strpos` to locate start and end markers directly.
     * - Improved error handling for cases where markers are not found.
     *
     * @param string $haystack The XML string from which the block is extracted.
     * @param string $caption The name of the block to extract.
     * @return string The XML content of the specified block.
     */
    private function getXMLBlock($haystack, $caption): string
    {
        $startStr = "<!--start_$caption-->";
        $endStr = "<!--end_$caption-->";

        $startPos = strpos($haystack, $startStr);
        if ($startPos === false) {
            return ''; // Handle error or unexpected state
        }

        $startPos += strlen($startStr);
        $endPos = strpos($haystack, $endStr, $startPos);
        if ($endPos === false) {
            return ''; // Handle error or unexpected state
        }

        return substr($haystack, $startPos, $endPos - $startPos);
    }

    /**
     * Loads style attributes for predefined elements from the XML data into the `styles` property.
     *
     * This method iterates through a predefined list of element names, retrieves their associated
     * style attributes from the `xmlData` array, and populates the `styles` property with these attributes.
     *
     * Changes from the original method:
     * - **Removed `eval()`**: The original method used `eval()` to dynamically access properties, which is
     *   considered unsafe and difficult to maintain. The new method accesses the XML data directly using array
     *   notation, making it safer and more readable.
     * - **Switched to Array Notation**: The new method uses array access (`$this->xmlData['styles']`) instead of
     *   `eval()` for fetching style attributes. This change simplifies the code and improves performance.
     * - **Added Default Values Handling**: The new method does not explicitly handle missing elements, but the
     *   code assumes that missing attributes will be handled gracefully by the way array elements are accessed.
     *
     * @return void
     */
    private function loadStyles(): void
    {
        $elements = [
            'menubutton',
            'menubackground',
            'menuentry',
            'menuentryactive',
            'menugroupicon',
            'menuicon',
            'menuactionicon',
            'menuhelpicon',
            'separator',
            'indicatorfalse',
            'indicatortrue',
            'indicatoronhold'
        ];
        $nodes = [];
        foreach ($elements as $element) {
            $nodes[$element] = $this->xmlData['styles'][$element]['@attributes'];
        }
        foreach ($nodes as $element => $node) {
            $this->styles[$element]['style'] = $node['style'];
            $this->styles[$element]['substyle'] = $node['substyle'];
        }
    }

    private function loadEntries(): void
    {
        // foreach ($this->xmlData->entries->entry as $entry) {
        //     dump($this->flattenArray($entry->toArray()));
        // }
        //dd(array_chunk($this->flattenArray($this->xmlData['entries']['entry']), 3));
    }
}
