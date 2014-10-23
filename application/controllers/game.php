<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 게임 플레이 관련 controller, 로그인이 안되어 있으면 오류 출력
*/
class Game extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->driver('cache');
	}

	/*
	@desc 목록 화면
	*/
	public function bms(){
		if(!$this->session->userdata("memberNo")) {
			$this->load->view("plzlogin");
			return;
		}
		$bmslist = $this->cache->file->get('bmslist');
		if(!$bmslist){
			$this->load->model("AdminModel");
			$this->AdminModel->makeBMSCache();
			$bmslist = $this->cache->file->get('bmslist');
		}
		$this->session->set_userdata("bpm",100);
		$this->session->set_userdata("autoplay","none");
		$this->load->view("game/list",array("list"=>$bmslist));
	}

	/*
	@desc 게임 플레이
	*/
	public function play($hash){
		if(!$this->session->userdata("memberNo")) {
			$this->load->view("plzlogin");
			return;
		}
		$bmsdata = $this->cache->file->get($hash);
		$bmsdata['hash'] = $hash;
		$this->load->view("game/play",$bmsdata);
	}

	public function bmslist($group){
		$bmslist = $this->cache->file->get('bmslist');
		$list = array();
		$group = urldecode($group);
		foreach($bmslist as $hash => $b){
			if($b['group'] == $group)
				$list[] = array_merge($b,array('hash'=>$hash));
		}
		usort($list, array('Game','levelSort'));
		echo json_encode($list);
	}

	public static function levelSort($a,$b){
		if((int)$a['level'] > (int)$b['level']) return 1;
		else if((int)$a['level'] < (int)$b['level']) return -1;
		else return 0;
	}

	/*
	@desc 게임 플레이 화면에서 data를 ajax로 가져오는데, 이 함수에서 가져 옴
	*/
	public function getData($hash,$type){
		$bmsdata = $this->cache->file->get($hash);
		//BPM의 변경이 있으면 시간이 다르므로 다시 재 생성함
		if($this->session->userdata("bpm")!=100){
			$this->load->model("AdminModel");
			$bmsdata = $this->AdminModel->makeBMSDataCache($hash,$bmsdata['data'],$this->session->userdata("bpm")/100);
		}
		//게임 시작 시 시작 시간과 어떤 bms파일을 플레이 했는지 세션에 저장 함
		if($type == 'bmsInfo'){
			$this->session->set_userdata("hash",$hash);
			$this->session->set_userdata("start",mktime());
		}
		//노트 배치와 관련된 설정이 되어 있으면 아래에서 변경함
		if($type == "playData" || $type=="longNoteList"){
			$note = $this->getOptionByThis("note");
			if($note == "Mirror"){
				//Mirror는 섞을 노트 Line이 지정되어 있음
				if($bmsdata['data']['7Key']) $dataReplacer = array("1"=>"9","2"=>"8","3"=>"5","4"=>"4","5"=>"3","8"=>"2","9"=>"1","6"=>"6");
				else $dataReplacer = array("1"=>"5","2"=>"4","3"=>"3","4"=>"2","5"=>"1","6"=>"6");
			} else if($note == "Random"){
				//Random은 무작위로 섞는다
				if($type == "playData"){
					$dataReplacer = $this->session->userdata("randomData");
					$this->session->set_userdata("randomData",null);
				} else {
					$list = array(1,2,3,4,5);
					if($bmsdata['data']['7Key']) { $list[] = 8; $list[] = 9; }
					$org = $list;
					shuffle($list);
					for($a=0,$loopa=sizeof($list); $a<$loopa; $a++){
						$dataReplacer[$org[$a]] = $list[$a];
					}
					$dataReplacer[6]=6;
					$this->session->set_userdata("randomData",$dataReplacer);
				}
			}
			if($note == "Mirror" || $note == "Random"){
				//위에서 섞은 노트 Line을 실제 데이터에 반영함
				$replaceList = array();
				foreach($bmsdata[$type] as $currentTime => $data1){
					if($type == "playData"){
						foreach($data1 as $object => $dataList)
							$replaceList[$currentTime][$dataReplacer[$object]] = $dataList;
					} else if($type == "longNoteList"){
						foreach($data1 as $object => $dataList)
							$replaceList[$currentTime][$dataReplacer[$object]] = $dataList;
					}
				}
				echo json_encode($replaceList);
				exit;
			}
			if($note == "SuperRandom" || $note == "HyperRandom"){
				//SuperRandom/HyperRandom은 노트 Line과 상관 없이 섞음
				//롱노트를 먼저 배치하고 남는 Line에 일반 노트를 배치하는 방법을 사용함
				if($type == "playData"){
					//DB에 이미 입력해놓은 롱노트 데이터를 가져 옴
					//DB에 저장한 이유는 아래에 설명
					$longNoteData = $this->db->where("hash",$this->session->userdata("randomData"))->get("randomTemp")->row();
					$longNoteData = unserialize($longNoteData->data);
					$this->db->where("hash",$this->session->userdata("randomData"))->delete("randomTemp");
					$this->session->set_userdata("randomData",null);
					$longNoteDataSize = sizeof($longNoteData);
				} else if($note=="longNoteList")
					$dbData = array();
				$replaceList = array();
				$list = array(1,2,3,4,5); //노트를 배치할 Line
				if($bmsdata['data']['7Key']) { $list[] = 8; $list[] = 9; } //7Key는 2개 더
				if($note=="HyperRandom") $list[] = 6; //HyperRandom은 스크래치까지 섞는것 임
				$size = sizeof($list);
				srand(mktime());
				//Data구조와 관련된건 AdminModel 참조
				foreach($bmsdata[$type] as $currentTime => $data1){
					if($type == "playData"){ //일반노트
						$org = $list;
						$orgSize = $size;
						for($a=0;$a<$longNoteDataSize; $a++){
							if($currentTime>=$longNoteData[$a]['start'] && $currentTime<=$longNoteData[$a]['end']){
								//이 노트의 시간에 롱노트가 있으면 배치를 못하게 목록에서 제거
								array_splice($org,array_search($longNoteData[$a]['key'],$org),1);
								$orgSize--;
							}
						}
						foreach($data1 as $object => $dataList) {
							if($note=="SuperRandom" && $object == 6) $targetObject = 6;
							else {
								//배치할 Line을 찾고, 그 Line에 다른 노트가 배치되지 못하게 목록에서 제거
								$targetObject = $org[rand(0,$orgSize-1)];
								array_splice($org,array_search($targetObject,$org),1);
								$orgSize--;
							}
							$replaceList[$currentTime][$targetObject] = $dataList;
						}
					} else if($type == "longNoteList"){
						$org = $list;
						$orgSize = $size;
						for($a=0,$loopa=sizeof($dbData);$a<$loopa; $a++){
							if($currentTime>=$dbData[$a]['start'] && $currentTime<=$dbData[$a]['end']){
								//롱노트가 겹치지 않게 다른 노트가 있는 Line은 제거
								array_splice($org,array_search($dbData[$a]['key'],$org),1);
								$orgSize--;
							}
						}
						foreach($data1 as $object => $dataList) {
							if($note=="SuperRandom" && $object == 6) $targetObject = 6; //SuperRandom에서 스크래치는 그대로
							else {
								//배치할 Line을 찾고, 그 Line에 다른 롱노트가 배치되지 못하게 목록에서 제거
								$targetObject = $org[rand(0,$orgSize-1)];
								array_splice($org,array_search($targetObject,$org),1);
								$orgSize--;
								//일반 노트에서 사용하기 위해서 별도로 저장
								$dbData[] = array("start"=>$currentTime,"end"=>$dataList['end'],"key"=>$targetObject);
							}
							$replaceList[$currentTime][$targetObject] = $dataList;
						}
					}
				}
				if($type == "longNoteList"){
					//롱노트일 경우 일반노트에서 사용하게 DB에 저장
					//이렇게 한 이유는 session의 제한은 4kb임. 이게 넘어서면 문제가 발생하므로, 안전하게 DB에 임시로 저장함.
					$curHash = $this->session->userdata("memberNo")."_".md5(mktime());
					$this->db->set(array("hash"=>$curHash,"data"=>serialize($dbData)))->insert("randomTemp");
					$this->session->set_userdata("randomData",$curHash);
				}
				echo json_encode($replaceList);
				exit;
			}
		}
		echo json_encode($bmsdata[$type]);
	}

	/*
	@desc 게임이 완료 된 후 스코어 저장
	*/
	public function updateScore(){
		if(!$this->session->userdata("memberNo")) {
			$this->load->view("plzlogin");
			return;
		}
		//Auto play가 하나라도 있으면 기록 저장 안함
		if($this->session->userdata("autoplay")!="none") return;
		$hash = $this->session->userdata("hash",$hash);
		$bmsdata = $this->cache->file->get($hash);
		//게임 플레이시 기록한 데이터 정리
		$old_result = $_POST['total'];
		$old_normal = $_POST['normalResult'];
		$old_long = $_POST['longResult'];
		$long = $result = $normal = array();
		foreach($old_long as $key => $val){
			$long["n".ucfirst($key)] = $val;
		}
		foreach($old_normal as $key => $val){
			$normal["n".ucfirst($key)] = $val;
		}
		foreach($old_result as $key => $val){
			$result["n".ucfirst($key)] = $val;
		}
		//기타 게임 시작 시간, 키 타입 등 정리해서 DB에 저장
		$result['dtStart'] = date("Y-m-d H:i:s",$this->session->userdata("start"));
		$result['dtEnd'] = date("Y-m-d H:i:s");
		$result['cBMS'] = $this->session->userdata("hash");
		$result['nUserNo'] = $this->session->userdata("memberNo");
		$result['fGrade'] = round(($_POST['totalGrades'] / $_POST['missCut']) / $_POST['totalNotes'] * 100,4);
		$result['nBPM'] = $this->session->userdata("bpm");
		$note = substr($this->getOptionByThis("note"),0,1);
		$result['emNote'] = $note;
		$result['emDead'] = ($_POST['ignoreRank']=="true"?'Y':'N');
		$this->load->model("RecordModel");
		$res = $this->RecordModel->insertRecord($result,$normal,$long);
		$this->session->set_userdata("lastresult",$res); //결과페이지에서 불러올 수 있게 record의 sequence 번호 session에 저장

		//폭사이거나 BPM을 변경 했을 경우 공식 IR 랭킹에는 반영하지 않음
		if($this->session->userdata("bpm")!=100 || $_POST['ignoreRank']=="true" ) return;

		//키 배치 타입
		$keyOption = str_replace("key","",$this->getOptionByThis("key"));

		$this->load->model("RankModel");
		$this->RankModel->load(array("nUserNo"=>$this->session->userdata("memberNo"),"cBMS"=>$this->session->userdata("hash"),"emKeyType"=>$keyOption));
		//기존 기록이 없으면, 신규 등록
		if($this->RankModel->getTotal()==0){
			$info = array();
			$info['nUserNo'] = $this->session->userdata("memberNo");
			$info['cBMS'] = $this->session->userdata("hash");
			$info['nRecordNo'] = $res;
			$info['nScore'] = $result['nScore'];
			$info['fGrade'] = $result['fGrade'];
			$info['emKeyType'] = $keyOption;
			$this->RankModel->insertRecord($info);
		} else {
			$myCurrentRank = $this->RankModel->get();
			//스코어가 갱신 되었을 때, Record번호와 그 점수, 정확도를 DB에 업데이트 함
			if($myCurrentRank->nScore<$result['nScore']){
				$info = array();
				$info['nRecordNo'] = $res;
				$info['nScore'] = $result['nScore'];
				$info['fGrade'] = $result['fGrade'];
				$this->RankModel->updateRecord($info,$myCurrentRank->nSeqNo);
			}
		}
	}

	/*
	@desc 결과 페이지
	*/
	public function result(){
		if(!$this->session->userdata("memberNo")) {
			$this->load->view("plzlogin");
			return;
		}
		//AutoPlay는 결과페이지 안보여줌
		if($this->session->userdata("autoplay")!="none") { $this->load->helper("url"); redirect("/game/bms"); exit; }

		//자신의 IR 랭킹 기록 및 이번 플레이 기록을 불러 옴
		$keyOption = str_replace("key","",$this->getOptionByThis("key"));

		$this->load->model("RecordModel");
		$this->RecordModel->load(array("nSeqNo"=>$this->session->userdata("lastresult")));
		$data = $this->RecordModel->get();

		$this->load->model("RankModel");
		$this->RankModel->load(array("nUserNo"=>$this->session->userdata("memberNo"),"cBMS"=>$this->session->userdata("hash"),"emKeyType"=>$keyOption));
		$myCurrentRank = $this->RankModel->get();

		$this->load->view("game/result",array("record"=>$data,"rank"=>$myCurrentRank));
	}

	/*
	@desc Option값을 class 내에서 쓸 수 있게 버퍼에 저장 했다가 불러 옴
	*/
	public function getOptionByThis($option){
		ob_start();
		$this->getOption($option);
		return ob_get_clean();
	}

	/*
	@desc Option값을 출력하는 함수
	*/
	public function getOption($option){
		$result = $this->db->where("nUserNo",$this->session->userdata("memberNo"))->where("vcKey",$option)->get("option");
		if($result->num_rows()==0) {
			switch($option){
				case "key": echo "key2"; break;
				case "bga": echo "bgaon"; break;
				case "volume": echo "100"; break;
				case "note": echo "Normal"; break;
				case "bgaPosition": echo json_encode(array("l"=>600,"t"=>125)); break;
				case "bgaSize": echo json_encode(array("w"=>256,"h"=>256)); break;
				case "autoplay": echo $this->session->userdata("autoplay"); break;
				case "bpm": echo $this->session->userdata("bpm"); break;
				case "keydetail": echo json_encode(array('6'=>65,'1'=>83,'2'=>68,'3'=>70,'4'=>32,'5'=>74,'8'=>75,'9'=>76)); break;
			}
		} else
			echo $result->row()->vcValue;
	}

	/*
	@desc 옵션 값을 DB에 저장하는 함수
	*/
	public function updateOption($option){
		switch($option){
			case "key":
			case "bga":
			case "note":
				$value = $this->input->get("type");
				break;
			case "bgaPosition":
				$data = $this->input->post("data");
				if($data=="init") $data = array("l"=>600,"t"=>125);
				$value = json_encode($data);
				break;
			case "bgaSize":
				$data = $this->input->post("data");
				if($data=="init") $data = array("w"=>256,"h"=>256);
				$value = json_encode($data);
				break;
			case "keydetail":
				$data = $this->input->post("data");
				$value = json_encode($data);
				break;
			case "volume":
				$value = $this->input->get("data");
				break;
			case "bpm":
				$this->session->set_userdata("bpm",100+$this->input->get("data"));
				exit();
			case "autoplay":
				$this->session->set_userdata("autoplay",$this->input->get("data"));
				exit();
		}
		$result = $this->db->where("nUserNo",$this->session->userdata("memberNo"))->where("vcKey",$option)->get("option");
		if($result->num_rows()==0)
			$this->db->set(array("nUserNo"=>$this->session->userdata("memberNo"),"vcKey"=>$option,"vcValue"=>$value))->insert("option");
		else
			$this->db->set(array("vcValue"=>$value))->where("nUserNo",$this->session->userdata("memberNo"))->where("vcKey",$option)->update("option");
	}
}
