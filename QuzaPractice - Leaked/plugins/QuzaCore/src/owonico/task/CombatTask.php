<?php

namespace owonico\task;
    
use owonico\{Main, Variables};
use owonico\manager\PlayerManager;
use pocketmine\event\entity\EntityDamageEvent;

class CombatTask extends \pocketmine\scheduler\Task {

    public $plugin;
    public $player;
    public $opponent;

    public function __construct(Main $plugin, \pocketmine\player\Player $player, \pocketmine\player\Player $opponent){
        $this->plugin = $plugin;
        $this->player = $player;
        $this->opponent = $opponent;

        PlayerManager::$isCombat[$player->getName()] = true;
        PlayerManager::$isCombat[$player->getName()] = true;
    }

    public function onRun(): void
    {
        if ($this->player->isConnected() && $this->opponent->isConnected() && $this->player->isAlive() && $this->opponent->isAlive()){
            PlayerManager::combatTimer($this->player);
            PlayerManager::combatTimer($this->opponent);

            if (PlayerManager::getCombatTimer($this->player) <= 0 && PlayerManager::getCombatTimer($this->opponent) <= 0){
                PlayerManager::removeCombatOpponent($this->player);
                PlayerManager::removeCombatOpponent($this->opponent);
                PlayerManager::removeCombatTimer($this->player);
                PlayerManager::removeCombatTimer($this->opponent);

                $this->player->setLastDamageCause(new EntityDamageEvent($this->player, 0, 0.0, []));
                $this->opponent->setLastDamageCause(new EntityDamageEvent($this->opponent, 0, 0.0, []));

                $this->player->sendMessage(Variables::Prefix . "§aYou are not in combat now!");
                $this->opponent->sendMessage(Variables::Prefix . "§aYou are not in combat now!");

                foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl){
                    $this->player->showPlayer($pl);
                    $this->opponent->showPlayer($pl);
                }
                $this->getHandler()?->cancel();
            }
            if ($this->player->getWorld() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld() || $this->opponent->getWorld() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld() ||
                $this->player->hasGravity() == false || $this->opponent->hasGravity() == false){
                PlayerManager::removeCombatOpponent($this->player);
                PlayerManager::removeCombatOpponent($this->opponent);
                PlayerManager::removeCombatTimer($this->player);
                PlayerManager::removeCombatTimer($this->opponent);
                $this->player->setLastDamageCause(new EntityDamageEvent($this->player, 0, 0.0, []));
                $this->opponent->setLastDamageCause(new EntityDamageEvent($this->opponent, 0, 0.0, []));
                $this->player->sendMessage(Variables::Prefix . "§aYou are not in combat now!");
                $this->opponent->sendMessage(Variables::Prefix . "§aYou are not in combat now!");

                foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl){
                    $this->player->showPlayer($pl);
                    $this->opponent->showPlayer($pl);
                }
                $this->getHandler()?->cancel();
            }
        } else{
            PlayerManager::removeCombatOpponent($this->player);
            PlayerManager::removeCombatOpponent($this->opponent);
            PlayerManager::removeCombatTimer($this->player);
            PlayerManager::removeCombatTimer($this->opponent);

            if ($this->player->isConnected()) {
                $this->player->sendMessage(Variables::Prefix . "§aYou are not in combat now!");
            }
            if ($this->opponent->isConnected()) {
                $this->opponent->sendMessage(Variables::Prefix . "§aYou are not in combat now!");
            }

            foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl){
                $this->player?->showPlayer($pl);
                $this->opponent?->showPlayer($pl);
            }
            $this->getHandler()?->cancel();
        }
    }

    public function onCancel(): void
    {
        unset(PlayerManager::$isCombat[$this->player->getName()]);
        unset(PlayerManager::$isCombat[$this->opponent->getName()]);
    }
}
