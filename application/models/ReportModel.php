<?php
class ReportModel extends CI_Model {

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

    public function getBillsDetailsUsingDates($tblName,$fromDate,$toDate){
        $this->db->select('billpayments.*,bills.id as bid,bills.date as bdate,bills.billNo,bills.retailerName as rtname,bills.routeName,bills.salesman');
        $this->db->join('billpayments','billpayments.billId=bills.id');
        $this->db->where('bills.date >=', $fromDate);
        $this->db->where('bills.date <=', $toDate);
        $this->db->where('billpayments.paidAmount >',0);
        $query = $this->db->get($tblName);
        return $query->result_array();  
    }

    public function getBillsDetailsUsingDatesWithCompany($tblName,$fromDate,$toDate,$company){
        $this->db->select('billpayments.*,bills.id as bid,bills.date as bdate,bills.billNo,bills.retailerName as rtname,bills.routeName,bills.salesman');
        $this->db->join('billpayments','billpayments.billId=bills.id');
        $this->db->where('bills.date >=', $fromDate);
        $this->db->where('bills.date <=', $toDate);
        $this->db->where('bills.compName', $company);
        $this->db->where('billpayments.paidAmount >',0);
        $query = $this->db->get($tblName);
        return $query->result_array();  
    }

    public function getCollectionsDetailsUsingDates($tblName,$fromDate,$toDate){
        $this->db->select('billpayments.*,bills.id as bid,bills.date as bdate,bills.billNo,bills.retailerName as rtname,bills.routeName,bills.salesman');
        $this->db->join('billpayments','billpayments.billId=bills.id');
        $this->db->where('DATE(billpayments.date) >=', $fromDate);
        $this->db->where('DATE(billpayments.date) <=', $toDate);
        $this->db->where('billpayments.paidAmount >',0);
        $query = $this->db->get($tblName);
        return $query->result_array();  
    }

    public function getCollectionsDetailsUsingDatesWithCompany($tblName,$fromDate,$toDate,$compName){
        $this->db->select('billpayments.*,bills.id as bid,bills.date as bdate,bills.billNo,bills.retailerName as rtname,bills.routeName,bills.salesman');
        $this->db->join('billpayments','billpayments.billId=bills.id');
        $this->db->where('DATE(billpayments.date) >=', $fromDate);
        $this->db->where('DATE(billpayments.date) <=', $toDate);
        $this->db->where('billpayments.paidAmount >',0);
        $this->db->where('bills.compName',$compName);
        $query = $this->db->get($tblName);
        return $query->result_array();  
    }
}
?>