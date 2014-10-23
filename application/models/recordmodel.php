<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RecordModel extends CI_Model {
    private $record = array();
    private $currentIdx = 0;
    private $total = 0;

    public function __construct(){
        parent::__construct();
    }

    function load($condition, $order=array(), $start=0, $limit=10){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        if($order[0]) $this->db->order_by($order[0],$order[1]);
        $this->db->limit($limit, $start);

        $result = $this->db->get("record");
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
    }

    function loadWithUser($condition, $order=array(), $start=0, $limit=10){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        if($order[0]) $this->db->order_by($order[0],$order[1]);
        $this->db->limit($limit, $start);
        $this->db->select("record.*, member.vcNickName");
        $this->db->join("member","member.nSeqNo = record.nUserNo");

        $result = $this->db->get("record");
        $this->db->trans_complete();
        $this->record = array();
        foreach($result->result() as $record){
            $this->record[] = $record;
        }
        $this->currentIdx = 0;
        $this->total = sizeof($this->record);
    }

    function insertRecord($info,$normal,$long){
        $long['emType'] = 'L';
        $normal['emType'] = 'N';
        $this->db->trans_start();
        $this->db->insert("record_detail",$long);
        $info['nLongDetailNo'] = $this->db->insert_id();
        $this->db->insert("record_detail",$normal);
        $info['nNormalDetailNo'] = $this->db->insert_id();
        $this->db->insert("record",$info);
        $res = $this->db->insert_id();
        $this->db->trans_complete();
        return $res;
    }

    function countWithCondition($condition){
        $this->db->trans_start();
        foreach($condition as $key => $val){
            $this->db->where($key,$val);
        }
        $result = $this->db->count_all_results("record");
        $this->db->trans_complete();
        return $result;
    }

    function deleteRecord($nSeqNo){
        $this->db->trans_start();
        $data = $this->db->where("nSeqNo",$nSeqNo)->get("record")->result();
        $this->db->where("nSeqNo",$nSeqNo)->delete("record");
        $this->db->where_in("nSeqNo",array($data->nNormalDetailNo,$data->nLongDetailNo))->delete("record_detail");
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