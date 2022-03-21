<?php
class CompanyModel extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }
    public function getdata($tableName)
    {
        $query=$this->db->get($tableName);
        return $query->result_array();
    }
    public function insert($tblName, $data) {      
        
        $this->db->insert($tblName, $data);
        return $this->db->insert_id();
    }
    public function update($tblName, $data, $id) {
        $this->db->where('id', $id);
        return $this->db->update($tblName, $data);  
    }

    public function getRecordByDistributorCode($tblName, $code) {
        $this->db->where('code', $code);
        $this->db->where('status', 1);
        $this->db->from($tblName);
        $data = $this->db->get();
        return $data->result_array();
    }

    public function show($tblName) {
        $query = $this->db->get($tblName);
        return $query->result_array();    
    }
     public function delete($tblName,$id)
     {
        $this->db->where('id',$id);
        return $this->db->delete($tblName,array('id'=>$id));
    }
    public function load($tblName, $id) {
        $this->db->where('id', $id);
        $query = $this->db->get($tblName);
        return $query->result_array();   
    }
}
?>