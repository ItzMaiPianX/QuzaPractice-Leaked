<?php

namespace owonico\task;

use owonico\{Main, Variables};
use pocketmine\scheduler\Task;

class BroadcastTask extends Task{

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player){
            if ($player->getWorld() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld()){
                switch (rand(1, 2)){
                    case 1:
                        $this->plugin->getServer()->broadcastMessage(Variables::Prefix . "§6Welcome to§b Quza§fNetwork§6 !");
                        break;
                    case 2:
                        $this->plugin->getServer()->broadcastMessage(Variables::Prefix . "§6Join to official discord (https://dsc.gg/quza) to learn more !");
                        break;
                    case 3:
                        $this->plugin->getServer()->broadcastMessage(Variables::Prefix . "§6Have fun !");
                        break;
                }
            }
        }
    }
}
