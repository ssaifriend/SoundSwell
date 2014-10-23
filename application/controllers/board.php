<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 게시판 관련 controller
*/
class Board extends CI_Controller {
	private $titleList = array("free"=>"게시판","request"=>"BMS 요청");
	public function __construct(){
		parent::__construct();
	}

	public function _remap($method, $params = array()){
		if(method_exists($this,$method))
			call_user_func_array(array($this, $method), $params);
		else {
			array_unshift($params,$method);
			call_user_func_array(array($this, "board"), $params);
		}
	}

	public function board($board,$articleNo = null){
		if($articleNo != null) return $this->view($board, $articleNo);
		$page = $this->input->get("page");
		if(!$page) $page = 1;
		$start = ($page-1) * 15;

		$this->load->model("BoardModel");
		$list = $this->BoardModel->getArticleList($board,array(array("emDel","N")),array("nSeqNo","desc"),$start);
		if($start==0) {
			$noticeList = $this->BoardModel->getArticleList($board,array(array("emDel","N"),array("emNotice","Y")),array("nSeqNo","desc"));
			$list = array_merge($noticeList,$list);
		}

		$data = array();
		$data['list'] = $list;
		$data['total'] = $this->BoardModel->countWithCondition(array(array("emDel","N")));
		$data['page'] = $page;
		$data['board'] = $board;
		$data['title'] = $this->titleList[$board];

		$this->load->view("board/list",$data);
	}

	public function write($board){
		if($this->input->post("articleNo")) return $this->edit($board);
		$this->load->model("BoardModel");
		$data= array();
		$data['vcTitle'] = htmlentities($this->input->post("title"));
		$data['tContents'] = htmlentities($this->input->post("contents"));
		$data['nUserNo'] = $this->session->userdata("memberNo");
		$this->load->model("MemberModel");
		$this->MemberModel->_getMemberByNo($data['nUserNo']);
		$data['vcNickname'] = $this->MemberModel->get("vcNickname");
		$data['dtRegdate'] = date("Y-m-d H:i:s");
		$idx = $this->BoardModel->insertArticle($board,$data);
		if($idx>0) echo "/board/".$board."/".$idx;
		else echo "false";
	}

	public function edit($board){
		$this->load->model("BoardModel");
		$articleNo = $this->input->post("articleNo");
		$article = $this->BoardModel->getArticle($board,$articleNo);
		if($article->nUserNo != $this->session->userdata("memberNo")){
			echo "false"; return;
		}
		$data = array();
		$data['vcTitle'] = htmlentities($this->input->post("title"));
		$data['tContents'] = htmlentities($this->input->post("contents"));
		$result = $this->BoardModel->updateArticle($board,$articleNo,$data);
		if($result) echo "/board/".$board."/".$articleNo;
		else echo "false";
	}

	public function delete($board){
		$this->load->model("BoardModel");
		$articleNo = $this->input->post("articleNo");
		$article = $this->BoardModel->getArticle($board,$articleNo);
		if($article->nUserNo != $this->session->userdata("memberNo")){
			echo "false"; return;
		}
		$result = $this->BoardModel->deleteArticle($board,$articleNo);
		if($result) echo "ok";
		else echo "false";
	}

	public function view($board,$articleNo){
		$this->load->model("BoardModel");
		$article = $this->BoardModel->getArticle($board,$articleNo);
		if($article == false || $article->emDel == "Y"){
			$this->load->view("board/error",array("error"=>"글이 존재하지 않습니다."));
			return;
		}

		$data = array();
		$data['article'] = $article;
		$data['board'] = $board;
		$data['memberNo'] = $this->session->userdata("memberNo");
		$data['load'] = $this->load;
		$data['title'] = $this->titleList[$board];
		$data['comments'] = $this->BoardModel->getCommentList($board,$articleNo);
		$this->db->set("nHit","`nHit`+1",false)->where("nSeqNo",$articleNo)->update("article");

		$this->load->view("board/view",$data);
	}

	public function writecomment($board){
		if($this->input->post("commentNo")) return $this->editcomment($board);
		$this->load->model("BoardModel");
		$data= array();
		$data['vcContents'] = htmlentities($this->input->post("contents"));
		$data['nUserNo'] = $this->session->userdata("memberNo");
		$this->load->model("MemberModel");
		$this->MemberModel->_getMemberByNo($data['nUserNo']);
		$data['vcNickname'] = $this->MemberModel->get("vcNickname");
		$data['dtRegdate'] = date("Y-m-d H:i:s");
		$idx = $this->BoardModel->insertComment($board,$this->input->post("articleNo"),$data);
		if($idx>0) echo "ok";
		else echo "false";
	}

	public function delcomment($board){
		$this->load->model("BoardModel");
		$record = $this->BoardModel->getComment($this->input->post("seq"));
		if($record->nUserNo != $this->session->userdata("memberNo")) {
			echo "false"; return;
		}
		$result = $this->BoardModel->deleteComment($board,$this->input->post("articleNo"),$this->input->post("seq"));
		if($result) echo "ok";
		else echo "false";
	}

	public function editcomment($board){
		$this->load->model("BoardModel");
		$record = $this->BoardModel->getComment($this->input->post("commentNo"));
		if($record->nUserNo != $this->session->userdata("memberNo")) {
			echo "false"; return;
		}
		$data = array();
		$data['vcContents'] = $this->input->post("contents");
		$result = $this->BoardModel->updateComment($board,$this->input->post("articleNo"),$this->input->post("commentNo"),$data);
		if($result) echo "ok";
		else echo "false";
	}
}
