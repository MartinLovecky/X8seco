<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Core\Xml\XmlArrayObject;
use Yuhzel\X8seco\Core\Xml\XmlParser;

class Eyepiece
{
    public ?XmlArrayObject $config = null;

    public function __construct(
        private XmlParser $xmlParser
    ) {}

    public function onSync()
    {
        $this->config = $this->xmlParser->parseXml('records_eyepiece.xml');

        if ($this->config->music_widget->enabled) {
            $musicwidget =  $this->config->music_widget;
            //ChatCommand::addCommand('emusic', 'Lists musics currently on the server (see: /eyepiece)');
            //dd($musicwidget);
        }

        $widgets = ['dedimania_records', 'live_rankings', 'local_records'];
        $gamemodes = ['time_attack' => 1];

        $dedimaniaRecords =  $this->config->dedimania_records;
        $liveRankings =  $this->config->live_rankings;
        $localRecords =  $this->config->local_records;
        $niceMode =  $this->config->nicemode;
        $checkpointwidget =  $this->config->checkpointcount_widget;
        $challengewidget =  $this->config->challenge_widget;
        $style =  $this->config->style->widget_race;
        $clock =  $this->config->clock_widget;
        //dd($clock);
        //dd($this->shitClock($clock));
        // scoretable_lists -> [top_average_times, dedimania_records, ultimania_records, local_records, ....]
        // CurrentMaxPlayers = 40;
        // CurrentMaxSpectators = 16
        //$this->loadTemplates($checkpointwidget, $challengewidget, $style);
    }

    // private function shitClock($clock)
    // {
    //     $clockStr = @file_get_contents("{$this->xmlParser->xmlPath}/maniaTemplates/enabled/clock_widget.xml");
    //     $replacements  = [
    //         '%background_style%' =>  $clock->race->background_style,
    //         '%background_substyle%' => $clock->race->background_substyle,
    //         '%posx%' => $clock->race->pos_x,
    //         '%posy%' => $clock->race->pos_y,
    //         '%time%' => $clock->timeformat,
    //         '%timezone%' => $clock->default_timezone->getTimezone()->getName(),
    //         '%beat%' => date('B', time())
    //     ];

    //     $maianLink = str_replace(array_keys($replacements), array_values($replacements), $clockStr);
    //     $manialink_cleaned = preg_replace('/\s+/', ' ', $maianLink);

    //     return $manialink_cleaned;
    // }
}
