<?php

namespace owonico\task;

use owonico\Main;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class PearlTask extends Task {

    public $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function onRun(): void
    {
        if($this->player == null){
            $this->getHandler()?->cancel();
        }

        if (!$this->player->isConnected()){
            if ($this->getHandler() !== null){
                $this->getHandler()->cancel();
            }
        }
        if (!isset(Main::$pearlCooldown[$this->player->getName()])){
            if ($this->getHandler() !== null){
                $this->getHandler()->cancel();
            }
        }
        if ($this->player->isConnected()) {
            --Main::$pearlCooldown[$this->player->getName()];
            $this->player->getXpManager()->setXpLevel(Main::$pearlCooldown[$this->player->getName()]);
            $this->player->getXpManager()->setXpProgress(Main::$pearlCooldown[$this->player->getName()] == 10 ? 1.0 : Main::$pearlCooldown[$this->player->getName()] / 10); //A good hack

            if (Main::$pearlCooldown[$this->player->getName()] == 0) {
                unset(Main::$pearlCooldown[$this->player->getName()]);
                $this->player->getXpManager()->setXpLevel(0);
                $this->player->getXpManager()->setXpProgress(0.0);
                $this->getHandler()?->cancel();
            }
        }
    }
}
