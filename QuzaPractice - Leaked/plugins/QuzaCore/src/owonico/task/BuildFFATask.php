<?php

namespace owonico\task;

use owonico\Main;
use owonico\utils\Utils;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;

class BuildFFATask extends Task {

    public $pos;
    public $world;
    public $timer = 10;

    public function __construct(Vector3 $pos, World $world){
        $this->pos = $pos;
        $this->world = $world;
        $this->timer = 10;
    }

    public function onRun(): void
    {
        $this->timer--;

        if ($this->timer <= 0){
            $position = $this->pos;
            $this->world->setBlockAt($position->getX(), $position->getY(), $position->getZ(), VanillaBlocks::AIR());
            $this->world->addParticle($position, new BlockBreakParticle(VanillaBlocks::SANDSTONE()));

            if (isset(Main::$bffaplacedblock[Utils::vectorToString($position)])){
                unset(Main::$bffaplacedblock[Utils::vectorToString($position)]);
            }

            $this->getHandler()?->cancel();
        }
    }
}
