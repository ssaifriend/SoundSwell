<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc IR 페이지 controller
*/
class IR extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->driver('cache');
	}

	/*
	@desc bms 목록 보는 페이지
	*/
	public function bms(){
		$bmslist = $this->cache->file->get('bmslist');
		$this->load->model("RecordModel");
		$this->RecordModel->loadWithUser(array(),array("nSeqNo","desc"),0,15);
		$this->load->view("ir/list",array("list"=>$bmslist,"RecordModel"=>$this->RecordModel));
	}

	/*
	@desc 키별로 랭킹 보여주는 페이지
	*/
	public function show($hash,$key = "key2"){
		$this->load->model("RankModel");
		$page = $this->input->get("page");
		if(!$page) $page = 1;
		$start = ($page-1) * 15;
		$condition = array("cBMS"=>$hash,"emKeyType"=>str_replace("key","",$key));
		$this->RankModel->loadWithUser($condition,array("nScore","desc"),$start);
		$data = $this->cache->file->get($hash);
		$total = $this->RankModel->countWithCondition($condition);
		$this->load->view("ir/show",array("RankModel"=>$this->RankModel,"info"=>$data['bmsInfo'],"start"=>$start,"total"=>$total,"page"=>$page,"hash"=>$hash,"key"=>$key));
	}
}