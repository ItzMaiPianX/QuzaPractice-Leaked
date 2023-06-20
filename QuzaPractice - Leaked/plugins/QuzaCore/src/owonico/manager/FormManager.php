<?php

namespace owonico\manager;

use owonico\{Main, Variables};
use owonico\manager\PlayerManager;
use maipian\form\pmforms\MenuForm;
use maipian\form\pmforms\MenuOption;
use maipian\form\pmforms\FormIcon;
use maipian\form\formapi\SimpleForm;
use maipian\scoreboard\Scoreboard;
use pocketmine\entity\Skin;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class FormManager{

    public static function getFFAForm(): MenuForm{
        return new MenuForm(
            "      ",
            "",
            [
                new MenuOption("§bNodebuff\n§8Players:§b " . Main::getWorldCount(Variables::Nodebuffffa), new FormIcon("quza/textures/ui/ui_png/nodebuff.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bCombo\n§8Players:§b " . Main::getWorldCount(Variables::Comboffa), new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bFist\n§8Players:§b " . Main::getWorldCount(Variables::Fistffa), new FormIcon("quza/textures/ui/ui_png/fist.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bSumo\n§8Players:§b " . Main::getWorldCount(Variables::Sumoffa), new FormIcon("quza/textures/ui/ui_png/sumo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bGapple\n§8Players:§b " . Main::getWorldCount(Variables::Gappleffa), new FormIcon("quza/textures/ui/ui_png/gapple.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bKnock\n§8Players:§b " . Main::getWorldCount(Variables::Knockffa), new FormIcon("quza/textures/ui/ui_png/knock.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bBuild\n§8Players:§b " . Main::getWorldCount(Variables::Buildffa), new FormIcon("quza/textures/ui/ui_png/build.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Nodebuffffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "NodebuffFFA";
                        PlayerManager::sendNodebuffKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 1:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Comboffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "ComboFFA";
                        PlayerManager::sendComboKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 2:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Fistffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "FistFFA";
                        PlayerManager::sendFistKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 3:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Sumoffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "SumoFFA";
                        PlayerManager::sendSumoKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 4:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Gappleffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "GappleFFA";
                        PlayerManager::sendGappleKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 5:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Knockffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "KnockFFA";
                        PlayerManager::sendKnockKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 6:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Buildffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "BuildFFA";
                        PlayerManager::sendBFFAKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                }
            },

            function (Player $submitter): void{
                //I dont want to handle when his close the form
            }
        );
    }

    public static function getSettingsForm(Player $player): MenuForm{
        $cps = new Config(Main::getInstance()->getDataFolder() . "settings/CPSPopup.yml", Config::YAML);
        $hitParticles = new Config(Main::getInstance()->getDataFolder() . "settings/HitParticles.yml", Config::YAML);
        $autoSprint = new Config(Main::getInstance()->getDataFolder() . "settings/AutoSprint.yml", Config::YAML);
        $scoreboard = new Config(Main::getInstance()->getDataFolder() . "settings/Scoreboard.yml", Config::YAML);
        if ($cps->exists($player->getXuid())) {
            $cpsStatus = "§bCPS\n§aEnabled";
        } else {
            $cpsStatus = "§bCPS\n§cDisabled";
        }
        if ($hitParticles->exists($player->getXuid())) {
            $hitStatus = "§bHit Effect\n§aEnabled";
        } else {
            $hitStatus = "§bHit Effect\n§cDisabled";
        }
        if ($autoSprint->exists($player->getXuid())) {
            $autoStatus = "§bAuto Sprint\n§aEnabled";
        } else {
            $autoStatus = "§bAuto Sprint\n§cDisabled";
        }
        if ($scoreboard->get($player->getXuid())) {
            $scoreStatus = "§bScoreboard\n§aEnabled";
        } else {
            $scoreStatus = "§bScoreboard\n§cDisabled";
        }

        return new MenuForm(
            "",
            "",
            [
                new MenuOption($cpsStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($hitStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($autoStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($scoreStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $submitter, int $selected) use ($cps, $autoSprint, $hitParticles, $scoreboard): void{
                switch ($selected){
                    case 0:
                        if ($cps->exists($submitter->getXuid())){
                            $cps->remove($submitter->getXuid());
                            $cps->save();

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled CPS");
                        } else{
                            $cps->set($submitter->getXuid(), true);
                            $cps->save();

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled CPS");
                        }
                        break;
                    case 1:
                        if ($hitParticles->exists($submitter->getXuid())){
                            $hitParticles->remove($submitter->getXuid());
                            $hitParticles->save();

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Hit Effect");
                        } else{
                            $hitParticles->set($submitter->getXuid(), true);
                            $hitParticles->save();

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Hit Effect");
                        }
                        break;
                    case 2:
                        if ($autoSprint->exists($submitter->getXuid())){
                            $autoSprint->remove($submitter->getXuid());
                            $autoSprint->save();

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Auto Sprint");
                        } else{
                            $autoSprint->set($submitter->getXuid(), true);
                            $autoSprint->save();

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Auto Sprint");
                        }
                        break;
                    case 3:
                        if ($scoreboard->get($submitter->getXuid())){
                            $scoreboard->set($submitter->getXuid(), false);
                            $scoreboard->save();

                            Main::$scoreboardEnabled[$submitter->getName()] = false;
                            Scoreboard::remove($submitter);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Scoreboard");
                        } else{
                            $scoreboard->set($submitter->getXuid(), true);
                            $scoreboard->save();

                            Main::$scoreboardEnabled[$submitter->getName()] = true;

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Scoreboard");
                        }
                        break;
                }
            },
            function (Player $submitter): void{

            }
        );
    }

    public static function getRuleForm(): MenuForm{
        return new MenuForm(
            "§eWelcome to §l§eQuza",
            Main::getRuleContent(),
            [
                new MenuOption("§aAgree"),
                new MenuOption("§cDisagree")
            ],
            function(Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendMessage(Variables::Prefix . "§aHave Fun!");
                        break;
                    case 1:
                        $player->kick("§cYou must agree on the rule to play on server!");
                        break;
                }
            },
            function (Player $selecter): void{
                //$selecter->kick("§cYou must agree on the rule to play on server!");
            }
        );
    }

    public static function getCapeForm(): MenuForm{
        return new MenuForm(
            "",
            "",
            [
                new MenuOption("§cRemove Cape", new FormIcon("quza/textures/ui/ui_png/cancel.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§aSelect Cape", new FormIcon("quza/textures/ui/ui_png/cape.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected) : void{
                switch ($selected){
                    case 0:
                        $pdata = new Config(Main::getInstance()->getDataFolder() . "capes/data.yml", Config::YAML);
                        $oldSkin = $player->getSkin();
                        $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), "", $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                        $player->setSkin($setCape);
                        $player->sendSkin();

                        if($pdata->get($player->getXuid()) !== null){
                            $pdata->remove($player->getXuid());
                            $pdata->save();
                        }

                        $player->sendMessage(Variables::Prefix . "§aRemoved your cape!");
                        break;
                    case 1:
                        $player->sendForm(self::getCapeListForm());
                        break;
                }
            },
            function (Player $submiter): void{

            }
        );
    }

    public static function getCosmeticForm(): MenuForm{
        return new MenuForm(
            "",
            "",
            [
                new MenuOption("§bCape", new FormIcon("quza/textures/ui/ui_png/cape.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendForm(self::getCapeForm());
                        break;
                }
            },
            function (Player $player): void{

            }
        );
    }

    public static function getCapeListForm(): SimpleForm{
        $form = new SimpleForm(function (Player $player, $data = null){
            $result = $data;

            if(is_null($result)) {
                return true;
            }

            $cape = $data;
            $pdata = new Config(Main::getInstance()->getDataFolder() . "capes/data.yml", Config::YAML);

            if(!file_exists(Main::getInstance()->getDataFolder() . "capes/" . $data . ".png")) {
                $player->sendMessage(Variables::Prefix . "§cThe chosen skin is not available!");
            } else {
                $oldSkin = $player->getSkin();
                $capeData = Main::getInstance()->createCape($cape);
                $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                $player->setSkin($setCape);
                $player->sendSkin();

                $player->sendMessage(Variables::Prefix . "§aChanged your cape to " . $cape);

                $pdata->set($player->getXuid(), $cape);
                $pdata->save();
            }
        });
        $form->setTitle("  ");
        foreach(Main::getInstance()->getAllCapes() as $capes) {
            $form->addButton("§b$capes", -1, "quza/textures/ui/ui_png/cape.png", $capes);
        }
        return $form;
    }

    public static function getDuelForm(): MenuForm{
        return new MenuForm(
            "",
            "",
            [
                new MenuOption("§bUnranked"),
                new MenuOption("§bRanked")
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendMessage(Variables::Prefix . "§aComing Soon...");
                        //$player->sendForm(self::getUnrankedForm());
                        break;
                    case 1:
                        $player->sendMessage(Variables::Prefix . "§aComing Soon...");
                        //$player->sendForm(self::getRankedForm());
                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getUnrankedForm(): MenuForm{
        return new MenuForm(
            "Unranked",
            "",
            [
                new MenuOption("§cNodebuff"),
                new MenuOption("§bSumo"),
                new MenuOption("§eThe Bridge")
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->getServer()->dispatchCommand($player, "unrankednodebuff random");

                        break;
                    case 1:
                        $player->getServer()->dispatchCommand($player, "unrankedsumo random");

                        break;
                    case 2:
                        $player->getServer()->dispatchCommand($player, "tb random");
                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getRankedForm(): MenuForm{
        return new MenuForm(
            "Unranked",
            "",
            [
                new MenuOption("§cNodebuff"),
                new MenuOption("§bSumo"),
                new MenuOption("§eThe Bridge")
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->getServer()->dispatchCommand($player, "unrankednodebuff random");

                        break;
                    case 1:
                        $player->getServer()->dispatchCommand($player, "unrankedsumo random");

                        break;
                    case 2:
                        $player->getServer()->dispatchCommand($player, "tb random");
                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }
}
