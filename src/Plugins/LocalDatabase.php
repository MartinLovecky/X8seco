<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Exception;
use Yuhzel\Xaseco\Database\Fluent;
use Yuhzel\Xaseco\Services\{Log, Basic};
use Yuhzel\Xaseco\Core\Types\{Challenge, PlayerList};

/**
 * This script saves record into a local database.
 * You can modify this file as you want, to advance
 * the information stored in the database!
 *
 * @author    Florian Schnell
 * @version   2.0
 * Updated by Xymph
 * Updated by Yuhzel
 * Dependencies: requires plugin.panels.php on TMF
 */
class LocalDatabase
{
    // @phpstan-ignore-next-line
    private array $ldb_settings = [];
    public function __construct(
        private Fluent $fluent,
        // private ManiaLinks $maniaLinks,
        // private MsgLog $msgLog,
        // private Panels $panels,
        // private Record $record,
        // private RecordList $recordList,
        // private Player $player,
        private PlayerList $playerList,
        private Challenge $challenge,
    ) {
    }

    public function onStartup(): void
    {
        $this->ldb_settings['display'] = filter_var($_ENV['display'], FILTER_VALIDATE_BOOLEAN);
        $this->ldb_settings['limit'] = is_numeric($_ENV['limit']) ? (int)($_ENV['limit']) : 50;

        $this->ldb_connect();
    }

    // called in onStartup
    private function ldb_connect(): void
    {
        // List of tables you need to process
        $tables = ['challenges', 'players', 'records', 'players_extra'];

        foreach ($tables as $table) {
            try {
                // Create table if they dont exist
                if ($this->fluent->execSQLFile($table)) {
                    if(!$this->fluent->validStructure($table)) {
                        Basic::console('ERROR 1 LocalDatabase');
                    }
                } else {
                    if(!$this->fluent->validStructure($table)) {
                        Basic::console('ERROR 2 LocalDatabase');
                    }
                }
            } catch (Exception $e) {
                Log::error("Error processing table {$table}: {$e->getMessage()}");
            }
        }
    }

    public function playerExist()
    {
    }

    //REVIEW - SUS
    public function onSync(): void
    {
        $this->playerList->resetPlayers();
    }

    //NOTE - I dont understand why we want get data from DB  when we have acces to current challange info
    public function getCPS(): int
    {
        return $this->challenge->nbCheckpoints;
    }

    // NOTE: since we handle DB difrently id is string
    public function getPlayerId(string $login): string
    {
        if(array_key_exists($login, $this->playerList->players)) {
            return $this->playerList->players[$login]->login;
        }

        //NOTE - this should never happen
        return $this->fluent->query
            ->from('players')
            ->select('PlayerId')
            ->where('Login', $login)
            ->fetch('PlayerId');
    }

    //  public function onNewChallenge(): void
    //  {
    //      dd($this->challenge);
    //      $this->challenge->uid = 1234;
    //      //REVIEW - SUS
    //       $ldb_records->clear();
    //       $aseco->server->records->clear();

    //       //on relay, ignore master server's challenge
    //       if ($aseco->server->isrelay) {
    //           $challenge->id = 0;
    //           return;
    //       }
    //      try {
    //          $result = $this->fluent->query
    //              ->from('challenges AS c')
    //              ->leftJoin('records AS r ON r.ChallengeId = c.Id')
    //              ->leftJoin('players AS p ON r.PlayerId = p.Id')
    //              ->select('c.Id AS ChallengeId, r.Score, p.NickName, p.Login, r.Date, r.Checkpoints')
    //              ->where('c.Uid', $this->challenge->uid)
    //              ->orderBy('r.Score ASC')
    //              ->limit($this->ldb_settings['limit'])
    //              ->fetch();
    //      } catch (Exception $e) {
    //          Log::error("Could not get challenge info! {$e->getMessage()}");
    //      }
    //       //challenge not found
    //      if (!$result) {
    //          Log::error("Could not get challenge info! {$this->challenge->uid}" . E_USER_WARNING);
    //          return;
    //      } else {
    //           //Player
    //          $this->player->nickname ?? $result['NickName'];
    //          $this->player->login ?? $result['Login'];

