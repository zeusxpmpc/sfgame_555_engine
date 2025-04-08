<?php

class Server{

	public static function isBeerFest(){
		return 0;
	}
	
	public static function isQuestSkip(){
		global $SERVER_TIME_HOURS;
		
		return true;
		return (explode(":", $SERVER_TIME_HOURS)[0] == 23);
	}
	
	public static function getEventId(){
		global $db;
		//todo
		//4 mush //2 epic // 3 gold // 1 exp
		return 6;
	}

	public static function getEventBonusExp($value){
		global $EVENT_EXP;
		
		if(Server::getEventId() == $EVENT_EXP){
			return $value;
		}
		return 0;
	}

	public static function getEventBonusSilver($value){
		global $EVENT_GOLD;

		if(Server::getEventId() == $EVENT_GOLD){
			return $value * 4;
		}
		return 0;
	}

	public static function getItemMushValueAfterBuy(){
		return 0;
	}
	
	public static function getVersion(){
		return 543;
	}

	public static function createFightResp(Mob &$attacker, Mob &$defender){
		global $MOVE_NORMAL_ID, $MOVE_SHIELD_ID, $MOVE_DODGE_ID, $MOVE_CRITIC_ID;

		$ret = "";

		$ret .= $attacker->getFightAttr();
		$ret .= "/";
		$ret .= $defender->getFightAttr();
		
		$ret .= ";";

		$logs = "";
		$logs_reverse = "";

		//$logs = array();

		$attacker_first = (($attacker->attrluck+1) / $attacker->lvl) > (($defender->attrluck+1) / $defender->lvl);
		$round = 1;
		
		while($attacker->hp > 0 && $defender->hp > 0){

			$adata = Server::calculateFightMove($attacker, $defender);
			$ddata = Server::calculateFightMove($defender, $attacker);

			if(!$attacker_first){
				$adata['dmgtype'] = 0;
				$adata['dmg'] = 0;
			}
			
			$logs .= ($attacker->hp -= $ddata['dmg']) . "/" . $adata['dmg'] . "/" . $adata['dmgtype'] . "/"; //attacker
			$logs .= ($defender->hp -= $adata['dmg']) . "/" . $ddata['dmg'] . "/" . $ddata['dmgtype'] . "/"; //defender

			$attacker_first = true;
			//$logs_reverse .= $defender->hp . "/" . $ddata['dmg'] . "/" . $ddata['dmgtype'] . "/";
			//$logs_reverse .= $attacker->hp . "/" . $adata['dmg'] . "/" . $adata['dmgtype'] . "/";
		}

		$ret .= $logs;
		$ret .= ";";

		$ret .= $attacker->getFightFace() . "/1";
		$ret .= "/";
		$ret .= $defender->getFightFace() . "/1";

		$ret .= ";";

		$ret .= $attacker->getFightWeapon();
		$ret .= "/";
		$ret .= $defender->getFightWeapon();

		$ret .= ";";

		$ret .= $attacker->getFightShield();
		$ret .= "/";
		$ret .= $defender->getFightShield();

		return explode("/", $ret);
	}

	public static function calculateFightMove(Mob &$attacker, Mob &$defender){
		global $CLASS_WARRIOR_ID, $CLASS_MAGE_ID, $CLASS_SCOUT_ID, $MOVE_NORMAL_ID, $MOVE_SHIELD_ID, $MOVE_DODGE_ID, $MOVE_CRITIC_ID;

		$log = array('dmg' => 0, 'dmgtype' => 0);

		$log['dmg'] = rand($attacker->dmgmin, $attacker->dmgmax);
		$log['dmgtype'] = $MOVE_NORMAL_ID;

		$criticchance = $attacker->attrluck * 5 / ($defender->lvl * 2);
		
		if($criticchance > 50){
			$criticchance = 50;
		}

		if(rand(1, 100) <= $criticchance){
			$log['dmg'] = rand($attacker->dmgmax, $attacker->dmgmax*2);
			$log['dmgtype'] = $MOVE_CRITIC_ID;
		}


		//CALCULATE BLOCK or DODGE
		if($attacker->classid != $CLASS_MAGE_ID){
			if($defender->classid == $CLASS_WARRIOR_ID){
				//scout attack warrior
				if(rand(1, 100) <= $defender->shield['dmgmin'] && $defender->shield['dmgmin'] <= 25){
					//blok
					$log['dmg'] = 0;
					$log['dmgtype'] = $MOVE_SHIELD_ID;
				}
			}
			else if($defender->classid == $CLASS_SCOUT_ID){
				//scout attack scout
				if(rand(1,100) <= 50){
					//unik
					$log['dmg'] = 0;
					$log['dmgtype'] = $MOVE_DODGE_ID;
				}
			}
		}

		//CALCULATE ARMOR REDUCTION
		if($log['dmg'] > 0){
			if($attacker->classid != $CLASS_MAGE_ID){

				$dmg_reduction = abs(round($defender->armor / $attacker->lvl)) +1;
	
				if($dmg_reduction > 0){
					if($dmg_reduction > $defender->getMaxReduction()){
						$dmg_reduction = $defender->getMaxReduction();
					}
		
					$rdmg = round($log['dmg'] * ($dmg_reduction/100));
					if($rdmg <= $log['dmg']){
						$log['dmg'] = $log['dmg'] - $rdmg;
					}
					else if($log['dmg'] > 0){
						$log['dmg'] = 1;
					}
				}
			}
			//CALCULATE MAGIC RESIST IF defender is not mage too
			else if($defender->classid != $CLASS_MAGE_ID){
	
				$dmg_reduction = abs(round($defender->attrint / $attacker->attrint + $defender->attrint)) +1;
				
				//exit("REDUCTION:" . $dmg_reduction);

				$log['dmg'] -= $dmg_reduction;

				if($log['dmg'] <1){
					$log['dmg'] = 1;
				}
			}
		}

		return $log;
	}

