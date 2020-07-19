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
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class MainCommand extends Command implements PluginOwned, CommandExecutor{
    use PluginOwnedTrait;

    /** @var Subcommand[] */
    private $subcommands = [];

    /** @var ErrorHandler */
    private $errorHander;

    /**
     * @param string     $name
     * @param PluginBase $owner
     */
    public function __construct(string $name, PluginBase $owner){
        parent::__construct($name);
        $this->owningPlugin = $owner;
        $this->errorHander = new ErrorHandler($this);
        $this->errorHander->register(InvalidCommandSyntaxException::class, function(CommandSender $sender, Subcommand $subcommand) : void{
            $sender->sendMessage($sender->getLanguage()->translateString("commands.generic.usage", [$subcommand->getUsage()]));
        });

        if($owner instanceof LanguageHolder){
            $label = strtolower($owner->getName());
            $this->setUsage($owner->getLanguage()->translate("commands.$label.usage"));
            $this->setDescription($owner->getLanguage()->translate("commands.$label.description"));
        }
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param string[]      $args
     *
     * @return bool
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        $label = array_shift($args) ?? "";
        foreach($this->subcommands as $key => $subcommand){
            if($subcommand->checkLabel($label)){
                try{
                    $subcommand->handle($sender, $args);
                }catch(\Exception $e){
                    if(!$this->errorHander->handle($e, $sender, $subcommand))
                        throw $e;
                }
                return true;
            }
        }
        throw new InvalidCommandSyntaxException();
    }

    /**
     * @param CommandSender          $sender
     * @param string                 $str
     * @param float[]|int[]|string[] $params
     *
     * @return string
     */
    public function getMessage(CommandSender $sender, string $str, array $params = []) : string{
        if($this->owningPlugin instanceof LanguageHolder){
            return $this->owningPlugin->getLanguage()->translate($str, $params);
        }

        return $sender->getLanguage()->translateString($str, $params);
    }

    /**
     * @param CommandSender          $sender
     * @param string                 $str
     * @param float[]|int[]|string[] $params
     */
    public function sendMessage(CommandSender $sender, string $str, array $params = []) : void{
        $sender->sendMessage($this->getMessage($sender, $str, $params));
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

    /** @return ErrorHandler */
    public function getErrorHander() : ErrorHandler{
        return $this->errorHander;
    }
}