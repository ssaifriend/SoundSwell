<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc IR 랭킹 관련 model, memberlistmodel과 비슷하므로 필요한것만 주석담
*/
class RankModel extends CI_Model {
    private $record = array();
    private $currentIdx = 0;
    private $total = 0;

    public function __construct(){
        parent::__construct();
    }

	/*
	@desc 순수 랭킹데이터만 테이블에서 가져오고자 할 때 사용
	*/
    function load($condition, $order=array(), $start=0, $limit=10){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        if($order[0]) $this->db->order_by($order[0],$order[1]);
        $this->db->limit($limit, $start);

        $result = $this->db->get("rank");
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
    }

	/*
	@desc 닉네임을 포함한 데이터를 가져오고자 할 때 사용
	*/
    function loadWithUser($condition, $order=array(), $start=0, $limit=10){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        if($order[0]) $this->db->order_by($order[0],$order[1]);
        $this->db->limit($limit, $start);
        $this->db->select("rank.*, member.vcNickName");
        $this->db->join("member","member.nSeqNo = rank.nUserNo");

        $result = $this->db->get("rank");
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
    }

	/*
	@desc join 등 복잡한 쿼리문 사용 할 때 사용
	*/
    function loadSetColumn($columns, $table, $condition, $join=array(), $order=array(), $start=0, $limit=10){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        for($a=0,$loopa=sizeof($order); $a<$loopa; $a++){
            $this->db->order_by($order[$a][0],$order[$a][1]);
        }
        $this->db->limit($limit, $start);
        for($a=0,$loopa=sizeof($columns); $a<$loopa; $a++){
            if(is_array($columns[$a])) $this->db->select($columns[$a][0],$columns[$a][1]);
            else $this->db->select($columns[$a]);
        }
        for($a=0,$loopa=sizeof($join); $a<$loopa; $a++){
            $this->db->join($join[$a][0],$join[$a][1]);
        }
        $this->db->from($table);

        $result = $this->db->get();
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
    }

	/*
	@desc 총 갯수 확인 함수
	*/
    function countWithCondition($condition){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        $result = $this->db->count_all_results("rank");
        $this->db->trans_complete();
        return $result;
    }

    function insertRecord($info){
        $this->db->trans_start();
        $this->db->insert("rank",$info);
        $res = $this->db->insert_id();
        $this->db->trans_complete();
        return $res;
    }

    function updateRecord($info,$nSeqNo){
        $this->db->trans_start();
        $this->db->set($info);
        $this->db->where("nSeqNo",$nSeqNo);
        $this->db->update("rank");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }

    function deleteRecord($nSeqNo){
        $this->db->trans_start();
        $this->db->where("nSeqNo",$nSeqNo)->delete("rank");
        $res = $this->db->affected_rows();
        $this->db->trans_complete();
        if($res==0) return false;
        return true;
    }

    function get(){
        return $this->record[$this->currentIdx];
    }

    function prev(){
        if($this->currentIdx == 0) return false;
        $this->currentIdx--;
        return true;
    }

    function next(){
        if($this->currentIdx+1 == $this->total) return false;
        $this->currentIdx++;
        return true;
    }

    function isLast(){
        if($this->currentIdx+1 == $this->total) return true;
        return false;
    }

    function isFirst(){
        if($this->currentIdx == 0) return true;
        return false;
    }

    function first(){
        $this->currentIdx = 0;
    }

    function last(){
        $this->currentIdx = $this->total - 1;
    }

    function go($idx){
        if($idx < 0 || $idx + 1 >= $this->total) return false;
        $this->currentIdx = $idx;
        return true;
    }

    function getTotal(){
        return $this->total;
    }

    function getCurrent(){
        return $this->currentIdx;
    }
}