<?php

namespace phpcube\economy;

use onebone\economyapi\EconomyAPI;
use pocketmine\player\Player;

final class EconomyProvider{
    /**
     * @return EconomyAPI
     */
    public static function getProvider() : EconomyAPI {
        return EconomyAPI::getInstance();
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function balance(Player $player) : int {
        return self::getProvider()->myMoney($player) ?? 0;
    }

    /**
     * @param Player $player
     * @param $amount
     * @return void
     */
    public static function reduce(Player $player, $amount) : void {
        self::getProvider()->reduceMoney($player, $amount);
    }

    /**
     * @param Player $player
     * @param $amount
     * @return void
     */
    public static function add(Player $player, $amount) : void {
        self::getProvider()->addMoney($player, $amount);
    }

}