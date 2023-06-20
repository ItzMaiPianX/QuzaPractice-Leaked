<?php

namespace owonico\task;

use owonico\Main;
use owonico\manager\PlayerManager;
use maipian\scoreboard\Scoreboard;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class ScoreboardTask extends Task {

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
            $this->getHandler()?->cancel();
        }

        if ($this->player->isConnected()) {
            if (Main::$scoreboardEnabled[$this->player->getName()]) {
                $kill = PlayerManager::getPlayerKill($this->player);
                $death = PlayerManager::getPlayerDeath($this->player);
                $coin = PlayerManager::getPlayerCoin($this->player);
                $cps = Main::getInstance()->getCPS($this->player);
                $region = Main::getInstance()->getConfig()->get("region");

                $playerCount = count($this->plugin->getServer()->getOnlinePlayers());
                $combatTimer = PlayerManager::getCombatTimer($this->player);
                $ping = $this->player !== null ? $this->player->getNetworkSession()->getPing() : 0;

                if ($this->player->getWorld() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld()) {
                    $lines = [
                        1 => "§r§8 -------------- ",
                        2 => "§r  ",
                        3 => "§b Online:§f {$playerCount}",
                        4 => "§b Ping:§f {$ping}ms",
                        5 => "§r   ",
                        6 => "§b Coins:§f {$coin}",
                        7 => "§b K:§f {$kill} §bD:§f {$death}",
                        8 => "§r    ",
                        9 => "§f§8 --------------",
                        10 => "§b  play.quza.my.to"
                    ];
                    Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                    foreach ($lines as $line => $content) {
                        Scoreboard::setLine($this->player, $line, $content);
                    }
                }
                if (!isset(Main::$playerArena[$this->player->getName()])) return;
                switch (Main::$playerArena[$this->player->getName()]) {
                    case "Lobby":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Online:§f {$playerCount}",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r   ",
                            6 => "§b Coins:§f {$coin}",
                            7 => "§b K:§f {$kill} §bD:§f {$death}",
                            8 => "§r    ",
                            9 => "§f§8 --------------",
                            10 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "NodebuffFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Nodebuff",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "ComboFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Combo",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "FistFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Fist",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "SumoFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Sumo",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "GappleFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Gapple",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "KnockFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena§f: Knock",
                            4 => "§b Ping:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "BuildFFA":
                        $lines = [
                            1 => "§r§8 -------------- ",
                            2 => "§r  ",
                            3 => "§b Arena:§f Build",
                            4 => "§b Ping§f:§f {$ping}ms",
                            5 => "§r    ",
                            6 => "§b Combat:§f {$combatTimer}s",
                            7 => "§r   ",
                            8 => "§f§8 --------------",
                            9 => "§b  play.quza.my.to"
                        ];
                        Scoreboard::new($this->player, "Quza", "§l§b{$region} §fPractice");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                }
            }
        }
    }
}

