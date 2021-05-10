<?php

/**
 * @name LifeJobs
 * @author pju6791
 * @version 3.0.0
 * @api 3.0.0
 * @main pju6791\LifeJobs\LifeJobs
 * @website https://github.com/Le0onKR
 * @description [PMMP] LifeJobs System
 */

namespace pju6791\LifeJobs;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\block\Crops;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use function time;
use function mkdir;
use function is_null;
use function strtolower;
use function json_encode;
use function json_decode;
use function date_default_timezone_set;

class LifeJobs extends PluginBase implements Listener {

    public static $prefix = '§l§a[직업] §r§f';

    public static $instance = null;

    public $cool = [];

    public $LifeJobs, $db;

    public $level = null;

    public $FormId = [
        121201212, 699689899,
        989898989, 656464646,
        979897655, 334678989
    ];

    public static function getInstance() :?LifeJobs {
        return self::$instance;
    }

    public function onLoad() {
        self::$instance = $this;

        date_default_timezone_set('Asia/Seoul');
    }

    public function onEnable() {

        if(($this->level = $this->getServer()->getPluginManager()->getPlugin("PLevel"))) {
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $command = new PluginCommand('직업', $this);
        $command->setDescription('LifeJobs Command');
        $this->getServer()->getCommandMap()->register('직업', $command);

        @mkdir($this->getDataFolder());
        $this->LifeJobs = new Config($this->getDataFolder() . "LifeJobs.yml", Config::YAML, [
            "player" => []
        ]);
        $this->db = $this->LifeJobs->getAll();
    }

    public function onJoin(PlayerJoinEvent $event) {

        $player = $event->getPlayer();

        if(!isset($this->db["player"][strtolower($player->getName())])) {
            $this->db["player"][strtolower($player->getName())]["Job"] = "일반인";
        }
    }

    public function onInter(PlayerInteractEvent $event) {

        $player = $event->getPlayer();
        $item = $event->getItem();

        if($this->myLifeJobs($player) == "전사") {
            if($item->getId() == ItemIds::IRON_SWORD) {
                if($item->getDamage() == 5) {
                    if(!isset($this->cool[$player->getName()])) {
                        $this->cool[$player->getName()] = time() + 20 * 5;
                        $player->sendMessage(static::$prefix . "전사 스킬을 사용했습니다.");
                    }
                }
            }
        }

        if($this->myLifeJobs($player) == "도적") {
            if($item->getId() == ItemIds::STONE_SHOVEL) {
                if($item->getId() == 5) {
                    if(!isset($this->cool[$player->getName()])) {
                        $this->cool[$player->getName()] = time() + 20 * 7;
                        $player->sendMessage(static::$prefix . "도적 스킬을 사용했습니다.");
                    }
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event) {

        $player = $event->getPlayer();
        $block = $event->getBlock();
        $name = $player->getName();

        if($this->myLifeJobs($player) == "광부") {
            if($block->getId() == BlockIds::STONE) {
                $this->level->addXp($name, 2);
            } else {
                $this->level->addXp($name, 1);
            }

            if($block->getId() == BlockIds::IRON_ORE) {
                $this->level->addXp($name, 10);
            } else {
                $this->level->addXp($name, 5);
            }

            if($block->getId() == BlockIds::GOLD_ORE) {
                $this->level->addXp($name, 12);
            } else {
                $this->level->addXp($name, 7);
            }

            if($block->getId() == BlockIds::LAPIS_ORE) {
                $this->level->addXp($name, 7);
            } else {
                $this->level->addXp($name, 5);
            }

            if($block->getId() == BlockIds::COAL_ORE) {
                $this->level->addXp($player, 7);
            } else {
                $this->level->addXp($player, 7);
            }

            if($block->getId() == BlockIds::REDSTONE_ORE) {
                $this->level->addXp($name, 10);
            } else {
                $this->level->addXp($name, 7);
            }

            if($block->getId() == BlockIds::DIAMOND_ORE) {
                $this->level->addXp($name, 25);
            } else {
                $this->level->addXp($name, 20);
            }

            if($block->getId() == BlockIds::EMERALD_ORE) {
                $this->level->addXp($name, 30);
            } else {
                $this->level->addXp($name, 25);
            }
        }

        if($this->myLifeJobs($player) == "농부") {
            if($block instanceof Crops) {
                $this->level->addXp($name, 10);
            } else {
                $this->level->addXp($name, 5);
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool {

        $player = $sender;

        if($command->getName() === "직업") {
            if($player instanceof Player) {
                $this->LifeJobsForm($player);
            }
        }
        return true;
    }

    public function LifeJobsForm(Player $player) {

        $encode = json_encode([

            "type" => "form",
            "title" => "§lLifeJobs",
            "content" => "§l§fLifeJobs",
            "buttons" => [
                ["text" => "창 닫기\n§r§0▶ UI를 종료합니다."],
                ["text" => "직업 내정보\n§r§0▶ 직업 내정보를 확인합니다."],
                ["text" => "직업 가입\n§r§0▶ 직업을 가입합니다."],
                ["text" => "직업 탈퇴\n§r§0▶ 직업을 탈퇴합니다."]
            ]
        ]);
        $pack = new ModalFormRequestPacket();
        $pack->formId = $this->FormId[0];
        $pack->formData = $encode;
        $player->sendDataPacket($pack);
    }

    public function myJobInformation(Player $player) {

        $content = "\n§r§f> 이름 : {$player->getName()}\n\n§rf> §f직업 : {$this->myLifeJobs($player)}\n§r§f레벨 : {$this->level->getLevel($player)}레벨\n§r§f> 경험치 : {$this->level->getExp($player)}경험치";

        $encode = json_encode([

            "type" => "form",
            "title" => "§lLifeJobs MyInformation",
            "content" => $content,
            "buttons" => [
                ["text" => "돌아 가기\n§r§0▶ 이전 UI로 돌아갑니다."],
                ["text" => "창 닫기\n§r§0▶ UI를 종료합니다."]
            ]
        ]);
        $pack = new ModalFormRequestPacket();
        $pack->formId = $this->FormId[1];
        $pack->formData = $encode;
        $player->sendDataPacket($pack);
    }

    public function JobRegistration(Player $player) {

        $encode = json_encode([

            "type" => "form",
            "title" => "§lLifeJobs Registration",
            "content" => "§l§f가입하실 직업을 선택해주세요.",
            "buttons" => [
                ["text" => "전사\n§r§0▶ 전사라는 직업에 가입합니다."],
                ["text" => "농부\n§r§0▶ 농부라는 직업에 가입합니다."],
                ["text" => "광부\n§r§0▶ 광부라는 직업에 가입합니다."],
                ["text" => "도적\n§r§0▶ 도적이라는 직업에 가입합니다."]
            ]
        ]);
        $pack = new ModalFormRequestPacket();
        $pack->formId = $this->FormId[2];
        $pack->formData = $encode;
        $player->sendDataPacket($pack);
    }

    public function JobWithdrawal(Player $player) {

        $encode = json_encode([

            "type" => "form",
            "title" => "§lLifeJobs Withdrawal",
            "content" => "§l§b● §f직업에서 탈퇴하시겠습니까?",
            "buttons" => [
                ["text" => "예\n§r§0▶ 직업에서 탈퇴합니다."],
                ["text" => "아니오\n§r§0▶ 직업을 탈퇴하지 않습니다."]
            ]
        ]);
        $pack = new ModalFormRequestPacket();
        $pack->formId = $this->FormId[3];
        $pack->formData = $encode;
        $player->sendDataPacket($pack);
    }

    public function onData(DataPacketReceiveEvent $event) {

        $pack = $event->getPacket();
        $player = $event->getPlayer();

        if($pack instanceof ModalFormResponsePacket and $pack->formId == $this->FormId[0]) {

            $data = json_decode($pack->formData, true);

            //if(!is_null($data)) return;

            if($data == 0) {
                $player->sendMessage(static::$prefix . "UI를 종료했습니다.");
            }

            if($data == 1) {
                $this->myJobInformation($player);
            }

            if($data == 2) {
                if($this->myLifeJobs($player) == "일반인") {
                    $this->JobRegistration($player);
                } else {
                    $player->sendMessage(static::$prefix . "당신은 직업이 있습니다.");
                }
            }

            if($data == 3) {
                $this->JobWithdrawal($player);
            }
        }

        if($pack instanceof ModalFormResponsePacket and $pack->formId == $this->FormId[1]) {

            $data = json_decode($pack->formData, true);

            //if(!is_null($data)) return;

            if($data == 0) {
                $this->LifeJobsForm($player);
            }
        }

        if($pack instanceof ModalFormResponsePacket and $pack->formId == $this->FormId[2]) {

            $data = json_decode($pack->formData, true);

            //if(!is_null($data)) return;

            if($data == 0) {
                $this->LifeJobsRegistration($player, "전사");
            }

            if($data == 1) {
                $this->LifeJobsRegistration($player, "농부");
            }

            if($data == 2) {
                $this->LifeJobsRegistration($player, "광부");
            }

            if($data == 3) {
                $this->LifeJobsRegistration($player, "도적");
            }
        }

        if($pack instanceof ModalFormResponsePacket and $pack->formId == $this->FormId[3]) {

            $data = json_decode($pack->formData, true);

            //if(!is_null($data)) return;

            if($data == 0) {
                $this->LifeJobsWithdrawal($player);
            }
        }
    }

    public function myLifeJobs(Player $player) {

        return $this->db["player"][strtolower($player->getName())]["Job"];
    }

    public function LifeJobsRegistration(Player $player, string $Job) {

        $player->addTitle("§l§a!");
        $player->addSubTitle("§r§a{$Job}§f이란 직업에 가입했습니다.");
        $this->db["player"][strtolower($player->getName())]["Job"] = $Job;
        $this->onSave();
    }

    public function LifeJobsWithdrawal(Player $player) {

        $item = Item::get(ItemIds::BOOK, 10, 1);

        if($player->getInventory()->contains($item)) {
            $player->addTitle("§l§c!");
            $player->addSubTitle("직업에서 탈퇴했습니다.");
            $this->db["player"][strtolower($player->getName())]["Job"] = "일반인";
            $this->onSave();
            $player->getInventory()->removeItem($item);
            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_AMBIENT_POLLINATE);
        } else {
            $player->sendMessage(static::$prefix . "직업탈퇴권이 부족합니다.");
        }
    }

    public function onSave() {

        if($this->LifeJobs instanceof Config) {
            $this->LifeJobs->setAll($this->db);
            $this->LifeJobs->save();
        }
    }
}