    //           //Challenge
    //          $this->challenge->id ?? $result['ChallengeId'];

    //          // Record
    //          $this->record->score ?? $result['Score'];
    //          //TODO checks propably not array
    //          $this->record->checks ?? $result['Checkpoints'];
    //          dd($this->record, $result);
    //         //ldb_records FIXME - how this link with record ?
    //          $this->recordList->addRecord($this->record);
    //         // NOTE - this can be duplicate ?

    //      }

    //      try {
    //          $insert = $this->fluent->query
    //              ->insertInto('challenges')
    //              ->values([
    //                  'Uid' => $this->challenge->id,
    //                  'Name' => $this->challenge->name,
    //                  'Author' => $this->challenge->author,
    //                  'Environment' => $this->challenge->environment
    //              ])
    //              ->execute();
    //      } catch (Exception $e) {
    //          Log::error("Could not get challenge info! {$e->getMessage()}");
    //      }
    //      if ($insert) {
    //          $id = $this->fluent->query
    //              ->from('challenges')
    //              ->select('Id')
    //              ->where('Uid', $this->challenge->uid)
    //              ->fetch('Id');

    //          $this->challenge->id = $id;
    //          //REVIEW - MEGA SUS
    //      }

    //       //update aseco records SUS
    //      $aseco->server->records = $ldb_records;
    //  }

    //  //REVIEW - SUS
    //  public function onPlayerConnect(): void
    //  {
    //      $result = $this->fluent->query
    //          ->from('players')
    //          ->where('Login', $this->player->login)
    //          ->fetchAll('Id', 'Wins', 'TimePlayed', 'TeamName');

    //       //was retrieved
    //      if ($result) {
    //          $this->player->id = $result['Id'];
    //          $this->player->teamname ?? $result['TeamName'];
    //          $this->player->wins = ($this->player->wins == $result['Wins']) ? $this->player->wins : $result['Wins'];
    //          $this->player->timeplayed = ($this->player->timeplayed == $result['TimePlayed']) ? $this->player->timeplayed : $result['TimePlayed'];

    //          $this->fluent->query
    //              ->update('players')
    //              ->set([
    //                  'NickName' => $this->player->nickname,
    //                  'TeamName' => $this->player->teamname,
    //                  'UpdatedAt' => $this->record->date
    //              ])
    //              ->where('Login', $this->player->login)
    //              ->execute();
    //      }
    //       //could not be retrieved
    //      else {
    //           //insert player
    //          $this->player->id = 0;
    //          $stmt = $this->fluent->query
    //              ->insertInto('players')
    //              ->values([
    //                  'Login' => $this->player->login,
    //                  'Game' => 'TMF',
    //                  'NickName' => $this->player->nickname,
    //                  'Nation' => $this->player->nation,
    //                  'UpdatedAt' => $this->record->date
    //              ])
    //              ->execute();
    //          $id = $this->fluent->pdo->lastInsertId();
    //          $this->player->id =  $id;
    //          //NOTE - SUS
    //           //insert player's default extra data
    //          $stmt = $this->fluent->query
    //              ->insertInto('players_extra')
    //              ->values([
    //                  'playerID' => $this->player->id,
    //                  'cps' => $_ENV['AUTO_ENABLE_CPS'],
    //                  'dedicps' => $_ENV['AUTO_ENABLE_DEDICPS'],
    //                  'donations' => 0,
    //                  'style' => $_ENV['WINDOW_STYLE'],
    //                  'panels' =>
    //                  //REVIEW - this just eneble / disable
    //                  $_ENV['ADMIN_PANEL'] . '/' .
    //                      $_ENV['DONATE_PANEL'] . '/' .
    //                      $_ENV['RECORDS_PANEL'] . '/' .
    //                      $_ENV['VOTE_PANEL'] . '/'
    //              ])
    //              ->execute();
    //      }
    //  }

