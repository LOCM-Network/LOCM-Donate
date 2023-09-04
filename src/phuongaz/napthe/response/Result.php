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

namespace phuongaz\napthe\response;

use phuongaz\napthe\card\Card;
use phuongaz\NapThe\util\Util;

Class Result{

    private Card $card;
    private ?array $result;

    public function __construct(Card $card, ?array $result){
        $this->card = $card;
        $this->result = $result;
    }

    public function checkError() :bool{
        return is_null($this->result);
    }

    public function getCard() :Card{
        return $this->card;
    }

    public function getResult() :array{
        return $this->result;
    }

    public function getRequestId() :string{
        return $this->getResult()['request_id'];
    }

    public function getStatus() :int{
        return $this->getResult()['status'];
    }

    public function getMessage():string{
        return $this->getResult()['message'];
    }

    public function isPending() :bool{
        return ($this->getStatus() == Status::PENDING);
    }

    public function mapStatusString() :string{
        $data = $this->getStatus();
        return match ($data) {
            Status::SUCCESS => Util::getMessage('status.success'),
            Status::WRONG_AMOUNT => Util::getMessage('status.wrong.amount'),
            Status::WRONG_CARD => Util::getMessage('status.wrong.card'),
            Status::MAINTAINING => Util::getMessage('status.maintenance'),
            Status::PENDING => Util::getMessage('status.pending'),
            Status::UNKNOWN => Util::getMessage('status.error', $this->getMessage()),
            default => Util::getMessage('status.error.unknown'),
        };
    }
}