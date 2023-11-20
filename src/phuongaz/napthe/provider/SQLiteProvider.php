<?php

declare(strict_types=1);

namespace phuongaz\napthe\provider;

use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

class SQLiteProvider {

    const INIT = "cards.init";
    const INSERT = "cards.insert";
    const SELECT = "cards.select";
    const SELECT_TOP_10 = "cards.select_top";

    public function __construct(private DataConnector $connector){
        Await::g2c($this->connector->asyncGeneric(self::INIT));
    }

    public function getConnection() :DataConnector{
        return $this->connector;
    }

    public function close() : void{
        $this->connector->waitAll();
        $this->connector->close();
    }

    public function awaitInsert(string $player, int $amount, string $telco, ?\Closure $closure = null) : Generator {
        yield $this->connector->asyncInsert(self::INSERT, [
            "player" => $player,
            "amount" => $amount,
            "telco" => $telco,
            "time" => date("d/m/Y H:i:s")
        ]);
        $this->handleClosure($closure);
    }

    public function awaitSelect(string $player, ?\Closure $closure = null) : Generator {
        $result = yield from $this->connector->asyncSelect(self::SELECT, ["player" => $player]);
        if(empty($result)){
            $this->handleClosure($closure, null);
            return;
        }
        $this->handleClosure($closure, $result);
    }

    public function awaitGetTop(?\Closure $closure = null) : Generator {
        $result = yield from $this->connector->asyncSelect(self::SELECT_TOP_10);
        if(empty($result)){
            $this->handleClosure($closure, null);
            return;
        }
        $this->handleClosure($closure, $result);
    }

    private function handleClosure(?\Closure $closure, ...$param) : void{
        if($closure !== null){
            $closure(...$param);
        }
    }

}