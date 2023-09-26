<?php

namespace Terpz710\BlockAnnouncerPlus;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->saveResource("config.yml");
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $minedBlockId = $config->get("mined_block_id");

        if ($block->getId() === $minedBlockId) {
            $this->announceMinedBlock($player);
        }
    }

    private function announceMinedBlock(Player $player) {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $announcements = $config->get("announcements");

        if (!empty($announcements) && is_array($announcements)) {
            $message = $announcements[array_rand($announcements)];
            $message = str_replace("{player}", $player->getName(), $message);
            $this->getServer()->broadcastMessage($message);
        }
    }
}
