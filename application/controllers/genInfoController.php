<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/6/2018
 * Time: 9:47 AM
 */
class genInfoController extends MY_Controller{

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $this->load->model('periodicsem');
        $this->load->model('schoolsemester');
    }

    public function allSemester()
    {
        $periodicSemesters = $this->periodicsem->periodicSemeser();
        $data = [];

        if (!empty($periodicSemesters)){
            foreach ($periodicSemesters as $period){
                $data[] = [
                    $period->school_year,
                    ucwords($period->semester),
                    date('M d, Y', strtotime($period->semester_start)),
                    date('M d, Y', strtotime($period->semester_end)),
                    ucwords($period->period),
                    date('M d, Y', strtotime($period->period_start)),
                    date('M d, Y', strtotime($period->period_end)),
                ];
            }
        }

        echo json_encode(['data'=>$data]);
    }

    public function storePeriod()
    {
        if ( $this->input->server('REQUEST_METHOD') == 'POST'){

            $data = $this->input->post();
            $semesterDate = explode('-',$data['date_semester']);
            $semesterDateStart = date('Y-m-d',strtotime($semesterDate[0]));
            $semesterDateEnd = date('Y-m-d',strtotime($semesterDate[1]));

            $periodDate = explode('-', $data['date_period']);
            $periodDateStart = date('Y-m-d', strtotime($periodDate[0]));
            $periodDateEnd= date('Y-m-d', strtotime($periodDate[1]));

            $school_year = $data['school_year'];
            $semester = $data['semester'];

            $sy = new SchoolSemester();
            $syResult = $sy->search(['school_year'=>$school_year, 'semester'=>$semester]);

            if (empty($syResult)) {
                $schoolSemester = new SchoolSemester();
                $schoolSemester->school_year = $data['school_year'];
                $schoolSemester->semester = $data['semester'];
                $schoolSemester->date_start = $semesterDateStart;
                $schoolSemester->date_end = $semesterDateEnd;
                $schoolSemester->save();

                $schoolSemesterId = $schoolSemester->db->insert_id();
            }
            else{
                foreach ($syResult as $result){
                    $schoolSemesterId= $result->ss_id;
                }
            }

            $periodicSemester = new PeriodicSem();
            $periodicSemester->ss_id = $schoolSemesterId;
            $periodicSemester->periodic_id = $data['period_id'];
            $periodicSemester->date_start = $periodDateStart;
            $periodicSemester->date_end = $periodDateEnd;
            $periodicSemester->save();

        }

    }


}