<?php

class Player extends Mob{
	
	public int $id = 0;
	public $enabled = 0;

	public int $attrstrbonus = 0;
	public int $attrdexbonus = 0;
	public int $attrintbonus = 0;
	public int $attrwitbonus = 0;
	public int $attrluckbonus = 0;

	public int $bonuslootquest = 0; //amulet +10% szansy na znalezienie przedmiotu
	public int $bonusreaction = 0; //rękawice Rozpoczyna zawsze osoba z wyższym współczynnikiem reakcji.Premia do reakcji +1
	public int $bonusmushquest = 0; //zbroja +50% szansy na znalezienie grzyba
	public int $bonuscrit = 0; //bron Obrażenia ciosów krytycznych +5%
	public int $bonusgoldpvp = 0; //talizman +20% złota z walk na arenach
	public int $bonusexpquest = 0; //helm +10% doświadczenia z zadań
	public int $bonustimequest = 0; //buty -30 sekund czasu zadania
	public int $bonusgoldquest = 0; //pierscien +10% złota z zadań
	public int $bonusbeer = 0; //pas W karczmie możemy wypić łącznie 11 piw dziennie, w czym jedno za darmo

	public $itemsEq = array();
	public $itemsBp = array();
	public $itemsShakes = array();
	public $itemsFidget = array();
	public $itemsQuests = array();
	
	public $data = array();

