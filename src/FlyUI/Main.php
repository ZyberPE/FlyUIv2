<?php

declare(strict_types=1);

namespace FlyUI;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase{

    private static Main $instance;

    public static function getInstance() : Main{
        return self::$instance;
    }

    public function onEnable() : void{
        self::$instance = $this;

        $this->saveDefaultConfig();

        $this->getServer()->getCommandMap()->register("fly", new FlyCommand($this));

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function format(string $text) : string{
        return str_replace(
            ["&", "\\n"],
            ["§", "\n"],
            $text
        );
    }
}
