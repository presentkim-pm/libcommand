<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\lib\command;

use blugin\lib\lang\LanguageHolder;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

abstract class Subcommand implements PluginOwned{
    /** @var MainCommand */
    private $mainCommand;

    /** @var string */
    private $name;

    /** @var string[] */
    private $aliases;

    /**
     * @param MainCommand $mainCommand
     * @param string      $name
     * @param array       $aliases
     */
    public function __construct(MainCommand $mainCommand, string $name, array $aliases){
        $this->mainCommand = $mainCommand;
        $this->name = $name;
        $this->aliases = $aliases;
    }

    /**
     * @param CommandSender $sender
     * @param string[]      $args = []
     */
    public function handle(CommandSender $sender, array $args = []) : void{
        if(!$this->testPermission($sender))
            return;

        if(!$this->execute($sender, $args)){
            $sender->sendMessage($this->mainCommand->getMessage($sender, "commands.generic.usage", [$this->getUsage()]));
        }
    }

    /**
     * @param CommandSender $sender
     *
     * @return bool
     */
    public function testPermission(CommandSender $sender) : bool{
        if($sender->hasPermission($this->getPermission()))
            return true;

        $sender->sendMessage($this->mainCommand->getMessage($sender, TextFormat::RED . "%commands.generic.permission"));
        return false;
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function checkLabel(string $label) : bool{
        return strcasecmp($label, $this->name) === 0 || in_array($label, $this->aliases);
    }

    /** @return MainCommand */
    public function getMainCommand() : MainCommand{
        return $this->mainCommand;
    }

    /** @return string */
    public function getName() : string{
        return $this->name;
    }

    /** @param string $name */
    public function setName(string $name) : void{
        $this->name = $name;
    }

    /** @return string[] */
    public function getAliases() : array{
        return $this->aliases;
    }

    /** @param string[] $aliases */
    public function setAliases(array $aliases) : void{
        $this->aliases = $aliases;
    }

    /** @return string */
    public function getPermission() : string{
        return $this->mainCommand->getPermission() . "." . $this->getLabel();
    }

    /** @return string */
    public function getUsage() : string{
        $plugin = $this->mainCommand->getOwningPlugin();
        if($plugin instanceof LanguageHolder){
            $label = strtolower($plugin->getName());
            return $plugin->getLanguage()->translate("commands.$label.{$this->getLabel()}.usage");
        }
        return "";
    }

    /**
     * @param CommandSender $sender
     * @param string[]      $args = []
     *
     * @return bool
     */
    public abstract function execute(CommandSender $sender, array $args = []) : bool;

    /** @return string */
    public abstract function getLabel() : string;
}