<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CashBookController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('CashBookModel');
        date_default_timezone_set('Asia/Kolkata');
         ini_set('memory_limit', '-1');
    }
    public function index()
    {
        $data['employee']=$this->CashBookModel->getdata('employee');
        $data['bills']=$this->CashBookModel->getdata('bills');
        $this->load->view('CurrentDayBookView',$data);
    }
    public function PastDayBook()
    {
        $data['company']=$this->CashBookModel->getdata('company');
        $this->load->view('PastDayBookView',$data);
    }
    public function PeroidDayBook()
    {
        $data['company']=$this->CashBookModel->getdata('company');
        $this->load->view('PeroidDayBookView',$data);
    }
    public function EVauchers()
    {
        $data['company']=$this->CashBookModel->getdata('company');
        $this->load->view('EVauchersView',$data);
    }

    
    
    
}
