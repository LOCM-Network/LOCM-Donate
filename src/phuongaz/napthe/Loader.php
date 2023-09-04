<?php
/**
* MIT License
* 
* Copyright (c) 2023 Nguyen Tan Phuong
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

declare(strict_types=1);

namespace phuongaz\napthe;

use JsonException;
use phuongaz\napthe\card\Card;
use phuongaz\napthe\command\NapTheCommand;
use phuongaz\napthe\event\PlayerDonateEvent;
use phuongaz\napthe\response\Result;
use phuongaz\napthe\util\Settings;
use phuongaz\NapThe\util\Util;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase{
    use SingletonTrait;

    private Config $dataOffline;
    private Settings $setting;
    private array|false $language;
    private string $logger_path;

    public function onLoad():void{
        self::setInstance($this);
    }

    public function onEnable():void{
        $this->saveResource('language.ini');
        $this->saveResource('thedung.txt');
        $this->saveResource('offline.yml');
        $this->saveResource('setting.yml');
        $this->logger_path = $this->getDataFolder();
        $this->dataOffline = new Config($this->getDataFolder(). 'offline.yml', Config::YAML);
        $this->setting = new Settings(new Config($this->getDataFolder(). 'setting.yml', Config::YAML));
        $this->language = parse_ini_file($this->getDataFolder(). 'language.ini', true, INI_SCANNER_RAW);
        Server::getInstance()->getCommandMap()->register("napthe", new NapTheCommand());
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(),$this);
        $this->setting->startTPBank();
    }

    public function getSetting() :Settings{
        return $this->setting;
    }

    public function successCard(Player $player, Card $card, string $statusString): void {
        $setting = $this->getSetting();
        $telco = $card->getTelco();
        $amount = $card->getAmount();
        $ratio = $setting->getRatio();
        $bonus = $setting->getBonus();
        $coin = (int) round($amount / $ratio * $bonus);

        $event = new PlayerDonateEvent($player, $card, $statusString);
        $event->call();
        if ($event->isCancelled()) {
            return;
        }

        $playerName = $player->getName();
        $broadcastMessage = Util::getMessage('broadcast.success.card', $playerName, $coin, $amount, $telco, Util::getUnitBalance());
        $player->sendMessage(Util::getMessage('success.card.self', $coin, $amount, $telco, Util::getUnitBalance()));
        $this->broadcastMessage($broadcastMessage);
        $this->executeRewardsCommands($player, $coin);

        Util::logCard($player, $amount, $telco);
    }

    public function successBank(Player $player, string $amount): void {
        $message = Util::getMessage('success.bank.self', $player->getName(), $amount);
        $player->sendMessage($message);

        $ratio = $this->getSetting()->getRatio();
        $bonus = $this->getSetting()->getBonus();
        $coin = (int) round($amount / $ratio * $bonus);

        $this->broadcastMessage(Util::getMessage('broadcast.success.card', $player->getName(), $coin, $amount));
        $this->executeRewardsCommands($player, $coin);
    }

    private function broadcastMessage(string $message): void {
        Server::getInstance()->broadcastMessage($message);
    }

    private function executeRewardsCommands(Player $player, int $coin): void {
        $server = Server::getInstance();
        $sender = new ConsoleCommandSender($server, $server->getLanguage());

        foreach ($this->getSetting()->getRewardsCommand() as $cmd) {
            $cmd = str_replace('{player}', $player->getName(), $cmd);
            $cmd = str_replace('{money}', (string) $coin, $cmd);
            $server->getCommandMap()->dispatch($sender, $cmd);
        }
    }


    public function getOfflineData(): Config{
        return $this->dataOffline;
    }

    /**
     * @throws JsonException
     */
    public function addOfflineData(string $name, Result $result) :void {
        $data = [];
        $data['amount'] = $result->getCard()->getAmount();
        $data['telco'] = $result->getCard()->getTelco();
        $config = $this->getOfflineData();
        $config->set($name, $data);
        $config->save();
    }

    public function getLanguage() :array|false{
        return $this->language;
    }
}