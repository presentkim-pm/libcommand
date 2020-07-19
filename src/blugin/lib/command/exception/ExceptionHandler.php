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

use blugin\lib\command\MainCommand;
use blugin\lib\command\Subcommand;
use DaveRandom\CallbackValidator\BuiltInTypes;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use pocketmine\command\CommandSender;
use pocketmine\utils\Utils;
use Webmozart\Assert\Assert;

class ExceptionHandler{
    /** @var MainCommand */
    private $mainCommand;

    /** @var \Closure[] exception class => handler */
    private $handlers = [];

    /** @param MainCommand $mainCommand */
    public function __construct(MainCommand $mainCommand){
        $this->mainCommand = $mainCommand;
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

        $this->handlers[$className]($exception, $sender, $subcommand, $this);
        return true;
    }

    /**
     * @param string   $className
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
        $sig = new CallbackType(
            new ReturnType(BuiltInTypes::VOID),
            new ParameterType("e", $className, ParameterType::COVARIANT),
            new ParameterType("sender", CommandSender::class),
            new ParameterType("subcommand", Subcommand::class,ParameterType::COVARIANT | ParameterType::OPTIONAL),
            new ParameterType("command", MainCommand::class,  ParameterType::COVARIANT | ParameterType::OPTIONAL)
        );
        if(!$sig->isSatisfiedBy($handlerFunc)){
            throw new \TypeError("Declaration of callable `" . CallbackType::createFromCallable($handlerFunc) . "` must be compatible with `" . $sig . "`");
        }

        $this->handlers[$className] = $handlerFunc;
    }
}