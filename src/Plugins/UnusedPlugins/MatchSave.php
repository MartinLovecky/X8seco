<?php

// NOTE NOT USED

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Exception;
use Yuhzel\X8seco\Core\Gbx\GbxClient;
use Yuhzel\X8seco\Database\Fluent;
use Yuhzel\X8seco\Services\Log;
use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Core\Xml\XmlParser;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

// ffMod v1.4
// Original version by Sloth, via tm-forum.com
// Hack & Slash by AssemblerManiac
// Another Hack & Slash by (OoR-F)~fuckfish (http://fish.oorf.de) (with the help of XXX-Max and some code by Basti504)
// Formatting cleanup & TMF ManiaLink popups by Xymph
// Update by Yuhzel
// class MatchSave
// {
//     private ?XmlArrayObject $matchSettings = null;
//     private string $matchVersionNumber = 'v1.4';
//     //private array $teamForceTeams = [];
//     //private bool $matchDebug = false;

//     public function __construct(
//         private GbxClient $gbxClient,
//         private XmlParser $xmlParser,
//         private Fluent $fluent,
//     ) {}


//     public function onStartup(): void
//     {
//         $this->matchSettings = $this->xmlParser->parseXml('matchsave.xml');
//         $message = "Now Loading Matchsave ffMod {$this->matchVersionNumber}";
//         $this->gbxClient->addCall('ChatSendServerMessage', ['manialink' => $message]);
//         if ($this->matchSettings['save_to_db'] === true) {
//             $this->checkTables();
//         }
//     }


//     private function checkTables(): void
//     {
//         // List of tables you need to process
//         $tables = ['match_main', 'match_details'];
//         foreach ($tables as $table) {
//             try {
//                 // Create table if they dont exist
//                 if ($this->fluent->execSQLFile($table)) {
//                     if (!$this->fluent->validStructure($table)) {
//                         Basic::console('ERROR 1 MatchSave');
//                     }
//                 } else {
//                     if (!$this->fluent->validStructure($table)) {
//                         Basic::console('ERROR 2 MatchSave');
//                     }
//                 }
//             } catch (Exception $e) {
//                 Log::error("Error processing table {$table}: {$e->getMessage()}");
//             }
//         }
//     }
// }