    // // TODO - TEST
    // public function onPlayerDisconnect(): void
    // {
    //     if ($this->player->login == '') {
    //         return;
    //     }

    //     $played = $this->fluent->query
    //         ->from('players')
    //         ->select('TimePlayed')
    //         ->where('Login', $this->player->login)
    //         ->fetch('TimePlayed');

    //     $stmt = $this->fluent->query
    //         ->update('players')
    //         ->set([
    //             'TimePlayed' => $this->player->getTimeOnline() + $played
    //         ])
    //         ->where('Login', $this->player->login)
    //         ->execute();
    // }

    // public function onPlayerFinish(Record $finish_item): void
    // {
    //     if ($finish_item->score == 0 || !$finish_item->new) {
    //         return;
    //     }
    //     //ANCHOR - holy fuck
    //     $checkpoints = null;  // from plugin.checkpoints.php
    //     $login = $finish_item->player->login;
    //     $nickname = Basic::stripColors($finish_item->player->nickname);
    //     // reset lap 'Finish' flag & add checkpoints
    //     $finish_item->new = false;
    //     $finish_item->checks = $checkpoints[$login]->curr_cps ?? [];

    //     for ($i = 0; $i < $this->recordList->limit; $i++) {
    //         $cur_record = $this->recordList->getRecord($i);
    //         if (!$cur_record || $finish_item->score < $cur_record->score) {
    //             $cur_rank = -1;
    //             $cur_score = 0;

    //             for ($rank = 0; $rank < count($this->recordList->records); $rank++) {
    //                 $rec = $this->recordList->getRecord($rank);
    //                 if ($rec->player->login == $login) {
    //                     if ($finish_item->score > $rec->score) {
    //                         return;
    //                     } else {
    //                         $cur_rank = $rank;
    //                         $cur_score = $rec->score;
    //                         break;
    //                     }
    //                 }
    //             }

    //             $finish_time = $finish_item->score;
    //             $finish_time = Basic::formatTime($finish_time);

    //             // player has a record in topXX already
    //             if ($cur_rank != -1) {
    //                 // compute difference to old record
    //                 $diff = $cur_score - $finish_item->score;
    //                 // Integer division to get full seconds
    //                 $sec = intdiv($diff, 1000);
    //                 // Modulus for remaining milliseconds, then convert to hundredths
    //                 $hun = ($diff % 1000) / 10;
    //                 // update record if improved
    //                 if ($diff > 0) {
    //                     $finish_item->new = true;
    //                     $this->recordList->setRecord($cur_rank, $finish_item);
    //                 }
    //                 // player moved up in LR list
    //                 if ($cur_rank > $i) {
    //                     $this->recordList->moveRecord($cur_rank, $i);
    //                     $message = Basic::formatText(
    //                         $this->ldb_settings['messages']['RECORD_NEW_RANK'][0],
    //                         $nickname,
    //                         $i + 1,
    //                         'Time',
    //                         $finish_time,
    //                         $cur_rank + 1,
    //                         sprintf('-%d.%02d', $sec, $hun)
    //                     );

    //                     if ($this->ldb_settings['display'] && $i < $this->ldb_settings['limit']) {
    //                         $this->msgLog->send_window_message($message, false);
    //                     }
    //                 } else {
    //                     // do a player equaled his/her record message
    //                     if ($diff == 0) {
    //                         $message = Basic::formatText(
    //                             $this->ldb_settings['messages']['RECORD_EQUAL'][0],
    //                             $nickname,
    //                             $cur_rank + 1,
    //                             'Time',
    //                             $finish_time
    //                         );
    //                     } else {
    //                         $message = Basic::formatText(
    //                             $this->ldb_settings['messages']['RECORD_NEW'][0],
    //                             $nickname,
    //                             $i + 1,
    //                             'Time',
    //                             $finish_time,
    //                             $cur_rank + 1,
    //                             sprintf('-%d.%02d', $sec, $hun)
    //                         );
    //                     }

