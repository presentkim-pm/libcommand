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

namespace blugin\lib\command\exception\defaults;

use blugin\lib\command\exception\IHandleable;
use blugin\lib\command\MainCommand;
use blugin\lib\command\Subcommand;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;

abstract class GenericInvalidArgumentException extends InvalidCommandSyntaxException implements IHandleable{
    const LABEL = "";

    public static function getHandler() : \Closure{
        return function(GenericInvalidArgumentException $e, CommandSender $sender, Subcommand $subcommand, MainCommand $mainCommand) : void{
            $mainCommand->sendMessage($sender, "commands.generic.invalid" . $e::LABEL, [$e->getMessage()]);
        };
    }
}