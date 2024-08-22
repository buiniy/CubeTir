<?php

namespace phpcube;

use JsonException;
use phpcube\command\TirCommand;
use phpcube\economy\EconomyProvider;
use phpcube\listener\TirListener;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;

final class CubeTirLoader extends PluginBase
{
    use SingletonTrait;
    /**
     * @var Position|null
     */
    public static Position|null $holoTopPosition = null;
    /**
     * @var Position|null
     */
    public static Position|null $formTpPos = null;
    /**
     * @var Position|null
     */
    public static Position|null $tirLocation = null;
    /**
     * @var string
     */
    public static string $holoText_top = "";
    /**
     * @var FloatingTextParticle|null
     */
    public static FloatingTextParticle|null $particle_top = null;
    /**
     * @var Config
     */
    public Config $config;
    /**
     * @var Config
     */
    public Config $topdata;

    public function onEnable(): void {
        self::setInstance($this);
        $this->topdata = new Config($this->getDataFolder() . "topdata.json", Config::JSON, []);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,[
            "tir_location" => [
                "world" => "world",
                "pos_x" => 419,
                "pos_y" => 79,
                "pos_z" => 357,
                "distance" => 20,
                'payout_min' => 1,
                'payout_max' => 10,
            ],
            'hologram-top' => [
                "world" => "world",
                "pos_x" => 414,
                "pos_y" => 75,
                "pos_z" => 360,
                "top_content" => "§c§l ----- §aТоп 10 тир §c§l -----\n§r%top%",
            ],
            "form-tp-pos" => [
                "world" => "world",
                "pos_x" => 414,
                "pos_y" => 75,
                "pos_z" => 360,
            ],
        ]);

        $this->loadData();
        $this->createHologram();

        $this->getServer()->getPluginManager()->registerEvents(new TirListener($this), $this);
        $this->getServer()->getCommandMap()->register("", new TirCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (){
            $this->updateHologram();
        }), 20);

    }

    public function loadData(): void {
        self::$holoTopPosition = new Position(
            $this->config->getNested("hologram-top.pos_x"),
            $this->config->getNested("hologram-top.pos_y"),
            $this->config->getNested("hologram-top.pos_z"),
            Server::getInstance()->getWorldManager()->getWorldByName($this->config->getNested("hologram-top.world"))
        );
        self::$formTpPos = new Position(
            $this->config->getNested("form-tp-pos.pos_x"),
            $this->config->getNested("form-tp-pos.pos_y"),
            $this->config->getNested("form-tp-pos.pos_z"),
            Server::getInstance()->getWorldManager()->getWorldByName($this->config->getNested("form-tp-pos.world"))
        );

        self::$formTpPos = new Position(
            $this->config->getNested("form-tp-pos.pos_x"),
            $this->config->getNested("form-tp-pos.pos_y"),
            $this->config->getNested("form-tp-pos.pos_z"),
            Server::getInstance()->getWorldManager()->getWorldByName($this->config->getNested("form-tp-pos.world"))
        );

        self::$tirLocation = new Position(
            $this->config->getNested("tir_location.pos_x"),
            $this->config->getNested("tir_location.pos_y"),
            $this->config->getNested("tir_location.pos_z"),
            Server::getInstance()->getWorldManager()->getWorldByName($this->config->getNested("tir_location.world"))
        );
        self::$holoText_top = $this->config->getNested("hologram-top.top_content");
    }

    public function createHologram(): void {
        self::$particle_top = new FloatingTextParticle(self::$holoText_top, "");
        if(!self::$holoTopPosition->getWorld()->isChunkLoaded(self::$holoTopPosition->getX(), self::$holoTopPosition->getZ()))
            self::$holoTopPosition->getWorld()->loadChunk(self::$holoTopPosition->getX(), self::$holoTopPosition->getZ());
        self::$holoTopPosition->getWorld()->addParticle(self::$holoTopPosition, self::$particle_top, null);
    }


    /**
     * @return string
     */
    public function getTopStatistic() : string {
        $list = "";
        $topdata_players = $this->topdata->getAll();
        $i = 0;
        arsort($topdata_players);
        foreach($topdata_players as $account => $sec) {
            $i++;
            if($i >= 11) break;
            $list.="§c$i. §a§l{$account} §r§c× §fПопал в цель: §b→ §a§l{$sec} раз(а).§r§f\n";
        }
        return $list;
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function payTir(Player $player) : void {
        $sum = mt_rand($this->config->getNested('tir_location.payout_min', 1), $this->config->getNested('tir_location.payout_max', 2));
        EconomyProvider::add($player, $sum);
        if($this->topdata->exists(strtolower($player->getName()))) {
            $this->topdata->set(strtolower($player->getName()), $this->topdata->get(strtolower($player->getName())) + 1);
        } else {
            $this->topdata->set(strtolower($player->getName()), 1);
        }
        $this->topdata->save();
    }

    public function updateHologram(): void {
        self::$particle_top->setText(str_replace(['%top%'], [$this->getTopStatistic()], self::$holoText_top));
        self::$holoTopPosition->getWorld()->addParticle(self::$holoTopPosition, self::$particle_top, Server::getInstance()->getOnlinePlayers());
    }

    public static function sendSound(Player $player, string $soundName): void {
        $packet = new PlaySoundPacket();
        $packet->soundName = $soundName;
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
        $packet->volume = 1;
        $packet->pitch = 1;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

}
