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

namespace blugin\lib\command\exception;

use blugin\lib\command\exception\defaults\ArgumentLackException;
use blugin\lib\command\exception\defaults\GenericInvalidBlockException;
use blugin\lib\command\exception\defaults\GenericInvalidItemException;
use blugin\lib\command\exception\defaults\GenericInvalidNumberException;
use blugin\lib\command\exception\defaults\GenericInvalidPlayerException;
use blugin\lib\command\exception\defaults\GenericInvalidWorldException;
use blugin\lib\command\exception\defaults\GenericNumberTooBigException;
use blugin\lib\command\exception\defaults\GenericNumberTooSmallException;
use blugin\lib\command\MainCommand;
use blugin\lib\command\Subcommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\Utils;

class ExceptionHandler{
    /** @var MainCommand */
    private $mainCommand;

    /** @var \Closure[] exception class => handler */
    private $handlers = [];

    /** @param MainCommand $mainCommand */
    public function __construct(MainCommand $mainCommand){
        $this->mainCommand = $mainCommand;

        //Register default handlers
        $this->register(ArgumentLackException::class);
        $this->register(GenericInvalidBlockException::class);
        $this->register(GenericInvalidItemException::class);
        $this->register(GenericInvalidNumberException::class);
        $this->register(GenericInvalidPlayerException::class);
        $this->register(GenericInvalidWorldException::class);
        $this->register(GenericNumberTooBigException::class);
        $this->register(GenericNumberTooSmallException::class);
    }

    /**
     * @param \Exception    $exception
     * @param CommandSender $sender
     * @param Subcommand    $subcommand
     *
     * @return bool
     */
    public function handle(\Exception $exception, CommandSender $sender, Subcommand $subcommand) : bool{
        $className = get_class($exception);
        if(!isset($this->handlers[$className]))
            return false;

        $this->handlers[$className]($exception, $sender, $subcommand, $this->mainCommand);
        return true;
    }

    /**
     * @param string        $className
     * @param null|\Closure $handlerFunc \Closure(\Exception $e, CommandSender $sender, Subcommand $subcommand, MainCommand $command)
     */
    public function register(string $className, ?\Closure $handlerFunc = null) : void{
        Utils::testValidInstance($className, \Exception::class);
        if($handlerFunc === null){
            $instance = new $className();
            if($instance instanceof IHandleable){
                $handlerFunc = $instance::getHandler();
            }else{
                throw new \TypeError("$className is Not instanceof IHandleable. Must require '\\Closure \$handerFunc' parameter.");
            }
        }
        Utils::validateCallableSignature(function(\Exception $e, CommandSender $sender, Subcommand $subcommand, MainCommand $command) : void{
        }, $handlerFunc);

        $this->handlers[$className] = $handlerFunc;
    }
}