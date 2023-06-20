<?php

namespace owonico\manager;

use owonico\Main;
use owonico\rank\Rank;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class RankManager {

    /** @var Rank[] */
    public static $ranks = [];

    public static function init() {
        $ranks = [
            new Rank("Owner", "§bOWNER§r", ["quza.builder", "quza.operator", "quza.staff", "pocketmine.command.gamemode", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Developer", "§aDEVELOPER§r", ["quza.operator", "quza.staff"]),
            new Rank("Admin", "§aADMIN§r", ["quza.operator", "pocketmine.command.teleport", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Mod", "§aMOD§r", ["quza.moderator", "pocketmine.command.teleport", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Helper", "§aHELPER§r", ["quza.helper", "pocketmine.command.kick", "quza.staff"]),
            new Rank("Builder", "§aBUILDER§r", ["quza.builder", "quza.staff", "buildertools.command"]),
            new Rank("NAGA", "§aNAGA§r", ["quza.naga", "quza.mvp", "quza.vip"]),
            new Rank("MVP", "§aMVP+§r", ["quza.mvp", "quza.vip"]),
            new Rank("VIP", "§aVIP§r", ["quza.vip"]),
            new Rank("YouTube", "§cYOU§fTUBE§r", ["quza.naga", "quza.mvp", "quza.vip"]),
            new Rank("Famous", "§aFAMOUS§r", ["quza.mvp", "quza.vip"]),
            new Rank("Voter", "§aVOTER§r", ["quza.voter"]),
            new Rank("Player", "§aPLAYER§r", ["quza.player"], false)
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    public static function setPlayerRank(Player $player, string $rank) {
        /** @var Rank|null $rankClass */
        $rankClass = self::$ranks[strtolower($rank)] ?? null;
        if($rankClass === null) {
            $player->kick("Invalid rank ($rank)");
            Main::getInstance()->getLogger()->info("§cReceived invalid rank ($rank)");
            return;
        }
        $rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);

        $rankCfg->set($player->getXuid(), $rankClass->getName());
        $rankCfg->save();

        $player->recalculatePermissions();
        foreach ($rankClass->getPermissions() as $permission) {
            $player->addAttachment(Main::getInstance(), $permission, true);
        }
    }

    public static function saveVoteTime(Player $player) {
        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 1, "VoteDate" => time()], "Name", $player->getName()));
        //TODO

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->set($player->getName(), time());
        $voterCfg->save();

        if (self::getPlayerRank($player)->getName() == "Player") {
            self::setPlayerRank($player, "Voter");
        }
    }

    public static function hasVoted(Player $player): bool {
        return self::getPlayerRank($player)->getName() == "Voter";
    }

    public static function checkRankExpiration(Player $player, int $voteTime) {
        if(self::getPlayerRank($player)->getName() != "Voter") {
            return;
        }
        if($voteTime + 86400 >= time()) {
            return;
        }

        $player->sendMessage("§e§l§oRANKS:§r§f:§b Your VOTER rank expired. Vote again to extend it.");
        if(self::getPlayerRank($player)->getName() == "Voter") {
            self::setPlayerRank($player, "Player");
        }

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->remove($player->getName());
        $voterCfg->save();

        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 0], "Name", $player->getName()));
    }

    public static function getPlayerRank(Player $player): Rank {
        $rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        if (!$rankCfg->exists($player->getXuid())){
            self::setPlayerRank($player, "Player");
        }
        return self::$ranks[strtolower((string) $rankCfg->get($player->getXuid()))] ?? self::$ranks["player"];
    }

    public static function getRankByName(string $rank): ?Rank {
        return self::$ranks[strtolower($rank)] ?? null;
    }
}
