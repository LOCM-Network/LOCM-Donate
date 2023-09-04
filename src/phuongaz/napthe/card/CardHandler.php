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

namespace phuongaz\napthe\card;

use phuongaz\napthe\Loader;
use phuongaz\napthe\response\Result;
use phuongaz\napthe\task\PendingCardTask;
use pocketmine\utils\Internet;

Class CardHandler{

    public function postMethod(Card $card, string $command) :?Result {
        $url = $card->getChargeUrl();
        $result = Internet::postURL($url, $card->createDataPost($command), 1000,  [ "Content-Type" => "application/json" ]);
        if(is_null($result)){
            return null;
        }
        return new Result($card, json_decode($result->getBody(), true));
    }

    /**
     * When the card is pending, it will be handled by this method
     *
     * @param Card $card
     * @return void
     */
    public function handlePendingCard(Card $card) :void{
        $task = new PendingCardTask($card);
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 20);
    }
}