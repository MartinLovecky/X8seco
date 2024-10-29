<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Exception;
use Yuhzel\Xaseco\Services\Log;
use Yuhzel\Xaseco\Services\Basic;
use Yuhzel\Xaseco\Database\Fluent;
use Yuhzel\Xaseco\Core\Xml\XmlParser;

// ffMod v1.4
// Original version by Sloth, via tm-forum.com
// Hack & Slash by AssemblerManiac
// Another Hack & Slash by (OoR-F)~fuckfish (http://fish.oorf.de) (with the help of XXX-Max and some code by Basti504)
// Formatting cleanup & TMF ManiaLink popups by Xymph
// Update by Yuhzel
class MatchSave
{
    private array $matchSettings = [];
    //private array $teamForceTeams = [];
    //private string $matchVersionNumber = 'v1.4';
    //private bool $matchDebug = false;

    public function __construct(
        private XmlParser $xmlParser,
        private Fluent $fluent,
    ) {
    }


    public function onStartup(): void
    {
        // Sets $this->matchSettings from xml
        $this->matchLoadSettings();
        //dd($this->matchSettings);
        //NOTE - IF matchsave.xml exist in /app/xml there is realy no need to check isset but better be safe
        if (isset($this->matchSettings['save_to_db']) && $this->matchSettings['save_to_db'] === 'True') {
            $this->checkTables();
        }
    }

    private function matchLoadSettings(): void
    {
        $this->matchSettings = $this->xmlParser->parseXml('matchsave.xml')->toArray();
    }

    private function checkTables(): void
    {
        // List of tables you need to process
        $tables = ['match_main', 'match_details'];
        foreach ($tables as $table) {
            try {
                // Create table if they dont exist
                if ($this->fluent->execSQLFile($table)) {
                    if(!$this->fluent->validStructure($table)) {
                        Basic::console('ERROR 1 MatchSave');
                    }
                } else {
                    if(!$this->fluent->validStructure($table)) {
                        Basic::console('ERROR 2 MatchSave');
                    }
                }
            } catch (Exception $e) {
                Log::error("Error processing table {$table}: {$e->getMessage()}");
            }
        }
    }
}
