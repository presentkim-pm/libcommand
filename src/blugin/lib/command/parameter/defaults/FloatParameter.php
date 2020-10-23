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

namespace blugin\lib\command\parameter\defaults;

use blugin\lib\command\parameter\Parameter;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class FloatParameter extends Parameter{
    /** @var float|null */
    protected $min = null;

    /** @var float|null */
    protected $max = null;

    public function getType() : int{
        return AvailableCommandsPacket::ARG_TYPE_FLOAT;
    }

    public function getTypeName() : string{
        return "decimal";
    }

    public function getFailureMessage(CommandSender $sender, string $argument) : ?string{
        return null;
    }

    public function valid(CommandSender $sender, string $argument) : bool{
        return is_numeric($argument);
    }

    /** @return float|null */
    public function parse(CommandSender $sender, string $argument){
        if(!is_numeric($argument)){
            $this->sendMessage($sender, "commands.generic.num.invalid", [$argument]);
            return null;
        }

        $num = (float) $argument;
        if($this->min !== null && $num < $this->min){
            $this->sendMessage($sender, "commands.generic.num.tooSmall", [$argument, "$this->min"]);
            return null;
        }

        if($this->max !== null && $num > $this->max){
            $this->sendMessage($sender, "commands.generic.num.tooBig", [$argument, "$this->max"]);
            return null;
        }

        return $num;
    }

    /** @return float|null */
    public function parseSilent(CommandSender $sender, string $argument){
        if(!is_numeric($argument))
            return null;

        $num = (float) $argument;
        if($this->min !== null && $num < $this->min || $this->max !== null && $num > $this->max){
            return null;
        }

        return $num;
    }

    public function getMin() : ?float{
        return $this->min;
    }

    public function setMin(?float $min) : FloatParameter{
        $this->min = $min;
        return $this;
    }

    public function getMax() : ?float{
        return $this->max;
    }

    public function setMax(?float $max) : FloatParameter{
        $this->max = $max;
        return $this;
    }
}