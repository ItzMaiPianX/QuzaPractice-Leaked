<?php

namespace owonico\command;

use owonico\{Main, Variables};
use owonico\manager\RankManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class CoreCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("core", "Core commands");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;
        if (!$sender->hasPermission("quza.staff")){
            $sender->sendMessage(Variables::Prefix . "§cYou dont have permission to perform this command!");
            return;
        }

        if (isset($args[0])){
            switch ($args[0]){
                case "setrank":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core setrabk <Player> <Rank>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $rank = $args[2] ?? "Player";

                    $rankClass = RankManager::$ranks[strtolower($rank)] ?? null;
                    if ($rankClass === null){
                        $sender->sendMessage(Variables::Prefix . "§cRank with name {$rank} is not found!");
                        return;
                    }
                    $player = $this->plugin->getServer()->getPlayerExact($playername);
                    RankManager::setPlayerRank($player, $rank);
                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully changed {$player->getName()}'s rank to {$rankClass->getDisplayFormat()}");
                    $player->sendMessage(Variables::Prefix . "§aYour rank has been changed to " . $rankClass->getDisplayFormat() . " by §6" . $sender->getName());
                    break;
            }
        }

        if (isset($args[0])){
            switch($args[0]){
                case "mm":
                    switch (strtolower($args[1])) {
                        case "on":
                            $this->plugin->getConfig()->set("maintenance", true);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned on maintenance");
                            break;
                        case "off":
                            $this->plugin->getConfig()->set("maintenance", false);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned off maintenance");
                            break;
                    }
            }
        }
    }
}
