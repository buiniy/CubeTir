<?php

namespace phpcube\listener;

use JsonException;
use phpcube\CubeTirLoader;
use phpcube\utils\SoundUtils;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\sound\Sound;

class TirListener implements Listener {

    public CubeTirLoader $loader;

    public function __construct(CubeTirLoader $loader) {
        $this->loader = $loader;
    }

    /**
     * @param ProjectileHitBlockEvent $event
     * @return void
     * @throws JsonException
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void {
        $projectile = $event->getEntity();
        $blockHit = $event->getBlockHit();
        $shooter = $projectile->getOwningEntity();
        if ($shooter instanceof Player) {
            if ($blockHit instanceof Wool){
                if($blockHit->getPosition()->distance(CubeTirLoader::$tirLocation) <= CubeTirLoader::getInstance()->config->getNested("tir_location.distance")) {
                    if($blockHit->getColor()->getDisplayName() === "Red") {
                        $shooter->sendTitle("§c§lТИР", "§fВы §aпопали §fв мишень!");
                        CubeTirLoader::sendSound($shooter, SoundUtils::RANDOM_POP2);
                        CubeTirLoader::getInstance()->payTir($shooter);
                        $projectile->flagForDespawn();
                        $position = $blockHit->getPosition();;
                        $position->getWorld()->setBlock($position, VanillaBlocks::WOOL()->setColor(DyeColor::LIME));
                        $this->loader->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($position) {
                            $redWool = VanillaBlocks::WOOL()->setColor(DyeColor::RED);
                            $position->getWorld()->setBlock($position, $redWool);
                        }), 20 * 3);
                    }
                }
            }
        }
    }


}