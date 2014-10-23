<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if($this->session->userdata("memberNo")){
			$this->load->model("MemberModel");
			$this->MemberModel->_getMemberByNo($this->session->userdata("memberNo"));
		}
		if(!$this->session->userdata("memberNo") || $this->MemberModel->get("emAdmin")!="Admin"){
			show_404();
			return;
		}
	}

	/*
	@desc BMS 목록 작성
	*/
	public function updateBMS(){
		$this->load->model("AdminModel");
		$this->AdminModel->makeBMSCache();
	}

	public function main(){
		$this->load->view("admin/main");
	}

	public function member($page = 1){
		$start = ($page-1)*20;
		$memberList = $this->db->order_by("nSeqNo","desc")->limit(20,$start)->get("member")->result();
		$data = array();
		$data['memberList'] = $memberList;
		$data['page'] = $page;
		$data['total'] = $this->db->order_by("nSeqNo","desc")->count_all_results("member");
		$this->load->view("admin/member",$data);
	}

	public function memberRestore(){
		$seq = $this->input->post("nSeqNo");
		$this->db->set(array("emDel"=>"N"))->where("nSeqNo",$seq)->update("member");
	}

	public function memberDel(){
		$seq = $this->input->post("nSeqNo");
		$this->db->set(array("emDel"=>"Y"))->where("nSeqNo",$seq)->update("member");
	}

	public function memberAdmin(){
		$seq = $this->input->post("nSeqNo");
		$this->db->set(array("emAdmin"=>$this->input->post("admin")))->where("nSeqNo",$seq)->update("member");
	}

	public function memberPassword(){
		$seq = $this->input->post("nSeqNo");
		$this->db->set(array("cPassword"=>md5($this->input->post("password"))))->where("nSeqNo",$seq)->update("member");
	}

	public function login($page = 1){
		$start = ($page-1)*20;
		if($this->input->get("seqNo")) $this->db->where("nUserNo",$this->input->get("seqNo"));
		$list = $this->db->select("stat_login.*, member.vcUserId, member.vcNickname")->join("member","member.nSeqNo=stat_login.nUserNo")->order_by("stat_login.nSeqNo","desc")->order_by("stat_login.dtRegdate","desc")->limit(20,$start)->get("stat_login")->result();

		$data = array();
		$data['list'] = $list;
		$data['page'] = $page;
		if($this->input->get("seqNo")) $this->db->where("nUserNo",$this->input->get("seqNo"));
		$data['total'] = $this->db->order_by("nSeqNo","desc")->count_all_results("stat_login");
		$this->load->view("admin/login",$data);
	}
}
