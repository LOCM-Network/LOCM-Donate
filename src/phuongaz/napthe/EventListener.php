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
use phuongaz\azeconomy\EcoAPI;
use phuongaz\napthe\card\Card;
use phuongaz\NapThe\util\Util;
use phuongaz\tpbank\TPBankEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;

Class EventListener implements Listener{
    /** @var array<string, string> $bankCache */
    private array $bankCache = [];

    /**
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event) : void {
        $data = Loader::getInstance()->getOfflineData();
        $player = $event->getPlayer();
        $plugin = Loader::getInstance();
        if ($data->exists($player->getName())) {
            $oldData = $data->get($player->getName());
            $amount = $oldData['amount'];
            $money = $plugin->getSetting()->getRealMoney($amount);
            $telco = $oldData['telco'];
            $card = new Card($oldData);
            $message = Util::getMessage('success.offline', $money, $amount, $telco, Util::getUnitBalance());
            $plugin->successCard($player, $card, $message);
            $data->remove($player->getName());
            $data->save();
        }
        if (isset($this->bankCache[$player->getName()])) {
            $amount = $this->bankCache[$player->getName()];
            $plugin->successBank($player, $amount);
            unset($this->bankCache[$player->getName()]);
        }
    }

    public function onTPTransaction(TPBankEvent $event) : void {
        $settings = Loader::getInstance()->getSetting();
        if (!$settings->isTPBankSupport()) {
            return;
        }

        $history = $event->getHistory();
        if ($history->isCRDT()) {
            $description = $history->getDescription();
            $format = $settings->getTPFormat();

            $descriptionParts = explode(' ', $description);
            $formatParts = explode(' ', $format);

            if ($descriptionParts[0] === $formatParts[0]) {
                $playerPos = array_search('{player}', $formatParts);
                if ($playerPos !== false && isset($descriptionParts[$playerPos])) {
                    $playerName = $descriptionParts[$playerPos];
                    $player = Server::getInstance()->getPlayerExact($playerName);
                    if ($player !== null) {
                        Loader::getInstance()->successBank($player, $history->getAmount());
                    } else {
                        $this->bankCache[$playerName] = $history->getAmount();
                    }
                }
            }
        }
    }
}
