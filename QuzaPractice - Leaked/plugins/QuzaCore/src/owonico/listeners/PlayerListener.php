<?php

namespace owonico\listeners;

use owonico\{Main, Variables};
use owonico\manager\{FormManager, PlayerManager, RankManager};
use owonico\task\{Base, BuildFFATask, CombatTask, PearlTask, ScoreboardTask};
use owonico\utils\Utils;
use maipian\await\generator\Await;
use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\EnderPearl;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\sound\XpLevelUpSound;

class PlayerListener implements Listener{

    public $plugin;
    public $server;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->server = Server::getInstance();
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();


        Main::$playerArena[$player->getName()] = "Lobby";
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        $player->teleport($location);
        
        Main::ensureData($player);


        $rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        if (!$rankCfg->exists($player->getXuid())){
            RankManager::setPlayerRank($player, "Player");
        }

        $player->recalculatePermissions();
        foreach (RankManager::getPlayerRank($player)->getPermissions() as $permission){
            $player->addAttachment($this->plugin, $permission, true);
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $event->setJoinMessage("§8[§a+§8] §a" . $player->getDisplayName());

        $player->setGamemode(GameMode::ADVENTURE());

        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        $player->teleport($location);

        $player->sendMessage(Variables::Prefix . "§bLoading your data...");


        $scoreboard = new Config(Main::getInstance()->getDataFolder() . "settings/Scoreboard.yml", Config::YAML);

        if (!$scoreboard->exists($player->getXuid())){
            $scoreboard->set($player->getXuid(), true);
            $scoreboard->save();

            Main::$scoreboardEnabled[$player->getName()] = true;
        }


        Await::f2c(function () use ($player, $scoreboard) {
            $player->getHungerManager()->setFood(20);
            $player->getHungerManager()->setEnabled(false);
            $player->setMaxHealth(20);
            $player->setHealth(20);
            $player->getInventory()->setHeldItemIndex(0);
            $player->getXpManager()->setXpLevel(0);
            $player->getXpManager()->setXpProgress(0);
            $player->getEffects()->clear();

            PlayerManager::sendLobbyKit($player);

            $this->plugin->getScheduler()->scheduleRepeatingTask(new Base($this->plugin, $player), 20);
            $this->plugin->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($this->plugin, $player), 20);

            Main::$scoreboardEnabled[$player->getName()] = $scoreboard->get($player->getXuid());

            $player->sendMessage("\n\n§b Discord:§7 dsc.gg/quza\n§b YouTube:§7 @quza2018\n");

            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(5));

            $playercape = new Config($this->plugin->getDataFolder() . "capes/data.yml", Config::YAML);
            if(file_exists($this->plugin->getDataFolder() . "capes/" . $playercape->get($player->getXuid()) . ".png")) {
                $oldSkin = $player->getSkin();
                $capeData = $this->plugin->createCape($playercape->get($player->getXuid()));
                $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                $player->setSkin($setCape);
                $player->sendSkin();
            } else {
                $playercape->remove($player->getXuid());
                $playercape->save();
            }

            yield from $this->plugin->std->sleep(20);
        });

        /**
         * HyperiumMC function code
         */

        if ($this->isJohnnyWai($player)){
            $player->sendMessage("§l§8[§bQuanMC§8] §r§a已检测是Johnnywai");
            $player->setDisplayName("I'm very stupid, I liked copy and paste");
        }

        if ($this->plugin->getConfig()->get("maintenance") === true && !$player->hasPermission("quza.staff")){
            $player->kick("Server Maintenance", false);
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $event->setQuitMessage("§8[§c-§8] §c" . $player->getDisplayName());

        if (isset(Main::$playerArena[$player->getName()])){
            unset(Main::$playerArena[$player->getName()]);
        }
        if (isset(Main::$pearlCooldown[$player->getName()])){
            unset(Main::$pearlCooldown[$player->getName()]);
        }

        if (isset(PlayerManager::$isCombat[$player->getName()])){
            if (PlayerManager::getCombatOpponent($player) !== ""){
                $opponent = $this->server->getPlayerExact(PlayerManager::getCombatOpponent($player));
                $this->server->broadcastMessage(Variables::Prefix . "§a" . $player->getName() . " §8lost connection when fighting with §c" . $opponent->getName() . " §8[§c" . $opponent->getHealth() . " §aHP§8]");

                PlayerManager::addPlayerDeath($player);
                PlayerManager::addPlayerKill($opponent);

            }
        }
    }


    public function onDamage(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;

        if ($entity->getWorld() === $this->server->getWorldManager()->getDefaultWorld()){
            $event->cancel();
        }

        if ($event->getCause() == $event::CAUSE_FALL){
            $event->cancel();
        }
    }

    public function onInventoryChange(InventoryTransactionEvent $event){
        $translation = $event->getTransaction();
        $actions = $translation->getActions();
        $source = $translation->getSource();

        if ($source->getWorld() === $this->server->getWorldManager()->getDefaultWorld()){
            foreach ($actions as $action){
                if (!$source->hasPermission("quza.staff")) {
                    if ($action instanceof SlotChangeAction) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld() == $this->server->getWorldManager()->getDefaultWorld() || $player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa || $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa) {
            if (!$player->hasPermission("quza.staff")) {
                $event->cancel();
            }
            if ($player->getGamemode() !== GameMode::CREATIVE()) {
                $event->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld() === $this->server->getWorldManager()->getDefaultWorld()) {
            if (!$player->hasPermission("quza.staff") ||!$this->plugin->getServer()->isOp($player->getName())) {
                $event->cancel();
            }
            if (!$player->getGamemode() == GameMode::CREATIVE()) {
                $event->cancel();
            }
        }
    }

    public function onBlockClick(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($player->getWorld() == $this->server->getWorldManager()->getDefaultWorld()){
            if ($event->getAction() == $event::RIGHT_CLICK_BLOCK){
                $event->cancel();
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event){
        $player = $event->getPlayer();
        $event->cancel();
    }

    public function onDeath(PlayerDeathEvent $event){
        $event->setDrops([]);
        $event->setDeathMessage("");
        $event->setXpDropAmount(0);
        $this->server->dispatchCommand($event->getPlayer(), "hub");
    }

    public function onRespawn(PlayerRespawnEvent $event){
        $this->server->dispatchCommand($event->getPlayer(), "hub");
    }

    public function onRegen(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;

        if ($event->getRegainReason() == $event::CAUSE_SATURATION){
            $event->cancel();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event){
        //$player = $event->getPlayer();
        //$player->getHungerManager()->setFood(20);
        $event->cancel();
    }

    public function onProjectileHit(ProjectileHitBlockEvent $event){
        $projectile = $event->getEntity();
        $projectile->flagForDespawn();

        if ($projectile instanceof SplashPotion){
            $player = $projectile->getOwningEntity();

            if ($player === null)return;

            if ($player->isAlive()){
                if ($player instanceof Player){
                    if ($player->isConnected()){
                        if ($projectile->getLocation()->distance($player->getLocation()->asVector3()) <= 3){
                            $player->setHealth($player->getHealth() + 3.5);
                        }
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            if ($player->getLocation()->getY() < 5) {
                $player->getServer()->dispatchCommand($player, "hub");
            }
        } else {
            if($player->getLocation()->getY() < 1){
                $lastDmg = $player->getLastDamageCause();
                if ($lastDmg instanceof EntityDamageByEntityEvent){
                    $dmg = $lastDmg->getDamager();

                    if (!$dmg instanceof Player) return;

                    $player->getServer()->dispatchCommand($player, "hub");

                    $player->setLastDamageCause(new EntityDamageEvent($player, 0, 0.0, []));
                    $dmg->setLastDamageCause(new EntityDamageEvent($dmg, 0, 0.0, []));

                    $this->server->broadcastMessage("§a{$dmg->getName()} §7sent §c{$player->getName()}§7 to their void");

                    PlayerManager::addPlayerKill($dmg);
                    PlayerManager::addPlayerDeath($player);

                    $dmg->setHealth(20);
                    if (!isset(Main::$playerArena[$dmg->getName()])) return;
                    switch (Main::$playerArena[$dmg->getName()]){
                        case "NodebuffFFA":
                            PlayerManager::sendNodebuffKit($dmg);
                            break;
                        case "ComboFFA":
                            PlayerManager::sendComboKit($dmg);
                            break;
                        case "FistFFA":
                            PlayerManager::sendFistKit($dmg);
                            break;
                        case "SumoFFA":
                            PlayerManager::sendSumoKit($dmg);
                            break;
                        case "GappleFFA":
                            PlayerManager::sendGappleKit($dmg);
                            break;
                        case "KnockFFA":
                            PlayerManager::sendKnockKit($dmg);
                            break;
                        case "BuildFFA":
                            PlayerManager::sendBFFAKit($dmg);
                            break;
                    }
                }else{
                    $player->getServer()->dispatchCommand($player, "hub");
                }
            }
        }
    }

    public function onAttack(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;

        if ($event->getCause() == $event::CAUSE_FALL) $event->cancel();

        if ($entity->getWorld() === $this->server->getWorldManager()->getDefaultWorld()){
            $event->cancel();
        }

        if ($event->getCause() == $event::CAUSE_VOID){
            $lastDmg = $entity->getLastDamageCause();
            if ($lastDmg instanceof EntityDamageByEntityEvent){
                $dmg = $lastDmg->getDamager();

                if (!$dmg instanceof Player) return;

                $entity->getServer()->dispatchCommand($entity, "hub");

                $entity->setLastDamageCause($event);
                $dmg->setLastDamageCause($event);

                $this->server->broadcastMessage("§a{$entity->getName()}§7 sent§c {$dmg->getName()}§7 to their void");

                PlayerManager::addPlayerKill($dmg);
                PlayerManager::addPlayerDeath($entity);

                $dmg->setHealth(20);
                if (!isset(Main::$playerArena[$dmg->getName()])) return;
                switch (Main::$playerArena[$dmg->getName()]){
                    case "NodebuffFFA":
                        PlayerManager::sendNodebuffKit($dmg);
                        break;
                    case "ComboFFA":
                        PlayerManager::sendComboKit($dmg);
                        break;
                    case "FistFFA":
                        PlayerManager::sendFistKit($dmg);
                        break;
                    case "SumoFFA":
                        PlayerManager::sendSumoKit($dmg);
                        break;
                    case "GappleFFA":
                        PlayerManager::sendGappleKit($dmg);
                        break;
                    case "KnockFFA":
                        PlayerManager::sendKnockKit($dmg);
                        break;
                    case "BuildFFA":
                        PlayerManager::sendBFFAKit($dmg);
                        break;
                }
            }
        }
    }

    public function packetReceive(DataPacketReceiveEvent $e) : void{
        $cpsPopup = new Config($this->plugin->getDataFolder() . "settings/CPSPopup.yml", Config::YAML);
        $pk = $e->getPacket();
        $player = $e->getOrigin()->getPlayer();

        if($e->getOrigin()->getPlayer() == null) return;

        if ($cpsPopup->exists($e->getOrigin()->getPlayer()->getXuid())) {
            if (
                ($e->getPacket()::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $e->getPacket()->trData instanceof UseItemOnEntityTransactionData) ||
                ($e->getPacket()::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $e->getPacket()->sound === LevelSoundEvent::ATTACK_NODAMAGE) ||
                ($e->getPacket()::NETWORK_ID === PlayerActionPacket::NETWORK_ID && $e->getPacket()->action === PlayerAction::START_BREAK)
            ) {
                $this->plugin->addCPS($e->getOrigin()->getPlayer());
                $e->getOrigin()->getPlayer()->sendActionBarMessage("§b{$this->plugin->getCPS($e->getOrigin()->getPlayer())} CPS");
            }
        }

        if($pk instanceof InventoryTransactionPacket){
            if($pk->trData instanceof UseItemOnEntityTransactionData){
                if($pk->trData->getActionType() == UseItemOnEntityTransactionData::ACTION_ATTACK){
                    if($player->isSpectator() || $player->getGamemode()->equals(GameMode::SPECTATOR())){
                        $e->cancel();
                    }
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if($player === null) return;

        switch ($packet->pid()){
            case PlayerAuthInputPacket::NETWORK_ID:
                $autoSprint = new Config($this->plugin->getDataFolder() . "settings/AutoSprint.yml", Config::YAML);

                if ($autoSprint->exists($player->getXuid())){
                    if($player->isSprinting() && $packet->hasFlag(PlayerAuthInputFlags::DOWN)){
                        $player->setSprinting(false);
                    }elseif(!$player->isSprinting() && $packet->hasFlag(PlayerAuthInputFlags::UP)){
                        $player->setSprinting();
                    }
                }
                break;
            case InventoryTransactionPacket::NETWORK_ID:
                if($packet->trData->getTypeId() == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
                    $trData = $packet->trData;
                    if ($trData->getActionType() == UseItemOnEntityTransactionData::ACTION_ATTACK){
                        $entityId = $trData->getActorRuntimeId();
                        $hitParticles = new Config($this->plugin->getDataFolder() . "settings/HitParticles.yml", Config::YAML);

                        if ($hitParticles->exists($player->getXuid())){
                            $player->getServer()->broadcastPackets([$player], [AnimatePacket::create($entityId, AnimatePacket::ACTION_CRITICAL_HIT)]);
                        }
                    }
                }
                break;
        }
    }

    public function onWorldTp(EntityTeleportEvent $event){
        $entity = $event->getEntity();

        if (!$entity instanceof Player) return;

        if ($event->getFrom()->getWorld() !== $event->getTo()->getWorld()){
            if ($event->getTo()->getWorld()->getFolderName() != Variables::Nodebuffffa or $event->getTo()->getWorld()->getFolderName() != Variables::Comboffa or $event->getTo()->getWorld()->getFolderName() != Variables::Fistffa or $event->getTo()->getWorld()->getFolderName() != Variables::Sumoffa or $event->getTo()->getWorld()->getFolderName() != Variables::Gappleffa or $event->getTo()->getWorld()->getFolderName() != Variables::Knockffa or $event->getTo()->getWorld()->getFolderName() != Variables::Buildffa){
                unset(Main::$playerArena[$entity->getName()]);
            }
        }
    }

    public function onKnockback(EntityDamageByEntityEvent $event){
        $damager = $event->getDamager();
        $entity = $event->getEntity();
        if (!$damager instanceof Player && !$entity instanceof Player) return;

        if ($damager->getWorld()->getFolderName() === Variables::Nodebuffffa or
            $damager->getWorld()->getFolderName() === Variables::Fistffa or
            $damager->getWorld()->getFolderName() === Variables::Sumoffa or
            $damager->getWorld()->getFolderName() === Variables::Comboffa or
            $damager->getWorld()->getFolderName() === Variables::Gappleffa or
            $damager->getWorld()->getFolderName() === Variables::Knockffa or
            $damager->getWorld()->getFolderName() === Variables::Buildffa) {
        if ($entity->getHealth() <= $event->getBaseDamage()){
            $entity->getServer()->dispatchCommand($entity, "hub");
            $this->server->broadcastMessage("§a{$entity->getName()} §7was killed by §c{$damager->getName()}");

            PlayerManager::addPlayerKill($damager);
            PlayerManager::addPlayerDeath($entity);

            $damager->setHealth(20);
            if (!isset(Main::$playerArena[$damager->getName()])) return;
            switch (Main::$playerArena[$damager->getName()]){
                case "NodebuffFFA":
                    PlayerManager::sendNodebuffKit($damager);
                    break;
                case "ComboFFA":
                    PlayerManager::sendComboKit($damager);
                    break;
                case "FistFFA":
                    PlayerManager::sendFistKit($damager);
                    break;
                case "SumoFFA":
                    PlayerManager::sendSumoKit($damager);
                    break;
                case "GappleFFA":
                    PlayerManager::sendGappleKit($damager);
                    break;
                case "KnockFFA":
                    PlayerManager::sendKnockKit($damager);
                    break;
                case "BuildFFA":
                    PlayerManager::sendBFFAKit($damager);
                    break;
            }
        }
        }

        if (isset(Main::$playerArena[$entity->getName()])){
            switch (Main::$playerArena[$entity->getName()]){
                case "NodebuffFFA":
                case "GappleFFA":
                    $event->setKnockBack(0.39);
                    $event->setAttackCooldown(9);
                    break;
                case "SumoFFA":
                case "FistFFA":
                    $event->setKnockBack(0.38);
                    $event->setAttackCooldown(7);
                    break;
                case "ComboFFA":
                    $event->setKnockBack(0.28);
                    $event->setAttackCooldown(2);
                    break;
                default:
                    $event->setKnockBack(0.38);
                    $event->setAttackCooldown(8);
                    break;
            }
        }

        if ($damager->getWorld()->getFolderName() === Variables::Nodebuffffa or
            $damager->getWorld()->getFolderName() === Variables::Fistffa or
            $damager->getWorld()->getFolderName() === Variables::Sumoffa or
            $damager->getWorld()->getFolderName() === Variables::Comboffa or
            $damager->getWorld()->getFolderName() === Variables::Gappleffa or
            $damager->getWorld()->getFolderName() === Variables::Knockffa) {
            if (!isset(PlayerManager::$combatOpponent[$entity->getName()]) && !isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                PlayerManager::setCombatOpponent($entity, $damager);
                PlayerManager::setCombatTimer($entity, $damager);

                if (!$entity->isConnected() || !$damager->isConnected()) {
                    return;
                }
                $entity->sendMessage(Variables::Prefix . "§aYou are now in combat with §f" . $damager->getDisplayName());
                $damager->sendMessage(Variables::Prefix . "§aYou are now in combat with §f" . $entity->getDisplayName());

                $this->plugin->getScheduler()->scheduleRepeatingTask(new CombatTask($this->plugin, $entity, $damager), 20);

                foreach ($this->server->getOnlinePlayers() as $player) {
                    if ($player !== $damager) {
                        $entity->hidePlayer($player);
                    }
                    if ($player !== $entity) {
                        $damager->hidePlayer($player);
                    }
                }
            } elseif (isset(PlayerManager::$combatOpponent[$entity->getName()]) && !isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                $event->cancel();
                $damager->sendMessage(Variables::Prefix . "§cInterrupting is not allowed!");
            } elseif (!isset(PlayerManager::$combatOpponent[$entity->getName()]) && isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                $event->cancel();
                $damager->sendMessage(Variables::Prefix . "§cYour Enemy is §e" . PlayerManager::getCombatOpponent($damager));
            } elseif (isset(PlayerManager::$combatOpponent[$entity->getName()])) {
                if (PlayerManager::$combatOpponent[$entity->getName()] !== $damager->getName()) {
                    $event->cancel();
                    $damager->sendMessage(Variables::Prefix . "§cInterrupting is not allowed!");
                } else {
                    PlayerManager::setCombatTimer($entity, $damager);
                }
            }
        }
    }

    public function onItemUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->hasCustomName() && $player->getWorld() === $this->server->getWorldManager()->getDefaultWorld()) {
            switch ($item->getId()) {
                case VanillaItems::DIAMOND_SWORD()->getId():
                    $player->sendForm(FormManager::getFFAForm());
                    break;
                case VanillaItems::IRON_SWORD()->getId():
                    $player->sendForm(FormManager::getDuelForm());
                    break;
                case VanillaItems::DIAMOND()->getId():
                    $player->sendForm(FormManager::getCosmeticForm());
                    break;
                case VanillaItems::CLOCK()->getId():
                    $player->sendForm(FormManager::getSettingsForm($player));
                    break;
            }
        }

        if ($player->getWorld()->getFolderName() === Variables::Buildffa) return;
        if ($item instanceof EnderPearl){
            if (isset(Main::$pearlCooldown[$player->getName()])){
                $event->cancel();

                Await::f2c(function () use ($player){
                    $player->getInventory()->removeItem(VanillaItems::ENDER_PEARL());
                    $player->getInventory()->addItem(VanillaItems::ENDER_PEARL()); //to fix ghost item bug

                    yield from $this->plugin->std->sleep(3);
                });
            } else{
                Main::$pearlCooldown[$player->getName()] = 10;
                $this->plugin->getScheduler()->scheduleRepeatingTask(new PearlTask($player), 20);
            }
        }
    }

    /**
     * HyperiumMC function code
     */

    public function isJohnnyWai(Player $player){
        if (strtolower($player->getName()) == "johnnywai666"){
            return true;
        }
        return false;
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();

        $event->setFormat("§r§8[".RankManager::getPlayerRank($player)->getDisplayFormat() . "§r§8] §f" . $player->getDisplayName() . "§8 : §f" . $event->getMessage());

        /**
         * HyperiumMC function code
         */

        if ($this->isJohnnyWai($player)){
            $event->setMessage("§c已封锁刷屏仔的说话权! 你真可悲");
            $player->sendMessage("§e你觉得你有在这里说话的资格吗?? 垃圾");

            return;
        }
    }

    public function onBowBoost(EntityShootBowEvent $event)
    {
        $entity = $event->getEntity();
        $arrow = $event->getProjectile();
        $power = $event->getForce();

        if ($entity instanceof Player and $arrow instanceof Arrow) {
            if ($entity->getWorld() === $this->server->getWorldManager()->getWorldByName(Variables::Knockffa)) {
                if ($power <= 0.8 and $entity->getMovementSpeed() !== 0.0) {
                    $entity->setMotion($entity->getDirectionVector()->multiply(1.2));
                    $entity->broadcastAnimation(new HurtAnimation($entity));
                    $arrow->kill();
                    if ($entity->getHealth() > 1.0) {
                        $entity->setHealth($entity->getHealth() - 1.0);
                    }
                } elseif (($power <= 0.8) and $entity->getMovementSpeed() !== 0.0) {
                    //$arrow->kill();
                }
            }
        }
    }

    public function onBFFAPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($player->getWorld()->getFolderName() == Variables::Buildffa) {
            Main::$bffaplacedblock[Utils::vectorToString($block->getPosition()->asVector3())] = $block->getPosition()->asVector3();

            $this->plugin->getScheduler()->scheduleRepeatingTask(new BuildFFATask($block->getPosition()->asVector3(), $block->getPosition()->getWorld()), 20);

        }
    }

    public function onBFFABreak(BlockBreakEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld()->getFolderName() == Variables::Buildffa){
            if(isset(Main::$bffaplacedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())])){
                unset(Main::$bffaplacedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())]);
            } else {
                $event->cancel();
            }
        }
    }
}
