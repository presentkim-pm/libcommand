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
}