<?php

abstract class Mob{

	public string $name = "null";
	public string $guildname = "";
	public int $lvl = 1;
	public int $raceid = 0;
	public int $sexid = 0;
	public int $classid = 0;
	public $facearray = array(0, 0, 0, 0, 0, 0, 0, 0, 0);

    public int $dmgmin = 1;
	public int $dmgmax = 2;

	public int $hp = 0;
	public int $armor = 0;

	public int $attrstr = 0;
	public int $attrdex = 0;
	public int $attrint = 0;
	public int $attrwit = 0;
	public int $attrluck = 0;

	public $weapon = array('itemtype' => 0, 'itemid' => 1, 'itemclass' => 0, 'dmgmin' => 1, 'dmgmax' => 2, 'attrtype1' => 0, 'attrtype2' => 0, 'attrtype3' => 0, 'attrvalue1' => 0, 'attrvalue2' => 0, 'attrvalue3' => 0, 'silver' => 0, 'mush' => 0);
	public $shield = array('itemtype' => 0, 'itemid' => 2, 'itemclass' => 1, 'dmgmin' => 0, 'dmgmax' => 0, 'attrtype1' => 0, 'attrtype2' => 0, 'attrtype3' => 0, 'attrvalue1' => 0, 'attrvalue2' => 0, 'attrvalue3' => 0, 'silver' => 0, 'mush' => 0);

	public function getFightAttr(){
		return $this->hp."/".$this->attrstr."/".$this->attrdex."/".$this->attrint."/".$this->attrwit."/".$this->attrluck;
	}

	public function getFightFace(){
		return $this->name."/".$this->lvl."/".$this->raceid."/".$this->sexid."/".$this->classid."/".$this->facearray[0]."/".$this->facearray[1]."/".$this->facearray[2]."/".$this->facearray[3]."/".$this->facearray[4]."/".$this->facearray[5]."/".$this->facearray[6]."/".$this->facearray[7]."/".$this->facearray[8];
	}
	
	public function getFightWeapon(){
		return $this->weapon['itemtype'] ."/". $this->weapon['itemid'] + (($this->classid -1) * 1000)."/".$this->weapon['dmgmin']."/".$this->weapon['dmgmax']."/".$this->weapon['attrtype1']."/".$this->weapon['attrtype2']."/".$this->weapon['attrtype3']."/".$this->weapon['attrvalue1']."/".$this->weapon['attrvalue2']."/".$this->weapon['attrvalue3']."/".$this->weapon['silver']."/".$this->weapon['mush'];
	}

	public function getFightShield(){
		return $this->shield['itemtype'] ."/". $this->shield['itemid'] ."/".$this->shield['dmgmin']."/".$this->shield['dmgmax']."/".$this->shield['attrtype1']."/".$this->shield['attrtype2']."/".$this->shield['attrtype3']."/".$this->shield['attrvalue1']."/".$this->shield['attrvalue2']."/".$this->shield['attrvalue3']."/".$this->shield['silver']."/".$this->shield['mush'];
	}

	public function getMaxReduction(){
		if($this->classid > 0){
			return array(50, 10, 25)[$this->classid-1];
		}

		return 0;
	}
}