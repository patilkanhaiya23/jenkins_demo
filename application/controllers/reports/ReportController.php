<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
class ReportController extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('ReportModel');
        date_default_timezone_set('Asia/Kolkata');
         ini_set('memory_limit', '-1');
    }

    public function collectionReport()
    {
        $data['company']=$this->ReportModel->getdata('company');
        $this->load->view('reports/collectionwiseReportView',$data);
    }

    public function billwiseReport()
    {
        $data['company']=$this->ReportModel->getdata('company');
        $this->load->view('reports/billwiseCollectionView',$data);
    }

    public function billwiseReportData(){
        $fromDate=$this->input->post('fromDate');
        $toDate=$this->input->post('toDate');
        $category=$this->input->post('category');
        $company=$this->input->post('company');
        if($category=="summary"){
            $this->exportBillwiseDataToExcelSummary($fromDate,$toDate,$company);
        }else{
            $this->exportBillwiseDataToExcelDetail($fromDate,$toDate,$company);
        }
    }

    public function collectionwiseReportData(){
        $fromDate=$this->input->post('fromDate');
        $toDate=$this->input->post('toDate');
        $category=$this->input->post('category');
        $company=$this->input->post('company');

        if($category=="summary"){
            $this->exportCollectionwiseDataToExcelSummary($fromDate,$toDate,$company);
        }else{
            $this->exportCollectionwiseDataToExcelDetail($fromDate,$toDate,$company);
        }            
    }

    public function exportBillwiseDataToExcelSummary($fromDate,$toDate,$company){
        $newFileName='bill_wise_collection.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        foreach (range('A','J') as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->SetCellValue('B1', 'Billwise Details')->getStyle('B1:C1')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
        $spreadsheet->getActiveSheet()->getStyle("B1:C1")->getFont()->setBold( true );
        
        $spreadsheet->getActiveSheet()->SetCellValue('B2', $fromDate.' '.$toDate)->getStyle('B2:C2')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
        $spreadsheet->getActiveSheet()->getStyle("B2:C2")->getFont()->setBold( true );

        //Define outline border to the cells
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->SetCellValue('B4',' Billwise Collection Report - '.$fromDate.' - '.$toDate)->getStyle('B4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B4',$bankDetails[0]['distributorName'].' - Cheque Deposit Slip - '.$dateformat)->getStyle('B4:G4')->getFont()->setSize(16);
        $spreadsheet->getActiveSheet()->mergeCells("B4:G4");
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        // $spreadsheet->getActiveSheet()->getColumnDimension("B4:G4")->setAutoSize(true);

        $spreadsheet->getActiveSheet()->SetCellValue('C4', '');
        $spreadsheet->getActiveSheet()->getStyle("C4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D4', '');
        $spreadsheet->getActiveSheet()->getStyle("D4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E4', '');
        $spreadsheet->getActiveSheet()->getStyle("E4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F4', '');
        $spreadsheet->getActiveSheet()->getStyle("F4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G4', '');
        $spreadsheet->getActiveSheet()->getStyle("G4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Bills')->getStyle('B5:C5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Cheques')->getStyle('B5:C5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->applyFromArray($styleArray);
        
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("E5:F5");
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->getStyle("G5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G5")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $spreadsheet->getActiveSheet()->SetCellValue('B6', 'S. No.')->getStyle('B6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("B6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('C6', 'Bill No')->getStyle('C6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("C6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("C6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D6', 'Date')->getStyle('D6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("D6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("D6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E6', 'Retailer')->getStyle('E6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("E6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F6', 'Route')->getStyle('F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("F6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("F6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G6', 'Salesman')->getStyle('G6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("G6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('H6', 'Amount')->getStyle('H6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("H6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("H6")->applyFromArray($styleArray);

        $no=0;
        $rowCount = 7;
        $billDetails=array();
        if($company=="General"){
            $billDetails=$this->ReportModel->getBillsDetailsUsingDates('bills',$fromDate,$toDate);
        }else{
            $billDetails=$this->ReportModel->getBillsDetailsUsingDatesWithCompany('bills',$fromDate,$toDate,$company);
        }
        
        foreach ($billDetails as $element) {
        
            $newDate = date("d-M-Y", strtotime($element['bdate']));

            $no++;
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $rowCount, $no);
            $spreadsheet->getActiveSheet()->getStyle('B' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $rowCount, $element['billNo']);
            $spreadsheet->getActiveSheet()->getStyle('C' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $rowCount, $newDate);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $rowCount, $element['rtname']);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $rowCount,$element['routeName']);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('G' . $rowCount, $element['salesman']);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            $spreadsheet->getActiveSheet()->SetCellValue('H' . $rowCount, $element['paidAmount']);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $rowCount++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName=$newFileName;
        
        // $upload_path='./assets/deliveryslips/'.$fileName;
        // $writer->save($upload_path);
        // echo $newFileName;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

    public function exportBillwiseDataToExcelDetail($fromDate,$toDate,$company){
        $newFileName='bill_wise_collection.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        foreach (range('A','J') as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->SetCellValue('B1', 'Billwise Details')->getStyle('B1:C1')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
        $spreadsheet->getActiveSheet()->getStyle("B1:C1")->getFont()->setBold( true );
        
        $spreadsheet->getActiveSheet()->SetCellValue('B2', $fromDate.' '.$toDate)->getStyle('B2:C2')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
        $spreadsheet->getActiveSheet()->getStyle("B2:C2")->getFont()->setBold( true );

        //Define outline border to the cells
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->SetCellValue('B4',' Billwise Collection Report - '.$fromDate.' - '.$toDate)->getStyle('B4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B4',$bankDetails[0]['distributorName'].' - Cheque Deposit Slip - '.$dateformat)->getStyle('B4:G4')->getFont()->setSize(16);
        $spreadsheet->getActiveSheet()->mergeCells("B4:G4");
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        // $spreadsheet->getActiveSheet()->getColumnDimension("B4:G4")->setAutoSize(true);

        $spreadsheet->getActiveSheet()->SetCellValue('C4', '');
        $spreadsheet->getActiveSheet()->getStyle("C4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D4', '');
        $spreadsheet->getActiveSheet()->getStyle("D4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E4', '');
        $spreadsheet->getActiveSheet()->getStyle("E4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F4', '');
        $spreadsheet->getActiveSheet()->getStyle("F4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G4', '');
        $spreadsheet->getActiveSheet()->getStyle("G4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Bills')->getStyle('B5:C5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Cheques')->getStyle('B5:C5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->applyFromArray($styleArray);
        
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("E5:F5");
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->getStyle("G5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G5")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $spreadsheet->getActiveSheet()->SetCellValue('B6', 'S. No.')->getStyle('B6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("B6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('C6', 'Bill No')->getStyle('C6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("C6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("C6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D6', 'Date')->getStyle('D6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("D6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("D6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E6', 'Retailer')->getStyle('E6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("E6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F6', 'Route')->getStyle('F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("F6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("F6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G6', 'Salesman')->getStyle('G6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("G6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('H6', 'Amount')->getStyle('H6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("H6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("H6")->applyFromArray($styleArray);

        $no=0;
        $rowCount = 7;
        $billDetails=array();
        if($company=="General"){
            $billDetails=$this->ReportModel->getBillsDetailsUsingDates('bills',$fromDate,$toDate);
        }else{
            $billDetails=$this->ReportModel->getBillsDetailsUsingDatesWithCompany('bills',$fromDate,$toDate,$company);
        }
        
        foreach ($billDetails as $element) {
        
            $newDate = date("d-M-Y", strtotime($element['bdate']));

            $no++;
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $rowCount, $no);
            $spreadsheet->getActiveSheet()->getStyle('B' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $rowCount, $element['billNo']);
            $spreadsheet->getActiveSheet()->getStyle('C' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $rowCount, $newDate);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $rowCount, $element['rtname']);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $rowCount,$element['routeName']);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('G' . $rowCount, $element['salesman']);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            $spreadsheet->getActiveSheet()->SetCellValue('H' . $rowCount, $element['paidAmount']);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $rowCount++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName=$newFileName;
        
        $upload_path='./assets/deliveryslips/'.$fileName;
        $writer->save($upload_path);
        echo $newFileName;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

    public function exportCollectionwiseDataToExcelSummary($fromDate,$toDate,$company){
        $newFileName='collection_wise_report.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        foreach (range('A','J') as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->SetCellValue('B1', 'Collection wise Details')->getStyle('B1:C1')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
        $spreadsheet->getActiveSheet()->getStyle("B1:C1")->getFont()->setBold( true );
        
        $spreadsheet->getActiveSheet()->SetCellValue('B2', $fromDate.' '.$toDate)->getStyle('B2:C2')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
        $spreadsheet->getActiveSheet()->getStyle("B2:C2")->getFont()->setBold( true );

        //Define outline border to the cells
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->SetCellValue('B4',' Collection wise Report - '.$fromDate.' - '.$toDate)->getStyle('B4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B4',$bankDetails[0]['distributorName'].' - Cheque Deposit Slip - '.$dateformat)->getStyle('B4:G4')->getFont()->setSize(16);
        $spreadsheet->getActiveSheet()->mergeCells("B4:G4");
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        // $spreadsheet->getActiveSheet()->getColumnDimension("B4:G4")->setAutoSize(true);

        $spreadsheet->getActiveSheet()->SetCellValue('C4', '');
        $spreadsheet->getActiveSheet()->getStyle("C4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D4', '');
        $spreadsheet->getActiveSheet()->getStyle("D4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E4', '');
        $spreadsheet->getActiveSheet()->getStyle("E4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F4', '');
        $spreadsheet->getActiveSheet()->getStyle("F4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G4', '');
        $spreadsheet->getActiveSheet()->getStyle("G4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Bills')->getStyle('B5:C5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Cheques')->getStyle('B5:C5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->applyFromArray($styleArray);
        
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("E5:F5");
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->getStyle("G5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G5")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $spreadsheet->getActiveSheet()->SetCellValue('B6', 'S. No.')->getStyle('B6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("B6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('C6', 'Bill No')->getStyle('C6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("C6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("C6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D6', 'Date')->getStyle('D6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("D6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("D6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E6', 'Retailer')->getStyle('E6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("E6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F6', 'Route')->getStyle('F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("F6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("F6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G6', 'Salesman')->getStyle('G6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("G6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('H6', 'Amount')->getStyle('H6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("H6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("H6")->applyFromArray($styleArray);

        $no=0;
        $rowCount = 7;
        $billDetails=array();
        if($company=="General"){
            $billDetails=$this->ReportModel->getCollectionsDetailsUsingDates('bills',$fromDate,$toDate);
        }else{
            $billDetails=$this->ReportModel->getCollectionsDetailsUsingDatesWithCompany('bills',$fromDate,$toDate,$company);
        }

        foreach ($billDetails as $element) {
            $newDate = date("d-M-Y", strtotime($element[0]['bdate']));

            $no++;
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $rowCount, $no);
            $spreadsheet->getActiveSheet()->getStyle('B' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $rowCount, $element['billNo']);
            $spreadsheet->getActiveSheet()->getStyle('C' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $rowCount, $newDate);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $rowCount, $element['rtname']);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $rowCount,$element['routeName']);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('G' . $rowCount, $element['salesman']);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            $spreadsheet->getActiveSheet()->SetCellValue('H' . $rowCount, $element['paidAmount']);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $rowCount++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName=$newFileName;
        
        // $upload_path='./assets/deliveryslips/'.$fileName;
        // $writer->save($upload_path);
        // echo $newFileName;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

    public function exportCollectionwiseDataToExcelDetail($fromDate,$toDate,$company){
        $newFileName='collection_wise_report.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        foreach (range('A','J') as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->SetCellValue('B1', 'Collection wise Details')->getStyle('B1:C1')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
        $spreadsheet->getActiveSheet()->getStyle("B1:C1")->getFont()->setBold( true );
        
        $spreadsheet->getActiveSheet()->SetCellValue('B2', $fromDate.' '.$toDate)->getStyle('B2:C2')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
        $spreadsheet->getActiveSheet()->getStyle("B2:C2")->getFont()->setBold( true );

        //Define outline border to the cells
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->SetCellValue('B4',' Collection wise Report - '.$fromDate.' - '.$toDate)->getStyle('B4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B4',$bankDetails[0]['distributorName'].' - Cheque Deposit Slip - '.$dateformat)->getStyle('B4:G4')->getFont()->setSize(16);
        $spreadsheet->getActiveSheet()->mergeCells("B4:G4");
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle("B4:G4")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        // $spreadsheet->getActiveSheet()->getColumnDimension("B4:G4")->setAutoSize(true);

        $spreadsheet->getActiveSheet()->SetCellValue('C4', '');
        $spreadsheet->getActiveSheet()->getStyle("C4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D4', '');
        $spreadsheet->getActiveSheet()->getStyle("D4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E4', '');
        $spreadsheet->getActiveSheet()->getStyle("E4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F4', '');
        $spreadsheet->getActiveSheet()->getStyle("F4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G4', '');
        $spreadsheet->getActiveSheet()->getStyle("G4")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Bills')->getStyle('B5:C5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        // $spreadsheet->getActiveSheet()->SetCellValue('B5', 'No. of Cheques')->getStyle('B5:C5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B5:C5")->applyFromArray($styleArray);
        
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('E5', 'Total Amount')->getStyle('E5:F5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->mergeCells("E5:F5");
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E5:F5")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->SetCellValue('G5', 'Rs. ')->getStyle('G5')->getFont()->setSize(13);
        $spreadsheet->getActiveSheet()->getStyle("G5")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G5")->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->getActiveSheet()->getStyle('G5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $spreadsheet->getActiveSheet()->SetCellValue('B6', 'S. No.')->getStyle('B6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("B6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("B6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('C6', 'Bill No')->getStyle('C6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("C6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("C6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('D6', 'Date')->getStyle('D6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("D6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("D6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('E6', 'Retailer')->getStyle('E6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("E6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("E6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('F6', 'Route')->getStyle('F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("F6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("F6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('G6', 'Salesman')->getStyle('G6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("G6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("G6")->applyFromArray($styleArray);

        $spreadsheet->getActiveSheet()->SetCellValue('H6', 'Amount')->getStyle('H6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d1caca');
        $spreadsheet->getActiveSheet()->getStyle("H6")->getFont()->setBold( true );
        $spreadsheet->getActiveSheet()->getStyle("H6")->applyFromArray($styleArray);

        $no=0;
        $rowCount = 7;
        $billDetails=array();
        if($company=="General"){
            $billDetails=$this->ReportModel->getCollectionsDetailsUsingDates('bills',$fromDate,$toDate);
        }else{
            $billDetails=$this->ReportModel->getCollectionsDetailsUsingDatesWithCompany('bills',$fromDate,$toDate,$company);
        }

        foreach ($billDetails as $element) {
            $newDate = date("d-M-Y", strtotime($element[0]['bdate']));

            $no++;
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $rowCount, $no);
            $spreadsheet->getActiveSheet()->getStyle('B' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $rowCount, $element['billNo']);
            $spreadsheet->getActiveSheet()->getStyle('C' . $rowCount)->applyFromArray($styleArray);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $rowCount, $newDate);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('D' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $rowCount, $element['rtname']);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('E' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $rowCount,$element['routeName']);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        
            $spreadsheet->getActiveSheet()->SetCellValue('G' . $rowCount, $element['salesman']);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('G' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            $spreadsheet->getActiveSheet()->SetCellValue('H' . $rowCount, $element['paidAmount']);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('H' . $rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $rowCount++;
        }
        $writer = new Xlsx($spreadsheet);
        $fileName=$newFileName;
        
        $upload_path='./assets/deliveryslips/'.$fileName;
        $writer->save($upload_path);
        echo $newFileName;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

}