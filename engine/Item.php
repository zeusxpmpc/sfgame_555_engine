<?php

class Item{

	public int $id = 0;
	public int $itemtype = 0;
	public int $itemclass = 0;
	public int $itemid = 0;

	public int $dmgmin = 0;
	public int $dmgmax = 0;
	
	public int $attrtype1 = 0;
	public int $attrtype2 = 0;
	public int $attrtype3 = 0;
	
	public int $attrvalue1 = 0;
	public int $attrvalue2 = 0;
	public int $attrvalue3 = 0;

	public int $silver = 0;
	public int $mush = 0;

	public int $enchantid = 0;
	public int $enchantvalue = 0;

	public int $toilet = 0;
	public int $slotid = 0;
	public int $ownerid = 0;

	public bool $isepic = false;

	public int $attrstr = 0;
	public int $attrint = 0;
	public int $attrdex = 0;
	public int $attrwit = 0;
	public int $attrluck = 0;

	public int $armor = 0;

	public int $attrblock = 0;//todo

	public $table = "";

	public function insertTo($table){
		global $db;
		
		try{
			$qry = $db->prepare("INSERT INTO `$table`(`itemid`, `itemtype`, `itemclass`, `dmgmin`, `dmgmax`, `attrtype1`, `attrtype2`, `attrtype3`, `attrvalue1`, `attrvalue2`, `attrvalue3`, `silver`, `mush`, `enchantid`, `enchantvalue`, `toilet`, `slotid`, `ownerid`) 
			VALUES 
			(:itemid, :itemtype, :itemclass, :dmgmin, :dmgmax, :attrtype1, :attrtype2, :attrtype3, :attrvalue1, :attrvalue2, :attrvalue3, :silver, :mush, :enchantid, :enchantvalue, :toilet, :slotid, :ownerid)");
			$qry->bindParam(":itemid", $this->itemid);
			$qry->bindParam(":itemtype", $this->itemtype);
			$qry->bindParam(":itemclass", $this->itemclass);
			$qry->bindParam(":dmgmin", $this->dmgmin);
			$qry->bindParam(":dmgmax", $this->dmgmax);
			$qry->bindParam(":attrtype1", $this->attrtype1);
			$qry->bindParam(":attrtype2", $this->attrtype2);
			$qry->bindParam(":attrtype3", $this->attrtype3);
			$qry->bindParam(":attrvalue1", $this->attrvalue1);
			$qry->bindParam(":attrvalue2", $this->attrvalue2);
			$qry->bindParam(":attrvalue3", $this->attrvalue3);
			$qry->bindParam(":silver", $this->silver);
			$qry->bindParam(":mush", $this->mush);
			$qry->bindParam(":enchantid", $this->enchantid);
			$qry->bindParam(":enchantvalue", $this->enchantvalue);
			$qry->bindParam(":toilet", $this->toilet);
			$qry->bindParam(":slotid", $this->slotid);
			$qry->bindParam(":ownerid", $this->ownerid);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		return true;
	}

	public function loadFrom($table, $ownerid, $slotid){
		global $db;

		$this->slotid = $slotid;
		$this->ownerid= $ownerid;

		$qry = $db->prepare("SELECT * FROM `$table` WHERE `ownerid` = :ownerid AND `slotid` = :slotid LIMIT 1");
		$qry->bindParam(":ownerid", $ownerid, PDO::PARAM_INT);
		$qry->bindParam(":slotid", $slotid, PDO::PARAM_INT);
		if($qry->execute()){
			$item = $qry->fetch();
			if($item){
				$this->table = $table;
				$this->id = $item['id'];
				$this->itemtype = $item['itemtype'];
				$this->itemclass = $item['itemclass'];
				$this->itemid = $item['itemid'];
				$this->dmgmin = $item['dmgmin'];
				$this->dmgmax = $item['dmgmax'];
				$this->attrtype1 = $item['attrtype1'];
				$this->attrtype2 = $item['attrtype2'];
				$this->attrtype3 = $item['attrtype3'];
				$this->attrvalue1 = $item['attrvalue1'];
				$this->attrvalue2 = $item['attrvalue2'];
				$this->attrvalue3 = $item['attrvalue3'];
				$this->silver = $item['silver'];
				$this->mush = $item['mush'];
				$this->silver = $item['silver'];

				$this->enchantid = $item['enchantid'];
				$this->enchantvalue = $item['enchantvalue'];
				$this->toilet = $item['toilet'];
				$this->slotid = $item['slotid'];
				$this->ownerid = $item['ownerid'];

				return true;
			}
		}
		return false;
	}

	public function loadFromArray($item){
		$this->id = $item['id'];
		$this->itemtype = $item['itemtype'];
		$this->itemclass = $item['itemclass'];
		$this->itemid = $item['itemid'];
		$this->dmgmin = $item['dmgmin'];
		$this->dmgmax = $item['dmgmax'];
		$this->attrtype1 = $item['attrtype1'];
		$this->attrtype2 = $item['attrtype2'];
		$this->attrtype3 = $item['attrtype3'];
		$this->attrvalue1 = $item['attrvalue1'];
		$this->attrvalue2 = $item['attrvalue2'];
		$this->attrvalue3 = $item['attrvalue3'];
		$this->silver = $item['silver'];
		$this->mush = $item['mush'];
		$this->silver = $item['silver'];
		$this->enchantid = $item['enchantid'];
		$this->enchantvalue = $item['enchantvalue'];
		$this->toilet = $item['toilet'];
		$this->slotid = $item['slotid'];
		$this->ownerid = $item['ownerid'];
	}

	public function loadData(){
		
		$huj = array($this->attrtype1, $this->attrtype2, $this->attrtype3);
		$hujvalue = array($this->attrvalue1, $this->attrvalue2, $this->attrvalue3);

		if($this->itemtype < 11){

			if($this->itemtype == 2){//shield
				$this->attrblock = $this->dmgmin;
			}
			else if($this->itemtype > 2 && $this->itemtype < 8){
				$this->armor += $this->dmgmin;
			}

			for($i=1; $i<4;$i++){

				$attrtype = $huj[$i-1];
				$attrvalue = $hujvalue[$i-1];

				if($attrtype == 6)
				{
					$this->attrstr +=$attrvalue;
					$this->attrdex +=$attrvalue;
					$this->attrint +=$attrvalue;
					$this->attrwit +=$attrvalue;
					$this->attrluck +=$attrvalue;
					$i = 4;
				}
				else if($attrtype == 5){
					$this->attrluck +=$attrvalue;
				}
				else if($attrtype == 4){
					$this->attrwit +=$attrvalue;
				}
				else if($attrtype == 3){
					$this->attrint +=$attrvalue;
				}
				else if($attrtype == 2){
					$this->attrdex +=$attrvalue;
				}
				else if($attrtype == 1){
					$this->attrstr +=$attrvalue;
				}
			}
		}
	}

	public function update(){
		global $db;
		
		if($this->table == ""){
			return false;
		}
		
		$table = $this->table;

		try{
			$qry = $db->prepare("UPDATE `$table` SET 
			`itemid`=:itemid,
			`itemtype`=:itemtype,
			`itemclass`=:itemclass,
			`dmgmin`=:dmgmin,
			`dmgmax`=:dmgmax,
			`attrtype1`=:attrtype1,
			`attrtype2`=:attrtype2,
			`attrtype3`=:attrtype3,
			`attrvalue1`=:attrvalue1,
			`attrvalue2`=:attrvalue2,
			`attrvalue3`=:attrvalue3,
			`silver`=:silver,
			`mush`=:mush,
			`enchantid`=:enchantid,
			`enchantvalue`=:enchantvalue,
			`toilet`=:toilet,
			`slotid`=:slotid,
			`ownerid`=:ownerid WHERE `id` = :id");
			$qry->bindParam(":itemid", $this->itemid);
			$qry->bindParam(":itemtype", $this->itemtype);
			$qry->bindParam(":itemclass", $this->itemclass);
			$qry->bindParam(":dmgmin", $this->dmgmin);
			$qry->bindParam(":dmgmax", $this->dmgmax);
			$qry->bindParam(":attrtype1", $this->attrtype1);
			$qry->bindParam(":attrtype2", $this->attrtype2);
			$qry->bindParam(":attrtype3", $this->attrtype3);
			$qry->bindParam(":attrvalue1", $this->attrvalue1);
			$qry->bindParam(":attrvalue2", $this->attrvalue2);
			$qry->bindParam(":attrvalue3", $this->attrvalue3);
			$qry->bindParam(":silver", $this->silver);
			$qry->bindParam(":mush", $this->mush);
			$qry->bindParam(":enchantid", $this->enchantid);
			$qry->bindParam(":enchantvalue", $this->enchantvalue);
			$qry->bindParam(":toilet", $this->toilet);
			$qry->bindParam(":slotid", $this->slotid);
			$qry->bindParam(":ownerid", $this->ownerid);
			$qry->bindParam(":id", $this->id);

			if($qry->execute()){
				return true;
			}
		}
		catch(Exception $exception){
			Logger::error($exception);
			return false;
		}

		return true;
	}

	public function delete(){
		global $db;
		
		if($this->table == ""){
			return false;
		}

		$table = $this->table;

		$qry = $db->prepare("DELETE FROM $table WHERE `ownerid` = :ownerid AND `slotid` = :slotid AND `id` = :id");
		$qry->bindParam(":ownerid", $this->ownerid);
		$qry->bindParam(":slotid", $this->slotid);
		$qry->bindParam(":id", $this->id);
		if($qry->execute()){
			if($qry->rowCount()==1){
				return true;
			}
		}
		return false;
	}

	public function moveTo($table){
		$this->insertTo($table);
		return $this->delete();
	}

	public function copyTo($table){
		return $this->insertTo($table);
	}

	public function setEnchant(){
		global $ITEM_TYPE_WEAPON_ID, $ITEM_TYPE_SHIELD_ID, $ITEM_TYPE_ARMOR_ID, 
		$ITEM_TYPE_BOOTS_ID, $ITEM_TYPE_GLOVES_ID, $ITEM_TYPE_HELMET_ID, 
		$ITEM_TYPE_BELT_ID, $ITEM_TYPE_NECK_ID, $ITEM_TYPE_RING_ID, $ITEM_TYPE_AMULET_ID;
		
		switch($this->itemtype){
			case $ITEM_TYPE_GLOVES_ID:
				$this->enchantid = 855638016;
				$this->enchantvalue = 65536;
			break;

			case $ITEM_TYPE_AMULET_ID:
				$this->enchantid = 1694498816;
				$this->enchantvalue = 1310720;
			break;

			case $ITEM_TYPE_BELT_ID:
				$this->enchantid = 1191182336;
				$this->enchantvalue = 65536;
			break;

			case $ITEM_TYPE_RING_ID:
				$this->enchantid = 1526726656;
				$this->enchantvalue = 655360;
			break;

			case $ITEM_TYPE_BOOTS_ID:
				$this->enchantid = 687865856;
				$this->enchantvalue = 655360;
			break;

			case $ITEM_TYPE_HELMET_ID:
				$this->enchantid = 1023410176;
				$this->enchantvalue = 655360;
			break;

			case $ITEM_TYPE_NECK_ID:
				$this->enchantid = 1358954496;
				$this->enchantvalue = 655360;
			break;

			case $ITEM_TYPE_ARMOR_ID:
				$this->enchantid = 520093696;
				$this->enchantvalue = 1638400;
			break;

			case $ITEM_TYPE_WEAPON_ID:
				$this->enchantid = 184549376;
				$this->enchantvalue = 327680;
			break;

		}
	}

	public function isEnchanted(){
		return $this->enchantid!=0;
	}

	public function genItemKey($did){
		global $ITEM_TYPE_KEY_ID;
		
		$this->resetData();

		$this->silver = 100;
		$this->itemid = $did;
		$this->itemtype = $ITEM_TYPE_KEY_ID;
	}

	public function genItemForShop($shopid, $lvl, $classid, $eventid){
		global $SHOP_SHAKES_ID, $SHOP_FIDGET_ID, $EVENT_EPIC_ID, $EVENT_ALL_ID;

		$this->isepic = false;

		if($lvl >= 50){
			if($eventid == $EVENT_EPIC_ID || $eventid == $EVENT_ALL_ID){
				$this->isepic = rand(1, 100) < 25 ? true : false;
			}
			else{
				$this->isepic = rand(1, 100) < 3 ? true : false;
			}
		}

		if($shopid == $SHOP_SHAKES_ID){
			$this->genItemShakes($lvl, $classid, $eventid);
		}
		else if($shopid == $SHOP_FIDGET_ID){
			$this->genItemFidget($lvl, $classid, $eventid);
		}
	}

	private function genItemShakes($lvl, $classid, $eventid){
		global $CLASS_WARRIOR_ID, $CLASS_SCOUT_ID, $CLASS_MAGE_ID,
		$ITEM_TYPE_WEAPON_ID, $ITEM_TYPE_SHIELD_ID, $ITEM_TYPE_ARMOR_ID, $ITEM_TYPE_BOOTS_ID, $ITEM_TYPE_GLOVES_ID, $ITEM_TYPE_HELMET_ID, $ITEM_TYPE_BELT_ID, 
		$EVENT_EPIC_ID, $EVENT_ALL_ID;
		
		$this->itemtype = rand(1, 7);

		if(($classid != $CLASS_WARRIOR_ID) && ($this->itemtype == $ITEM_TYPE_SHIELD_ID)){
			while($this->itemtype == $ITEM_TYPE_SHIELD_ID){
				$this->itemtype = rand(1, 7);
			}
		}

		if($this->itemtype == $ITEM_TYPE_WEAPON_ID){

			if($classid == $CLASS_WARRIOR_ID){
				if($lvl < 3){
					$this->itemid = rand(1, 4);
				}
				else if($lvl < 10){
					$this->itemid = rand(5, 10);
				}
				else if($lvl < 20){
					$this->itemid = rand(5, 20);
				}
				else if($lvl < 30){
					$this->itemid = rand(10, 25);
				}
				elseif($lvl < 50){
					$this->itemid = rand(10, 30);
				}
				else{
					$this->itemid = rand(20, 30);
				}
			}
			else{
				if($lvl < 3){
					$this->itemid = rand(1, 1);
				}
				else if($lvl < 10){
					$this->itemid = rand(2, 5);
				}
				else if($lvl < 20){
					$this->itemid = rand(2, 8);
				}
				else if($lvl < 30){
					$this->itemid = rand(2, 10);
				}
				elseif($lvl < 50){
					$this->itemid = rand(5, 10);
				}
				else{
					$this->itemid = rand(6, 10);
				}
			}

			$this->genRandWeaponDmg($classid, $lvl);
		}
		else if($this->itemtype == $ITEM_TYPE_SHIELD_ID){

			if($lvl < 2){
				$this->itemid = rand(1, 1);
			}
			else if($lvl < 5){
				$this->itemid = rand(2, 4);
			}
			else if($lvl < 10){
				$this->itemid = rand(2, 5);
			}
			else if($lvl < 25){
				$this->itemid = rand(5, 8);
			}
			else{
				$this->itemid = rand(6, 10);
			}
			
			if($lvl < 5){
				$this->dmgmin = rand(5, 10);
			}
			else if($lvl < 25 ){
				$this->dmgmin = rand(5, 15);
			}
			else {
				$this->dmgmin = rand(15, 25);
			}
		}
		else{
			if($lvl < 3){
				$this->itemid = rand(1, 1);
			}
			else if($lvl < 10){
				$this->itemid = rand(2, 3);
			}
			else if($lvl < 20){
				$this->itemid = rand(2, 5);
			}
			else if($lvl < 30){
				$this->itemid = rand(3, 7);
			}
			elseif($lvl < 50){
				$this->itemid = rand(5, 9);
			}
			else{
				$this->itemid = rand(6, 10);
			}

			$basearmor = round(1 + floor(Utils::getArmorMultiplyByClassId($classid) * $lvl));
			$this->dmgmin = round(rand($basearmor * 0.2, $basearmor * 0.5));
		}

		if($this->isepic){
			if($this->itemtype == $ITEM_TYPE_WEAPON_ID || $this->itemtype == $ITEM_TYPE_SHIELD_ID){
				$this->itemid = rand(50, 60);
			}
			else{
				$this->itemid = rand(50, 58);
			}

			$this->genAttrEpic($lvl, $classid);
		}
		else{
			$this->genAttrNormal($lvl, $classid);
		}
		
		$this->itemclass = $classid;
	}

	private function genItemFidget($lvl, $classid, $eventid){
		global $ITEM_TYPE_NECK_ID, $ITEM_TYPE_RING_ID, $ITEM_TYPE_AMULET_ID, $ITEM_TYPE_POTION_ID,
		$EVENT_EPIC_ID, $EVENT_ALL_ID;

		$this->itemtype = array(8, 9, 10)[rand(0, 2)];

		if(rand(1, 100) <= 10){
			$this->itemtype = $ITEM_TYPE_POTION_ID;
		}

		switch($this->itemtype){
			case $ITEM_TYPE_NECK_ID: //8
				$this->itemid = rand(1, 21);
			break;
			case $ITEM_TYPE_RING_ID: //9
				$this->itemid = rand(1, 16);
			break;
			case $ITEM_TYPE_AMULET_ID: //10
				$this->itemid = rand(1, 37);
			break;
			case $ITEM_TYPE_POTION_ID: //12
				if($lvl < 15){
					$this->itemid = rand(1, 5);
				}
				else if($lvl < 30){
					$this->itemid = rand(1, 10);
				}
				else if($lvl < 50){
					$this->itemid = rand(1, 15);
				}
				else{
					if(rand(1, 5) == 5){
						$this->itemid = 16; // kurczak all
					}
					else{
						$this->itemid = rand(6, 15);
					}
				}
			break;
		}

		if($this->itemtype == $ITEM_TYPE_POTION_ID){
			$this->attrtype1 = 11; //typ czas trwania poty
			if($this->itemid < 6){
				$this->attrtype2 = $this->itemid;
				$this->attrvalue2 = 5;
				$this->silver = ($this->getRandSilverValue($lvl)*0.25);
			}
			else if($this->itemid < 11){
				$this->attrtype2 = $this->itemid - 5;
				$this->attrvalue2 = 15;
				$this->silver = ($this->getRandSilverValue($lvl)*0.50);
			}
			else if($this->itemid < 16){
				$this->attrtype2 = $this->itemid - 10;
				$this->attrvalue2 = 25;
				$this->silver = ($this->getRandSilverValue($lvl)*0.80);
			}
			else if($this->itemid == 16){
				//punkty zycia +25% na 7 dni
				$this->attrtype2 = 12;
				$this->attrvalue2 = 25;
				$this->silver = $this->getRandSilverValue($lvl);
			}

			if($this->itemid == 16){
				$this->attrvalue1 = 168; // czas 7 dni = 168h
				$this->mush = 15;
			}
			else{
				$this->attrvalue1 = 72; // czas 3 dni = 72h
			}
		}
		else{
			if($this->isepic){
				$this->itemid = rand(50, 60);
				$this->genAttrEpic($lvl, $classid);
			}
			else{
				$this->genAttrNormal($lvl, $classid);
			}
		}

		$this->itemclass = 1;
	}

	private function genAttrEpic($lvl, $classid){
		
		if(rand(1,5) <= 2){
			//wszystkie cechy
			$this->attrtype1 = 6; //all attributes
			$this->attrvalue1 = $this->getRandAttrValue($lvl);
			$this->mush = 15;
		}
		else{
			//3 cechy
			$this->attrtype1 = Utils::getAttrIdByClassid($classid);
			$this->attrtype2 = 4;
			$this->attrtype3 = 5;
			
			$value = $this->getRandAttrValue($lvl);
			$this->attrvalue1 = $value;
			$this->attrvalue2 = $value;
			$this->attrvalue3 = $value;

			$this->mush = 5;
		}
		$this->silver = $this->getRandSilverValue($lvl);
	}

	private function genAttrNormal($lvl, $classid){

		if(rand(1,5) == 5){ //dwie cechy
			if(rand(1, 2) == 1){
				$this->attrtype1 = Utils::getAttrIdByClassid($classid);
			}
			else{
				$this->attrtype1 = rand(1, 3);
			}
			$this->attrtype2 = rand(4, 5);
			$this->attrvalue1 = $this->getRandAttrValue($lvl);
			$this->attrvalue2 = $this->getRandAttrValue($lvl);
			$this->mush = 1;
		}
		else{ //jedna cecha
			$this->attrtype1 = rand(1, 5);

			if($this->attrtype1 < 4){
				if(rand(1, 2) == 1){
					$this->attrtype1 = Utils::getAttrIdByClassid($classid);
				}
				else{
					$this->attrtype1 = rand(1, 3);
				}
			}
			$this->attrvalue1 = $this->getRandAttrValue($lvl);
		}
		$this->silver = $this->getRandSilverValue($lvl);
	}

	private function getRandSilverValue($lvl){
		return rand($lvl*2, $lvl * 5) * 100;
	}

	private function getRandAttrValue($lvl){
		return rand($lvl, $lvl * 2) + rand(round(abs($lvl/100)), round(abs($lvl/10)));
	}

	public function getArray(){
		return array('itemtype' => $this->itemtype, 'itemid' =>  $this->itemid, 'itemclass' =>  $this->itemclass, 'dmgmin' =>  $this->dmgmin, 'dmgmax' =>  $this->dmgmax, 'attrtype1' =>  $this->attrtype1, 'attrtype2' =>  $this->attrtype2, 'attrtype3' =>  $this->attrtype3, 'attrvalue1' =>  $this->attrvalue1, 'attrvalue2' =>  $this->attrvalue2, 'attrvalue3' =>  $this->attrvalue3, 'silver' =>  $this->silver, 'mush' =>  $this->mush);
	}

	public function getItemIdView(){
		if($this->itemtype > 7){
			return $this->itemid + $this->enchantvalue;
		}
		return $this->itemid + (($this->itemclass -1) * 1000) + $this->enchantvalue;
	}

	public function getItemTypeView(){
		return $this->itemtype + $this->enchantid;
	}

	public function genBasicWeaponByClass($classid){
		$this->itemtype = 1;
		$this->itemclass = $classid;
		$this->itemid = 1;

		$lvl = 1;
		$basedmg = round(rand(($lvl/2 +1) * Utils::getDmgMultiplyByClassId($classid) /2, ($lvl +1) * Utils::getDmgMultiplyByClassId($classid)/2));
		$this->dmgmin = round($basedmg * Utils::getDmgMultiplyByClassId($classid) /2);
		$this->dmgmax = round($basedmg * Utils::getDmgMultiplyByClassId($classid));

		$this->silver = 1;
		$this->slotid = 9;
	}

	public function genRandWeaponDmg($classid, $lvl){
		$basedmg = $lvl * Utils::getDmgMultiplyByClassId($classid);
		$admg= array(0.1, 0.2, 0.3, 0.4, 0.5)[rand(0, 4)];
		$this->dmgmin = round($lvl + $basedmg * ($admg));
		$this->dmgmax = round($lvl + $basedmg * ($admg +1));
	}

	public function pushToArray(&$ret, $index){
		$ret[$GLOBALS[$index . $this->slotid]] = $this->getItemTypeView();
		$ret[$GLOBALS[$index . $this->slotid] + 1] = $this->getItemIdView();
		$ret[$GLOBALS[$index . $this->slotid] +2] = $this->dmgmin;
		$ret[$GLOBALS[$index . $this->slotid] +3] = $this->dmgmax;
		$ret[$GLOBALS[$index . $this->slotid] +4] = $this->attrtype1;
		$ret[$GLOBALS[$index . $this->slotid] +5] = $this->attrtype2;
		$ret[$GLOBALS[$index . $this->slotid] +6] = $this->attrtype3;
		$ret[$GLOBALS[$index . $this->slotid] +7] = $this->attrvalue1;
		$ret[$GLOBALS[$index . $this->slotid] +8] = $this->attrvalue2;
		$ret[$GLOBALS[$index . $this->slotid] +9] = $this->attrvalue3;
		$ret[$GLOBALS[$index . $this->slotid] +10] = $this->silver;
		$ret[$GLOBALS[$index . $this->slotid] +11] = $this->mush;
	}

	private function resetData(){
		$this->id = 0;
		$this->itemtype = 0;
		$this->itemclass = 0;
		$this->itemid = 0;
		$this->dmgmin = 0;
		$this->dmgmax = 0;
		$this->attrtype1 = 0;
		$this->attrtype2 = 0;
		$this->attrtype3 = 0;
		$this->attrvalue1 = 0;
		$this->attrvalue2 = 0;
		$this->attrvalue3 = 0;
		$this->silver = 0;
		$this->mush = 0;
		$this->enchantid = 0;
		$this->enchantvalue = 0;
		$this->toilet = 0;
		$this->slotid = 0;
		$this->ownerid = 0;
		$this->isepic = false;
	}

	public function isEmpty(){
		if($this->id == 0){
			return true;
		}
		return false;
	}
}