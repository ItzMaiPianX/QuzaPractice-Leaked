<?php

namespace owonico\listeners;

use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\Server;

class ServerListener implements Listener {

    public function onRegenerate(QueryRegenerateEvent $event){
        $event->getQueryInfo()->setMaxPlayerCount(count(Server::getInstance()->getOnlinePlayers()) + 1);
        $event->getQueryInfo()->setListPlugins(false);
        $event->getQueryInfo()->setServerName("§l§bQuza§fNetwork");
    }
}