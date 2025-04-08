<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header("content-type: text/plain utf-8");

include("engine/Logger.php");
include("db.php");
include("engine/Globals.php");
include("engine/Utils.php");

include("engine/Mob.php");
include("engine/Monster.php");
include("engine/Server.php");
include("engine/Item.php");
include("engine/Player.php");

$ret = array();

if(!isset($_GET['req'])){
	exit("Błąd: Brak parametrów !");
}

if(!isset($_GET['req']) OR strlen($_GET['req']) < 35){
	exit(join("/", array($ERR_SESSION_EXPIRED)));
}

$SSID = substr($_GET['req'], 0, 32);
$ACT = substr($_GET['req'], 32, 3);
$DATA = substr($_GET['req'], 35);

$player = new Player;

if($ACT != $ACT_LOGIN && $ACT != $ACT_REGISTER){
	
	if(!$player->loginBySession($SSID)){
		exit($ERR_SESSION_EXPIRED);
	}
	
	if($player->enabled != 1){
		exit($ERR_LOCKED_ADMIN);
	}
}

switch ($ACT){
	
    case $ACT_REGISTER:
		
		//DATA:zzzz;zzzz;zzzz@wp.pl;;;5;2;1;3/305/1/3/0/3/3/5/1/;
		$data = explode(";", $DATA);
		
		$name = $data[0];
		$pass = $data[1];
		$email = strtolower($data[2]);

		$raceid = $data[5];
		$sexid = $data[6];
		$classid = $data[7];

		$face = $data[8];
		
		if(strlen($name) < 4 OR strlen($name) > 20){
			$ret = array($ERR_NAME_LENGHT);
			break;
		}
		
		if(!Utils::isNameCorrect($name)){
			$ret = array($ERR_NAME_REJECTED);
		}
		
		if(Utils::isNameExist($name)){
			$ret = array($ERR_NAME_EXISTS);
			break;
		}
		
		if(strlen($pass) < 4) {
			$ret = array($ERR_PASSWORD_TOO_SHORT);
			break;
		}
		else if(strlen($pass) > 30){
			$ret = array($ERR_WRONG_PASSWORD);
			break;
		}
         
		if(!Utils::isEmailCorrect($email)){
			$ret = array($ERR_EMAIL_REJECTED);
			break;
		}
		
		if(Utils::isEmailExist($email)){
			$ret = array($ERR_EMAIL_EXISTS);
			break;
		}
		
		if(Utils::isMaxLoginPerIp($CLIENT_IP)){
			//todo
			//$ret = array($ERR_ACCOUNTS_PER_IP);
			//break;
		}
		
		if(Utils::isFaceDataCorrect($face, $raceid, $sexid)){
			$ret = array($ERR_FACE_DATA_INCORRECT);
			break;
		}
		
		$pass = md5($pass);
		$playerid = Server::registerPlayer($name, $pass, $email, $raceid, $sexid, $classid, $face);

		if($playerid == 0){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if(!$player->loginById($playerid)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();

		$item = new Item();
		$item->genBasicWeaponByClass($classid);
		$item->ownerid = $playerid;

		if(!$item->insertTo('items_players')){
			//Server::deletePlayerById($playerid);
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->rerollItems($SHOP_FIDGET_ID);
		$player->rerollItems($SHOP_SHAKES_ID);
		
		$ret = array($ACT_REGISTER . $playerid);
		
	break;
	
	case $ACT_LOGIN:

		$data = explode(';', $DATA);
        $name = $data[0];
        $pass = $data[1];
		$version = $data[2];
		
		if(!Utils::isNameCorrect($name)){
			$ret = array($ERR_LOGIN_FAILED);
			break;
		}
		
		if(strlen($pass) != 32) {
			$ret = array($ERR_WRONG_PASSWORD);
			break;
		}
		
		$player = new Player;
		
		if(!$player->loginByPass($name, $pass)){
			$ret = array($ERR_LOGIN_FAILED);
			break;
		}
		
		if($player->enabled != 1){
			$ret = array($ERR_LOCKED_ADMIN);
			break;
		}

		$player->loadData();
		$ret = $player->getResp();
		
		$ret[0] = $ACT_LOGIN . $ret[0];
		
		$DealerAktion = 0; // ??? DealerAktion
		$ParseSavegame = 0; //?? ParseSavegame(par[0],false);
		
		$ret[] .= join(";", [$ParseSavegame, $DealerAktion, $player->data['ssid'], $player->data['mushbuy'], Server::getVersion(), Server::isBeerFest()]);
	
	break;
	
	case $ACT_LOGOUT:
	
		if($player->logout()){
			$ret = array($RESP_LOGOUT_SUCCESS);
		}
		else{
			$ret = array($ERR_SESSION_EXPIRED);
		}
		
	break;
	
	case $ACT_SETTINGS:
		$ret = array($ACT_SETTINGS);
	break;
	
	case $ACT_CHANGE_FACE:
		//zxcv;zxcv;2;2;7/302/303/2/1/2/2/2/1/1
		//zxcv;zxcv;1;2;5/404/403/5/1/4/3/1/1/1
		$in = explode(";", $DATA);

		$raceid = $in[2];
		$sexid = $in[3];

		$face = $in[4];

		if(Utils::isFaceDataCorrect($face, $raceid, $sexid)){
			$ret = array($ERR_FACE_DATA_INCORRECT);
			break;
		}

		$player->loadData();

		if($player->data['silver'] < 100){
			$ret = array($ERR_GOLD); //or ERR_GUILD_LACK_GOLD
			break;
		}

		try{
			$qry = $db->prepare("UPDATE `players` SET `face` = :face, `raceid` = :raceid, `sexid` = :sexid WHERE `id` = ".$player->id."");
			$qry->bindParam(":face", $face, PDO::PARAM_STR);
			$qry->bindParam(":raceid", $raceid, PDO::PARAM_INT);
			$qry->bindParam(":sexid", $sexid, PDO::PARAM_INT);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->data['face'] = $face;
		$player->data['raceid'] = $raceid;
		$player->data['sexid'] = $sexid;

		if(!$player->setSilver($player->data['silver'] - 100)){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		$ret = $player->getResp();
		$ret[0] = $RESP_CHANGE_FACE_OK . $ret[0];
		$ret[] .= ";" . urldecode($player->data['pdesc']) . ";";

	break;

	case $ACT_CHANGE_PASS:
		
		$in = explode(";", $DATA);
		//asdf;stare;nowe;nowe1

		$name = $in[0];
		$oldpass = $in[1];
		$newpass1 = $in[2];
		$newpass2 = $in[3];
		
		if($newpass1 != $newpass2){
			$ret = array($ERR_WRONG_PASSWORD);
			break;
		}
		if(strlen($newpass1) < 4) {
			$ret = array($ERR_PASSWORD_TOO_SHORT);
			break;
		}
		else if(strlen($newpass1) > 30){
			$ret = array($ERR_WRONG_PASSWORD);
			break;
		}

		$player->loadData();

		if(md5($oldpass) != $player->data['pass']){
			$ret = array($ERR_WRONG_PASSWORD);
			break;
		}

		$newpass = md5($newpass1);

		try{
			$qry = $db->prepare("UPDATE `players` SET `pass` = :pass WHERE `id` = ".$player->id."");
			$qry->bindParam(":pass", $newpass);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$ret = array($RESP_CHANGE_PASS_OK);

	break;
	
	case $ACT_CHANGE_EMAIL:
		
		$in = explode(';', $DATA);

		$pass = $in[1];
		$email1 = strtolower($in[2]);
		$email2 = strtolower($in[3]);

		$player->loadData();

		if(md5($pass) != $player->data['pass']){
			$ret = array($ERR_WRONG_PASSWORD);
            break;
		}

		if($player->data['emailconfirm'] == 1){
			//aktywowany
			if($email1 != $player->data['email']){
				$ret = array($ERR_EMAIL_WRONG);
				break;
			}
		}
		else{
			//nieaktywowany
			if($email1 != $email2){
				$ret = array($ERR_EMAIL_WRONG);
				break;
			}
		}

		if(!Utils::isEmailCorrect($email2) || $email2 == $player->data['email']){
			$ret = array($ERR_EMAIL_REJECTED);
			break;
		}
		if(Utils::isEmailExist($email2)){
			$ret = array($ERR_EMAIL_EXISTS);
			break;
		}

		try{
			$qry = $db->prepare("UPDATE `players` SET `email` = :email, `emailconfirm` = 0 WHERE `id` = ".$player->id."");
			$qry->bindParam(":email", $email2, PDO::PARAM_STR);
			$qry->execute();
		}
		catch(PDOException $exception){
			Logger::error($exception);
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$ret = array($RESP_CHANGE_EMAIL_OK);
		
	break;
	
	case $ACT_HERO:
		
		$player->loadData();
		$player->checkMount();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();
		$ret[0] = $ACT_HERO . $ret[0];
		$ret[] .= ";" . urldecode($player->data['pdesc']) . ";";
		
	break;

	case $ACT_WORK_START:
		
		if(!is_numeric($DATA)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$hours = intval($DATA);

		$player->loadData();

		$goldend = $player->getWorkRate() * $hours;
		$end = $SERVER_TIME + (3600 * $hours);

		$qry = $db->query("UPDATE `players` SET `workendsilver` = " .$goldend." WHERE `id` = " .$player->id."");

		$player->setStatus($STATUS_WORK, $hours, $end);

		$ret = $player->getResp();
		$ret[0] = $RESP_WORK_START . $ret[0];
		
	break;

	case $ACT_WORK_CANCEL:
		$player->loadData();

		if($player->data['statusid'] != $STATUS_WORK){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		
		if(!$player->setStatus(0, 0, 0)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$ret = $player->getResp();
        $ret[0] = $RESP_WORK_STOP . $ret[0];
	break;
	
	case $ACT_ARENA_ENTER:
	case $ACT_TAVERN_ENTER:
	case $ACT_WORK_ENTER:
	case $ACT_DUNGEON_ENTER:

		$player->loadData();

		$player->checkMount();

		if($player->data['statusid'] == $STATUS_QUEST){

			if($player->data['statusend'] < $SERVER_TIME){
				$player->loadItemsEq();
				$player->loadItemsQuests();
				$ret = $player->getRespFinishquest();
				$ret[0] = $RESP_QUEST_DONE . $ret[0];
				break;
			}

			$ret = $player->getResp();
			$ret[0] = Server::isQuestSkip()==true?$RESP_QUEST_SKIP_ALLOWED:$RESP_QUEST_START . $ret[0];
			$preventTv = 0;
			$ret[] = ";" . Server::getEventId() . ";" . $preventTv;
			break;
		}
		else if($player->data['statusid'] == $STATUS_WORK){
			if($player->data['statusend'] < $SERVER_TIME){
				//todo
				$ret = $player->getRespFinishwork();
				break;
			}
			$ret = array($ACT_WORK_ENTER . $player->getWorkRate() . ";0");
			break;
		}

		if($ACT == $ACT_TAVERN_ENTER){
			if($player->data['questrerolltime'] < $SERVER_TIME){
				$player->rerollQuests();
				$qry = $db->query("UPDATE `players` SET `questrerolltime` = " . $SERVER_TIME_TOMORROW . " WHERE `id` = " . $player->id . "");
			}

			$player->loadItemsQuests();
			$ret = $player->getResp();
			$ret[0] = Server::isQuestSkip()==true?$RESP_QUEST_SKIP_ALLOWED:$RESP_QUEST_STOP . $ret[0];
			$preventTv = 0;
			$ret[] = ";" . Server::getEventId() . ";" . $preventTv;
			break;
		}
		else if($ACT == $ACT_WORK_ENTER){
			$ret = array($ACT_WORK_ENTER . $player->getWorkRate() . ";0");
			break;
		}
		else if($ACT == $ACT_DUNGEON_ENTER){
			$ret = $player->getRespDung();
			break;
		}
		else if($ACT == $ACT_ARENA_ENTER){
			$ret = array("011huj;66;koxy;0/");
		}

	break;

	case $ACT_ARENA_FIGHT:

		$player->loadData();

		if($player->data['statusid'] != 0){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$name = $DATA;
		$defenderid = Utils::getPlayerIdByName($name);

		if(!Utils::isNameCorrect($name) || !Utils::isNameExist($name) || $name == $player->data['name'] || $defenderid == 0){
			$ret = array($RESP_ATTACK_NOT_EXIST);
			break;
		}

		$defender = new Player;
		$defender->loginById($defenderid);
		$defender->loadData();
		$defender->loadItemsEq();

		if($player->data['arenatime'] >= $SERVER_TIME){
			if($player->data['mush'] <= 0){
				$ret = array( $ERR_NO_MUSH_PVP );
				break;
			}
			$player->setMush($player->data['mush'] -1);
		}
		
		$player->loadItemsEq();
		
		$ret = Server::createFightResp($player, $defender);

		$ret[0] = $RESP_QUEST_DONE . $ret[0];

		$type = 2;// PVP - arena
		$honor = 0;
		$silver = 0;

		if($player->hp > 0){
			$winner = $player;
			$loser = $defender;
		}
		else{
			$winner = $defender;
			$loser = $player;
		}

		$honor = Server::calculateArenaHonor($winner->lvl, $loser->lvl);
		

		$ret[] .= ";" . $type .";0;" . $honor. ";" . $silver;

		/*
		 par[0].split("/"),   -- fighterData
		 par[1].split("/"),  -- fightData
		 par[2].split("/"), -- faceData
		 (par[3] + "/" + par[4]).split("/") -- weaponData
		 par[5] == "2", -- isPvP
		 par[6] == "1", -- getPilz
		 ,int(par[7]) -- HonorGain
		 ,int(par[8]) -- GoldGain
		 
		 ,par[5] == "3" -- isMQ
		 */

	break;

	case $ACT_DUNGEON_FIGHT:

		if(!is_numeric($DATA) || $DATA < 1 || $DATA > 15){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$did = intval($DATA);

		$player->loadData();

		if($player->data['statusid'] != 0){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$dlvl = $player->data['d'.$did] -1;

		if($dlvl >= 12 || $dlvl < 1){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if($player->data['dungeontime'] > $SERVER_TIME){
			if($player->data['mush'] <= 0){
				$ret = array($ERR_NO_MUSH_BAR);
				break;
			}
			$player->setMush($player->data['mush']-1);
		}
		else{
			$player->data['dungeontime'] = $SERVER_TIME + 3600;
		}

		$qry = $db->query("UPDATE `players` SET `dungeontime` = ".$player->data['dungeontime']." WHERE `id` = " . $player->id . " LIMIT 1");

		$monster = new Monster;
		$monster->loadForDung($did, $dlvl);

		$player->loadItemsEq();
		
		$ret = Server::createFightResp($player, $monster);

		$exp = 0;
		$silver = 0;

		if($player->hp > 0){
			$exp = Utils::getRandQuestExpByLvl($player->lvl) * 10;
			$exp += Server::getEventBonusExp($exp);

			$silver = Utils::getRandQuestSilverByLvl($player->lvl);

			$player->addExp($exp);
			$player->addSilver($silver);
			$qry = $db->query("UPDATE `players` SET `d$did` = `d$did` +1 WHERE `id` = " . $player->id . " LIMIT 1");
		}

		$ret[] = "0;3;0;" . $exp . ";" . $silver . ";" . 0 . ";";
		
		$savegame = $player->getResp();

		for($i=1;$i<Count($savegame) - 1;$i++)
		{
			array_push($ret, $savegame[$i]);
		}

		$ret[0] = $RESP_DUNGEON_FIGHT . $ret[0];

	break;

	case $ACT_QUEST_START:

		$questid = explode(";", $DATA)[0];
		$player->loadData();

		if($questid < 1 || $questid > 3 || $player->data['statusid'] != 0){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$questtime = $player->questTimeWithMount(explode(";", $player->data['questtime'])[$questid-1]);

		if($player->data['thirst'] < $questtime) {
            $ret = array($ERR_NO_ENDURANCE);
            break;
        }

		$player->loadItemsQuests();
		$player->loadItemsBp();

		$slotok = true;

		if(!$player->haveFreeSlotBp()){
			foreach($player->itemsQuests as $item){
				if($item['slotid'] == $questid){
					$slotok = false;
				}
			}
		}
		
		if(!$slotok){
			$ret = array($ERR_INVENTORY_FULL_ADV);
			break;
		}

		$questtime +=$SERVER_TIME;

		if(!$player->setStatus($STATUS_QUEST, $questid, $questtime)){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		
		$ret = $player->getResp();
		
		$ret[0] = Server::isQuestSkip()==true?$RESP_QUEST_SKIP_ALLOWED:$RESP_QUEST_START . $ret[0];

	break;

	case $ACT_QUEST_CANCEL:
		
		$player->loadData();

		if($player->data['statusid'] != $STATUS_QUEST){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		
		if(!$player->setStatus(0, 0, 0)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadItemsQuests();
		$ret = $player->getResp();
        $ret[0] = $RESP_QUEST_STOP . $ret[0];
		
	break;

	case $ACT_QUEST_SKIP:

		if(!Server::isQuestSkip()){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();

		if($player->data['statusid'] != $STATUS_QUEST){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if($player->data['mush'] <= 0){
			$ret = array($ERR_NO_MUSH_BAR);
			break;
		}

		$player->setMush($player->data['mush']-1);
		$player->loadItemsEq();
		$player->loadItemsQuests();
		$ret = $player->getRespFinishquest();
		$ret[0] = $RESP_QUEST_DONE . $ret[0];

	break;

	case $ACT_DRINK_BEER:

		$player->loadData();

		if($player->data['thirst'] >= 5800){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if($player->data['mush'] <= 0){
			$ret = array($ERR_NO_MUSH_BAR);
			break;
		}

		$player->setThirst($player->data['thirst'] +=1200);
		$player->setMush($player->data['mush']-1);
		$player->setBeers($player->data['beers']+1);

		$ret = $player->getResp();
		$ret[0] = $ACT_TAVERN_ENTER . $ret[0];

	break;

	case $ACT_STALL_ENTER:
		
		$player->loadData();

		if($player->data['statusid'] != 0){
			$ret = $player->getResp();
			$ret[0] = Server::isQuestSkip()==true?$RESP_QUEST_SKIP_ALLOWED:$RESP_QUEST_START . $ret[0];
			break;
		}

		$ret = array($ACT_STALL_ENTER);
		
	break;

	case $ACT_STALL_BUY:

		$mountid = $DATA;

		if($mountid < 1 || $mountid > 4 || !is_numeric($DATA)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();

		if($mountid < $player->data['mountid']){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$costsilver = 0;
		$costmush = 0;

		switch($mountid){
			case $MOUNT_10_ID:
				$costsilver = 100;
			break;
			case $MOUNT_20_ID:
				$costsilver = 52;
			break;
			case $MOUNT_30_ID:
				$costsilver = 10;
				$costmush = 1;
			break;
			case $MOUNT_50_ID:
				$costmush = 25;
			break;
		}

		if($costmush > $player->data['mush']){
			$ret = array($ERR_NO_MUSH_BAR);
			break;
		}

		if($costsilver > $player->data['silver']){
			$ret = array($ERR_GOLD);
			break;
		}

		if($player->updateMount($mountid)){
			$player->setMush($player->data['mush'] - $costmush);
			$player->setSilver($player->data['silver'] - $costsilver);
		}
		
		$player->loadData();
		$player->checkMount();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();
		$ret[0] = $ACT_HERO . $ret[0];
		$ret[] .= ";" . urldecode($player->data['pdesc']) . ";";
		
	break;
	
	case $ACT_HERO_DESC:
		
		$newdesc = $DATA;
		$pattern = '/^[a-zA-Z0-9!@#$%^&*()<>+=_\-,.\s]+$/';
		
		if(strlen($newdesc) > 238){
			$ret = array($ERR_TEXT_TOO_LONG);
			break;
		}

		if(!preg_match($pattern, $newdesc)){
			$ret = array($ERR_PLAYER_DESC);
			break;
		}
		
		$qry = $db->prepare("UPDATE `players` SET `pdesc` = :pdesc WHERE `id` = :pid");
		$qry->bindParam(":pdesc", $newdesc);
		$qry->bindParam(":pid", $player->id);

		if(!$qry->execute()){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$ret = array($RESP_PLAYER_DESC_SUCCESS);
		
	break;

	case $ACT_ENTER_SHOP_FIDGET:
	case $ACT_ENTER_SHOP_SHAKES:
		$player->loadData();
		if($player->data['shoprerolltime'] < $SERVER_TIME){
			$player->rerollItems($SHOP_FIDGET_ID);
			$player->rerollItems($SHOP_SHAKES_ID);
		}
		$player->loadItemsShops();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();

		$ret[0] = $ACT . $ret[0];
        $ret[] = ";" . Server::getEventId();
	
	break;

	case $ACT_REROLL_ITEMS:

		$player->loadData();

		if($player->data['mush'] < 1){
			$ret = array($ERR_NO_MUSH_MQ);
			break;
		}

		if(!is_numeric($DATA)){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		
		$shopid = ltrim(intval($DATA), '0');

		if($shopid == $SHOP_SHAKES_ID){
			$actshop = $ACT_ENTER_SHOP_SHAKES;
			$table = 'items_shakes';
		}
		else if($shopid == $SHOP_FIDGET_ID){
			$actshop = $ACT_ENTER_SHOP_FIDGET;
			$table = 'items_fidget';
		}
		else{
			break;
		}

		if(!$player->rerollItems($shopid)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if(!$player->setMush($player->data['mush']-1)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();
		$player->loadItemsShops();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();
		$ret[0] = $actshop . $ret[0];
        $ret[] = ";" . Server::getEventId();
		

	break;

	case $ACT_USE_ITEM:

		$in = explode(';', $DATA);

		$ret = "";
		
		$sourcebox = intval($in[0]); // 1 move item  z hero //  2 move item z backpack // 3, 4 item z shop shakes or fidget
		$fromid = intval($in[1]); // select slot
		$targetbox = intval($in[2]); // 
		$toid = intval($in[3]); // new select slot // -1 hero // 1-5 backpack

		$eventid = Server::getEventId();

		$BOX_SELL = 0;
		$BOX_HERO = 1;
		$BOX_BP = 2;
		$BOX_SHAKES = 3;
		$BOX_FIDGET = 4;

		if($fromid < 1 || $fromid > 10){
			break;
		}

		$handitem = new Item();

		if($sourcebox == $BOX_HERO){
			$handitem->loadFrom('items_players', $player->id, $fromid);
		}
		else if($sourcebox == $BOX_BP){
			$fromid +=10;
			$handitem->loadFrom('items_players', $player->id, $fromid);
		}
		else if($sourcebox == $BOX_SHAKES){
			$handitem->loadFrom('items_shakes', $player->id, $fromid);
		}
		else if($sourcebox == $BOX_FIDGET){
			$handitem->loadFrom('items_fidget', $player->id, $fromid);
		}

		if($handitem->isEmpty()){
			break;
		}
		
		$targetslot = new Item();
		
		if($targetbox == $BOX_HERO){
			$toid = Utils::getSlotIdForItemType($handitem->itemtype);
			$targetslot->loadFrom('items_players', $player->id, $toid);
		}
		else if($targetbox == $BOX_BP){
			if($toid < 1 || $toid > 5){
				break;
			}
			$toid+=10;
			$targetslot->loadFrom('items_players', $player->id, $toid);
		}

		$player->loadData();

		switch($sourcebox){
			case $BOX_FIDGET:
			case $BOX_SHAKES:

				if($targetbox != $BOX_HERO && $targetbox != $BOX_BP){
					break;
				}

				if($player->data['silver'] < $handitem->silver){
					$ret = array($ERR_GOLD);
					break;
				}
	
				if($player->data['mush'] < $handitem->mush){
					$ret = array($ERR_NO_MUSH_BAR);
					break;
				}

				if((($targetbox == $BOX_HERO || $targetbox == $BOX_BP) && $handitem->itemtype < 11) || ($targetbox == $BOX_BP && $handitem->itemtype == 12)){
					//przenosimy zwykly item do bp lub hero
					if(!$targetslot->isEmpty()){
						break;
					}
					$newitem = clone $handitem;
					$newitem->slotid = $toid;
					$newitem->silver = abs(round($newitem->silver * 0.5));
					$newitem->mush = 0;
					$newitem->copyTo('items_players');
				}
				else if($targetbox == $BOX_HERO && $handitem->itemtype == 12){
					//przenosimy potke do herobox
					if(!$player->setPotion($handitem->itemid)){
						break;
					}
				}

				$player->setSilver($player->data['silver'] - $handitem->silver);
				$player->setMush($player->data['mush'] -$handitem->mush);
				
				if($sourcebox == $BOX_FIDGET){
					$handitem->genItemForShop($SHOP_FIDGET_ID, $player->lvl, $player->classid, $eventid);
				}
				else if($sourcebox == $BOX_SHAKES){
					$handitem->genItemForShop($SHOP_SHAKES_ID, $player->lvl, $player->classid, $eventid);
				}
				$handitem->update();

			break;
			case $BOX_BP:

				if($targetbox == $BOX_SELL){
					//sprzedaz
					$player->addSilver($handitem->silver);
					$handitem->delete();
					break;
				}
				else if($sourcebox == $BOX_BP && $targetbox == $BOX_BP){
					//przenoszenie w plecaku
					if($targetslot->isEmpty()){
						$handitem->slotid = $targetslot->slotid;
						$handitem->update();
					}
					else {
						//zamien itemy miejscami w plecaku
						$slotidold = $handitem->slotid;
						$handitem->slotid = $targetslot->slotid;
						$handitem->update();
		
						$targetslot->slotid = $slotidold;
						$targetslot->update();
					}
				}
				else if($targetbox == $BOX_HERO){
					if($toid == 0 && $handitem->itemtype > 10){
						if($handitem->itemtype == 11){
							//klucze
							if($handitem->itemid < 10){
								if($player->data['d' . $handitem->itemid] == 0){
									$player->setDungeon($handitem->itemid, 1);
									$handitem->delete();
									$ret = $player->getRespDung();
									break;
								}
							}
						}
						else if($handitem->itemtype == 12){
							//potki
							if($player->setPotion($handitem->itemid)){
								$handitem->delete();
							}
						}
					}
					else if($targetslot->isEmpty() && $handitem->itemtype < 11){
						//przenies z plecak do eq
						$handitem->slotid = $targetslot->slotid;
						$handitem->update();
					}
					else if(!$targetslot->isEmpty() && $handitem->itemtype < 11 && $targetslot->itemtype < 11 && $targetslot->itemtype == $handitem->itemtype){
						//zamien item z eq na bp jesli jest zwyklym przedmiotem i itemtype == itemtype
						$targetslot->slotid = $handitem->slotid;
						$targetslot->update();
						$handitem->slotid = $toid;
						$handitem->update();
					}
				}

			break;
			case $BOX_HERO:
				if($targetbox == $BOX_BP){
					if($targetslot->isEmpty()){
						$handitem->slotid = $targetslot->slotid;
						$handitem->update();
					}
					else if(!$targetslot->isEmpty() && $handitem->itemtype < 11 && $targetslot->itemtype < 11 && $targetslot->itemtype == $handitem->itemtype){
						//zamien item z eq na bp jesli jest zwyklym przedmiotem i itemtype == itemtype
						$targetslot->slotid = $handitem->slotid;
						$targetslot->update();
						$handitem->slotid = $toid;
						$handitem->update();
					}
				}
			break;
		}

		if($ret == ""){
			$player->loadData();
			$player->loadItemsEq();
			$player->loadItemsBp();
			$player->loadItemsShops();
			$ret = $player->getResp();
			$ret[0] = $RESP_SAVEGAME_STAY . $ret[0];
		}

	break;

	case $ACT_KILL_POTION:

		$pid = intval($DATA);

		if($pid < 1 || $pid > 3){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		if(!$player->removePotion($pid)){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();
		$ret[0] = $RESP_SAVEGAME_STAY . $ret[0];

	break;
	
	case $ACT_MUSHDEALER_ENTER:
		$ret = array($ACT_MUSHDEALER_ENTER);
	break;

	case $ACT_BUY_STAT:

		$attrid = intval($DATA);

		if($attrid < 1 || $attrid > 5){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();

		$attrname = "";
		$attrvalue = 0;
		$attrvaluebuy = 0;
		$psilver = $player->data['silver'];

		if($attrid == 1){
			$attrname = "attrstr";
		}
		else if($attrid == 2){
			$attrname = "attrdex";
		}
		else if($attrid == 3){
			$attrname = "attrint";
		}
		else if($attrid == 4){
			$attrname = "attrwit";
		}
		else if($attrid == 5){
			$attrname = "attrluck";
		}

		$attrvalue = $player->data[$attrname];
		$attrvaluebuy = $player->data[$attrname . "buy"];

		if($attrvaluebuy >= 15000){
			$ret = array($ERR_UNKNOWN);
			break;
		}
		
		$cost = ($attrvaluebuy + 5) * 5;

		if($psilver < $cost)
		{
			$ret = array($ERR_GOLD);
			break;
		}

		$psilver-=$cost;
		$attrvalue++;
		$attrvaluebuy++;

		$qry = $db->prepare("UPDATE `players` SET `$attrname` = :attrvalue, `".$attrname."buy` = :attrvaluebuy, `silver` = :psilver WHERE `players`.`id` = :pid");
		$qry->bindParam(":attrvalue", $attrvalue);
		$qry->bindParam(":attrvaluebuy", $attrvaluebuy);
		$qry->bindParam(":psilver", $psilver);
		$qry->bindParam(":pid", $player->id);
		if(!$qry->execute()){
			$ret = array($ERR_UNKNOWN);
			break;
		}

		$player->loadData();
		$player->loadItemsEq();
		$player->loadItemsBp();
		$ret = $player->getResp();
		$ret[0] = $RESP_SAVEGAME_STAY . $ret[0];

	break;

	case $ACT_HALL_OF_FAME:

		//asdf;-1

		$res = [];
		$pos = 0;
		$posFrom = 0;

		$in = explode(';', $DATA);

		if (ctype_digit($in[1])) {
			$pos = (int) str_replace(';', '', $DATA);
		} else {
			$nick = $in[0];

			$qry = $db->prepare("
				SELECT pos FROM (
					SELECT name, 
						RANK() OVER (ORDER BY honor DESC, lvl DESC, id DESC) AS pos
					FROM players
				) ranked_users
				WHERE name = :name
			");
			$qry->bindParam(':name', $nick, PDO::PARAM_STR);
			$qry->execute();
			
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			$pos = $result ? (int) $result['pos'] : 1; // Jeśli gracz nie znaleziony, ustawiamy pozycję na 1
		}

		$playerCount = Server::getPlayersCount();
		$pos = max(8, min($pos, max(8, $playerCount))); // Zapewnia poprawny zakres pozycji

		$posFrom = max(0, $pos - 8);

		$qry = $db->prepare("
			SELECT p.*, g.name AS guild 
			FROM players p
			LEFT JOIN guilds g ON g.id = p.guildid
			ORDER BY p.honor DESC, p.lvl DESC, p.id DESC
			LIMIT :posFrom, 15
		");
		$qry->bindParam(':posFrom', $posFrom, PDO::PARAM_INT);
		$qry->execute();
		$res = $qry->fetchAll(PDO::FETCH_ASSOC);

		$ret = [$ACT_HALL_OF_FAME];
		$index = 0;
		$pos -= 7;

		foreach ($res as $data) {
			$ret[$index] = urldecode($pos);
			$ret[$index + 1] = $data['name'];
			$ret[$index + 2] = $data['guild'] ?? '';
			$ret[$index + 3] = ($data['classid'] == 2) ? "-" . $data['lvl'] : $data['lvl'];
			$ret[$index + 4] = $data['honor'];

			if ($data['classid'] == 3) {
				$ret[$index] = "-" . $ret[$index];
			}

			$pos++;
			$index += 5;
		}

		$ret[0] = $ACT_HALL_OF_FAME . $ret[0];
		$ret[] = ";";

	break;

	case $ACT_VIEW_OTHER_PLAYER:

		$char = new Player;
		
		if(!Utils::isNameCorrect($DATA)){
			$ret = array($RESP_PLAYER_NOT_FOUND);
			break;
		}

		if(!$char->loginByName($DATA)){
			$ret = array($RESP_PLAYER_NOT_FOUND);
			break;
		}

		$char->loadData();
		$char->loadItemsEq();
		
		$ret = $char->getResp();
		$ret [0] = "1110000000000";
		
		$ret[511] = ";" . Utils::utf8Format(urldecode($char->data['pdesc'])) . ";" . Server::getGuildNameById($char->guildid) . ";";

	break;
	
	default:
		$ret = array("E999");
	break;
	
}

/*if(isset($_GET['req'])){
	$log = '['.date('Y-m-d H:i:s').'] ' . "ACT:$ACT" . "	DATA:$DATA" . PHP_EOL;
	//$log.= join("/", $ret);
	$log.= '['.date('Y-m-d H:i:s').'] ' . "RESP:" . join("/", $ret) . PHP_EOL;
	file_put_contents('REQUEST_LOGS.log', $log, FILE_APPEND | LOCK_EX);
}*/


exit(join("/", $ret));