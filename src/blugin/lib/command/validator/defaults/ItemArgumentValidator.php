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
 * it under the terms of the MIT License.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/mit MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\lib\command\validator\defaults;

use blugin\lib\command\exception\defaults\GenericInvalidItemException;
use blugin\lib\command\validator\ArgumentValidator;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class ItemArgumentValidator implements ArgumentValidator{
    public static function validate(string $argument) : Item{
        try{
            return ItemFactory::fromStringSingle($argument);
        }catch(\InvalidArgumentException $e){
            throw new GenericInvalidItemException($argument);
        }
    }
}