    //                     if ($this->ldb_settings['display'] && $i < $this->ldb_settings['limit']) {
    //                         $this->msgLog->send_window_message($message, false);
    //                     }
    //                 }
    //             }
    //             // player hasn't got a record yet
    //             else {
    //                 // if previously tracking own/last local record, now track new one
    //                 if (isset($checkpoints[$login]) && $checkpoints[$login]->loclrec == 0 && $checkpoints[$login]->dedirec == -1) {
    //                     $checkpoints[$login]->best_fin = $checkpoints[$login]->curr_fin;
    //                     $checkpoints[$login]->best_cps = $checkpoints[$login]->curr_cps;
    //                 }
    //                 // insert new record at the specified position
    //                 $finish_item->new = true;
    //                 $this->recordList->addRecord($finish_item, $i);

    //                 $message = Basic::formatText(
    //                     $this->ldb_settings['messages']['RECORD_FIRST'][0],
    //                     $nickname,
    //                     $i + 1,
    //                     'Time',
    //                     $finish_time
    //                 );

    //                 if ($this->ldb_settings['display'] && $i < $this->ldb_settings['limit']) {
    //                     $this->msgLog->send_window_message($message, false);
    //                 }
    //             }

    //             if ($finish_item->new) {
    //                 $this->ldb_insert_record($finish_item);

    //                 if ($i == 0) {
    //                     $this->maniaLinks->setRecordsPanel('local', Basic::formatTime($finish_item->score));
    //                 }

    //                 $this->panels->onNewChallenge2(null);

    //                 $finish_item->pos = $i + 1;
    //                 // $aseco->releaseEvent('onLocalRecord', $finish_item);
    //             }
    //         }
    //         return;
    //     }
    // }

    // public function onPlayerWins()
    // {
    //     $wins = $this->player->getWins();
    //     $stmt = $this->fluent->query
    //         ->update('players')
    //         ->set(['Wins' => $wins])
    //         ->where('Login', $this->player->login)
    //         ->execute();

    //     if (!$stmt) {
    //         trigger_error(E_USER_WARNING);
    //     }
    // }

    // // called in onPlayerFinish
    // private function ldb_insert_record(Record $finish_item): void
    // {
    //     $playerid = $finish_item->player->id;
    //     $cps = implode(',', $finish_item->checks);
    //     // if you want query you need implement Duplicate Key Update
    //     // 1) get records -> check exist -> update
    //     // I will use PDO here
    //     $query = 'INSERT INTO records
    //       (ChallengeId, PlayerId, Score, Date, Checkpoints)
    //       VALUES (:ChallengeId, :PlayerId, :Score, NOW(), :Checkpoints)
    //       ON DUPLICATE KEY UPDATE
    //       Score = VALUES(Score), Date = VALUES(Date), Checkpoints = VALUES(Checkpoints)';
    //     // Prepare the statement
    //     $statement = $this->fluent->pdo->prepare($query);
    //     // Bind the values to the placeholders
    //     $statement->bindValue(':ChallengeId', $this->challenge->id, PDO::PARAM_INT);
    //     $statement->bindValue(':PlayerId', $playerid, PDO::PARAM_INT);
    //     $statement->bindValue(':Score', $finish_item->score, PDO::PARAM_INT);
    //     $statement->bindValue(':Checkpoints', $cps, PDO::PARAM_STR);

    //     // Execute the statement
    //     $result = $statement->execute();
    //     if (!$result) {
    //         Log::error('Could not insert/update record!');
    //         trigger_error(E_USER_WARNING);
    //     }
    // }



    // //NOTE - Depens on our aproach curenly DB is accessable all the time no need to "reconnect"
    // private function onEverySecond()
    // {
    //     //TODO -  wtf is going on here
    // }
}
