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

namespace blugin\lib\command\hanlder;

use blugin\lib\command\Overload;
use pocketmine\command\CommandSender;
use pocketmine\utils\Utils;

class ClosureCommandHandler implements ICommandHandler{
    /** @var \Closure */
    protected $closure;

    public function __construct(\Closure $closure){
        Utils::validateCallableSignature(function(CommandSender $sender, array $args, Overload $overload) : bool{
            return true;
        }, $closure);

        $this->closure = $closure;
    }

    /**
     * @param mixed[] $args name => value
     */
    public function handle(CommandSender $sender, array $args, Overload $overload) : bool{
        ($this->closure)($sender, $args, $overload);
    }
}