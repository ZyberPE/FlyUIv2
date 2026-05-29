<?php

declare(strict_types=1);

namespace FlyUI;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\player\Player;

class EventListener implements Listener{

    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function disableFly(Player $player, string $message) : void{

        if($player->isFlying() || $player->getAllowFlight()){

            $player->setFlying(false);
            $player->setAllowFlight(false);

            $player->sendMessage(
                $this->plugin->format($message)
            );
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event) : void{

        $entity = $event->getEntity();
        $damager = $event->getDamager();

        foreach([$entity, $damager] as $player){

            if($player instanceof Player){

                $this->disableFly(
                    $player,
                    $this->plugin->getConfig()->get("combat-disabled-message")
                );
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) : void{

        $player = $event->getPlayer();

        $this->disableFly(
            $player,
            $this->plugin->getConfig()->get("logout-disabled-message")
        );
    }
}