	public function loadData(){
		global $db, $SERVER_TIME;

		try{
			$qry = $db->prepare("SELECT * FROM `players` WHERE `id` = :id LIMIT 1");
			$qry->execute([':id' => $this->id]);
			$this->data = $qry->fetch(PDO::FETCH_ASSOC);
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		$this->name = $this->data['name'];
		$this->lvl = $this->data['lvl'];
		$this->raceid = $this->data['raceid'];
		$this->sexid = $this->data['sexid'];
		$this->classid = $this->data['classid'];
		$this->facearray = explode("/", $this->data['face']);

		$this->attrstr += $this->data['attrstr'];
		$this->attrdex += $this->data['attrdex'];
		$this->attrint += $this->data['attrint'];
		$this->attrwit += $this->data['attrwit'];
		$this->attrluck += $this->data['attrluck'];

		$this->guildid = $this->data['guildid'];
	}

	public function checkMount(){
		global $SERVER_TIME;
		if($this->data['mounttime'] < $SERVER_TIME){
			$this->updateMount(0);
		}
	}
	
	public function loadItemsEq(){
		global $db, $SERVER_TIME;

		$this->attrstrbonus = 0;
		$this->attrdexbonus = 0;
		$this->attrintbonus = 0;
		$this->attrwitbonus = 0;
		$this->attrluckbonus = 0;
		$this->armor = 0;

		$itemsdata = null;

		try{
			$qry = $db->query("SELECT * FROM `items_players` WHERE `ownerid` = ".$this->id." AND `slotid` <= 10");
			$itemsdata = $qry->fetchAll();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		foreach($itemsdata as $itemdata){
			$itemeq = new Item;
			$itemeq->loadFromArray($itemdata);
			$itemeq->loadData();

			$this->attrstrbonus += $itemeq->attrstr;
			$this->attrdexbonus += $itemeq->attrdex;
			$this->attrintbonus += $itemeq->attrint;
			$this->attrwitbonus += $itemeq->attrwit;
			$this->attrluckbonus += $itemeq->attrluck;
			$this->armor += $itemeq->armor;

			if($itemeq->itemtype == 1){ //weapon
				$this->weapon = $itemeq->getArray();
			}
			else if($itemeq->itemtype == 2){//shield
				$this->shield = $itemeq->getArray();
			}
			
			array_push($this->itemsEq, $itemeq);
		}

		for($i=1;$i<4;$i++){ //potions

			if($this->data['potiontime'.$i] > $SERVER_TIME){
				$pid = $this->data['potionid'.$i];
				$value = 0.0;

				if($this->data['potionid'.$i] < 6){
					$value = 0.05;
				}
				else if($this->data['potionid'.$i] < 11){
					$value = 0.15;
				}
				else if($this->data['potionid'.$i] < 17){
					$value = 0.25;
				}

				switch (($pid - 1) % 5) {
					case 0:
						// sila
						$this->attrstrbonus += 1 + ($this->attrstr * $value);
						break;
					case 1:
						// dex
						$this->attrdexbonus += 1 + ($this->attrdex * $value);
						break;
					case 2:
						// int
						$this->attrintbonus += 1 + ($this->attrint * $value);
						break;
					case 3:
						// wit
						$this->attrwitbonus += 1 + ($this->attrwit * $value);
						break;
					case 4:
						// luck
						$this->attrluckbonus += 1 + ($this->attrluck * $value);
						break;
				}

				if($pid == 16){
					$this->attrwitbonus += ($this->attrwit * $value);
				}
			}

			$this->attrstr = $this->data['attrstr'] + $this->attrstrbonus;
			$this->attrint = $this->data['attrint'] + $this->attrintbonus;
			$this->attrdex = $this->data['attrdex'] + $this->attrdexbonus;
			$this->attrwit = $this->data['attrwit'] + $this->attrwitbonus;
			$this->attrluck = $this->data['attrluck'] + $this->attrluckbonus;
		}

		$this->hp = $this->attrwit * Utils::getHpMultiplyByClassId($this->data['classid']) * ($this->lvl + 1);

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

		$this->dmgmin = round($this->weapon['dmgmin'] * (1 + ($baseattr /10)));
		$this->dmgmax = round($this->weapon['dmgmax'] * (1 + ($baseattr /10)));

		return true;
	}

	public function loadItemsShops(){
		global $db;

		$itemsdata = array();
		$this->itemsShakes = array();

		try{
			$qry = $db->query("SELECT * FROM `items_shakes` WHERE `ownerid` = ".$this->id." AND `slotid` <= 7");
			$itemsdata = $qry->fetchAll();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		foreach($itemsdata as $itemdata){
			$itemshakes = new Item;
			$itemshakes->loadFromArray($itemdata);
			array_push($this->itemsShakes, $itemshakes);
		}

		$itemsdata = array();
		$this->itemsFidget = array();

		try{
			$qry = $db->query("SELECT * FROM `items_fidget` WHERE `ownerid` = ".$this->id." AND `slotid` <= 7");
			$itemsdata = $qry->fetchAll();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		foreach($itemsdata as $itemdata){
			$itemfidget = new Item;
			$itemfidget->loadFromArray($itemdata);
			array_push($this->itemsFidget, $itemfidget);
		}
	}

	public function loadItemsQuests(){
		global $db;
		try{
			$qry = $db->query("SELECT * FROM `items_quests` WHERE `ownerid` = ".$this->id." ORDER BY `slotid` ASC");
			$this->itemsQuests = $qry->fetchAll();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return true;
	}

	public function loadItemsBp(){
		global $db;

		$itemsdata = array();
		$this->itemsBp = array();

		try{
			$qry = $db->query("SELECT * FROM `items_players` WHERE `ownerid` = ".$this->id." AND `slotid` > 10");
			$itemsdata = $qry->fetchAll();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}

		foreach($itemsdata as $itemdata){
			$itembp = new Item;
			$itembp->loadFromArray($itemdata);
			array_push($this->itemsBp, $itembp);
		}

	}
	
	public function loginByPass($name, $pass){
		global $db, $CLIENT_IP, $SERVER_TIME;
		try{
			$qry = $db->prepare("SELECT `id`, `pass`, `enabled` FROM `players` WHERE `name` = :name LIMIT 1");
        	$qry->execute([':name' => $name]);
			
			$player = $qry->fetch(PDO::FETCH_ASSOC);

			if($player && $player['pass'] == $pass){
				$this->id = $player['id'];
				$this->enabled = $player['enabled'];
				$ssid = Utils::generateSession();

				$sessiontime = $SERVER_TIME + 3600; // godzina
				$qry = $db->prepare("UPDATE `players` SET `ssid` = :ssid, `sessiontime` = :sessiontime WHERE `id` = :id");
				$qry->execute([':ssid' => $ssid, ':sessiontime' => $sessiontime, ':id' => $this->id]);
				if($qry->rowCount() == 1){
					return true;
				}
			}
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return false;
	}
	
	public function loginBySession($ssid){
		global $db, $SERVER_TIME;
		try{
			$qry = $db->prepare("SELECT `id`, `sessiontime`, `enabled` FROM `players` WHERE `ssid` = :ssid LIMIT 1");
			$qry->bindParam(':ssid', $ssid);
			$qry->execute();
			$player = $qry->fetch(PDO::FETCH_ASSOC);
			if($player && $player['sessiontime'] > $SERVER_TIME){
				$this->id = $player['id'];
				$this->enabled = $player['enabled'];
				return true;
			}
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return false;
	}

	public function loginById($id){
		global $db;
		try{
			$qry = $db->prepare("SELECT `id` FROM `players` WHERE `id` = :id LIMIT 1");
			$qry->bindParam(':id', $id);
			$qry->execute();
			if($qry->rowCount() == 1){
				$this->id = $id;
				return true;
			}
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return false;
	}

	public function loginByName($name){
		global $db;
		try{
			$qry = $db->prepare("SELECT `id` FROM `players` WHERE `name` = :name LIMIT 1");
			$qry->bindParam(':name', $name);
			$qry->execute();
			if($qry->rowCount() == 1){
				$this->id = $qry->fetch()['id'];
				return true;
			}
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return false;
	}
	
	public function logout(){
		global $db;
		
		if ($this->id != 0){
			try{
				$qry = $db->prepare("UPDATE `players` SET `sessiontime` = 0, `ssid` = '' WHERE `id` = :id LIMIT 1");
				$qry->bindParam(':id', $this->id, PDO::PARAM_INT);
				$qry->execute();
				if ($qry->rowCount() === 1){
					return true;
				}
			}
			catch(PDOException $exception){
				Logger::error($exception);
				return false;
			}
		}
		return false;
	}
	
	public function changeDesc($text){
		global $db;
		
		$qry = $db->prepare("UPDATE `players` SET `pdesc` = :pdesc WHERE `id` = :id");
		$qry->bindParam(":pdesc", $text, PDO::PARAM_STR);
		$qry->bindParam(":id", $this->id, PDO::PARAM_INT);
		$qry->execute();
		
		$this->data['pdesc'] = $text;

		return ($qry->rowCount() == 1);
	}

	public function setSilver($value){
		global $db;

		try{
			$qry = $db->prepare("UPDATE `players` SET `silver` = :silver WHERE `id` = ".$this->id."");
			$qry->bindParam(":silver", $value);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		$this->data['silver'] = $value;
		return true;
	}

	public function addSilver($value){
		$this->setSilver($this->data['silver'] + $value);
	}

	public function setMush($value){
		global $db;

		try{
			$qry = $db->prepare("UPDATE `players` SET `mush` = :mush WHERE `id` = ".$this->id."");
			$qry->bindParam(":mush", $value);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		$this->data['mush'] = $value;
		return true;
	}

	public function addMush($value){
		$this->setMush($this->data['mush'] + $value);
	}

	public function addExp($value)
	{
		global $db, $ORDER_LVL_ID;

		// Dodajemy doświadczenie
		$this->data['pexp'] += $value;
		$lvlup = false;
		$lvlold = $this->data['lvl'];

		// Sprawdzamy, czy gracz zdobył wystarczająco doświadczenia, by awansować na wyższy poziom
		while (Utils::getExpForNextLvl($this->data['lvl']) <= $this->data['pexp']) {
			// Zwiększamy poziom
			$this->data['lvl']++;
			$lvlup = true;
		}

		if ($lvlup) {
			// Obliczamy nadmiarowe doświadczenie, które zostaje po awansie
			$expRequiredForCurrentLvl = Utils::getExpForNextLvl($lvlold);  // Doświadczenie wymagane dla poprzedniego poziomu
			$this->data['pexp'] -= $expRequiredForCurrentLvl;  // Nadmiar doświadczenia po awansie

			// Sprawdzamy, czy poziom osiągnął wartość odpowiadającą 2.5 razy stary poziom
			if ($this->data['lvl'] == round(2.5 * $lvlold)) {
				// Zwiększamy poziom zamówienia, jeżeli wymagane
				$this->setOrder($ORDER_LVL_ID, $this->data['o'.$ORDER_LVL_ID] + 1);
			}
		}

		// Przygotowanie zapytania SQL
		try {
			// Poprawiona składnia, aby uniknąć wstawiania ID bezpośrednio do zapytania
			$qry = $db->prepare("UPDATE `players` SET `pexp` = :pexp, `lvl` = :plvl WHERE `id` = :id");
			$qry->bindParam(":pexp", $this->data['pexp']);
			$qry->bindParam(":plvl", $this->data['lvl']);
			$qry->bindParam(":id", $this->id); // Zabezpieczenie przed SQL Injection
			$qry->execute();
		} catch (PDOException $exception) {
			// Lepsza obsługa błędów z dodatkowym komunikatem
			Logger::error("Error in addExp method: " . $exception->getMessage());
			return false;
		}

		return true;
	}

	public function setThirst($value){
		global $db;
		if($value <0){
			$value = 0;
		}
		$this->data['thirst'] = $value;
		try{
			$qry = $db->prepare("UPDATE `players` SET `thirst` = :pth WHERE `id` = ".$this->id."");
			$qry->bindParam(":pth", $this->data['thirst']);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return true;
	}

	public function setBeers($value){
		global $db;

		$this->data['beers'] = $value;

		try{
			$qry = $db->prepare("UPDATE `players` SET `beers` = :pbeers WHERE `id` = ".$this->id."");
			$qry->bindParam(":pbeers", $this->data['beers']);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		return true;
	}

	public function setDungeon($did, $dlvl){
		global $db;

		try{
			$qry = $db->prepare("UPDATE `players` SET `d$did` = :dlvl WHERE `id` = ".$this->id."");
			$qry->bindParam(":dlvl", $dlvl);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		$this->data['d'.$did] = $dlvl;
		return true;
	}

	public function setPotion($pid){
		global $db, $SERVER_TIME;

		$mpid = ($pid - 1) % 5; // 0 = sila // 1 dex itp
		$value = 0;

		$qry = $db->prepare("SELECT `potionid1`, `potionid2`, `potionid3`, `potiontime1`, `potiontime2`, `potiontime3` FROM `players` WHERE `id` = $this->id");
		if(!$qry->execute()){
			return false;
		}

		$data = $qry->fetch();

		if(!$data){
			return false;
		}

		$potionslotid = 0;

		if($pid != 16){

			for($i=1;$i<4;$i++){
				if($data['potionid'.$i] != 0){
					$spid = ($data['potionid'.$i] - 1) % 5;
					if($spid == $mpid){
						if($data['potionid'.$i] <= $pid){
							//zaloz tu potke
							$potionslotid = $i;

							$i=666;
						}
					}
				}
			}
			
		}
		else{
			for($i=1;$i<4;$i++){
				if($data['potionid'.$i] == 16){
					$potionslotid = $i;
					$i=666;
				}
			}
		}

		if($potionslotid == 0){
			for($i=1;$i<4;$i++){
				if($data['potionid'.$i] == 0){
					$potionslotid = $i;
					$i=666;
				}
			}
		}

		if($potionslotid != 0){
			
			$time = $data['potiontime'.$potionslotid];
			if($time > $SERVER_TIME){
				if($pid == 16){
					$time += + 604800; //7 dni
				}
				else{
					$time += + 259200; //3dni
				}
			}
			else{
				if($pid == 16){
					$time = $SERVER_TIME + 604800; //7 dni
				}
				else{
					$time = $SERVER_TIME + 259200; //3dni
				}
			}
			$qry = $db->query("UPDATE `players` SET `potionid$potionslotid`=$pid, `potiontime$potionslotid` = $time WHERE `id` = $this->id");

			$this->data['potionid'.$potionslotid] = $pid;
			$this->data['potiontime'.$potionslotid] = $time;

			return true;
		}

		return false;
	}

	public function setOrder($oid, $olvl){
		global $db;
		try{
			$qry = $db->prepare("UPDATE `players` SET `o$oid` = $olvl  WHERE `id` = ".$this->id."");
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		$this->data['o'.$oid] = $olvl;
	}

	public function removePotion($pid){
		global $db;

		try{
			$qry = $db->prepare("UPDATE `players` SET `potionid$pid` = 0, `potiontime$pid` = 0  WHERE `id` = ".$this->id."");
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		$this->data['potionid'.$pid] = 0;
		$this->data['potiontime'.$pid] = 0;
		return true;
	}

	public function rerollItems($shopid){
		global $db, $SERVER_TIME_TOMORROW, $SHOP_SHAKES_ID, $SHOP_FIDGET_ID;

		$eventid = Server::getEventId();

		if($shopid == $SHOP_SHAKES_ID){
			$table = 'items_shakes';
		}
		else if($shopid == $SHOP_FIDGET_ID){
			$table = 'items_fidget';
		}
		else{
			return false;
		}

		try{
			$qry = $db->query("DELETE FROM `$table` WHERE `ownerid` = $this->id");
			$qry = $db->query("UPDATE `players` SET `shoprerolltime` = $SERVER_TIME_TOMORROW WHERE `id` = $this->id");
		}
		catch(PDOException $exception){
			Logger::error($exception);
			return false;
		}
		
		for($i = 0;$i<6;$i++){
			$item = new Item();
			$item->genItemForShop($shopid, $this->data['lvl'], $this->data['classid'], $eventid);
			$item->ownerid = $this->id;
			$item->slotid = $i+1;
			if(!$item->insertTo($table)){
				return false;
			}
		}

		return true;
	}

	public function rerollQuests(){
		global $db, $SERVER_TIME_TOMORROW, $EVENT_EXP, $EVENT_EPIC, $EVENT_GOLD, $EVENT_ALL;

		$questlvl = "";
		$questtype = "";
		$questenemy = "";
		$questlocation = "";
		$questtime = "";
		$questexp = "";
		$questsilver = "";

		//$enemynormal = rand(1, 138);
		//$enemyspecial = array(139, 145, 148, 152, 155, 157)[rand(0, 5)];

		$db->query("DELETE FROM `items_quests` WHERE `ownerid` = " . $this->id . "");

		$eventid = Server::getEventId();

		for($i=1;$i<4;$i++){

			$lvl = rand(1, 55);
			$type = array(1, 2, 3, 6)[rand(0, 3)];
			$enemy = rand(1, 138);
			$location = rand(1, 20);

			$time = Utils::getRandQuestTimeByLvl($this->lvl);
			
			if($this->data['thirst'] <= 180 && $this->data['thirst'] > 60){ //3min
				$time = rand(1, 3);
			}

			$exp = Utils::getRandQuestExpByLvl($this->lvl);
			$exp = $exp * (1 + $time);
			$exp += Server::getEventBonusExp($exp);

			$silver = Utils::getRandQuestSilverByLvl($this->lvl);
			$silver = $silver * (1 + $time);
			$silver += Server::getEventBonusSilver($silver);

			$questlvl .= $lvl .";";
			$questtype .= $type .";";
			$questenemy .= $enemy .";";
			$questlocation .= $location .";";
			$questtime .= ($time*60) .";";
			$questexp .= $exp .";";
			$questsilver .= $silver .";";

			if(rand(1, 100) <= (15 + $this->bonuslootquest)){

				$item = new Item;
				$item->genItemForShop(rand(1, 2), $this->lvl, $this->classid, $eventid);

				for($e=1;$e<10;$e++){
					if($this->lvl >= ($e*10) && $this->data['d'.$e] == 0){
						$item->genItemKey($e);
						$e=666;
					}
				}
				$item->mush = 0;
				$item->ownerid = $this->id;
				$item->slotid = $i;
				$item->insertTo('items_quests');
			}
		}

		//quest type 1 = TXT_QUEST_SCOUT_TITLE
		// 2 = TXT_QUEST_COLLECT_TITLE
		// 3 = TXT_QUEST_FETCH_TITLE
		// 4 = TXT_QUEST_KILL_TITLE
		// 5 = TXT_QUEST_TRANSPORT_TITLE
		// 6 = TXT_QUEST_ESCORT_TITLE

		$reroll = $SERVER_TIME_TOMORROW;

		$qry = $db->prepare("UPDATE `players` SET `questrerolltime` = :qreroll, `questlvl` = :qlvl, `questtype` = :qtyp, `questenemy` = :qenemy, `questlocation` = :qloc, `questtime` = :qtime, `questexp` = :qexp, `questsilver` = :qsilver WHERE `players`.`id` = :id");
		$qry->bindParam(":qreroll", $reroll);
		$qry->bindParam(":qlvl", $questlvl);
		$qry->bindParam(":qtyp", $questtype);
		$qry->bindParam(":qenemy", $questenemy);
		$qry->bindParam(":qloc", $questlocation);
		$qry->bindParam(":qtime", $questtime);
		$qry->bindParam(":qexp", $questexp);
		$qry->bindParam(":qsilver", $questsilver);
		$qry->bindParam(":id", $this->id);

		if(!$qry->execute()){
			return false;
		}

		$this->data['questrerolltime'] = $reroll;
		$this->data['questlvl'] = $questlvl;
		$this->data['questtype'] = $questtype;

		$this->data['questenemy'] = $questenemy;
		$this->data['questlocation'] = $questlocation;
		$this->data['questtime'] = $questtime;

		$this->data['questexp'] = $questexp;
		$this->data['questsilver'] = $questsilver;

		return true;
	}

	public function updateMount($mountid){
		global $db, $SERVER_TIME;

		$czaswynajmu = 1209600;

		if($this->data['mountid'] == $mountid && $this->data['mounttime'] > $SERVER_TIME){
			$this->data['mounttime'] += $czaswynajmu;
		}
		else if($mountid != 0){
			$this->data['mounttime'] = $SERVER_TIME + $czaswynajmu;
		}
		else{
			$this->data['mounttime'] = 0;
		}

		$this->data['mountid'] = $mountid;

		$qry = $db->prepare("UPDATE `players` SET `mountid` = :mid, `mounttime` = :mtime WHERE `players`.`id` = :pid");
		$qry->bindParam(":mid", $this->data['mountid']);
		$qry->bindParam(":mtime", $this->data['mounttime']);
		$qry->bindParam(":pid", $this->id);
		if(!$qry->execute()){
			return false;
		}
		return true;
	}

	public function setStatus($statusid, $statusextra, $statusend){
		global $db;

		$qry = $db->prepare("UPDATE `players` SET `statusid` = :stid, `statusextra` = :stex, `statusend` = :sten WHERE `players`.`id` = :pid");
		$qry->bindParam(":stid", $statusid);
		$qry->bindParam(":stex", $statusextra);
		$qry->bindParam(":sten", $statusend);
		$qry->bindParam(":pid", $this->id);
		if(!$qry->execute()){
			return false;
		}

		$this->data['statusid'] = $statusid;
		$this->data['statusextra'] = $statusextra;
		$this->data['statusend'] = $statusend;
		return true;
	}

	public function questTimeWithMount($qtime){
		global $SERVER_TIME;
		if($this->data['mounttime'] > $SERVER_TIME && $qtime >= 60){
			$mountid = (($this->data['towerlvl'] - 1) * 65536) + $this->data['mountid'];
			if($mountid > 0 && $mountid < 5){
				$qtime -= $qtime * array(0.10, 0.20, 0.30, 0.50)[$mountid-1];
			}
		}
		return $qtime;
	}

	public function getWorkRate(){
		$base = $this->lvl * 10;
		return $base + Server::getEventBonusSilver($base);
	}

	public function haveFreeSlotBp(){
		if(count($this->itemsBp) >= 5){
			return false;
		}
		return true;
	}

	public function getCountDungEnd(){
		$end = 0;

		for($i=1;$i<14;$i++){
			if($this->data['d'.$i] == 12){
				$end++;
			}
		}
		return $end;
	}

	public function getFreeSlotBp(){
		global $db;
		
		for($i=11;$i<16;$i++){
			$qry = $db->query("SELECT `id` FROM `items_players` WHERE `ownerid` = ".$this->id." AND `slotid` = ".$i." LIMIT 1");
			if($qry->rowCount() ==0){
				return $i;
			}
		}
		return 0;
	}

	public function getRespFinishquest(){
		global $db, $SERVER_TIME, $SERVER_TIME_TOMORROW;

		$reward_mush = 0;
		$reward_exp = 0;
		$reward_silver = 0;

		$monster = new Monster;
		$monster->loadForQuest($this->data);

		$ret = Server::createFightResp($this, $monster);

		$eventid = Server::getEventId();
		$questid = $this->data['statusextra'];

		$win = $this->hp>0?true:false;

		if($win){
			$qry = $db->query("UPDATE `players` SET `questscount` = `questscount` +1 WHERE `id` = ".$this->id);
			$reward_exp = explode(";", $this->data['questexp'])[$questid-1];
			$reward_silver = explode(";", $this->data['questsilver'])[$questid-1];

			$this->addSilver($reward_silver);
			$this->addExp($reward_exp);

			foreach($this->itemsQuests as $item){
				if($item['slotid'] == $questid){
					$item = new Item;
					
					if($item->loadFrom('items_quests', $this->id, $questid)){
						
						if($this->haveFreeSlotBp()){
							$freeslotid = $this->getFreeSlotBp();
							if($freeslotid != 0){
								$item->slotid = $freeslotid;
								$item->insertTo('items_players');
							}
						}
					}
				}
			}

			if($this->data['firstquesttime'] < $SERVER_TIME){
				$reward_mush = 1;
				$qry = $db->query("UPDATE `players` SET `firstquesttime` = ". ($SERVER_TIME_TOMORROW) . " WHERE `id` = ".$this->id."");
			}
			else{
				$mushchance = 8;

				switch($this->data['mountid']){
					case 2:
						$mushchance = 7;
					break;
					case 3:
						$mushchance = 6;
					break;
					case 4:
						$mushchance = 5;
					break;
				}

				if($eventid == 4){//mush
					$mushchance *= 2;
				}

				if(rand(1, 100) <= $mushchance){
					$reward_mush = 1;
				}
			}

			if($reward_mush >= 1){
				$this->addMush(1);
			}
		}

		$seconds = explode(";", $this->data['questtime'])[$this->data['statusextra']-1];
		$this->setThirst($this->data['thirst'] - $seconds);
		$this->setStatus(0, 0, 0);
		$this->rerollQuests();

		$ret[] .= $this->data['mush'] . ';1;' . $reward_mush . ';' . $reward_exp . ';'. $reward_silver .';-1';
		
		return $ret;
	}

	public function getRespFinishwork(){
		global $RESP_WORK_END;
		$reward = $this->data['workendsilver'];
		$this->setStatus(0,0,0);
		$this->addSilver($reward);
		$ret = $this->getResp();
		$ret[] = ";" . $reward;
		$ret[0] = $RESP_WORK_END . $ret[0];
		return $ret;
	}

	public function getRespDung(){
		global $ACT_DUNGEON_ENTER;

		$jsonData = file_get_contents('engine/data/monsters_dungeons.json');
        $monsters_dung = json_decode($jsonData, true);
		
		$ret = $this->getResp();
		$ret[0] = $ACT_DUNGEON_ENTER . $ret[0];
		$faceid = ";";

		for($i=0;$i<13;$i++){
			$dlvl = $this->data['d'.$i +1];

			if($dlvl == 1){
				$this->setDungeon(($i+1), 2);
				$dlvl = 2;
			}

			if($dlvl == 0){
				$faceid .= "0/";
			}
			else if($dlvl < 13){ 
				$faceid .= $monsters_dung[($i * 10) + ($dlvl -2)]['faceid'] . "/";
			}

		}
		$ret[] .= $faceid;
		return $ret;
	}
	
	public function getResp(){
		global $db, $SERVER_TIME;
		
		$ret = array_fill(0, 513, '0');
		
		$ret[$GLOBALS['INDEX_PLAYER_PAYMENT_ID']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_ID']] = $this->id;
		$ret[$GLOBALS['INDEX_PLAYER_LAST_ONLINE']] = $this->data['lastonline'];
		$ret[$GLOBALS['INDEX_PLAYER_REGISTRATION_DATE']] = $this->data['regdate'];
		
		//$qry = $db->query("SELECT `id` FROM `messages` WHERE `reciverid` = ".$this->id."");
		$ret[$GLOBALS['INDEX_PLAYER_MSG_COUNT']] = 0;//$qry->rowCount();
		
		$ret[$GLOBALS['INDEX_PLAYER_LEVEL']] = $this->data['lvl'];
		$ret[$GLOBALS['INDEX_PLAYER_EXP']] = $this->data['pexp'];
		$ret[$GLOBALS['INDEX_PLAYER_EXP_NEXT']] = Utils::getExpForNextLvl($this->data['lvl']);
		
		$ret[$GLOBALS['INDEX_PLAYER_HONOR']] = $this->data['honor'];
		$ret[$GLOBALS['INDEX_PLAYER_RANK']] = $this->data['rank'];
		$ret[$GLOBALS['INDEX_PLAYER_CLASS_RANK']] = $this->data['rankclass'];
		
		$ret[$GLOBALS['INDEX_PLAYER_SILVER']] = $this->data['silver'];
		$ret[$GLOBALS['INDEX_PLAYER_MUSH']] = $this->data['mush'];
		$ret[$GLOBALS['INDEX_PLAYER_MUSH_GAINED']] = $this->data['mushbuy'];
		$ret[$GLOBALS['INDEX_PLAYER_MUSH_SPEND']] = 999;
		
		//----------------- PLAYER LOOKS ---------------/

		$facearray = explode("/", $this->data['face']);
		for($i=0;$i<9;$i++){
			$ret[$GLOBALS['INDEX_PLAYER_FACE'] + $i] = $facearray[$i];
		}

		$ret[$GLOBALS['INDEX_PLAYER_RACE']] = $this->data['raceid'];
		$ret[$GLOBALS['INDEX_PLAYER_SEX']] = $this->data['sexid'];
		$ret[$GLOBALS['INDEX_PLAYER_CLASS']] = $this->data['classid'];
		//----------------- PLAYER LOOKS ---------------/

		$ret[$GLOBALS['INDEX_PLAYER_ATTR_STR']] = $this->data['attrstr'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_DEX']] = $this->data['attrdex'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_INT']] = $this->data['attrint'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_WIT']] = $this->data['attrwit'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_LUCK']] = $this->data['attrluck'];
		
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_STR_ITEMS']] = $this->attrstrbonus;
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_DEX_ITEMS']] = $this->attrdexbonus;
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_INT_ITEMS']] = $this->attrintbonus;
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_WIT_ITEMS']] = $this->attrwitbonus;
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_LUCK_ITEMS']] = $this->attrluckbonus;
		
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_STR_BUY']] = $this->data['attrstrbuy'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_DEX_BUY']] = $this->data['attrdexbuy'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_INT_BUY']] = $this->data['attrintbuy'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_WIT_BUY']] = $this->data['attrwitbuy'];
		$ret[$GLOBALS['INDEX_PLAYER_ATTR_LUCK_BUY']] = $this->data['attrluckbuy'];
		///--------------------- STATS ATTRIBUTES-------------///
		
		
		$ret[$GLOBALS['INDEX_PLAYER_STATUS']] = $this->data['statusid'];
		$ret[$GLOBALS['INDEX_PLAYER_STATUS_EXTRA']] = $this->data['statusextra'];
		$ret[$GLOBALS['INDEX_PLAYER_STATUS_END']] = $this->data['statusend'];

		foreach($this->itemsEq as $item){
			$item->pushToArray($ret, 'INDEX_PLAYER_ITEM_SLOT_');
		}

		////////////// BACKPACK ////////////////
		foreach($this->itemsBp as $item){
			$item->pushToArray($ret, 'INDEX_PLAYER_ITEM_SLOT_');
		}

		$questlvl = explode(";", $this->data['questlvl']);
		if(count($questlvl) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LEVEL_1']] = $questlvl[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LEVEL_2']] = $questlvl[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LEVEL_3']] = $questlvl[2];
		}

		$questtype = explode(";", $this->data['questtype']);
		if(count($questtype) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_TYPE_1']] = $questtype[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_TYPE_2']] = $questtype[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_TYPE_3']] = $questtype[2];
		}

		$questenemy = explode(";", $this->data['questenemy']);
		if(count($questenemy) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_ENEMY_1']] = $questenemy[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_ENEMY_2']] = $questenemy[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_ENEMY_3']] = $questenemy[2];
		}

		$questlocation = explode(";", $this->data['questlocation']);
		if(count($questlocation) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LOCATION_1']] = $questlocation[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LOCATION_2']] = $questlocation[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_OFFER_LOCATION_3']] = $questlocation[2];
		}

		$questtime = explode(";", $this->data['questtime']);

		if(count($questtime) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_DURATION_1']] = $this->questTimeWithMount($questtime[0]);
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_DURATION_2']] = $this->questTimeWithMount($questtime[1]);
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_DURATION_3']] = $this->questTimeWithMount($questtime[2]);
		}

		foreach($this->itemsQuests as $item)
		{
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']]] = $item['itemtype'];
			if($item['itemtype'] > 7){
				$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +1] = $item['itemid'];
			}
			else{
				$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +1] = $item['itemid'] + (($item['itemclass']-1) * 1000);
			}
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +2] = $item['dmgmin'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +3] = $item['dmgmax'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +4] = $item['attrtype1'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +5] = $item['attrtype2'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +6] = $item['attrtype3'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +7] = $item['attrvalue1'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +8] = $item['attrvalue2'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +9] = $item['attrvalue3'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +10] = $item['silver'];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_ITEM_' . $item['slotid']] +11] = $item['mush'];
		}

		$questexp = explode(";", $this->data['questexp']);
		if(count($questexp) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_EXP_1']] = $questexp[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_EXP_2']] = $questexp[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_EXP_3']] = $questexp[2];
		}

		$questsilver = explode(";", $this->data['questsilver']);
		if(count($questsilver) >= 3){
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_SILVER_1']] = $questsilver[0];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_SILVER_2']] = $questsilver[1];
			$ret[$GLOBALS['INDEX_PLAYER_QUEST_SILVER_3']] = $questsilver[2];
		}

		$ret[$GLOBALS['INDEX_PLAYER_MOUNT_ID']] = (($this->data['towerlvl'] - 1) * 65536) + $this->data['mountid'];
		
		////////////// SHAKES ITEMS  //////////////// ZBROJOWNIA
		foreach($this->itemsShakes as $item){
			$item->pushToArray($ret, 'INDEX_PLAYER_SHAKES_ITEM_SLOT_');
		}
		foreach($this->itemsFidget as $item){
			$item->pushToArray($ret, 'INDEX_PLAYER_FIDGET_ITEM_SLOT_');
		}
		////////////// FIDGET ITEMS //////////////// MAGIC SHOP

		$ret[$GLOBALS['INDEX_PLAYER_GUILD_ATTACK_TIME']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_DEFEND_TIME']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_MSG_UNREADED']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_MSG']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_RANK']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_MUSHROOMS_MAY_DONATE']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_ALBUM']] = 0;

