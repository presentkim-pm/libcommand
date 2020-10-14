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

namespace blugin\lib\command;

use blugin\lib\command\config\CommandConfigData;
use blugin\lib\command\constraint\Constraint;
use blugin\lib\command\overload\NamedOverload;
use blugin\lib\command\overload\Overload;
use blugin\lib\command\parameter\Parameter;
use blugin\lib\translator\TranslatorHolder;
use blugin\utils\arrays\ArrayUtil as Arr;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BaseCommand extends Command implements PluginOwned{
    /** @var PluginBase&TranslatorHolder */
    private PluginBase $owningPlugin;

    /** @var Constraint[] */
    protected array $constraints = [];

    /** @var Overload[] */
    protected array $overloads = [];

    protected CommandConfigData $configData;

    /** @param string[] $aliases */
    public function __construct(string $label, PluginBase $owner, CommandConfigData $configData){
        $check = false;
        foreach(class_implements(get_class($owner)) as $className){
            if(preg_match("/^blugin(.*)lib[\\\]translator[\\\]TranslatorHolder$/", $className)){
                $check = true;
                break;
            }
        }
        if($check === false)
            throw new \InvalidArgumentException("BaseCommand's plugin must implement TranslatorHolder.");

        parent::__construct($configData->getName(), "", null, $configData->getAliases());
        $this->owningPlugin = $owner;
        $this->configData = $configData;

        $this->setLabel($label);
        $permissionName = "{$this->getLabel()}.cmd";
        $this->setPermission($permissionName);
        $this->recalculatePermission($permissionName, $configData->getPermission());
        $this->setDescription($this->getMessage(null, "commands.{$this->getLabel()}.description"));
    }

    /** @param string[] $args */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$this->owningPlugin->isEnabled() || !$this->testPermission($sender))
            return false;

        foreach($this->constraints as $constraint){
            if(!$constraint->test($sender, $this, $args)){
                $constraint->onFailure($sender, $this, $args);
                return true;
            }
        }

        if(empty($this->overloads))
            return false;

        foreach($this->overloads as $key => $overload){
            if($overload->valid($sender, $args)){
                $result = $overload->parse($sender, $args);
                switch($result){
                    case Overload::ERROR_NAME_MISMATCH:
                        break;
                    case Overload::ERROR_PARAMETER_INVALID:
                    case Overload::ERROR_PARAMETER_INSUFFICIENT:
                        $this->sendMessage($sender, "commands.generic.usage", ["/$commandLabel " . $overload->toUsageString($sender)]);
                        return true;
                    case Overload::ERROR_PERMISSION_DENIED:
                        $this->sendMessage($sender, TextFormat::RED . "%commands.generic.permission");
                        return true;
                    default:
                        return is_numeric($result) ? true : $overload->onParse($sender, $result);
                }
            }
        }
        $this->sendMessage($sender, "commands.generic.usage", [$this->getUsage($sender, $commandLabel)]);
        return true;
    }

    public function getUsage(?CommandSender $sender = null, ?string $commandLabel = null) : string{
        $usage = "/" . $commandLabel ?? $this->getName() . "}";

        $count = count($this->overloads);
        if($count === 0)
            return $usage;

        if($count === 1)
            return "$usage {$this->overloads[0]->toUsageString($sender)}";

        return Arr::map($this->overloads, function(Overload $overload) use ($sender): string{
            return $overload instanceof NamedOverload ? $overload->getTranslatedName($sender) : $overload->toUsageString($sender);
        })->join(" | ", "$usage <", ">");
    }

    public function getMessage(?CommandSender $sender, string $str, array $params = []) : string{
        $str = $this->owningPlugin->getTranslator()->translateTo($str, $params, $sender);
        return Server::getInstance()->getLanguage()->translateString($str, $params);
    }

    /** @param string[] $params */
    public function sendMessage(CommandSender $sender, string $str, array $params = []) : void{
        $sender->sendMessage(new TranslationContainer($this->getMessage($sender, $str, $params), $params));
    }

    /** @return Constraint[] */
    public function getConstraints() : array{
        return $this->constraints;
    }

    public function addConstraint(Constraint $constraint) : BaseCommand{
        $this->constraints[] = $constraint;
        return $this;
    }

    /** @return Overload[] */
    public function getOverloads() : array{
        return $this->overloads;
    }

    public function addOverload(?Overload $overload = null) : BaseCommand{
        if($overload === null){
            $overload = new Overload($this);
        }
        $this->overloads[] = $overload;
        if($overload instanceof NamedOverload){
            $childData = $this->getConfigData()->getChildren($overload->getLabel());
            $overload->setName($childData->getName());
            $overload->setAliases($childData->getAliases());
            $this->recalculatePermission($overload->getPermission(), $childData->getPermission());
        }
        return $this;
    }

    public function addNamedOverload(string $name) : BaseCommand{
        return $this->addOverload(new NamedOverload($this, $name));
    }

    /** @return Parameter[][] */
    public function asOverloadsArray(Player $player) : array{
        $overloads = [];
        foreach($this->overloads as $overload){
            $overloads[] = $overload->getParameters($player);
        }
        return $overloads;
    }

    public function recalculatePermission(string $permissionName, string $default) : void{
        $permissionManager = PermissionManager::getInstance();
        $permission = $permissionManager->getPermission($permissionName);
        if($permission === null){
            $permission = new Permission($permissionName);
            $permissionManager->addPermission($permission);
        }
        $permission->setDefault($default);
    }

    public function getConfigData() : CommandConfigData{
        return $this->configData;
    }

    /** @return PluginBase */
    public function getOwningPlugin() : Plugin{
        return $this->owningPlugin;
    }
}