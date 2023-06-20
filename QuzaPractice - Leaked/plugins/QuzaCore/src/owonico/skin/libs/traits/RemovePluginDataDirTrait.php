<?php

namespace owonico\skin\libs\traits;

use pocketmine\plugin\PluginBase;

use function count;
use function file_exists;
use function is_dir;
use function rmdir;
use function scandir;

/** This trait override most methods in the {@link PluginBase} abstract class. */
trait RemovePluginDataDirTrait{
    /** Trying remove empty data dir on plugin load */
    protected function onLoad() : void{
        $this->removePluginDataDir();
    }

    /** Trying remove empty data dir on plugin enable */
    protected function onEnable() : void{
        $this->removePluginDataDir();
    }

    /** Trying remove empty data dir on plugin disable */
    protected function onDisable() : void{
        $this->removePluginDataDir();
    }

    private function removePluginDataDir() : void{
        /** @var PluginBase $this */
        $dataFolder = $this->getDataFolder();
        if(
            file_exists($dataFolder) && // If the data folder exists
            is_dir($dataFolder) && // And it's a directory
            count(scandir($dataFolder)) === 2 // And it contains only the . and .. folders
        ){
            rmdir($dataFolder); // Remove the data folder
        }
    }
}