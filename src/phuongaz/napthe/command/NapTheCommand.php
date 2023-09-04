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

namespace phuongaz\napthe\command;

use phuongaz\napthe\form\NapTheForm;
use pocketmine\command\{Command, CommandSender};
use phuongaz\NapThe\util\Util;
use pocketmine\player\Player;

class NapTheCommand extends Command{

    public function __construct(){
        parent::__construct('napthe', 'Nạp thẻ', 'Usage: /napthe');
        $this->setPermission('napthe.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool{
        if($sender instanceof Player){
            $form = new NapTheForm();
            $form->simpleForm($sender);
            return true;
        }else{
            $sender->sendMessage(Util::getMessage('only.player'));
        }
        return false;
    }
}