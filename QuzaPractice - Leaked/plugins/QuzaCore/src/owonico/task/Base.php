<?php

namespace owonico\task;

use owonico\Main;
use owonico\manager\PlayerManager;
use owonico\utils\Utils;
use owonico\Variables;
use maipian\scoreboard\scoreboard;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;

class Base extends Task{

    public $plugin;
    public $player;

    public function __construct(Main $plugin, Player $player){
        $this->plugin = $plugin;
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
        if($this->player->isConnected()) {
            $this->player->setScoreTag("§bPing:§f " . $this->player->getNetworkSession()->getPing() . "ms§8 | §bCPS:§f " . Main::getInstance()->getCPS($this->player));
        }
    }

}