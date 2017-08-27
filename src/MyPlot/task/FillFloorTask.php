<?php
namespace MyPlot\task;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;


use MyPlot\MyPlot;
use MyPlot\Plot;

class FillFloorTask extends PluginTask {
	/** @var MyPlot $plugin */
	private $plugin;

	private $level, $height, $bottomBlock, $plotFillBlock, $plotFloorBlock, $plotBeginPos, $xMax, $zMax, $maxBlocksPerTick, $pos;

	public function __construct(MyPlot $plugin, Plot $plot, $maxBlocksPerTick = 256, $id, $meta) {
		parent::__construct($plugin);
		$this->plotBeginPos = $plugin->getPlotPosition($plot);
		$this->level = $this->plotBeginPos->getLevel();
		$plotLevel = $plugin->getLevelSettings($plot->levelName);
		$plotSize = $plotLevel->plotSize;
		$this->xMax = $this->plotBeginPos->x + $plotSize;
		$this->zMax = $this->plotBeginPos->z + $plotSize;
		$this->height = $plotLevel->groundHeight;
		$this->plotFloorBlock = $plotLevel->plotFloorBlock; ////
		$this->maxBlocksPerTick = $maxBlocksPerTick;
        $blockID = $id;
        $blockMETA = $meta;
		$this->pos = new Vector3($this->plotBeginPos->x, 0, $this->plotBeginPos->z);
		$this->plugin = $plugin;
		$this->plugin->getLogger()->debug("Floor Task started at plot {$plot->X};{$plot->Z}");
	}

	public function onRun($currentTick) {
		foreach ($this->level->getEntities() as $entity) {
			if (($plot = $this->plugin->getPlotByPosition($entity)) != null) {
				if ($plot->X === $this->plotBeginPos->x and $plot->Z === $this->plotBeginPos->z) {
					if (!$entity instanceof Player) {
						$entity->close();
					}else{
						$this->plugin->teleportPlayerToPlot($entity, $plot);
					}
				}
			}
		}
		$blocks = 0;
		while ($this->pos->x < $this->xMax) {
			while ($this->pos->z < $this->zMax) {
                $this->pos->y = $this->height;
                $block = Block::get($blockID, $blockMETA);
                $this->level->setBlock($this->pos, $block, false, false);
                $blocks++;
                if ($blocks === $this->maxBlocksPerTick) {
                    $this->getOwner()->getServer()->getScheduler()->scheduleDelayedTask($this, 1);
                    return;
                }
				$this->pos->y = 0;
				$this->pos->z++;
			}
			$this->pos->z = $this->plotBeginPos->z;
			$this->pos->x++;
		}
		foreach ( $this->level->getTiles() as $tile) {
			if (($plot = $this->plugin->getPlotByPosition($tile)) != null) {
				if ($plot->X === $this->plotBeginPos->x and $plot->Z === $this->plotBeginPos->z) {
					$tile->close();
				}
			}
		}
		$this->plugin->getLogger()->debug("Floor task completed at {$this->plotBeginPos->x};{$this->plotBeginPos->z}");
	}
}