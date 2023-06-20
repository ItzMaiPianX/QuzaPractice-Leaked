<?php

namespace owonico\command;

use owonico\{Main, Variables};
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class InfoCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("info", "Read the server info");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;

        $sender->sendMessage("\n§b Discord:§7 dsc.gg/quza\n§b YouTube:§7 @quza2018\n");
    }
}
