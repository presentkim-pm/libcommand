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

namespace kim\present\lib\command\parameter\defaults;

use kim\present\lib\command\parameter\Parameter;
use kim\present\lib\stringutils\StringUtils as Str;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\Player;

class Vector3Parameter extends Parameter{
    /** @var bool Whether to rounding down the coordinates */
    protected $isFloor = false;

    public function getType() : int{
        return AvailableCommandsPacket::ARG_TYPE_POSITION;
    }

    public function getTypeName() : string{
        return "x y z";
    }

    public function prepare() : Parameter{
        $this->setLength(3);
        return $this;
    }

    public function valid(CommandSender $sender, string $argument) : bool{
        $pattern = "/^" . ($sender instanceof Player ? "(~|~\+)?" : "") . "-?(\d+|\d*\.\d+)$/";
        foreach(explode(" ", $argument) as $coord){
            if(!preg_match($pattern, $coord))
                return false;
        }
        return true;
    }

    /** @return Vector3|null */
    public function parseSilent(CommandSender $sender, string $argument){
        if(!$this->valid($sender, $argument))
            return null;

        $argument = explode(" ", $argument);
        $coords = [
            "x" => $argument[0],
            "y" => $argument[1],
            "z" => $argument[2]
        ];
        foreach($coords as $coordName => &$coord){
            if($sender instanceof Player && Str::startsWith($coord, "~")){
                /** @var Player $sender */
                $coord = $sender->getLocation()->{$coordName} + (float) substr($coord, 1);
            }else{
                $coord = (float) $coord;
            }

            if($this->isFloor()){
                $coord = (int) $coord;
            }
        }
        return new Vector3(...$coords);
    }

    public function isFloor() : bool{
        return $this->isFloor;
    }

    public function setFloor(bool $isFloor) : Vector3Parameter{
        $this->isFloor = $isFloor;
        return $this;
    }
}