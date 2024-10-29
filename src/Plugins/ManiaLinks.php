<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use RuntimeException;
use Yuhzel\Xaseco\Core\Gbx\GbxClient;
use Yuhzel\Xaseco\Services\Basic;

class ManiaLinks
{
    public array $ml_custom_ui = [
        'global' => true,
        'notice' => true,
        'challenge_info' => true,
        'net_infos' => true,
        'chat' => true,
        'checkpoint_list' => true,
        'round_scores' => true,
        'scoretable' => true
    ];

    public array $ml_records = [
        'local' => '   --.--',
        'dedi' => '   --.--',
        'tmx' => '   --.--'
    ];

    public $auto_scorepanel;

    public function __construct(private GbxClient $client)
    {
    }

    public function onNewChallenge(): void
    {
        $this->scorepanel_off();
        $this->statspanels_off();
    }

    private function scorepanel_off(): void
    {
        $this->setCustomUIField('scoretable', false);
        $xml = '<manialinks><manialink id="0"><line></line></manialink>' .
            $this->getCustomUIBlock() . '</manialinks>';

        $this->client->addCall('SendDisplayManialinkPage', [
            'manialink' => $xml,
            'duration' => 0,
            'display' => false
        ]);
    }
    private function statspanels_off(): void
    {
        $xml = '<manialink id="9"></manialink>';
        $this->client->addCall('SendDisplayManialinkPage', [
            'manialink' => $xml,
            'duration' => 0,
            'display' => false
        ]);
    }

    private function setCustomUIField(string $field, bool $value): void
    {
        $this->ml_custom_ui[$field] = $value;
    }

    private function getCustomUIBlock(): string
    {
        $templatePath = Basic::path() . 'app/xml/maniaTemplates/costum/custom_ui.xml';
        $template = @file_get_contents($templatePath);

        if (!$template) {
            throw new RuntimeException("Unable to load the template file: {$templatePath}");
        }

        foreach ($this->ml_custom_ui as $key => $value) {
            $placeholder = '%' . $key . '%';
            $replacement = Basic::bool2text($value);
            $template = str_replace($placeholder, $replacement, $template);
        }

        return $template;
    }
}
