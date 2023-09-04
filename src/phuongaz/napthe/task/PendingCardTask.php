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

namespace phuongaz\napthe\task;

use JsonException;
use phuongaz\napthe\card\Card;
use phuongaz\napthe\card\CardHandler;
use phuongaz\napthe\form\NapTheForm;
use phuongaz\napthe\Loader;
use phuongaz\napthe\response\Result;
use phuongaz\napthe\response\Status;
use phuongaz\NapThe\util\Util;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PendingCardTask extends Task{

    private Card $card;
    private int $timeout;

    public function __construct(Card $card){
        $this->timeout = Loader::getInstance()->getSetting()->getTimeoutPending();
        $this->card = $card;
    }

    /**
     * @throws JsonException
     */
    public function onRun() :void{
        $plugin = Loader::getInstance();
        if($this->timeout == 0){
            $handler = new CardHandler();
            $result = $handler->postMethod($this->card, 'check');
            if($result->isPending()){
                $handler->handlePendingCard($this->card);
                $this->sendMessage($result, Util::getMessage('pending'));
                $this->getHandler()->cancel();
            }
            if($result->getStatus() !== Status::PENDING){
                $id = $result->getRequestId();
                $name = explode("|", $id)[0];
                if($result->getStatus() == Status::SUCCESS){
                    if(($player = Server::getInstance()->getPlayerExact($name)) !== null){
                        $plugin->successCard($player, $result->getCard(), $result->mapStatusString());
                    }else{
                        $plugin->addOfflineData($name, $result);
                    }
                    $this->getHandler()->cancel();
                    return;
                }
                $form = new NapTheForm();
                if(($player = Server::getInstance()->getPlayerExact($name)) !== null){
                    $form->invoke($player, $result);
                    $player->sendMessage($result->mapStatusString());
                }
                $this->getHandler()->cancel();
            }
            $this->timeout = 10;
        }
        --$this->timeout;
    }

    /**
     * Explode the request ID and send the message to the player.
     *
     * @param Result|null $result
     * @param string $message
     * @return void
     **/
    public function sendMessage(?Result $result, string $message):void{
        $id = $result->getRequestId();
        $name = explode("|", $id)[0];
        if(($player = Server::getInstance()->getPlayerExact($name)) !== null){
            $player->sendMessage($message);
        }
    }
}