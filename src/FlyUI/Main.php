<?php

declare(strict_types=1);

namespace FlyUI;

use jojoe77777\FormAPI\SimpleForm;

use pocketmine\plugin\PluginBase;

use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener{

    /** @var array<string, bool> */
    private array $noFall = [];

    public function onEnable() : void{

        $this->saveDefaultConfig();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getLogger()->info("FlyUI Enabled");
    }

    public function format(string $text) : string{

        return str_replace(
            ["&", "\\n"],
            ["§", "\n"],
            $text
        );
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!$sender instanceof Player){
            return true;
        }

        switch($command->getName()){

            case "fly":
            case "flyui":

                if(!$sender->hasPermission("flyui.use")){

                    $sender->sendMessage(
                        $this->format(
                            $this->getConfig()->get("no-permission")
                        )
                    );

                    return true;
                }

                $sender->sendMessage(
                    $this->format(
                        $this->getConfig()->get("open-message")
                    )
                );

                $form = new SimpleForm(function(Player $player, ?int $data) : void{

                    if($data === null){

                        $player->sendMessage(
                            $this->format(
                                $this->getConfig()->get("close-message")
                            )
                        );

                        return;
                    }

                    switch($data){

                        case 0:

                            $combatLogger = $this->getServer()->getPluginManager()->getPlugin("CombatLogger");

                            if($combatLogger !== null){

                                if(method_exists($combatLogger, "isTagged")){

                                    if($combatLogger->isTagged($player) && !$player->hasPermission("flyui.bypasscombat")){

                                        $player->sendMessage(
                                            $this->format(
                                                $this->getConfig()->get("combat-message")
                                            )
                                        );

                                        return;
                                    }
                                }
                            }

                            $player->setAllowFlight(true);
                            $player->setFlying(true);

                            $player->sendMessage(
                                $this->format(
                                    $this->getConfig()->get("enabled-message")
                                )
                            );

                        break;

                        case 1:

                            if(!$player->isOnGround()){
                                $this->noFall[$player->getName()] = true;
                            }

                            $player->setFlying(false);
                            $player->setAllowFlight(false);

                            $player->sendMessage(
                                $this->format(
                                    $this->getConfig()->get("disabled-message")
                                )
                            );

                        break;

                        case 2:

                            $player->sendMessage(
                                $this->format(
                                    $this->getConfig()->get("close-message")
                                )
                            );

                        break;
                    }
                });

                $form->setTitle(
                    $this->format(
                        $this->getConfig()->get("title")
                    )
                );

                $form->setContent(
                    $this->format(
                        $this->getConfig()->get("content")
                    )
                );

                $buttons = $this->getConfig()->get("buttons");

                $form->addButton(
                    $this->format($buttons["enable"])
                );

                $form->addButton(
                    $this->format($buttons["disable"])
                );

                $form->addButton(
                    $this->format($buttons["close"])
                );

                $sender->sendForm($form);

            return true;
        }

        return false;
    }

    public function disableFly(Player $player, string $message) : void{

        if($player->isFlying() || $player->getAllowFlight()){

            if(!$player->isOnGround()){
                $this->noFall[$player->getName()] = true;
            }

            $player->setFlying(false);
            $player->setAllowFlight(false);

            $player->sendMessage(
                $this->format($message)
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
                    $this->getConfig()->get("combat-disabled-message")
                );
            }
        }
    }

    public function onFallDamage(EntityDamageEvent $event) : void{

        $entity = $event->getEntity();

        if(!$entity instanceof Player){
            return;
        }

        if(
            $event->getCause() === EntityDamageEvent::CAUSE_FALL &&
            isset($this->noFall[$entity->getName()])
        ){
            $event->cancel();
            unset($this->noFall[$entity->getName()]);
        }
    }

    public function onQuit(PlayerQuitEvent $event) : void{

        $player = $event->getPlayer();

        $this->disableFly(
            $player,
            $this->getConfig()->get("logout-disabled-message")
        );
    }
}
