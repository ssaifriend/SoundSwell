<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc Mypage Controller
*/
class Mypage extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->driver('cache');
	}

	/*
	@desc 마이 페이지 메인
	*/
	public function main(){
		if(!$this->session->userdata("memberNo")) {
			$this->load->view("plzlogin");
			return;
		}
		//유저가 최근 기록한 15개 기록 불러옴
		$bmslist = $this->cache->file->get('bmslist');
		$this->load->model("RecordModel");
		$this->RecordModel->load(array("nUserNo"=>$this->session->userdata("memberNo")),array("nSeqNo","desc"),0,15);
		$playCount = $this->RecordModel->countWithCondition(array("nUserNo"=>$this->session->userdata("memberNo")));

		//자기가 기록하고 있는 랭킹 가져오기
		$page = $this->input->get("page");
		if(!$page) $page = 1;
		$start = ($page-1) * 15;
		$this->load->model("RankModel");
		//최종 쿼리문: select a.*, c.`nBPM`, c.`emNote`, c.`emDead`, c.`dtEnd`, (select count(`nSeqNo`) as `nRank` from `rank` as b where b.`cBMS`=a.`cBMS` and b.`emKeyType`=a.`emKeyType` and b.`nScore`>a.`nScore`) as `nRank from `rank` as a left join `record` as c on c.`nSeqNo`=a.`nRecordNo` where a.`nUserNo`=회원번호 order by `nRank` asc, c.`dtEnd` desc limit 0,15
		$this->RankModel->loadSetColumn(array("a.*","c.`nBPM`","c.`emNote`","c.`emDead`","c.`dtEnd`",array(" (select count(`nSeqNo`) as `nRank` from `rank` as b where b.`cBMS`=a.`cBMS` and b.`emKeyType`=a.`emKeyType` and b.`nScore`>a.`nScore`) as `nRank`",false)),"`rank` as a",array("a.nUserNo"=>$this->session->userdata("memberNo")),array(array("`record` as c ","c.nSeqNo = a.`nRecordNo`")),array(array("nRank","asc"),array("c.`dtEnd`","desc")),$start,15);
		$total = $this->RankModel->countWithCondition(array("nUserNo"=>$this->session->userdata("memberNo")));

		$this->load->view("mypage/main",array("list"=>$bmslist,"RecordModel"=>$this->RecordModel,"RankModel"=>$this->RankModel,"page"=>$page,"total"=>$total,"playCount"=>$playCount));
	}

	/*
	@desc 정보 업데이트
	*/
	public function updateInfo(){
		$password = $this->input->post("password");
		$email = $this->input->post("email");

		$this->load->model("MemberModel");
		$obj = (object)array();
		$obj->nSeqNo = $this->session->userdata("memberNo");
		$this->MemberModel->setInfo($obj);
		$this->MemberModel->set("cPassword",md5($password));
		$this->MemberModel->set("vcEmail",$email);
		$res = $this->MemberModel->doUpdate();
		if($res) echo 'ok';
		else echo 'false';
	}
}
