<?php

namespace Terpz710\BlockAnnouncerPlus;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        
        $config = $this->getConfig();
        $minedBlockId = $config->get("mined_block_id");
        
        if ($block->getId() === $minedBlockId) {
            $this->announceMinedBlock($player);
            $this->incrementMinedBlockCount($player);
        }
    }

    private function announceMinedBlock(Player $player) {
        $config = $this->getConfig();
        $announcements = $config->get("announcements");
        $minedBlockCount = $this->getMinedBlockCount($player);

        if (!empty($announcements) && is_array($announcements)) {
            $message = $announcements[array_rand($announcements)];
            $message = str_replace("{player}", $player->getName(), $message);
            $message = str_replace("{amount}", $minedBlockCount, $message);
            $this->getServer()->broadcastMessage($message);
        }
    }

    private function getMinedBlockCount(Player $player) {
        $config = $this->getConfig();
        $minedBlockId = $config->get("mined_block_id");

        $playerDataFolder = $this->getDataFolder() . "player_data/";

        if (!file_exists($playerDataFolder)) {
            @mkdir($playerDataFolder, 0755, true);
        }

        $playerDataFile = $playerDataFolder . strtolower($player->getName()) . ".json";

        if (file_exists($playerDataFile)) {
            $playerData = json_decode(file_get_contents($playerDataFile), true);
            if (isset($playerData[$minedBlockId])) {
                return $playerData[$minedBlockId];
            }
        }

        return 0;
    }

    private function incrementMinedBlockCount(Player $player) {
        $config = $this->getConfig();
        $minedBlockId = $config->get("mined_block_id");

        $playerDataFolder = $this->getDataFolder() . "player_data/";

        if (!file_exists($playerDataFolder)) {
            @mkdir($playerDataFolder, 0755, true);
        }

        $playerDataFile = $playerDataFolder . strtolower($player->getName()) . ".json";

        if (file_exists($playerDataFile)) {
            $playerData = json_decode(file_get_contents($playerDataFile), true);
        } else {
            $playerData = [];
        }

        if (!isset($playerData[$minedBlockId])) {
            $playerData[$minedBlockId] = 0;
        }

        $playerData[$minedBlockId]++;
        file_put_contents($playerDataFile, json_encode($playerData));
    }
}
