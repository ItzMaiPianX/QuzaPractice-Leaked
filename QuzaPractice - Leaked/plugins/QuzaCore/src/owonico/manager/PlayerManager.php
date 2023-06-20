<?php

namespace owonico\manager;

use owonico\{Main, Variables};
use owonico\rank\Rank;
use maipian\form\pmforms\MenuForm;
use maipian\form\pmforms\MenuOption;
use maipian\form\formapi\SimpleForm;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class PlayerManager{

    public static array $isCombat;
    public static array $combatTimer;
    public static array $combatOpponent;


    public static function getPlayerKill(Player $player){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Kills.yml", Config::YAML);

        if (!$config->exists($player->getXuid())) return 0;

        return $config->get($player->getXuid());
    }

    public static function getPlayerDeath(Player $player){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Deaths.yml", Config::YAML);

        if (!$config->exists($player->getXuid())) return 0;

        return $config->get($player->getXuid());
    }

    public static function getPlayerCoin(Player $player){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        if (!$config->exists($player->getXuid())) return 0;

        return $config->get($player->getXuid());
    }

    public static function addPlayerKill(Player $player){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Kills.yml", Config::YAML);

        $count = $config->get($player->getXuid());
        $count++;
        $config->set($player->getXuid(), $count);
        $config->save();
    }

    public static function addPlayerDeath(Player $player){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Deaths.yml", Config::YAML);

        $count = $config->get($player->getXuid());
        $count++;
        $config->set($player->getXuid(), $count);
        $config->save();
    }

    public static function addPlayerCoin(Player $player, int $amount){
        $config = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        $count = $config->get($player->getXuid());
        $config->set($player->getXuid(), $count + $amount);
        $config->save();
    }

    public static function sendLobbyKit(Player $player) {
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(0, VanillaItems::DIAMOND_SWORD()->setCustomName("§r§bPlay FFA"));
        $player->getInventory()->setItem(1, VanillaItems::IRON_SWORD()->setCustomName("§r§bPlay Duels"));
        $player->getInventory()->setItem(7, VanillaItems::DIAMOND()->setCustomName("§r§bCosmetics"));
        $player->getInventory()->setItem(8, VanillaItems::CLOCK()->setCustomName("§r§bSettings"));
    }

    public static function sendNodebuffKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 0xCCCCCCC,0,false));
        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);

        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENDER_PEARL, 0, 16));
        $player->getInventory()->addItem($item->get(ItemIds::SPLASH_POTION, 22, 34));
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendComboKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, 8));
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendFistKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->addItem(VanillaItems::STEAK());
    }

    public static function sendSumoKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->getInventory()->setItem(0, VanillaItems::STICK());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC,100,false));
    }

    public static function sendGappleKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getEffects()->clear();
        $player->setHealth(20);

        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);

        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);

        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, 64));
        $player->getInventory()->addItem($helmet);
        $player->getInventory()->addItem($chestplate);
        $player->getInventory()->addItem($leggins);
        $player->getInventory()->addItem($boots);

        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendResistanceKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getInventory()->addItem(VanillaItems::DIAMOND_SWORD()->setUnbreakable());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC, 100, false));
    }

    public static function sendBFFAKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->getEffects()->clear();
        $player->setHealth(20);

        $helmet = VanillaItems::IRON_HELMET();
        $chestplate = VanillaItems::IRON_CHESTPLATE();
        $leggins = VanillaItems::IRON_LEGGINGS();
        $boots = VanillaItems::IRON_BOOTS();

        $sword = VanillaItems::IRON_SWORD();
        $pickaxe = VanillaItems::IRON_PICKAXE();

        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 20);

        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem(VanillaBlocks::SANDSTONE()->asItem()->setCount(128));
        $player->getInventory()->setItem(4, VanillaItems::ENDER_PEARL()->setCount(2));
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(5));

        $pickaxe->addEnchantment($unbreaking);
        $player->getInventory()->addItem($pickaxe);

        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendKnockKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $stick = VanillaItems::STICK();
        $stick->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 4));

        $bow = VanillaItems::BOW();
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 3));
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY()));

        $player->getInventory()->addItem($stick);
        $player->getInventory()->addItem($bow->setUnbreakable());
        $player->getInventory()->setItem(9, VanillaItems::ARROW());

        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC,100,false));
    }


    public static function getCombatTimer(Player $player){
        if (isset(self::$combatTimer[$player->getName()])){
            return self::$combatTimer[$player->getName()];
        }
        return 0;
    }

    public static function combatTimer(Player $player){
        if (!isset(self::$combatTimer[$player->getName()])){
            return;
        }
        --self::$combatTimer[$player->getName()];
    }

    public static function removeCombatTimer(Player $player){
        if (!isset(self::$combatTimer[$player->getName()])){
            return;
        }
        unset(self::$combatTimer[$player->getName()]);
    }

    public static function setCombatTimer(Player $player, Player $enemy){
        self::$combatTimer[$player->getName()] = 10;
        self::$combatTimer[$enemy->getName()] = 10;
    }

    public static function removeCombatOpponent(Player $player){
        if (!isset(self::$combatOpponent[$player->getName()])){
            return;
        }
        unset(self::$combatOpponent[$player->getName()]);
    }

    public static function setCombatOpponent(Player $player, Player $enemy){
        self::$combatOpponent[$player->getName()] = $enemy->getName();
        self::$combatOpponent[$enemy->getName()] = $player->getName();
    }

    public static function getCombatOpponent(Player $player){
        if (!isset(self::$combatOpponent[$player->getName()])){
            return "";
        }
        return self::$combatOpponent[$player->getName()];
    }

    public static function hasCombatOpponent(Player $player){
        if (isset(self::$combatOpponent[$player->getName()])){
            return true;
        }
        return false;
    }

}