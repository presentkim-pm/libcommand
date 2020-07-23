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

use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;

/**
 * This trait override most methods in the {@link PluginBase} abstract class.
 */
trait SubcommandTrait{
    /** @var MainCommand */
    private $mainCommand;

    /**
     * @param string|null $label
     *
     * @return MainCommand
     */
    public function getMainCommand(?string $label = null) : MainCommand{
        if($this->mainCommand === null){
            $this->mainCommand = $this->createCommand($label);
        }

        return $this->mainCommand;
    }

    /**
     * Create command
     *
     * @param string|null $label
     *
     * @return MainCommand
     */
    public function createCommand(?string $label = null) : MainCommand{
        $label = trim(strtolower($label ?? $this->getName()));
        /** @noinspection PhpParamsInspection */
        $command = new MainCommand($label, $this);
        $command->setPermission("$label.cmd");
        $command->setAliases($this->getConfig()->getNested("command.aliases", []));

        return $command;
    }

    public function recalculatePermissions() : void{
        $permissionManager = PermissionManager::getInstance();
        $config = $this->getConfig();

        $defaultValue = $config->getNested("command.permission");
        if($defaultValue !== null){
            $permissionManager->getPermission($this->mainCommand->getPermission())->setDefault($defaultValue);
        }
        foreach($this->mainCommand->getSubcommands() as $key => $subcommand){
            $label = $subcommand->getLabel();
            $defaultValue = $config->getNested("command.children.$label.permission");
            if($defaultValue !== null){
                $permissionManager->getPermission($subcommand->getPermission())->setDefault($defaultValue);
            }
        }
    }

    public function onLoad() : void{
        $this->getMainCommand();
    }

    public function onEnable() : void{
        $this->getServer()->getCommandMap()->register($this->getName(), $this->getMainCommand());
    }

    public function onDisble() : void{
        $this->getServer()->getCommandMap()->unregister($this->getMainCommand());
    }
}