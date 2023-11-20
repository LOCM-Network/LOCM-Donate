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

Class Card{

    private array $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function getChargeUrl(): string {
        return 'https://'.Loader::getInstance()->getSetting()->getDriver().'/chargingws/v2?';
    }
    public function createDataPost(string $command) :array {
        $dataPost = [];
        $dataPost['request_id'] = $this->data['request_id'];
        $dataPost['code'] = $this->data['code'];
        $dataPost['partner_id'] = $this->data['partner_id'];
        $dataPost['serial'] = $this->data['serial'];
        $dataPost['telco'] = $this->data['telco'];
        $dataPost['command'] = $command;  
        $dataPost['amount'] = $this->data['amount'];
        $dataPost['sign'] = $this->getSign();
        return $dataPost;
    }

    public function getTelco() :string{
        return $this->data['telco'];
    }

    public function getAmount() :int{
        return $this->data['amount'];
    }
    public function getSign(): string{
        $data = [];
        $data[] = $this->data['partner_key'];
        $data[] = $this->data['code']; 
        $data[] = $this->data['serial'];
        ksort($data);
        $sign = implode('', $data);
        return md5($sign);
    }

}