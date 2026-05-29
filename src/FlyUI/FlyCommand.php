<?php

declare(strict_types=1);

namespace FlyUI;

use jojoe77777\FormAPI\SimpleForm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;

class FlyCommand extends Command{

    private Main $plugin;

    public function __construct(Main $plugin){
        parent::__construct(
            "fly",
            "Open Fly UI",
            "/fly",
            ["flyui"]
        );

        $this->setPermission("flyui.use");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{

        if(!$sender instanceof Player){
            return;
        }

        if(!$this->testPermission($sender)){
            $sender->sendMessage(
                $this->plugin->format(
                    $this->plugin->getConfig()->get("no-permission")
                )
            );
            return;
        }

        $sender->sendMessage(
            $this->plugin->format(
                $this->plugin->getConfig()->get("open-message")
            )
        );

        $form = new SimpleForm(function(Player $player, ?int $data) : void{

            if($data === null){

                $player->sendMessage(
                    $this->plugin->format(
                        $this->plugin->getConfig()->get("close-message")
                    )
                );

                return;
            }

            switch($data){

                case 0:

                    $combatLogger = $this->plugin->getServer()->getPluginManager()->getPlugin("CombatLogger");

                    if($combatLogger !== null){

                        if(method_exists($combatLogger, "isTagged")){

                            if($combatLogger->isTagged($player) && !$player->hasPermission("flyui.bypasscombat")){

                                $player->sendMessage(
                                    $this->plugin->format(
                                        $this->plugin->getConfig()->get("combat-message")
                                    )
                                );

                                return;
                            }
                        }
                    }

                    $player->setAllowFlight(true);
                    $player->setFlying(true);

                    $player->sendMessage(
                        $this->plugin->format(
                            $this->plugin->getConfig()->get("enabled-message")
                        )
                    );

                break;

                case 1:

                    $player->setFlying(false);
                    $player->setAllowFlight(false);

                    $player->sendMessage(
                        $this->plugin->format(
                            $this->plugin->getConfig()->get("disabled-message")
                        )
                    );

                break;

                case 2:

                    $player->sendMessage(
                        $this->plugin->format(
                            $this->plugin->getConfig()->get("close-message")
                        )
                    );

                break;
            }
        });

        $form->setTitle(
            $this->plugin->format(
                $this->plugin->getConfig()->get("title")
            )
        );

        $form->setContent(
            $this->plugin->format(
                $this->plugin->getConfig()->get("content")
            )
        );

        $buttons = $this->plugin->getConfig()->get("buttons");

        $form->addButton(
            $this->plugin->format($buttons["enable"])
        );

        $form->addButton(
            $this->plugin->format($buttons["disable"])
        );

        $form->addButton(
            $this->plugin->format($buttons["close"])
        );

        $sender->sendForm($form);
    }
}
