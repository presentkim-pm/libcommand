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

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;

/**
 * This trait override most methods in the {@link PluginBase} abstract class.
 */
trait SubcommandTrait{
    /** @var MainCommand */
    private $mainCommand;

    /** @return MainCommand */
    public function getMainCommand() : MainCommand{
        return $this->mainCommand;
    }

    /**
     * Register Main command
     *
     * @param string|null $label
     *
     * @return MainCommand
     */
    public function registerCommand(?string $label = null) : MainCommand{
        $label = trim(strtolower($label ?? $this->getName()));
        /** @noinspection PhpParamsInspection */
        $this->mainCommand = new MainCommand($label, $this);
        $this->mainCommand->setPermission("$label.cmd");
        $this->mainCommand->setAliases($this->getConfig()->getNested("command.aliases", []));

        $this->getServer()->getCommandMap()->register($this->getName(), $this->mainCommand);
        return $this->mainCommand;
    }

    public function recalculatePermissions() : void{
        $permissions = PermissionManager::getInstance()->getPermissions();
        $config = $this->getConfig();

        $mainPermission = $this->mainCommand->getPermission();
        $defaultValue = $config->getNested("permission.main", Permission::DEFAULT_FALSE);
        if($defaultValue !== null){
            $permissions[$mainPermission]->setDefault($config->getNested("permission.main"));
        }
        foreach($this->mainCommand->getSubcommands() as $key => $subcommand){
            $label = $subcommand->getLabel();
            $defaultValue = $config->getNested("permission.children.$label", Permission::DEFAULT_FALSE);
            if($defaultValue !== null){
                $permissions["$mainPermission.$label"]->setDefault($defaultValue);
            }
        }
    }
}