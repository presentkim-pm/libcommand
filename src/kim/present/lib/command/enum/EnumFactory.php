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

namespace kim\present\lib\command\enum;

use kim\present\lib\arrayutils\ArrayUtils as Arr;
use kim\present\lib\stringutils\StringUtils as Str;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class EnumFactory{
    use SingletonTrait;

    /** @var Enum[] name => enum */
    protected $enums = [];

    private function __construct(){
        $server = Server::getInstance();
        $this->set(Enum::BOOLEAN, ["true" => true, "false" => false]);
        $this->set(Enum::PLAYERS, $players =
            Arr::mapKeyFromAs($server->getOnlinePlayers(), function(Player $player) : string{
                return strtolower($player->getName());
            })
        );

        /** @var Player[] $players */
        $this->set(Enum::PLAYERS_INCLUE_OFFLINE,
            Arr::from(scandir($server->getDataPath() . "players/"))
                ->filter(function(string $fileName) : bool{ return Str::endsWith($fileName, ".dat"); })
                ->mapAssocAs(function(string $fileName) use ($players): array{
                    $playerName = Str::removeExtension($fileName);
                    if(isset($players[$playerName])){
                        $playerName = $players[$playerName]->getName();
                    }
                    return [strtolower($playerName), $playerName];
                })
        );

        $this->set(Enum::WORLDS,
            Arr::mapKeyFromAs($server->getLevels(), function(Level $world) : string{
                return strtolower($world->getFolderName());
            })
        );
    }

    /** @return Enum[] */
    public function getAll() : array{
        return $this->enums;
    }

    /** @param mixed[]|null $values name => value */
    public function get(string $name) : ?Enum{
        return $this->enums[$name] ?? null;
    }

    /** @param mixed[]|null $values name => value */
    public function set(string $name, array $values = []) : Enum{
        if(!isset($this->enums[$name])){
            $this->enums[$name] = new Enum($name, $values);
        }elseif($values !== null && $this->enums[$name]->getAll() !== $values){
            $this->enums[$name]->setAll($values);
        }
        return $this->enums[$name];
    }
}