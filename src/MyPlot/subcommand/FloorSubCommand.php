<?php
namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FloorSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function canUse(CommandSender $sender) {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.floor");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) {
		$plot = $this->getPlugin()->getPlotByPosition($sender->getPosition());
		if ($plot === null) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notinplot"));
			return true;
		}
		if ($plot->owner !== $sender->getName() and !$sender->hasPermission("myplot.admin.floor")) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notowner"));
			return true;
		}
		if (isset($args[0])) {
			$economy = $this->getPlugin()->getEconomyProvider();
			$price = $this->getPlugin()->getLevelSettings($plot->levelName)->floorPrice;
			if ($economy !== null and !$economy->reduceMoney($sender, $price)) {
				$sender->sendMessage(TextFormat::RED . $this->translateString("floor.nomoney"));
				return true;
			}
            $id = intval($args[0]);
            $meta = intval($args[1]);
			$maxBlocksPerTick = $this->getPlugin()->getConfig()->get("floorBlocksPerTick", 256);
			if ($this->getPlugin()->floorPlot($plot, $maxBlocksPerTick, $id, $meta)) {
				$sender->sendMessage("Replace floor in progress...");
			} else {
				$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
			}
		} else {
			$plotId = TextFormat::GREEN . $plot . TextFormat::WHITE;
			$sender->sendMessage("Please choose a block ID to replace the floor with. /p floor ID");
		}
		return true;
	}
}