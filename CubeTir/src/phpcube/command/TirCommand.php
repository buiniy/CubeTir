<?php
namespace phpcube\command;

use phpcube\CubeTirLoader;
use pocketmine\command\{Command, CommandSender};
use phpcube\form\SimpleForm;
use pocketmine\player\Player;

class TirCommand extends Command
{

    public CubeTirLoader $loader;

    public function __construct(CubeTirLoader $loader)
    {
        $this->loader = $loader;
        parent::__construct("tir", "Тир", null, []);
        $this->setPermission("cmd.tir");
    }

    public function TopForm($player) : void{
        $form = new SimpleForm(function ($player, $data = null) {
            if ($data === null || $data === 'close') {
                return;
            }
            if($data === "tp") {
                $player->teleport(CubeTirLoader::$formTpPos);
            }
        });
        $form->setTitle("§8(§cТир§r§8) §a× §fТоп метких в тире");
        $form->setContent(str_replace(['%top%'], [$this->loader->getTopStatistic()], CubeTirLoader::$holoText_top));
        $form->addButton("§e§lТП на тир", -1, "", "tp");
        $form->addButton("§c§lЗакрыть меню.\n§8§l[§0§l Закрывает меню §8§l]", -1, "", "close");
        $player->sendForm($form);
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Вы не игрок");
            return false;
        }

        $this->TopForm($sender);

        return true;
    }
}