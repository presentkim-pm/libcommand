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

use blugin\lib\command\enum\Enum;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;

class EnumUpdateListener implements Listener{
    /**
     * @priority MONITOR
     */
    public function onPlayerJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        Enum::create(Enum::PLAYERS)->set(strtolower($player->getName()), $player);
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerQuit(PlayerQuitEvent $event) : void{
        $player = $event->getPlayer();
        Enum::create(Enum::PLAYERS)->remove(strtolower($player->getName()));
    }

    /**
     * @priority MONITOR
     */
    public function onWorldLoad(WorldLoadEvent $event) : void{
        $world = $event->getWorld();
        Enum::create(Enum::WORLDS)->set(strtolower($world->getFolderName()), $world);
    }

    /**
     * @priority MONITOR
     */
    public function onWorldUnload(WorldUnloadEvent $event) : void{
        $world = $event->getWorld();
        Enum::create(Enum::WORLDS)->remove(strtolower($world->getFolderName()));
    }
}
