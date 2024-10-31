<?php

/**
 * This class replace old plugin system from aseco
 * IF you want inturduce new plugin or some that was not converted you need create
 * PluginName.php in /src/Plugins and then create namespace Yuhzel\X8seco\Plugins;
 * class PluginName ... your code
 * after that put PluginName into list of plugins
 */

declare(strict_types=1);

namespace Yuhzel\X8seco\App;

use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Services\Log;
use Yuhzel\X8seco\Plugins\{
    Tmxv,
    LocalDatabase,
    Panels,
    Style,
    Rasp,
    ManiaLive,
    FufiMenu,
    FlexiTime,
    ChatAdmin,
    ChatDedimania,
    ChatCmd,
    Rounds,
    Eyepiece,
    Checkpoints,
    Cpll,
    Dedimania,
    CpLiveAdvanced,
    ManiaKarma,
    Nickname,
    RaspJukebox,
    RaspVotes,
};

class PluginManager
{
    public function __construct(
        public ChatCmd $chatCmd,
        public ChatAdmin $chatAdmin,
        public Tmxv $tmxv,
        public LocalDatabase $localDatabase,
        public Panels $panels,
        public Style $style,
        public Rasp $rasp,
        public ManiaLive $maniaLive,
        public FufiMenu $fufiMenu,
        public FlexiTime $flexiTime,
        public ChatDedimania $chatDedimania,
        public Rounds $rounds,
        public Eyepiece $eyepiece,
        public Checkpoints $checkpoints,
        public Cpll $cpll,
        public Dedimania $dedimania,
        public CpLiveAdvanced $cpLiveAdvanced,
        public ManiaKarma $maniaKarma,
        public Nickname $nickname,
        public RaspJukebox $raspJukebox,
        public RaspVotes $raspVotes,
    ) {
    }

    /**
     * Any plugin dependency will resolve automaticly
     *
     * @param string $pluginName exmp. LocalDatabase in this form
     * @return null|object class exmp. $this->localDatabase;
     */
    public function getPlugin(string $pluginName): mixed
    {
        if (property_exists($this, lcfirst($pluginName))) {
            $plugin = lcfirst($pluginName);
            return $this->{$plugin};
        }

        Basic::console("Plugin {$pluginName} not found make sure it is in PluginManager constructor.");
        Log::error("Plugin {$pluginName} not found make sure it is in PluginManager constructor.");
        return null;
    }

    public function onStartup(): void
    {
        foreach (get_object_vars($this) as $plugin) {
            if (method_exists($plugin, 'onStartup')) {
                $plugin->onStartup();
            }
        }
    }

    public function onSync(): void
    {
        foreach (get_object_vars($this) as $plugin) {
            if (method_exists($plugin, 'onSync')) {
                $plugin->onSync();
            }
        }
    }

    public function onPlayerConnect(string $login)
    {
        foreach (get_object_vars($this) as $plugin) {
            if (method_exists($plugin, 'onPlayerConnect')) {
                $plugin->onPlayerConnect($login);
            }
        }
    }

    public function onNewChallenge()
    {
        $this->tmxv->onNewChallenge();
        $this->checkpoints->onNewChallenge();
        $this->cpll->onNewChallenge();
        //$this->dedimania->onNewChallenge();
    }
}
