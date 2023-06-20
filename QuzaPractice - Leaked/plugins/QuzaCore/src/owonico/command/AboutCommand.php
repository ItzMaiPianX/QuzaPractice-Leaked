<?php

namespace owonico\command;

use owonico\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AboutCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("about", "Gets the version of this server including any plugins in use", "This server is running QuanzaMC v1.2.0 Minecraft: Bedrock Edtion 1.20.0", ["ver", "version"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $sender->sendMessage("This server is running QuanMC v1.2.1 Minecraft: Bedrock Edtion 1.20.0");
    }
}
