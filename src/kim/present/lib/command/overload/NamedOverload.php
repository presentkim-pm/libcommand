<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\lib\command\overload;

use kim\present\lib\command\BaseCommand;
use kim\present\lib\command\parameter\additions\ConstParameter;
use kim\present\lib\command\parameter\Parameter;
use kim\present\lib\command\traits\LabelHolderTrait;
use kim\present\lib\command\traits\NameHolderTrait;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class NamedOverload extends Overload{
    use LabelHolderTrait, NameHolderTrait;

    /** @var string[] */
    private $aliases = [];

    public function __construct(BaseCommand $baseCommand, string $name){
        $this->baseCommand = $baseCommand;
        $this->setLabel($name);
        $this->setName($name);
    }

    public function getMessageId(string $str) : string{
        return "commands.{$this->baseCommand->getLabel()}.{$this->getLabel()}.$str";
    }

    /** @return string[] */
    public function getAliases() : array{
        return $this->aliases;
    }

    /** @param string[] $aliases */
    public function setAliases(array $aliases) : Overload{
        $this->aliases = $aliases;
        return $this;
    }

    public function getPermission() : string{
        return $this->baseCommand->getPermission() . "." . $this->name;
    }

    /** @return Parameter[] */
    public function getParameters(Player $player) : array{
        return array_merge([$this->getNameParameter(false, $this->getTranslatedName($player))], parent::getParameters($player));
    }

    public function addParamater(Parameter $parameter) : Overload{
        parent::addParamater($parameter);
        $configData = $this->getBaseCommand()->getConfigData()->getChildren($this->getLabel());
        if($configData !== null){
            $childData = $configData->getChildren($parameter->getLabel());
            if($childData !== null){
                $parameter->setName($childData->getName());
            }
        }
        return $this;
    }

    public function toUsageString(?CommandSender $sender = null) : string{
        return $this->getTranslatedName($sender) . " " . parent::toUsageString($sender);
    }

    public function getTranslatedName(?CommandSender $sender = null) : string{
        $messageId = $this->getMessageId("name");
        $name = $this->getBaseCommand()->getMessage($sender, $messageId);
        return $messageId === $name ? $this->getName() : $name;
    }

    public function getNameParameter(?bool $exact = false, ?string $name = null) : ConstParameter{
        return (new ConstParameter($name ?? $this->name))->setOverload($this)->setExact($exact);
    }

    public function testName(CommandSender $sender, ?string $name) : bool{
        if($name === null)
            return false;

        if($this->getNameParameter()->parseSilent($sender, $name) !== null)
            return true;

        foreach($this->aliases as $alias){
            if($this->getNameParameter(false, $alias)->parseSilent($sender, $name) !== null)
                return true;
        }
        return false;
    }

    /** @param string[] $args */
    public function valid(CommandSender $sender, array $args) : bool{
        return $this->testName($sender, array_shift($args));
    }

    /**
     * @param string[] $args
     *
     * @return mixed[]|int name => value. if parse failed return int
     */
    public function parse(CommandSender $sender, array $args){
        if(!$this->testName($sender, array_shift($args)))
            return self::ERROR_NAME_MISMATCH;

        return parent::parse($sender, $args);
    }
}