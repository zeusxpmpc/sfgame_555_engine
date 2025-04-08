<?php

class Monster extends Mob{

    //1 woj // 2 scout // 3 mage
    // -1 szpony // -2 wiatr // -3 kulki // -4 patyk // -5 udko // -6 skala

    public function loadForQuest($playerdata){
        
        $jsonData = file_get_contents('engine/data/monsters_quests.json');
        $monsters_quest = json_decode($jsonData, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            Logger::error("ERROR JSON: " . json_last_error_msg());
            return false;
        }

        $questid = $playerdata['statusextra'];
        //1 SCOUT // 2 COLLECT // 3 FETCH //4 KILL //5 TRANSPORT //def ESCORT
        $questype = explode(";", $playerdata['questtype'])[$questid-1];
        $questenemy = explode(";", $playerdata['questenemy'])[$questid-1];
        $questlocation = explode(";", $playerdata['questlocation'])[$questid-1];

        $monsterdata = $monsters_quest[array_rand($monsters_quest)];

        $this->name = "monster";
        $this->facearray[0] = "-" . $monsterdata['faceid'];

        if(array_key_exists("classid", $monsterdata)){
            $this->classid = $monsterdata['classid'];
        }
        
        if(array_key_exists("weaponid", $monsterdata)){
            if($monsterdata['weaponid'] > 0){
                $this->weapon['itemtype'] = 1;
                $this->weapon['itemid'] = $monsterdata['weaponid'];
            }
            else{
                $this->weapon['itemtype'] = $monsterdata['weaponid'];
            }
        }

        if(array_key_exists("shieldid", $monsterdata) && array_key_exists("shieldblock", $monsterdata)){
            $this->shield['itemtype'] = 2;
            $this->shield['itemid'] = $monsterdata['shieldid'];
            $this->shield['dmgmin'] = $monsterdata['shieldblock'];
        }

        $this->lvl = $playerdata['lvl'];
        $this->attrstr = $this->getRandMonsterAttrByLvl($this->lvl);
        $this->attrint = $this->getRandMonsterAttrByLvl($this->lvl);
        $this->attrdex = $this->getRandMonsterAttrByLvl($this->lvl);
        $this->attrwit = $this->getRandMonsterAttrByLvl($this->lvl);
        $this->attrluck = $this->getRandMonsterAttrByLvl($this->lvl);

        $this->hp = $this->attrwit * Utils::getHpMultiplyByClassId($this->classid) * ($this->lvl + 1);

        $basedmg = ($this->lvl * $this->lvl + 1) * Utils::getDmgMultiplyByClassId($this->classid);
        $basedmg = round($basedmg);

        $this->dmgmin = round($basedmg /4);
        $this->dmgmax = round($basedmg /2);

        $basearmor = rand($this->lvl * 2, $this->lvl * 5);
        $this->armor = $basearmor * Utils::getArmorMultiplyByClassId($this->classid);
    }

    private function getRandMonsterAttrByLvl($lvl){
        return rand($this->lvl * 2, $this->lvl * 5);
    }

    public function loadForDung($did, $dlvl){

        $jsonData = file_get_contents('engine/data/monsters_dungeons.json');
        $monsters_dung = json_decode($jsonData, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            Logger::error("ERROR JSON: " . json_last_error_msg());
            return false;
        }

        $monsterdata = $monsters_dung[$did + ($dlvl-2)];

        $this->name = $monsterdata['name'];
        $this->facearray[0] = "-" . $monsterdata['faceid'];
        $this->lvl = $monsterdata['lvl'];
        $this->classid = $monsterdata['classid'];  

        if($monsterdata['weaponid'] > 0){
            $this->weapon['itemid'] = $monsterdata['weaponid'] + (1000 * ($this->classid-1));
            $this->weapon['itemtype'] = 1;
        }
        else{
            $this->weapon['itemtype'] = $monsterdata['weaponid'];
        }

        if(array_key_exists('shieldid', $monsterdata)){
            $this->shield['itemid'] = $monsterdata['shieldid'];
            $this->shield['dmgmin'] = $monsterdata['shieldblock'];
        }

        $this->attrstr = $monsterdata['attrstr'];
        $this->attrint = $monsterdata['attrint'];
        $this->attrdex = $monsterdata['attrdex'];
        $this->attrwit = $monsterdata['attrwit'];
        $this->attrluck = $monsterdata['attrluck'];
        
        $baseattr = 1;

		if($this->classid == 1){
			$baseattr = $this->attrstr;
		}
		else if($this->classid == 2){
			$baseattr = $this->attrint;
		}
		else if($this->classid == 3){
			$baseattr = $this->attrdex;
		}

		$this->dmgmin = round(1 * ($baseattr /10));
		$this->dmgmax = round(Utils::getDmgMultiplyByClassId($this->classid) * ($baseattr /10));

        $this->hp = $monsterdata['hp'];
        $this->armor = $monsterdata['armor'];

        return true;
    }
}