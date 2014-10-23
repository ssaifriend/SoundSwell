<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 메인 페이지 controller
*/
class Main extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}

	/*
	@desc 메인
	*/
	public function index() {
		$this->load->library('user_agent');
		//다른데서 타고 왔으면 어디서 타고 왔는지 확인해 보기 위해 체크
		$url = parse_url($this->agent->referrer(), PHP_URL_HOST);
		if($url && $url != 'soundswell.880217.org' && $url != 'soundswell.kr'){
			$this->db->set(array("vcReferer"=>$this->agent->referrer(),"vcUrl"=>$this->uri->uri_string(),"vcUA"=>$this->agent->agent_string()))->insert("stat_referer");
		}
		//각 게시판 데이터 불러옴
		$this->load->model("BoardModel");
		$data = array();
		$data['totalUser'] = $this->db->where('emDel','N')->from('member')->count_all_results();
		$data['totalPlayCount'] = $this->db->from('record')->count_all_results();
		$data['totalRankCount'] = $this->db->from('rank')->count_all_results();
		$data['freeBoardNotice'] = $this->BoardModel->getArticleList("free",array(array("emDel","N"),array("emNotice","Y")),array("nSeqNo","desc"),0,10);
		$data['freeBoard'] = $this->BoardModel->getArticleList("free",array(array("emDel","N")),array("nSeqNo","desc"),0,10-sizeof($data['freeBoardNotice']));
		$data['requestBoard'] = $this->BoardModel->getArticleList("request",array(array("emDel","N")),array("nSeqNo","desc"),0,10);
		$this->load->view('main/index',$data);
	}

	/*
	@desc 로그인 전/후 페이지
	*/
	public function loginLoad(){
		$this->load->model('MemberModel');
		if($this->MemberModel->isLogin()) {
			$this->MemberModel->_getMemberByNo($this->session->userdata("memberNo"));
			$this->load->view("main/logout",array("member"=>$this->MemberModel));
		} else $this->load->view("main/login");
	}

	/*
	@desc 로그인 체크
	*/
	public function login(){
		$userid = $this->input->post("userid");
		$password = $this->input->post("password");
		if(!$userid || !$password) { echo "false"; return; }

		$this->load->model('MemberModel');
		$member = $this->MemberModel->_getMemberById($userid);
		if($member == false) { echo "false"; return; }
		if($this->MemberModel->get("emDel") == "Y") { echo "false"; return; }
		if($this->MemberModel->get("vcUserId") != $userid) { echo "false"; return; }
		if($this->MemberModel->get("cPassword") != md5($password)) { echo "false"; return; }

		$this->MemberModel->doLogin();
		echo "ok";
	}

	/*
	@desc 회원 가입 시 ID가 존재 하는지
	*/
	public function isExists(){
		$userid = $this->input->get("id");
		$this->load->model('MemberModel');
		$member = $this->MemberModel->_getMemberById($userid);
		if($member == false) { echo "ok"; return; }
		echo "false";
	}

	/*
	@desc 회원 가입 체크
	*/
	public function join(){
		$userid = $this->input->post("userid");
		$password = $this->input->post("password");
		$password2 = $this->input->post("password2");
		$nickname = $this->input->post("nickname");
		$email = $this->input->post("email");
		$data = array("vcUserId"=>$userid,"cPassword"=>md5($password),"vcNickname"=>$nickname,"vcEmail"=>$email);
		$this->load->model('MemberModel');
		foreach($data as $key => $val)
			$this->MemberModel->set($key,$val);
		$result = $this->MemberModel->doJoin();
		if(!$result) { echo "error"; return; }
		echo "ok";
	}

	/*
	@desc 로그아웃
	*/
	public function logout(){
		$this->load->model('MemberModel');
		$this->MemberModel->doLogout();
	}
}
