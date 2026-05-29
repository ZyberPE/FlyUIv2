<?php

declare(strict_types=1);

namespace FlyUI;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\player\Player;

class EventListener implements Listener{

    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onDamage(EntityDamageByEntityEvent $event) : void{

        $entity = $event->getEntity();
        $damager = $event->getDamager();

        foreach([$entity, $damager] as $player){

            if($player instanceof Player){

                if($player->isFlying() || $player->getAllowFlight()){

                    $player->setFlying(false);
                    $player->setAllowFlight(false);

                    $player->sendMessage(
                        $this->plugin->format(
                            $this->plugin->getConfig()->get("combat-disabled-message")
                        )
                    );
                }
            }
        }
    }
}