		$ret[$GLOBALS['INDEX_PLAYER_GUILD_JOIN_DATE']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_NEW_FLAGS']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_WE_MISS_YOU']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_ARMOR']] = $this->armor;
		
		$ret[$GLOBALS['INDEX_PLAYER_DMG_MIN']] = $this->weapon['dmgmin'];
		$ret[$GLOBALS['INDEX_PLAYER_DMG_MAX']] = $this->weapon['dmgmax'];
		
		$ret[$GLOBALS['INDEX_PLAYER_LIFE']] = $this->hp;
		
		$ret[$GLOBALS['INDEX_PLAYER_MOUNT_DURATION']] = $this->data['mounttime'];
		
		$ret[$GLOBALS['INDEX_PLAYER_TRANSACTION_COUNT']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_EVASION']] = 0; // unik procent ?
		$ret[$GLOBALS['INDEX_PLAYER_MAGICRSISTANCE']] = 0; // magic resist...
		
		$ret[$GLOBALS['INDEX_PLAYER_THIRST']] = $this->data['thirst'];
		$ret[$GLOBALS['INDEX_PLAYER_BEERS']] = $this->data['beers'];

		$ret[$GLOBALS['INDEX_PLAYER_DUNGEON_DONE']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_DUNGEON_TIME']] = $this->data['dungeontime'];
		$ret[$GLOBALS['INDEX_PLAYER_ARENA_TIME']] = $this->data['arenatime'];
		
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_EXP_BONUS']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_GOLD_BONUS']] = 0;
		
