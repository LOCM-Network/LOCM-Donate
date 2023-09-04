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

namespace phuongaz\NapThe\util;

use phuongaz\napthe\Loader;
use pocketmine\player\Player;

class Util {

    /**
     * Replace holder with args
     *
     * @param string $holder
     * @param array $args
     *
     * @return string
     */
    public static function parseHolder(string $holder, ...$args): string {
        $holder = str_replace("{unit}", self::getUnitBalance(), $holder);
        return str_replace(array_map(function ($i) {
            return "{" . $i . "}";
        }, array_keys($args)), array_values($args), $holder);
    }

    public static function getMessage(string $message, ...$args): string {
        $language = Loader::getInstance()->getLanguage();
        if(!isset($language[$message])) return $message;
        return self::parseHolder($language[$message], ...$args);
    }

    public static function getUnitBalance() :string{
        $lang = Loader::getInstance()->getLanguage();
        return $lang['unit'];
    }

    /**
     * Generate request id for request card so as not to be confused with other requests
     *
     * @param string $prefix
     * @param int $length
     * @param string $split
     *
     * @return string
     */
    public static function generateRequestID(string $prefix, int $length = 10, string $split = "|"): string {
        $randomPart = substr(md5(uniqid((string)mt_rand(), true)), 0, $length - strlen($prefix));
        return $prefix . $split . $randomPart;
    }

    public static function availableCard(string|int $serial, string|int $pin, string $type) :bool {
        $isAvailable = false;
        switch ($type) {
            case "Zing":
            case "Gate":
                if (is_string($serial) && is_string($pin)) {
                    $isAvailable = true;
                }
                break;
                //TODO: Check the number of pin and serial
            case "Viettel":
            case "Vinaphone":
            case "Mobifone":
            case "Vietnamobi":
                if (is_numeric($serial) && is_numeric($pin)) {
                    $isAvailable = true;
                }
                break;
        }
        return $isAvailable;
    }

    public static function logCard(Player $player, int $amount, string $telco, $type = 'thedung.txt'):void{
        $logPath = Loader::getInstance()->getDataFolder() . 'logs/';
        $file = $logPath . $type;
        $data = $player->getName() .'|'.$amount.'|'.date("H:i:s d-m-Y"). '|'. $telco;
        $fh = fopen($file,"a") or die("cant open file");
        fwrite($fh,$data);
        fwrite($fh,"\r\n");
        fclose($fh);
    }
}