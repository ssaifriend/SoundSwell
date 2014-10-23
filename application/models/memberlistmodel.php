<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 회원 목록을 뽑아오기 위한 class
*/
class MemberListModel extends CI_Model {
    private $record = array();
    private $currentIdx = 0;
    private $total = 0;

    public function __construct(){
        parent::__construct();
    }

    function getMemeberList($condition ,$order=array() ,$start=0 ,$limit=15){
        $this->db->trans_start();
        foreach($condition as $key => $val){ //조건값 적용
            $this->db->where($key,$val);
        }
        if($order[0]) $this->db->order_by($order[0],$order[1]); //정렬
        $this->db->limit($limit, $start); //일정 갯수만 가져옴
        
        $result = $this->db->get("member");
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
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