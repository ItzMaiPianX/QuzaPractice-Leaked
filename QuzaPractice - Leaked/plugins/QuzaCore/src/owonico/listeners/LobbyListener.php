<?php

namespace owonico\listeners;

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\Listener;

class LobbyListener implements Listener {

    public function onBlockSpread(BlockSpreadEvent $event){
        $event->cancel();
    }

    public function onExplode(ExplosionPrimeEvent $event){
        $event->cancel();
    }

}