		$ret[$GLOBALS['INDEX_PLAYER_EMAIL_CONFIRMED']] = $this->data['emailconfirm'];
		
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_LVL']] = $this->lvl;
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_DUNG']] = $this->getCountDungEnd();
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_ARENA']] = $this->data['pvpwin'];
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_QUEST']] = $this->data['questscount'];
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_WORK_HOURS']] = $this->data['workhours'];
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_WORK_GOLD']] = $this->data['workgold'];
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_HONOR']] = $this->data['honor'];
		$ret[$GLOBALS['INDEX_PLAYER_ORDER_MUSH']] = round($this->data['mushbuy'] / 10);

		$ret[$GLOBALS['INDEX_PLAYER_LOCKDURATION']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_MUSH_BOUGHT_SINCE_LAST_LOGIN']] = 0;

		$ret[$GLOBALS['INDEX_PLAYER_FOO']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_BAR']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_HELLO']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_FIRST_PAYMENT']] = 0;

		for($i=1;$i<14;$i++){
			$ret[$GLOBALS['INDEX_PLAYER_DUNGEON_'.$i]] = $this->data['d'.$i];
		}
		
		$ret[$GLOBALS['INDEX_PLAYER_TOILET']] = 0;

		$ret[$GLOBALS['INDEX_PLAYER_PHP_SESSION']] = 0;

		for($i=1;$i<4;$i++){
			$ret[$GLOBALS['INDEX_PLAYER_POTION_TYPE'] + ($i-1)] = $this->data['potionid'.$i];
			$ret[$GLOBALS['INDEX_PLAYER_POTION_TIME'] + ($i-1)] = $this->data['potiontime'.$i];
			if($this->data['potionid'.$i] < 6){
				$ret[$GLOBALS['INDEX_PLAYER_POTION_VALUE'] + ($i-1)] = 5;
			}
			else if($this->data['potionid'.$i] < 11){
				$ret[$GLOBALS['INDEX_PLAYER_POTION_VALUE'] + ($i-1)] = 15;
			}
			else if($this->data['potionid'.$i] < 17){
				$ret[$GLOBALS['INDEX_PLAYER_POTION_VALUE'] + ($i-1)] = 25;
			}
			/*else if($this->data['potionid'.$i] == 16){
				$ret[$GLOBALS['INDEX_PLAYER_POWER_LIFE_POTION']] = 25;
			}*/
		}

		
		$ret[$GLOBALS['INDEX_PLAYER_LAST_LOGIN_IP']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_MUSHROOM_BOUGHT_AMOUNT']] = 0;
		$ret[$GLOBALS['INDEX_PLAYER_GUILD_WAR_STATUS']] = 0;

		$ret[$GLOBALS['INDEX_PLAYER_TIME']] = $SERVER_TIME;
		$ret[$GLOBALS['INDEX_PLAYER_SSID']] = $this->data['ssid'];
		$ret[$GLOBALS['INDEX_PLAYER_TOWER_LEVEL']] = $this->data['towerlvl'];

		
		return $ret;
	}
}