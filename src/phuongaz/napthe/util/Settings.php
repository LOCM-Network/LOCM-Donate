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

namespace phuongaz\napthe\util;

use phuongaz\napthe\Loader;
use phuongaz\tpbank\API;
use pocketmine\utils\Config;

class Settings {

    private string $driver;
    private string $partnerId;
    private string $partnerKey;
    private int $ratio;
    private float|int $bonus;

    private array $rewards_command;

    private int $timeout_pending;

    private string $currency;

    /** @var array{enable: bool, account_number: string, account: string, password: string, interval: int, format: string} */
    private array $tpbankData;

    public function __construct(Config $config) {
        $this->driver = $config->get('driver');
        $this->partnerId = $config->get('partner_id');
        $this->partnerKey = $config->get('partner_key');
        $this->ratio = $config->get('ratio');
        $this->bonus = $config->get('bonus');
        $this->rewards_command = $config->get('rewards_command');
        $this->timeout_pending = $config->get('timeout_pending');
        $this->currency = $config->get('currency');
        $this->tpbankData = $config->get("tpbank");
    }

    public function getDriver(): string {
        return $this->driver;
    }

    public function getPartnerId(): string {
        return $this->partnerId;
    }

    public function getPartnerKey(): string {
        return $this->partnerKey;
    }

    public function getRatio(): int {
        return $this->ratio;
    }

    public function getBonus(): float|int {
        return $this->bonus;
    }

    public function getRewardsCommand(): array {
        return $this->rewards_command;
    }

    public function getTimeoutPending(): int {
        return $this->timeout_pending;
    }

    public function getRealMoney(int $money): int {
        return $money * $this->ratio + ($money/100 * $this->bonus);
    }

    public function getCurrency() : string {
        return $this->currency;
    }

    public function isTPBankSupport() : bool {
        return $this->tpbankData["enable"];
    }

    public function getTPAccountNumber() : string {
        return $this->tpbankData["account_number"];
    }

    public function getTPAccount() : string {
        return $this->tpbankData["account"];
    }

    public function getTPPassword() : string {
        return $this->tpbankData["password"];
    }

    public function getTPFormat() : string {
        return $this->tpbankData["format"];
    }

    public function getTPInterval() : int {
        return $this->tpbankData["interval"];
    }

    public function startTPBank() : void {
        if($this->isTPBankSupport()) {
            $api = new API($this->getTPAccountNumber(), $this->getTPAccount(), $this->getTPPassword(), Loader::getInstance());
            $api->runTask($this->getTPInterval());
        }
    }

}