	public static function registerPlayer($name, $pass, $email, $raceid, $sexid, $classid, $face){
		global $db, $CLIENT_IP, $SERVER_TIME_TOMORROW;
		
		$face = substr($face, 0, -1);
		
		$defstats = Utils::getDefaultPlayerStats($raceid, $sexid, $classid);

		$str = $defstats['str'];
		$dex = $defstats['dex'];
		$int = $defstats['int'];
		$wit = $defstats['wit'];
		$luck = $defstats['luck'];

		$rerolltime = $SERVER_TIME_TOMORROW;
		
		try{
			$qry = $db->prepare("INSERT INTO `players`
			(`name`, `pass`, `email`, `lastip`, `raceid`, `sexid`, `classid`, `face`, `attrstr`, `attrdex`, `attrint`, `attrwit`, `attrluck`) VALUES
			(:name, :pass, :email, :lastip, :raceid, :sexid, :classid, :face, :attrstr, :attrdex, :attrint, :attrwit, :attrluck)");
			$qry->bindParam(":name", $name, PDO::PARAM_STR);
			$qry->bindParam(":pass", $pass, PDO::PARAM_STR);
			$qry->bindParam(":email", $email, PDO::PARAM_STR);
			$qry->bindParam(":lastip", $CLIENT_IP, PDO::PARAM_STR);
			$qry->bindParam(":raceid", $raceid, PDO::PARAM_INT);
			$qry->bindParam(":sexid", $sexid, PDO::PARAM_INT);
			$qry->bindParam(":classid", $classid, PDO::PARAM_INT);
			$qry->bindParam(":face", $face, PDO::PARAM_STR);
			$qry->bindParam(":attrstr", $str, PDO::PARAM_INT);
			$qry->bindParam(":attrdex", $dex, PDO::PARAM_INT);
			$qry->bindParam(":attrint", $int, PDO::PARAM_INT);
			$qry->bindParam(":attrwit", $wit, PDO::PARAM_INT);
			$qry->bindParam(":attrluck", $luck, PDO::PARAM_INT);
			$qry->execute();
		} catch (PDOException $exception) {
			Logger::error($exception);
			return 0;
		}

		return $db->lastInsertId();
	}

	public static function deletePlayerById($playerid){
		global $db;

		try{
			$qry = $db->prepare("DELETE FROM `players` WHERE `id` = :id");
			$qry->bindParam(":id", $playerid);
			if ($qry->execute() && $qry->rowCount() == 1) {
				return true;
			}
		}
		catch(PDOException $exception){
			Logger::error($exception);
		}
		return false;
	}

	public static function getPlayersCount() {
		global $db;
	
		try {
			$qry = $db->prepare("SELECT COUNT(*) as count FROM players");
			$qry->execute();
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			return $result ? (int) $result['count'] : 0;
		} catch (PDOException $exception) {
			Logger::error($exception);
			return 0; // Zwróć 0 w razie błędu
		}
	}

	public static function getGuildNameById($guildid) {
		global $db;
	
		if ($guildid == 0) {
			return "";
		}
	
		try {
			$qry = $db->prepare("SELECT name FROM guilds WHERE id = :id LIMIT 1");
			$qry->bindParam(":id", $guildid, PDO::PARAM_INT);
			$qry->execute();
			
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			return $result ? $result['name'] : "";
		} catch (PDOException $exception) {
			Logger::error($exception);
			return "";
		}
	}

	public static function calculateArenaHonor($winnerLvl, $loserLvl, $basePoints = 100, $k = 5) {
		$levelDifference = $winnerLvl - $loserLvl;
		return round($basePoints / (1 + $k * $levelDifference));
	}

}