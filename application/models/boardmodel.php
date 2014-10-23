<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 게시판 작성에 쓰이는 class
*/
class BoardModel extends CI_Model {
    private $allowBoard = array("free","request");
    public function __construct(){
        parent::__construct();
    }

	/*
	@desc 하나의 글을 가져오는 함수
	*/
    public function getArticle($board,$articleNo){
        $this->db->trans_start();
        $result = $this->db->where("vcBoard",$board)->where("nSeqNo",$articleNo)->get("article");
        $this->db->trans_complete();
        if($result->num_rows()==0) return false;
        return $result->row();
    }

	/*
	@desc 글 목록을 가져오는 함수
	*/
    public function getArticleList($board,$condition = array(),$order=array(),$start=0,$limit=15){
        $this->db->trans_start();
        $this->db->where("vcBoard",$board);
        for($a=0,$loopa=sizeof($condition); $a<$loopa; $a++){
            if(sizeof($condition[$a])==2) $this->db->where($condition[$a][0],$condition[$a][1]);
            else if($condition[$a][1] == "like") $this->db->like($condition[$a][0],$condition[$a][2],(isset($condition[$a][3])?$condition[$a][3]:"both"));
        }
        $this->db->select("*")->select("( select count(`nSeqNo`) as `cmt` from `article_comment` where `article`.`nSeqNo`=`article_comment`.`nArticleNo` and `article_comment`.`emDel`='N') as `cmt`",false);
        if(sizeof($order)!=0) $this->db->order_by($order[0],$order[1]);
        $this->db->limit($limit,$start);
        $result = $this->db->get("article");
        $this->db->trans_complete();
        if($result->num_rows()==0) return array();
        return $result->result();
    }

	/*
	@desc 총 갯수 추출을 위한 함수
	*/
    function countWithCondition($condition){
        $this->db->trans_start();
        for($a=0,$loopa=sizeof($condition); $a<$loopa; $a++){
            if(sizeof($condition[$a])==2) $this->db->where($condition[$a][0],$condition[$a][1]);
            else if($condition[$a][1] == "like") $this->db->like($condition[$a][0],$condition[$a][2],(isset($condition[$a][3])?$condition[$a][3]:"both"));
        }
        $result = $this->db->count_all_results("article");
        $this->db->trans_complete();
        return $result;
    }

	/*
	@desc 하나의 코멘트를 가져오기 위한 함수
	*/
    public function getComment($commentNo){
        $this->db->trans_start();
        $result = $this->db->where("nSeqNo",$commentNo)->get("article_comment");
        $this->db->trans_complete();
        if($result->num_rows()==0) return false;
        return $result->row();
    }

	/*
	@desc 특정 글의 코멘트 목록을 가져오기 위한 함수
	*/
    public function getCommentList($board,$articleNo){
        if($this->getArticle($board,$articleNo) == false) return array();
        $this->db->trans_start();
        $this->db->where("nArticleNo",$articleNo);
		$this->db->where("emDel","N");
        $this->db->order_by("nSeqNo","asc");
        $result = $this->db->get("article_comment");
        $this->db->trans_complete();
        if($result->num_rows()==0) return array();
        return $result->result();
    }

    public function insertArticle($board, $data){
        if(!in_array($board,$this->allowBoard)) return false;
        $data['vcBoard'] = $board;
        $this->db->trans_start();
        $this->db->set($data)->insert("article");
        $res = $this->db->insert_id();
        $this->db->trans_complete();
        return $res;
    }

    public function updateArticle($board, $articleNo, $data){
        if(!in_array($board,$this->allowBoard)) return false;
        $this->db->trans_start();
        $this->db->set($data)->where("nSeqNo",$articleNo)->update("article");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }

    public function deleteArticle($board, $articleNo){
        if(!in_array($board,$this->allowBoard)) return false;
        $this->db->trans_start();
        $this->db->set(array("emDel"=>"Y"))->where("nSeqNo",$articleNo)->update("article");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }

    public function insertComment($board, $articleNo, $data){
        if($this->getArticle($board,$articleNo) == false) return false;
        $data['nArticleNo'] = $articleNo;
        $this->db->trans_start();
        $this->db->set($data)->insert("article_comment");
        $res = $this->db->insert_id();
        $this->db->trans_complete();
        return $res;
    }

    public function updateComment($board, $articleNo, $commentNo, $data){
        if($this->getArticle($board,$articleNo) == false) return false;
        $this->db->trans_start();
        $this->db->set($data)->where("nSeqNo",$commentNo)->update("article_comment");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }

    public function deleteComment($board, $articleNo, $commentNo){
        if($this->getArticle($board,$articleNo) == false) return false;
        $this->db->trans_start();
        $this->db->set(array("emDel"=>"Y"))->where("nSeqNo",$commentNo)->update("article_comment");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }
}