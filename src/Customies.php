<?php
declare(strict_types=1);

namespace customies;

use Closure;
use customies\block\CustomiesBlockFactory;
use customies\world\LevelDB;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;
use ReflectionException;

class Customies extends PluginBase {

	protected function onLoad(): void {
		$provider = new WritableWorldProviderManagerEntry(\Closure::fromCallable([LevelDB::class, 'isValid']), fn(string $path) => new LevelDB($path), Closure::fromCallable([LevelDB::class, 'generate']));
		$this->getServer()->getWorldManager()->getProviderManager()->addProvider($provider, "leveldb", true);
		$this->getServer()->getWorldManager()->getProviderManager()->setDefault($provider);
	}

	/**
	 * @throws ReflectionException
	 */
	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents(new CustomiesListener(), $this);

		CustomiesBlockFactory::init();

		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(static function (): void {
			// This task is scheduled with a 0-tick delay so it runs as soon as the server has started. Plugins should
			// register their custom blocks in onEnable() before this is executed.
			CustomiesBlockFactory::updateRuntimeMappings();
			CustomiesBlockFactory::addWorkerInitHook();
		}), 0);
	}
}