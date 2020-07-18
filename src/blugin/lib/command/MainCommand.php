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

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class MainCommand extends Command implements PluginOwned, CommandExecutor{
    use PluginOwnedTrait;

    /** @var Subcommand[] */
    private $subcommands = [];

    /**
     * @param string     $name
     * @param PluginBase $owner
     */
    public function __construct(string $name, PluginBase $owner){
        parent::__construct($name);
        $this->owningPlugin = $owner;
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string[]      $args
     *
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        return $this->owningPlugin->isEnabled() && $this->testPermission($sender) && $this->onCommand($sender, $this, $commandLabel, $args);
    }

    /**
     * @param CommandSender $sender
     * @param Command       $command
     * @param string        $label
     * @param string[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        $label = array_shift($args);
        foreach($this->subcommands as $key => $subcommand){
            if($subcommand->checkLabel($label)){
                $subcommand->handle($sender, $args);
                return true;
            }
        }
        return false;
    }

    /**
     * @param CommandSender          $sender
     * @param string                 $str
     * @param float[]|int[]|string[] $params
     *
     * @return string
     */
    public function getMessage(CommandSender $sender, string $str, array $params = []) : string{
        return $sender->getLanguage()->translateString($str, $params);
    }

    /** @return Subcommand[] */
    public function getSubcommands() : array{
        return $this->subcommands;
    }

    /** @param Subcommand $subcommand */
    public function registerSubcommand(Subcommand $subcommand) : void{
        $this->subcommands[$subcommand->getLabel()] = $subcommand;
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function unregisterSubcommand(string $label) : bool{
        if(isset($this->subcommands[$label])){
            unset($this->subcommands[$label]);
            return true;
        }
        return false;
    }
}