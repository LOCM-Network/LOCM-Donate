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

namespace phuongaz\napthe\form;

use jojoe77777\FormAPI\{CustomForm, SimpleForm};
use phuongaz\napthe\card\Card;
use phuongaz\napthe\card\CardHandler;
use phuongaz\napthe\Loader;
use phuongaz\napthe\response\Result;
use phuongaz\napthe\response\Status;
use phuongaz\NapThe\util\Util;
use pocketmine\player\Player;

class NapTheForm{

    private static array $telcos = ['Viettel', 'Vietnamobi', 'Vinaphone', 'Mobifone', 'Zing', 'Gate'];
    private static array $amount = [10000, 20000, 50000, 100000, 200000, 500000, 1000000];

    public function simpleForm(Player $player) :void {
        $form = new SimpleForm(function(Player $player, ?int $data){
            if($data === null){
                return;
            }
            switch($data){
                case 0:
                    $this->cards($player);
                    break;
                case 1:
                    $this->others($player);
                    break;
            }
        });
        $form->setTitle(Util::getMessage("main.title"));
        $form->setContent(Util::getMessage("main.content"));
        $form->addButton(Util::getMessage("main.button"));
        $form->addButton(Util::getMessage("main.button2"));
        $player->sendForm($form);
    }

    public function others(Player $player) :void {
        $form = new CustomForm(function(Player $player, ?array $data){
            if(is_null($data)) $this->simpleForm($player);
        });
        $form->setTitle(Util::getMessage("others.title"));
        $form->addLabel(Util::getMessage("others.label"));
        $player->sendForm($form);
    }

    public function cards(Player $player):void{
        $form = new SimpleForm(function(Player $player, ?int $data){
            if(is_null($data)) return;
            $this->CustomForm($player, self::$telcos[$data]);
        });
        $form->setTitle(Util::getMessage("cards.title"));
        foreach(self::$telcos as $telco){
            $form->addButton(Util::getMessage("cards.buttons", $telco), 1, $this->getCardLogo($telco));
        }

        $player->sendForm($form);
    }

    public function CustomForm(Player $player, string $telco, $content = '') :void{
        $plugin = Loader::getInstance();
        $form = new CustomForm(function(Player $player, ?array $data) use ($plugin, $telco){
            if(is_null($data)) {
                $this->cards($player);
                return;
            }
            $amount = self::$amount[$data[1]];
            if(isset($data[2]) and isset($data[3])){
                if(Util::availableCard($data[2], $data[3], $telco) === false){
                    $player->sendMessage(Util::getMessage('invalid.card'));
                    return;
                }
                $serial = $data[2];
                $code = $data[3];
                $data_c = [];
                $data_c['request_id'] = Util::generateRequestID($player->getName());
                $data_c['code'] = $code;
                $data_c['partner_id'] = $plugin->getSetting()->getPartnerId();
                $data_c['partner_key'] = $plugin->getSetting()->getPartnerKey();
                $data_c['serial'] = $serial;
                $data_c['telco'] = $telco;
                $data_c['amount'] = $amount;
                $card = new Card($data_c);
                $handle = new CardHandler();
                $result = $handle->postMethod($card, 'charging');
                $this->invoke($player, $result);
            }
        });
        $amounts = array_map(function($amount) use ($plugin){
            $realAmount = $plugin->getSetting()->getRealMoney($amount);
            return Util::getMessage('dropdown.amount', $amount, $realAmount);
        }, self::$amount);

        $form->setTitle(Util::getMessage('input.card.title', $telco));
        $form->addLabel($content);
        $form->addDropDown(Util::getMessage('dropdown.amount.title'), $amounts);
        $form->addInput(Util::getMessage('input.card.seri.title'), Util::getMessage('input.card.seri.placeholder'));
        $form->addInput(Util::getMessage('input.card.code.title'), Util::getMessage('input.card.seri.placeholder'));
        $player->sendForm($form);
    }

    public function invoke(Player $player, ?Result $result) :void{
        $plugin = Loader::getInstance();
        if(is_null($result)){
            $this->CustomForm($player, Util::getMessage('error'));
            return;
        }
        if($result->checkError()){
            $mess = Util::getMessage("error");
            $this->CustomForm($player, $result->getCard()->getTelco(), $mess);
            return;
        }
        $status = $result->getStatus();
        if($status == Status::SUCCESS){
            $plugin->successCard($player, $result->getCard(), $result->mapStatusString());
        }
        if($status == Status::PENDING){
            $handle = new CardHandler();
            $handle->handlePendingCard($result->getCard());
        }
        $this->CustomForm($player, $result->getCard()->getTelco(), $result->mapStatusString());
    }

    private function getCardLogo(string $telco) :string {
        return match ($telco) {
          "Viettel" => "https://thesieure.com/storage/userfiles/images/thecao/the-viettel.png",
          "Vietnamobi" => "https://iconape.com/wp-content/files/wl/364879/png/vietnamobile-logo.png",
          "Vinaphone" => "https://thesieure.com/storage/userfiles/images/thecao/the-vinaphone.png",
          "Mobifone" => "https://thesieure.com/storage/userfiles/images/thecao/the-mobifone.jpeg",
          "Zing" => "https://thesieure.com/storage/userfiles/images/thecao/the-zing.png",
          "Gate" => "https://thesieure.com/storage/userfiles/images/thecao/the-gate.png",
        };
    }
}