<?php

namespace owonico;

use owonico\command\{CoreCommand, HubCommand, AboutCommand, PingCommand, InfoCommand};
use owonico\listeners\{LobbyListener, PlayerListener, ServerListener};
use owonico\manager\RankManager;
use owonico\task\BroadcastTask;
use owonico\skin\PersonaSkinAdapter;
use owonico\skin\libs\traits\RemovePluginDataDirTrait;
use maipian\await\generator\Await;
use maipian\await\std\AwaitStd;
use maipian\scoreboard\Scoreboard;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\Server;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{
    use RemovePluginDataDirTrait;

    public static Config $settings;

    private ?SkinAdapter $originalAdaptor = null;

    public static $instance;

    public AwaitStd $std;
    
    public $cps;

    public array $encryptionKeys;

    public static array $playerArena;
    public static array $pearlCooldown;
    public static array $scoreboardEnabled;
    public static array $bffaplacedblock;
    public static array $bffablocktimer;




    /**
     * @return mixed
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function onEnable(): void
    {

        $this->getServer()->getNetwork()->setName(Variables::Motd);

        self::$instance = $this;
        self::$settings = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $this->std = AwaitStd::init($this);
        RankManager::init();

        $this->initTask();
        $this->initCommand();
        $this->initFFA();
        $this->initListener();
        $this->initDataFolders();

        $this->originalAdaptor = SkinAdapterSingleton::get();
        SkinAdapterSingleton::set(new PersonaSkinAdapter());

        $this->getLogger()->info("Suscees detect extends libs !");

        /**
         * HyperiumMC code
         */

        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            public function __construct(Main $plugin){
                $this->plugin = $plugin;
            }

            public function onRun(): void
            {
                $pass = $this->plugin->getConfig()->get("password");

                if (base64_decode($pass) !== "QuzaNetwork2018"){
                    $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                    $this->plugin->getLogger()->critical("§cWrong Password!");
                }
            }
        }, 20);

        /**
         * HyperiumMC code
         */

        foreach($this->getServer()->getResourcePackManager()->getResourceStack() as $resourcePack){
            $uuid = $resourcePack->getPackId();
            if($this->getConfig()->getNested("resource-packs.{$uuid}", "") !== ""){
                $encryptionKey = $this->getConfig()->getNested("resource-packs.{$uuid}");
                $this->encryptionKeys[$uuid] = $encryptionKey;
                $this->getLogger()->debug("Loaded encryption key for resource pack $uuid");
            }
        }
        $this->getServer()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
            $packets = $event->getPackets();
            foreach($packets as $packet){
                if($packet instanceof ResourcePacksInfoPacket){
                    foreach($packet->resourcePackEntries as $index => $entry){
                        if(isset($this->encryptionKeys[$entry->getPackId()])){
                            $contentId = $this->encryptionKeys[$entry->getPackId()];
                            $packet->resourcePackEntries[$index] = new ResourcePackInfoEntry($entry->getPackId(), $entry->getVersion(), $entry->getSizeBytes(), $contentId, $entry->getSubPackName(), $entry->getPackId(), $entry->hasScripts(), $entry->isRtxCapable());
                        }
                    }
                }
            }
        }, EventPriority::HIGHEST, $this);
    }

    public function initTask(){
        $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), 15*150);
    }

    public function initDataFolders(){
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "rank");
        @mkdir($this->getDataFolder() . "settings");
        @mkdir($this->getDataFolder() . "stats");
        @mkdir($this->getDataFolder() . "capes");

        $this->saveDefaultConfig();


        //cape
        foreach (["Red Creeper.png"] as $cape){
            $this->saveResource("capes/" . $cape);
        }

        $this->getLogger()->info("Loaded Data Folders");
    }

    public function initListener(){
        $this->getServer()->getPluginManager()->registerEvents(new LobbyListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ServerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        $this->getLogger()->info("Loaded Listeners...");
    }

    public function initCommand(): void{
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("about"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("clear"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("tell"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("kill"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("list"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("listperms"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("checkperm"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("me"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("ban-ip"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("unban-ip"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("seed"));;
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("title"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("time"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("timings"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("status"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("transferserver"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-on"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-all"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-off"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("particle"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("pardon"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("difficulty"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("effect"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("enchant"));

        $this->getServer()->getCommandMap()->register("hub", new HubCommand($this));
        $this->getServer()->getCommandMap()->register("core", new CoreCommand($this));
        $this->getServer()->getCommandMap()->register("about", new AboutCommand($this));
        $this->getServer()->getCommandMap()->register("ping", new PingCommand($this));
        $this->getServer()->getCommandMap()->register("info", new InfoCommand($this));
    }

    public function initFFA(){
        $this->loadWorld(Variables::Nodebuffffa);
        $this->loadWorld(Variables::Comboffa);
        $this->loadWorld(Variables::Fistffa);
        $this->loadWorld(Variables::Sumoffa);
        $this->loadWorld(Variables::Gappleffa);
        $this->loadWorld(Variables::Buildffa);
        $this->loadWorld(Variables::Knockffa);
    }

    public function loadWorld(string $world){
        Await::f2c(function () use ($world) {
            $wrmgr = $this->getServer()->getWorldManager();

            $wrmgr->loadWorld($world, true);
            $wrd = $wrmgr->getWorldByName($world);

            $wrd->setTime(1000);
            $wrd->stopTime();

            yield from $this->std->sleep(1);
        });
    }

    public function onDisable(): void
    {
        if($this->originalAdaptor !== null){
            SkinAdapterSingleton::set($this->originalAdaptor);
        }
    }

    public function createCape($capeName) {
        $path = $this->getDataFolder() . "capes/$capeName.png";
        $img = imagecreatefrompng($path);
        $bytes = "";
        try {
            for ($y = 0; $y < imagesy($img); $y++) {
                for ($x = 0; $x < imagesx($img); $x++) {
                    $rgba = @imagecolorat($img, $x, $y);
                    $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            @imagedestroy($img);
        }catch (\Exception $exception){
            $this->getLogger()->info("Broken srgb profile");
        }

        return $bytes;
    }

    public function getAllCapes() {
        $list = array();

        foreach(array_diff(scandir($this->getDataFolder() . "capes/"), ["..", "."]) as $data) {
            $dat = explode(".", $data);

            if($dat[1] == "png") {
                array_push($list, $dat[0]);
            }
        }

        return $list;
    }

    public function addCPS(Player $player): void{
        $time = microtime(true);
        $this->cps[$player->getName()][] = $time;
    }

    public function getCPS(Player $player): int{
        $time = microtime(true);
        return count(array_filter($this->cps[$player->getName()] ?? [], static function(float $t) use ($time):bool{
            return ($time - $t) <= 1;
        }));
    }

    public static function getRuleContent() : string{
        $content = [
            TextFormat::GRAY . "By joining in our server, you have agreed to follow our rules and we have all the rights to give Punishments"
            , ""
            , TextFormat::GRAY . "- Minimum of 10ms debounce time"
            , TextFormat::GRAY . "- If your mouse double clicks"
            , TextFormat::GRAY . "  be sure to use DC Prevent while playing"
            , TextFormat::GRAY . "- No hacking or any unfair advantages"
            , TextFormat::GRAY . "- No macros or firekeys"
            , TextFormat::GRAY . "- No hate Speech (Racism, Death Threats, etc.)"
            , TextFormat::GRAY . "- No using any clients that provide advantages (Toolbox)"
            , TextFormat::GRAY . "- No using 'No Hurt Cam'"
            , TextFormat::GRAY . "- No abusing bugs or glitches"
            , ""
            , TextFormat::GRAY . "If you happen to cheat on other servers"
            , TextFormat::GRAY . "Make sure you restart your pc when logging on to OceanMC"
        ];
        return implode("\n", $content);
    }

    public static function getWorldCount(string $world): int{
        $world = Server::getInstance()->getWorldManager()->getWorldByName($world);

        if ($world == null){
            return 0;
        }
        if ($world->getPlayers() == null){
            return 0;
        }
        return count($world->getPlayers());
    }
    
    public static function ensureData(Player $player){
        $coins = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);
        $kill = new Config(Main::getInstance()->getDataFolder() . "stats/Kills.yml", Config::YAML);
        $death = new Config(Main::getInstance()->getDataFolder() . "stats/Deaths.yml", Config::YAML);

        if (!$coins->exists($player->getXuid())){
            $coins->set($player->getXuid(), 125);
            $coins->save();
        }
        if (!$kill->exists($player->getXuid())){
            $kill->set($player->getXuid(), 0);
            $kill->save();
        }
        if (!$death->exists($player->getXuid())){
            $death->set($player->getXuid(), 0);
            $death->save();
        }
    }


}