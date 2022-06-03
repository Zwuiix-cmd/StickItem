<?php

namespace Zwuiix\Stick;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;

//EVENT
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

//ITEM
use pocketmine\block\BlockIds;
use pocketmine\item\Item;

class Main extends PluginBase implements  Listener
{

    private $antipearmcooldown = [];
    private $dantipearmcooldown = [];
    private $antibuild = [];
    private $antibuilds = [];
    public static $instance;

    public function onEnable()
    {
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function removeTask($id) {
        $this->getScheduler()->cancelTask($id);
    }

    public function onLoad(){
      $this->reloadConfig();
    }

    public static function getInstance() : Main {
      return self::$instance;
    }

    public function onTag(EntityDamageEvent $event){
        if(!$event instanceof EntityDamageByEntityEvent) return;
           $player = $event->getEntity();
           $damager = $event->getDamager();

           $config = new Config($this->getDataFolder()."config.yml", Config::YAML);

           if(!$damager instanceof Player && !$player instanceof Player) return;
           $item = $damager->getInventory()->getItemInHand();
           if($item->getId() == $config->get("ID-PEARL")){
            $name = $damager->getName();
            if (!isset($this->dantipearmcooldown[$name])) $this->dantipearmcooldown[$name] = time();

            if (time() < $this->dantipearmcooldown[$name]) {
                if ($event->isCancelled()) return;
                $event->setCancelled();
                $second = $this->dantipearmcooldown[$name] - time();
                $damager->sendTip("§4- §cVous devez attendre $second seconde(s) §4-");


            }else {

                if ($event->isCancelled()) return;
                $damager->sendMessage("§eAntiPearl §f» §aVous avez bien antipearl {$player->getName()} !");
                $player->sendMessage("§eAntiPearl §f» §aVous avez été antipearl par {$damager->getName()} !");
                $this->antipearmcooldown[$player->getName()] =  time() + $config->get("Pearl-Cooldown");
                $this->dantipearmcooldown[$damager->getName()] =  time() + $config->get("PearlD-Cooldown");
            }
           }
           if($item->getId() == $config->get("ID-ANTIBUILD")){
               $name = $damager->getName();
               if (!isset($this->antibuild[$name])) $this->antibuild[$name] = time();

               if (time() < $this->antibuild[$name]) {
                   if ($event->isCancelled()) return;
                   $event->setCancelled();
                   $second = $this->antibuild[$name] - time();
                   $damager->sendTip("§4- §cVous devez attendre $second seconde(s) §4-");

               }else {

                   if ($event->isCancelled()) return;
                   $damager->sendMessage("§eAntiPearl §f» §aVous avez bien antibuild {$player->getName()} !");
                   $player->sendMessage("§eAntiPearl §f» §aVous avez été antibuild par {$damager->getName()} !");
                   $this->antibuilds[$player->getName()] =  time() + $config->get("ANTIBUILD-Cooldown");
                   $this->antibuild[$damager->getName()] =  time() + $config->get("ANTIBUILD-Cooldown");
               }
           }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        if (!$event->getPlayer() instanceof Player) return;
        $name = $event->getPlayer()->getName();
        if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

        if (time() < $this->antibuilds[$name]) {
            if ($event->isCancelled()) return;
            $event->setCancelled();
            $second = $this->antibuilds[$name] - time();
            $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        if (!$event->getPlayer() instanceof Player) return;
        $name = $event->getPlayer()->getName();
        if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

        if (time() < $this->antibuilds[$name]) {
            if ($event->isCancelled()) return;
            $event->setCancelled();
            $second = $this->antibuilds[$name] - time();
            $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
        }
    }

    public function onUse(PlayerInteractEvent $event):void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $item = $player->getInventory()->getItemInHand();
        $block = $event->getBlock();

        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        if($item->getId() == Item::ENDER_PEARL){
            if (!isset($this->antipearmcooldown[$name])) $this->antipearmcooldown[$name] = time();

            if (time() < $this->antipearmcooldown[$name]) {
                if ($event->isCancelled()) return;
                $event->setCancelled();
                $second = $this->antipearmcooldown[$name] - time();
                $player->sendTip("§4- §cVous devez attendre $second seconde(s) §4-");
            }
        }elseif($block->getId() == BlockIds::FENCE_GATE or $block->getId() == BlockIds::CHEST or $block->getId() == BlockIds::ENDER_CHEST or $block->getId() == BlockIds::TRAPDOOR or $block->getId() == BlockIds::WOODEN_DOOR_BLOCK){
            if(!$event->getPlayer() instanceof Player) return;
            $name = $event->getPlayer()->getName();
            if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

            if (time() < $this->antibuilds[$name]) {
                if ($event->isCancelled()) return;
                $event->setCancelled();
                $second = $this->antibuilds[$name] - time();
                $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
            }
        }
    }
}