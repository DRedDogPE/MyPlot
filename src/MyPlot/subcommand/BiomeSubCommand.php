<?php
namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\level\generator\biome\Biome;

class BiomeSubCommand extends SubCommand
{
	/** @var int[] $biomes */
	private $biomes = [        
		"OCEAN" => Biome::OCEAN,
		"PLAINS" => Biome::PLAINS,
		"DESERT" => Biome::DESERT,
		"MOUNTAINS" => Biome::MOUNTAINS,
		"FOREST" => Biome::FOREST,
		"TAIGA" => Biome::TAIGA,
		"SWAMP" => Biome::SWAMP,
		"RIVER" => Biome::RIVER,
		"HELL" => Biome::HELL,
		// "END" => Biome::END,
		// "FROZEN_OCEAN" => Biome::FROZEN_OCEAN,
		// "FROZEN_RIVER" => Biome::FROZEN_RIVER,
		"ICE_PLAINS" => Biome::ICE_PLAINS,
		// "ICE_MOUNTAINS" => Biome::ICE_MOUNTAINS,
		// "MUSHROOM_ISLAND" => Biome::MUSHROOM_ISLAND,
		// "MUSHROOM_ISLAND_SHORE" => Biome::MUSHROOM_ISLAND_SHORE,
		"BEACH" => Biome::BEACH,
		// "DESERT_HILLS" => Biome::DESERT_HILLS,
		// "FOREST_HILLS" => Biome::FOREST_HILLS,
		// "TAIGA_HILLS" => Biome::TAIGA_HILLS,
		"SMALL_MOUNTAINS" => Biome::SMALL_MOUNTAINS,
		"BIRCH_FOREST" => Biome::BIRCH_FOREST,
		// "BIRCH_FOREST_HILLS" => Biome::BIRCH_FOREST_HILLS,
		// "ROOFED_FOREST" => Biome::ROOFED_FOREST,
		// "COLD_TAIGA" => Biome::COLD_TAIGA,
		// "COLD_TAIGA_HILLS" => Biome::COLD_TAIGA_HILLS,
		// "MEGA_TAIGA" => Biome::MEGA_TAIGA,
		// "MEGA_TAIGA_HILLS" => Biome::MEGA_TAIGA_HILLS,
		// "EXTREME_HILLS_PLUS" => Biome::EXTREME_HILLS_PLUS,
		// "SAVANNA" => Biome::SAVANNA,
		// "SAVANNA_PLATEAU" => Biome::SAVANNA_PLATEAU,
		"MESA" => Biome::MESA
		// "MESA_PLATEAU_F" => Biome::MESA_PLATEAU_F,
		// "MESA_PLATEAU" => Biome::MESA_PLATEAU,
		// "VOID" => Biome::VOID
	];

	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function canUse(CommandSender $sender) {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.biome");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) {
		if (empty($args)) {
			$biomes = TextFormat::WHITE . implode(", ", array_keys($this->biomes));
			$sender->sendMessage($this->translateString("biome.possible", [$biomes]));
			return true;
		}
		$player = $sender->getServer()->getPlayer($sender->getName());
		$biome = strtoupper($args[0]);
		$plot = $this->getPlugin()->getPlotByPosition($player->getPosition());
		if ($plot === null) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notinplot"));
			return true;
		}
		if ($plot->owner !== $sender->getName() and !$sender->hasPermission("myplot.admin.biome")) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notowner"));
			return true;
		}
		if(is_numeric($biome)) {
			$biome = (int) $biome;
			if($biome > 27 or $biome < 0) {
				$sender->sendMessage(TextFormat::RED . $this->translateString("biome.invalid"));
				$biomes = implode(", ", array_keys($this->biomes));
				$sender->sendMessage(TextFormat::RED . $this->translateString("biome.possible", [$biomes]));
				return true;
			}
			$biome = Biome::getBiome($biome);
		}else{
		if (!isset($this->biomes[$biome])) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("biome.invalid"));
			$biomes = implode(", ", array_keys($this->biomes));
			$sender->sendMessage(TextFormat::RED . $this->translateString("biome.possible", [$biomes]));
			return true;
		}
		$biome = Biome::getBiome($this->biomes[$biome]);
		}
		if ($this->getPlugin()->setPlotBiome($plot, $biome)) {
			$sender->sendMessage($this->translateString("biome.success", [$biome->getName()]));
		} else {
			$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
		}
		return true;
	}
}