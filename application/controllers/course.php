<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course extends MY_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Subject');
    $this->load->model('Room_list');
    $this->load->model("Sched_subj");
    $this->load->model("Sched_day");
    $this->load->model("Block_section");
    $this->load->model("Block_section_subjects");
    $this->load->model("Plotted_time");
    $this->load->model("Plotted_schedule");
    $this->load->model("Program_list");
    $this->load->model('Sched_time');
    $this->load->model('Curr_codelist');
    $this->load->model('Subj_sched_day');

      if ( $this->userType() != 'dean' && $this->userType() != 'admin' ){
          $data['title'] = 'Access Forbidden';
          $this->load->view('errors/error_403',$data);
          exit();
      }

  }

  public function index() 
  {

    $time = new Sched_time();
    $plot = new Plotted_time();
    $program = new Program_list();
    $subject = new Subject();
    $sched_day = new Sched_day();
    $block_section = new Block_section();

    $this->undo_schedule();

    $time->load_last_input();

    $plotted = $plot->search(["st_id" => $time->st_id]);

    $data['time'] = $time;
    $data['block_section'] = $block_section->get();
    $data['programList'] = $program->get_by_user();
    $data['lectureRooms'] = $this->getRooms("Lecture");
    $data['labRooms'] = $this->getRooms("Laboratory");
    $data['subjectList'] = $subject->all();
    $data['plotted'] = $plotted;
    $data['sched_day'] = $sched_day->get();
    $data['section_school_year'] = $block_section->schoolYear();
    $data['title'] = 'Course Schedule';

    $this->load->view('includes/header', $data);
    $this->load->view('includes/menu');
    $this->load->view('course/css');
    $this->load->view('course/index', $data);
    $this->load->view('includes/footer');
    $this->load->view('includes/js');
    $this->load->view('course/js');
  }

  public function saveOtherSection()
  {
      if($this->input->method() =='post'){
          $request = $this->input->post('data');

          $schedule = new Sched_subj();
          $existing_schedule = $schedule->existingSchedule(['ss_id'=>$request['ss_id']]);

          $subj_id = $ss_id = null;

          if (!empty($existing_schedule)){
              foreach ($existing_schedule as $result){
                  $search_results = $schedule->search(['ss_id'=>$result->subj_id, 'bs_id'=>$request['bs_id']]);

                  if (empty($search_results)){
                      $s = new Sched_subj();
                      $s->year_lvl = $result->year_lvl;
                      $s->subj_id = $result->subj_id;
                      $s->bs_id = $request['bs_id'];
                      $s->avs_status = 'active';
                      $s->sem = $result->sem;
                      $s->sy = $result->sy;
                      $s->save();

                      $ss_id = $s->db->insert_id();
                      $subj_id= $result->subj_id;
                  }
                  break;
              }
              foreach ($existing_schedule as $result1){
                  $sub_sched_day = new Subj_sched_day();
                  $sub_sched_day->time_start = $result1->time_start;
                  $sub_sched_day->time_end = $result1->time_end;
                  $sub_sched_day->user_id = $result1->user_id;
                  $sub_sched_day->user_id = $result1->user_id;
                  $sub_sched_day->rl_id = $result1->rl_id;
                  $sub_sched_day->sd_id= $result1->sd_id;
                  $sub_sched_day->type = $result1->type;
                  $sub_sched_day->ss_id= $ss_id;
                  $sub_sched_day->save();
              }
              $block_section_subject = new Block_section_subjects();
              $block_section_result = $block_section_subject->search(['bs_id'=>$request['bs_id'], 'subj_id'=>$subj_id]);
                    if (!empty($block_section_result)){
                        foreach ($block_section_result as $result){
                            $block_section_subject->load($result->bss_id);
                            $block_section_subject->status = 1;
                            $block_section_subject->save();
                        }
                    }
          }
          echo true;
      }
  }

  public function subjectSchedule()
  {
          $request = $this->input->get('data');
          $data = [];
          $sched = new Sched_subj();
          $results = $sched->schedule([
              'sy' => $request['sy'],
              'semester' => $request['semester'],
//              'year_level' => $request['year_level'],
              'subj_id' => $request['subj_id'],
              'subject_name' => $request['subject_name'],
          ]);
          if (!empty($results)) {
              foreach ($results as $result) {
                  $data[] = [
                      'section'=>strtoupper($result->sec_code).'<span class="hide">'.$result->ss_id.'</span>',
                      'subject'=>ucwords($result->subj_name),
                      'room'=>ucwords($result->room_code),
                      'day'=>strtoupper($result->day)
                      .' '.date('h:i', strtotime($result->time_start))
                      .' - '.date('h:i A', strtotime($result->time_end)),
                      'ss_id'=>$result->ss_id
                  ];
              }
          }
          echo json_encode(['data'=>$data]);
  }

  public function getRoom()
  {
      $data = [];
      $type = $this->input->get('type');
      $rooms = $this->getRooms($type);
      if (!empty($rooms)){
          foreach ($rooms as $room){

              $html = ' <div class="panel panel-info">';
              $html .= ' <div class="panel-heading clearfix">';
              $html .= '<span class="panel-title">'.strtoupper($room->room_code).'</span>';
              $html .= '<small class="pull-right">Available Time Percentage: 27%</small>';
              $html .= '</div>';
              $html .= '<div class="panel-body p-t-10 p-l-10 p-r-10 p-b-10">';
              $html .= '<div class="roomCalendar" id="'.$room->room_code.'"></div>';
              $html .= '</div>';
              $html .= '<div class="panel-footer clearfix">';
              $html .= '<small class="pull-right">Total Unit Plotted: 27</small>';
              $html .= '</div>';
              $html .= '</div>';
              $data[] = ['code'=>$html];
          }
      }
      echo json_encode($data);
  }

  public function loadRooms()
  {
      $type = $this->input->get('type');
      $rooms = $this->getRooms($type);
      $data = [];

      if (!empty($rooms)){
          foreach ($rooms as $room){
            $data[] = [
                'code'=>strtoupper($room->room_code)
            ];
          }
      }
      echo json_encode($data);

  }

  public function check_plotted()
  {

        $bs_id = $this->input->get('bs_id');
        
        $bss = new Block_section_subjects();
        $subjects = $bss->check_plotted($bs_id);
        $result = '';


        $data =array();

        if(!empty($subjects)){
            
            foreach ($subjects as $subject) {

                if($subject->status == 0){
                    $result = $subject->status;
                    break;
                }
                else{
                    $result = $subject->status;
                }
            }


        }
        echo $result;     
  }

  public function save_schedule()
  {
    if ($this->input->method() == 'get' && array_key_exists('event', $_GET)) {

        $event = $this->input->get('event');

        $user = $this->userInfo;

        $start = date("Y-m-d H:i:s", strtotime($event['start']));

        $room_id = $this->get_room_id($event['room']);

        $subject_hour = $this->get_subject_hour(['type' => $event['type'], 'subj_id' => $event['sub_id']]);

        $schedule = $this->session->userdata('schedule');

        $time_end = $this->get_time_end(['hour' => $subject_hour['hour'], 'day' => $event['selected_days'], 'start' => $start]);

        $start = date("H:i", strtotime($event['start']));

        $this->db->trans_begin();

      //find the subject id  and block section id on sched_subj table

      $schedSubj = SchedSubj::where(['subj_id'=>$event['sub_id'], 'bs_id'=>$event['bs_id']])->first();

      //if subject id and block section id not exist save to sched_subj
      //otherwise disregard.
        $ss_id = null;

      if(empty($schedSubj)){

          $ss = SchedSubj::create([
              'year_lvl' => $schedule['year'],
              'sy' => $schedule['sy'],
              'subj_id' => $event['sub_id'],
              'sem' => $schedule['semester'],
              'avs_status' => 'active',
              'bs_id' => $event['bs_id']
          ]);

        $ss_id = $ss->ss_id;
      }
      else{
          $ss_id = $schedSubj->ss_id;
      }

      foreach ($event['selected_days'] as $day_id) {

        $data = array('time_start' => $start, 'time_end' => $time_end, 'room' => $room_id, 'day' => $day_id, 'sem'=>$schedule['semester']);

        if (empty($this->isTimeVacant($data))) {
          $ssd = new Subj_sched_day();
          $ssd->time_start = $start;
          $ssd->time_end = $time_end;
          $ssd->sd_id = $day_id;
          $ssd->ss_id = $ss_id;
          $ssd->type = $event['type'];
          $ssd->rl_id = $room_id;
          $ssd->user_id = $user->user_id;
          $ssd->save();

          // if vacant update the status to 1, meaning successfully added
            $blockSectionSubject = BlockSectionSubject::where(['bs_id' => $event['bs_id'], 'type' => $event['type'], 'subj_id' => $event['sub_id']])->first();

          if (!empty($blockSectionSubject)) {
              $bss = BlockSectionSubject::find($blockSectionSubject->bss_id);
              $bss->status = 1;
              $bss->save();
          }

        }


      }

      if ($this->db->trans_status() === FALSE) {
        // $this->db->trans_rollback();
        $this->db->trans_rollback();
      } else {
        // $this->db->trans_commit();
        $this->db->trans_commit();
        echo true;
      }

    }
  }

  public function undo_schedule() 
  {
    $schedule = $this->session->userdata('schedule');
    $bs_id = $schedule['bs_id'];

    $bss = new Block_section_subjects();
    $results = $bss->search(array('bs_id' => $bs_id, 'status' => 0));

    if (!empty($results)) {
      $bs = new Block_section();
      $bs->load($bs_id);
      $bs->delete();
    }
  }

  public function get_room_plotted() 
  {

    $code = $this->input->get('room_code');
    $sched_subj = new Sched_subj();
    $result = $sched_subj->get_schedule($code);
    $data = array();

    $days = array("Sunday" => 0, "Monday" => 1, "Tuesday" => 2, "Wednesday" => 3, "Thursday" => 4, "Friday" => 5, "Saturday" => 6);

    if (!empty($result)) {
      foreach ($result as $subject) {
        $data[] = array(
          'title' => ucwords($subject->subj_name),
          'start' => date('H:i', strtotime($subject->time_start)),
          'end' => date('H:i', strtotime($subject->time_end)),
          'dow' => array($days[$subject->composition]),
          'backgroundColor' => $this->random_color(),
          'textColor' => 'black',
          "rendering" => 'background',
        );
      }
    }

    echo json_encode($data);
  }

  public function get_block_subject() 
  {

    $type = $this->input->get('type');
    $schedule = $this->session->userdata('schedule');

    $bss = new Block_section_subjects();
    $subjects = $bss->get_subject(array('type' => $type, 'bs_id' => $schedule['bs_id']));
    $data = array();

      if (!empty($subjects)) {
          foreach ($subjects as $subject) {
        $data[] = array(
          'subj_id' => $subject->subj_id,
          'name' => strtoupper($subject->subj_name),
          'code' => strtoupper($subject->subj_code),
          'bs_id' => $subject->bs_id,
            'section'=>$subject->sec_code,
            'semester'=>$subject->semister,
            'year_level'=>$subject->year_lvl,
            'sy'=>$subject->sy,
        );
      }
    }

    echo json_encode(array('data' => $data));
  }

  public function create_schedule() 
  {

    $this->remove_unsuccessful_schedule();

    $subjects = $data = $data_array = $sched = array();

    $type = $this->input->get('type');

    $request = $this->input->get('sched');

    $this->session->set_userdata(array('schedule_time' => $request['schedule']));

    $time = plotted_time();


    if ($type == "curriculum") {
      $data_array = array('program_id' => $request['program'], 'year_level' => $request['curryearlvl'], 'semester' => $request['currsemister'], 'sy' => $request['currsy']);
      $subjects = $this->getCurriculumSubject($data_array);
    } else if ($type == 'subject') {
      $subjects = $request['subjects'];
    }

    $this->db->trans_begin();

    if (!empty($subjects)) {

      $block_section = new Block_section();
      $block_section->sec_code = $request['section_code'];
      $block_section->activation = 'active';
      $block_section->year_lvl = $request['year'];
      $block_section->semister = $request['semister'];
      $block_section->sy = $request['sy'];
      $block_section->pl_id = $type == 'curriculum' ? $request['program'] : 0;
      $block_section->save();

      $bs_id = $block_section->db->insert_id();


      $this->session->set_userdata(
        array(
          'schedule' => array(
            'bs_id' => $bs_id,
            'sy' => $request['sy'],
            'semester' => $request['semister'],
            'year' => $request['year'],
            'code' => $request['section_code'],
            'program_name' => $p = isset($request['prog_name']) ? $request['prog_name'] : '',
            'major' => $request['major']
          )
        )
      );

      $subject_data = array();

      foreach ($subjects as $subject) {

        $block_section_subject_data = array('bs_id' => $bs_id, 'subj_id' => $subject['subj_id'], 'type' => 'lec');
        $sched_subj_data = array('year_level' => $request['year'], 'sy' => $request['sy'], 'sem' => $request['semister'], 'subj_id' => $subject['subj_id'], 'bs_id' => $bs_id, 'type' => 'lec');


        $this->save_block_section_subject($block_section_subject_data);
        // $this->save_sched_subj($sched_subj_data);

        $lab_unit = $subject['lab_unit'];
        if ($lab_unit > 0 && $subject['lec_unit'] != 0 ) {

          $block_section_subject_data['type'] = 'lab';
          $sched_subj_data['type'] = 'lab';
          // $this->save_sched_subj($sched_subj_data);
          $this->save_block_section_subject($block_section_subject_data);

        }
      }

    }

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      echo false;
    } else {

      $this->db->trans_commit();
      echo true;
    }
  }

  public function get_revision() 
  {

    $pl_id = trim($this->input->get('pl_id'));

    $c = new Curr_codelist();
    $result = $c->get_curriculum($pl_id);
    $data = array();


    if (!empty($result)) {
      foreach ($result as $temp) {
        $data[] = array(
          'sy' => $temp->eff_sy
        );
      }
    }

    echo json_encode($data);
  }

  public function get_room()
  {

    $room = new Room_list();

    $type = trim($this->input->get('type'));

    $rooms = $room->search(array('type' => $type));
    $data = array();

    if (!empty($rooms)) {
      foreach ($rooms as $room) {
        $data[] = array(
          'room_code' => strtoupper($room->room_code),
          'room' => ucwords(strtolower($room->room_name))
        );
      }
    }

    echo json_encode($data);
  }

  public function get_subject() 
  {

    $subject = new Subject();
    $unit = trim($this->input->get('unit'));
    $subjects = $subject->get_by_type($unit);
    $data = array();

    if (!empty($subjects)) {
      foreach ($subjects as $temp) {
        $data[] = array(
          'id' => $temp->subj_id,
          'name' => ucwords(strtolower($temp->subj_name)),
          'code' => strtoupper($temp->subj_code),
        );
      }
    }

    echo json_encode(array('data' => $data));
  }

  public function getRooms($type) 
  {
    $room = new Room_list;
    $list = $room->show(array("type" => $type));
    if (!empty($list)) {
      return $list;
    }
  }

  public function loadPlottedEvents() 
  {
    $this->load->model("Plotted_schedule");
    $plotted = new Plotted_schedule();

    $list = $plotted->get_by_user();
    $rendered = array();

    foreach ($list as $key => $value) {
      $rendered[] = array(
        "room" => $value->room_code,
        "start" => date("Y-m-d {$value->time_start}", strtotime("{$value->composition} this week")),
        "end" => date("Y-m-d {$value->time_end}", strtotime("{$value->composition} this week")),
        "rendering" => 'background',
        "backgroundColor" => "red" //Blue
      );
    }
    echo json_encode($rendered);
  }

  public function saveSubjectSched() 
  {
    $block = new Block_section;
    $sched = new Sched_subj;

    $data = $this->input->post('input');
    $section = $this->input->post('section');
    $secData = $this->input->post('sectionData');
    // SAVE SECTION //
    $block->sec_code = $section;
    $block->activation = "close";
    $block->description = "";
    $block->year_lvl = $secData['year'];
    $block->semister = $secData['semister'];
    $block->sy = $secData['sy'];
    $block->pl_id = $secData['program'];
    $block->save();


    $entries = array();

    if ($block->db->affected_rows() > 0) {
      $bs_id = $block->db->insert_id();
      // SAVE SUBJECT SCHED //
      foreach ($data as $key1 => $value1) {
        foreach ($value1 as $value) {

          $entries[] = array(
            "year_lvl" => $value1['year_lvl'],
            "sy" => $value1['sy'],
            "sem" => $value1['sem'],
            "subj_id" => $value1['subj_id'],
            "sd_id" => getDayDetails("composition", $value1['composition'], "sd_id"),
            "rl_id" => $value1['rl_id'],
            "time_start" => $value1['time_start'],
            "time_end" => $value1['time_end'],
            "bs_id" => $bs_id,
            "avs_status" => "active"
          );
        }
      }

      $this->db->insert_batch("sched_subj", $entries);
    }
  }

  public function getMoveRooms() 
  {

    $type = $this->input->get("type");
    $except = $this->input->get("except");


    $query = $this->db->query("SELECT * FROM room_list where type = '{$type}' AND rl_id != '{$except}'");
    $list = $query->result();

    if (!empty($list)) {
      echo json_encode($list);
    }
  }

  public function getSectionList() 
  {
    $query = $this->db->query("
                  SELECT block_section.*, program_list.prog_name,prog_code
				  FROM block_section
				  LEFT JOIN program_list 
                  ON program_list.pl_id = block_section.pl_id");
    $list = $query->result();
    echo json_encode(array('data' => $list));
  }

  public function viewSectionSchedule() 
  {
      $data = [];   $subj_id = null;

      $bs_id = $this->input->get( 'bs_id' );

      $sched = new Subj_sched_day();

      $schedules = $sched->schedule()->search( ["bs_id" => $bs_id] );

      $block_section = new Block_section();

      foreach ( $block_section->program( $bs_id ) as $section ) {
      $section_details = [
          "sy" => $section->sy,
          "major" => $section->major,
          "year" => $section->year_lvl,
          "section" => $section->sec_code,
          "semister" => $section->semister,
          "prog_name" => $section->prog_name
      ];
    }
    // END SECTION DETAILS //

    foreach ($schedules as $schedule) {
      if ($subj_id != $schedule->subj_id) {
        $color = $this->random_color();
      }
      $subj_id = $schedule->subj_id;

      $composition = $this->getDayDetails("sd_id", $schedule->sd_id, "composition");

      $room_code = $this->getRoomDetails("rl_id", $schedule->rl_id, "room_code");
      $room_type = $this->getRoomDetails("rl_id", $schedule->rl_id, "type");

      $data[] = array(
        "ss_id" => $schedule->ss_id,
        "id" => $room_code . $schedule->ss_id,
        "composition" => $composition,
        "key" => $room_code . $schedule->ss_id,
        "year_lvl" => $schedule->year_lvl,
        "sy" => $schedule->sy,
        "sem" => $schedule->sem,
        "subj_id" => $schedule->subj_id,
        "sd_id" => $schedule->sd_id,
        "rl_id" => $schedule->rl_id,
        "time_start" => $schedule->time_start,
        "time_end" => $schedule->time_end,
        "room" => $room_code,
        "title" => $this->getSubjCode($schedule->subj_id) . " - " . substr($room_type, 0, 3),
        "start" => date("Y-m-d {$schedule->time_start}", strtotime("{$composition} this week")),
        "end" => date("Y-m-d {$schedule->time_end}", strtotime("{$composition} this week")),
        "allDay" => false,
        "color" => $color,
        "textColor" => "#FFF",
        "type" => $room_type,
        "bs_id" => $schedule->bs_id
      );
    }
    echo json_encode(array($data, $section_details));
  }

  public function loadEditRenderingEvents() 
  {

    $bs_id = $this->input->get("bs_id");
    $sched = new Sched_subj;
    $sched->toJoin = array("Room_list" => "Sched_subj", "Sched_day" => "Sched_subj", "Subject" => "Sched_subj");
    $sched->db->where("bs_id != {$bs_id}");
    $list = $sched->get();

    $rendered = array();
    foreach ($list as $key => $value) {
      $rendered[] = array(
        "room" => $value->room_code,
        "start" => date("Y-m-d {$value->time_start}", strtotime("{$value->composition} this week")),
        "end" => date("Y-m-d {$value->time_end}", strtotime("{$value->composition} this week")),
        "rendering" => 'background',
        "backgroundColor" => random_color()//Blue
      );
    }
    echo json_encode($rendered);
  }

  public function updateSchedule() 
  {
    $data = $this->input->post('input');

    foreach ($data as $key1 => $value1) {
      $sched = new Sched_subj;
      $sched->load($value1['ss_id']);
      $update = array("year_lvl" => $value1['year_lvl'],
        "sy" => $value1['sy'],
        "sem" => $value1['sem'],
        "subj_id" => $value1['subj_id'],
        "sd_id" => getDayDetails("composition", $value1['composition'], "sd_id"),
        "rl_id" => $value1['rl_id'],
        "time_start" => $value1['time_start'],
        "time_end" => $value1['time_end'],
        "bs_id" => $value1['bs_id'],
        "avs_status" => "active"
      );

      if (!empty($update)) {
        foreach ($update as $key => $value) {
          $sched->$key = $value;
        }
        $sched->save();
      }

    }
  }

  public function get_schedule() 
  {
    $bs = new Block_section();
    $bs = $bs->get_last_row();

    $data = array();


    if (!empty($bs)) {
      foreach ($bs as $b) {
        $bs_id = $b->bs_id;
      }
      $bss = new Block_section_subjects();
      $lab_hour = $this->input->get('lab_hour');
      $results = $bss->get_by_block(array($bs_id, $lab_hour));

      if (!empty($results)) {
        foreach ($results as $result) {
          $type = '';

          if ($result->lab_hour > 0 && $lab_hour > 0) {
            $type = '- lab';
          }
          $data[] = array(
            'id' => $result->subj_id,
            'name' => ucwords(strtolower($result->subj_name)) . $type,
            'code' => strtoupper($result->subj_code),
          );
        }
      }


    }
    echo json_encode(array('data' => $data));
  }

  public function get_schedule_time() 
  {

    $data = array();

    if ($this->session->has_userdata('schedule_time')) {

      //plotted_time is a helper function
      $time = plotted_time();
      $start = $time['start'];
      $end = $time['end'];

      $request = $this->input->get('time');
      if (!empty($request)) {
        $start = date('H:i', strtotime($request));
      }


      $plotted_time = new Plotted_time();
      $times = $plotted_time->get_by_sched(array($start, $end));

      if (!empty($times)) {
        foreach ($times as $time) {
          $data[] = array(
            'time' => date('h:i a', strtotime($time->time))
          );
        }
      }

    }
    echo json_encode($data);
  }

  public function get_plotted_room() 
  {
    $user = $this->session->userdata('CURRICULUM_logged');

    $ss = new Subj_sched_day();

    $request = $this->input->get();


    $results = $ss->get_schedule($request['room_code'],$request['sy'], $request['semester']);
    $data = array();

    $days = array("Sunday" => 0, "Monday" => 1, "Tuesday" => 2, "Wednesday" => 3, "Thursday" => 4, "Friday" => 5, "Saturday" => 6);

    if (!empty($results)) {
      foreach ($results as $result) {
    
        $data[] = array(
          'title' => ucwords($result->subj_name),
          'start' => date('H:i', strtotime($result->time_start)),
          'end' => date('H:i', strtotime($result->time_end)),
          'dow' => array($days[$result->composition]),
          'backgroundColor' => $color = $result->user_id == $user['id'] ?  $this->random_color() : '',
          'textColor' => $color = $result->user_id == $user['id'] ?  '#000' : '#eee',
          'ss_id'=>$result->ss_id,
          'type'=>$result->type,
          'rl_id'=>$result->rl_id,
        );

      }
    }
    echo json_encode($data);
  }

  public function setSchedule() 
  {

    $time = array(
      "morning" => array("start" => "07:30", "end" => "12:00"),
      "afternoon" => array("start" => "12:00", "end" => "18:00"),
      "evening" => array("start" => "18:00", "end" => "22:00")
    );

    $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

    $type = $this->input->get('type');

    $data = $this->input->get('sched');


    $x = 0;

    $sched = array();

    if ($type == "curriculum") {
      $data_array = array('program_id' => $data['program'], 'year_level' => $data['curryearlvl'], 'semester' => $data['currsemister'], 'sy' => $data['currsy']);
      $subjects = $this->getCurriculumSubject($data_array);

    } elseif ($type == "subject") {
      $subjects = $data['subjects'];
    }


    $startTime = $time[$data['schedule']]['start'];


    foreach ($subjects as $key => $subvalue) {

      $color = $this->random_color();


      if ($subvalue['lab_hour'] != 0) {

        $rooms = $this->getRooms("Laboratory");

        $interval = ($subvalue['lab_hour']);
        $time1 = $this->convertTime($interval);
        $time1 = explode(":", $time1);


        for ($z = 0; $z < $subvalue['split']; $z++) {


          if (strtotime($startTime) > strtotime($time[$data['schedule']]["end"])) {
            $x++;
            $startTime = $time[$data['schedule']]["start"];

          }


          $time2 = date('H:i', strtotime($startTime . '+' . $time1[0] . ' hour +' . $time1[1] . ' minute'));


          foreach ($rooms as $room) {

            if (
              !$this->isTimeVacant(
                array(
                  "room" => $room->rl_id,
                  "time_start" => $startTime,
                  "time_end" => $time2,
                  "day" => $days[$x])
              )
              &&
              !$this->isNotPlotted(
                array(
                  "room" => $room->rl_id,
                  "time_start" => $startTime,
                  "time_end" => $time2,
                  "day" => $days[$x])
              )
            ) {

              $sched[] = array(
                "ss_id" => "",
                "id" => $room->room_code . $subvalue['subj_id'] . $this->userInfo->user_id,
                "composition" => $days[$x],
                "key" => $room->room_code . $subvalue['subj_id'] . $this->userInfo->user_id,
                "year_lvl" => $data['year'],
                "sy" => $data['sy'],
                "sem" => $data['semister'],
                "subj_id" => $subvalue['subj_id'],
                "sd_id" => $this->getDayDetails("composition", $sched_days[$x], "sd_id"),
                "rl_id" => $room->rl_id,
                "time_start" => $startTime,
                "time_end" => $time2,
                "room" => $room->room_code,
                "title" => $subvalue['subj_name'] . " - Lab",
                "start" => date("Y-m-d {$startTime}", strtotime("{$days[$x]} this week")),
                "end" => date("Y-m-d {$time2}", strtotime("{$days[$x]} this week")),
                "allDay" => false,
                "color" => "#" . $color,
                "textColor" => "#000",
                "type" => "Laboratory",
                "bs_id" => ""
              );
              break;
            }
          }
          $startTime = $time2;
        }
      }


      if ($subvalue['lec_hour'] != 0) {

        $rooms = $this->getRooms("Lecture");

        $interval = ($subvalue['lec_hour']);

        $time1 = $this->convertTime($interval);
        $time1 = explode(":", $time1);


        for ($z = 0; $z < $subvalue['split']; $z++) {


          if (strtotime($startTime) > strtotime($time[$data['schedule']]["end"])) {
            $x++;
            $startTime = $time[$data['schedule']]["start"];
          }

          $time2 = date('H:i', strtotime($startTime . '+' . $time1[0] . ' hour +' . $time1[1] . ' minute'));

          foreach ($rooms as $room) {

            if (
              !$this->isTimeVacant(
                array(
                  "room" => $room->rl_id,
                  "time_start" => $startTime,
                  "time_end" => $time2,
                  "day" => $days[$x]))
              &&
              !$this->isNotPlotted(
                array(
                  "room" => $room->rl_id,
                  "time_start" => $startTime,
                  "time_end" => $time2,
                  "day" => $days[$x]))
            ) {

              $sched[] = array(
                "ss_id" => "",
                "id" => $room->room_code . $subvalue['subj_id'] . $this->userInfo->user_id,
                "composition" => $days[$x],
                "key" => $room->room_code . $subvalue['subj_id'] . $this->userInfo->user_id,
                "year_lvl" => $data['year'],
                // "sy" => $data['sy'],
                "sem" => $data['semister'],
                "subj_id" => $subvalue['subj_id'],
                "sd_id" => $this->getDayDetails("composition", $days[$x], "sd_id"),
                "rl_id" => $room->rl_id,
                "time_start" => $startTime,
                "time_end" => $time2,
                "room" => $room->room_code,
                "title" => $subvalue['subj_name'] . " - Lec",
                "start" => date("Y-m-d {$startTime}", strtotime("{$days[$x]} this week")),
                "end" => date("Y-m-d {$time2}", strtotime("{$days[$x]} this week")),
                "allDay" => false,
                "color" => "#" . $color,
                "textColor" => "#000",
                "type" => "Laboratory",
                "bs_id" => ""
              );
              break;
            }
          }
          $startTime = $time2;

        }
      }
    }
    $this->savePlottedSched($sched);

    echo json_encode($sched);
  }

  public function resetPlottedSchedule() 
  {
    $plotted = new Plotted_schedule();
    $plotted->load($this->userInfo->user_id);
    $plotted->delete();
    $result = $this->db->simple_query("DELETE FROM plotted_schedule where user_id = {$this->userInfo->user_id}");

    $result = $plotted->db->affected_rows();
    if ($result > 1) {
      echo json_encode(["result" => $result]);
    }
  }

  public function generateSectionCode($length = 5) 
  {
    $alphabets = range('A', 'Z');
    $numbers = range('0', '9');

    $final_array = array_merge($alphabets, $numbers);
    $password = '';

    while ($length--) {
      $key = array_rand($final_array);
      $password .= $final_array[$key];
    }
    echo $password;
  }

  public function updatePlottedSched() 
  {

    $sched = $this->input->get('s');

    $key = $sched['key'];
    $rl_id = $sched['rl_id'];
    $time_start = $sched['time_start'];
    $time_end = $sched['time_end'];
    $sd_id = $sched['composition'];

    if ($this->isTimeVacant(array("room" => $rl_id, "time_start" => $time_start, "time_end" => $time_end, "day" => $sd_id)) == true) {
      echo json_encode(["result" => "conflict"]);
    } else {
      if ($this->isNotPlotted(array("room" => $rl_id, "time_start" => $time_start, "time_end" => $time_end, "day" => $sd_id)) == true) {
        echo json_encode(["result" => "conflict"]);
      } else {
        $query = $this->db->simple_query("UPDATE plotted_schedule SET rl_id = {$rl_id}, sd_id = '{$sd_id}', time_start = '{$time_start}', time_end = '{$time_end}' WHERE `key` = '{$key}'");
        if ($query) {
          echo json_encode(["result" => true]);
        }
        echo json_encode(["result" => false]);
      }
    }
  }


  /*-------------------------- below this line are the private methods ----------------------------------------------*/
  protected function get_time_end($data = [])
  {
    $minutes = $data['hour'] * 60;

    $split = count($data['day']);

    $hour = $minutes / $split;

    $hour = date('G:i', mktime(0, $hour));

    $hour = explode(':', $hour);

    $time_end = $hour[1] == 0 ? date('H:i', strtotime('+' . $hour[0] . 'hour', strtotime($data['start']))) : date('H:i', strtotime('+' . $hour[0] . 'hour +' . $hour[1] . ' minutes', strtotime($data['start'])));

    return $time_end;
  }

  //return array
  protected function get_subject_hour($data = [])
  {

    $s = new Subject();
    $result = array();

    if ($data['type'] == 'lab') {

      $subject = $s->search(array('subj_id' => $data['subj_id']));
      foreach ($subject as $temp) {
        $result = array('hour' => $temp->lab_hour, 'type' => 'lab');
      }

    } else {
      $subject = $s->search(array('subj_id' => $data['subj_id']));
      foreach ($subject as $temp) {
        $result = array('hour' => $temp->lec_hour, 'type' => 'lec');
      }

    }

    return $result;
  }

  protected function get_room_id($code = false) 
  {
    $room_id = '';
    $room = new Room_list();
    $room_result = $room->search(array('room_code' => $code));
    if (!empty($room_result)) {
      foreach ($room_result as $room) {
        $room_id = $room->rl_id;
      }
    }
    return $room_id;
  }

  protected function remove_unsuccessful_schedule() 
  {
    if (array_key_exists('schedule', $_SESSION)) {

      $schedule = $this->session->userdata('schedule');
      $bs_id = $schedule['bs_id'];

      $sched_subj = new Sched_subj();

      $result = $sched_subj->search(array('bs_id' => $bs_id));

      if (empty($result)) {

        $bs = new Block_section();
        $result = $bs->search(array('bs_id' => $bs_id));

        if (!empty($result)) {

          $bs = new Block_section();
          $bs->load($bs_id);
          $bs->delete();

        }


      }

    }
  }

  protected function save_block_section_subject($data = [])
  {

    $block_section_subject = new Block_section_subjects();
    $block_section_subject->bs_id = $data['bs_id'];
    $block_section_subject->subj_id = $data['subj_id'];
    $block_section_subject->type = $data['type'];
    $block_section_subject->status = 0;
    $block_section_subject->save();
  }

  private function getCurriculumSubject($data = [])
  {
    $year = array("1st" => "First Year", "2nd" => "Second Year", "3rd" => "Third Year", "4th" => "Forth Year", "5th" => "Fifth Year");
    $semester = array("1st Semester" => "First Semester", "2nd Semester" => "Second Semester");
    $array = array();

    $query = "SELECT * FROM cur_subject
              INNER JOIN `subject` ON cur_subject.subj_id = `subject`.subj_id
              INNER JOIN year_sem ON cur_subject.ys_id = year_sem.ys_id
              INNER JOIN curr_codelist ON year_sem.cur_id = curr_codelist.cur_id
              WHERE curr_codelist.pl_id = {$data['program_id']}
              AND curr_codelist.eff_sy = '{$data['sy']}'
              AND year_sem.semister = '{$semester[$data['semester']]}'
              AND year_sem.`year` = '{$year[$data['year_level']]}'
              ";

    $query = $this->db->query($query);
    $results = $query->result();

    if (!empty($results)) {
      foreach ($results as $result) {
        $array[] = array(
          "subj_id" => $result->subj_id,
          "subj_code" => $result->subj_code,
          "lec_unit" => $result->lec_unit,
          "lab_unit" => $result->lab_unit,
          "subj_name" => $result->subj_name,
          "lab_hour" => $result->lab_hour,
          "lec_hour" => $result->lec_hour,
          "split" => $result->split,
          "cur_id" => $result->cur_id
        );
      }
    }

    return $array;
  }

  private function isNotPlotted($data) {
    $query = $this->db->query("SELECT
                                        *
                                    FROM
                                        plotted_schedule,
                                        sched_day
                                    WHERE (plotted_schedule.time_start < '" . $data['time_end'] . "' AND plotted_schedule.time_end > '" . $data['time_start'] . "')
                                    AND sched_day.composition = '" . $data['day'] . "'
                                    AND plotted_schedule.sd_id = sched_day.sd_id");
   
    return  $result = !empty($query->result()) ? true : false;
  }

  private function isTimeVacant($data = [])
  {
    $query = $this->db->query("
                    SELECT * FROM subj_sched_day
                    INNER JOIN sched_subj ON subj_sched_day.ss_id = sched_subj.ss_id
                    INNER JOIN sched_day ON subj_sched_day.sd_id = sched_day.sd_id
                    WHERE subj_sched_day.rl_id  =  {$data['room']}
                    AND sched_subj.sem = '{$data['sem']}'
                    AND sched_day.sd_id = {$data['day']}
                    AND (
                        subj_sched_day.time_start < '{$data['time_end']}'
                        AND subj_sched_day.time_end > '{$data['time_start']}'
                    )");

      return $query->result();
  }

  private function savePlottedSched($sched) 
  {

    $this->load->model("Plotted_schedule");
    $plotted = new Plotted_schedule;

    $data = array();
    foreach ($sched as $key => $value) {
      $data[] = array(
        "user_id" => $this->userInfo->user_id,
        "subj_id" => $value['subj_id'],
        "sd_id" => $value['sd_id'],
        "rl_id" => $value['rl_id'],
        "time_start" => $value['time_start'],
        "time_end" => $value['time_end'],
        "key" => $value['key']
      );
    }

    $this->db->insert_batch("plotted_schedule", $data);
  }

  private function getSubjCode($subj_id) 
  {
    $sub = new Subject;
    $list = $sub->search(array("subj_id" => $subj_id));

    return $list[$subj_id]->subj_name;
  }

  private function getRoomDetails($search, $param, $return) 
  {
    $room = new Room_list;
    $list = $room->show(array($search => $param));
    if (!empty($list)) {
      foreach ($list as $key => $value) {
        return $value->$return;
      }
    }
  }

  private function convertTime($dec) 
  {
    $seconds = ($dec * 3600);
    $hours = floor($dec);
    $seconds -= $hours * 3600;
    $minutes = floor($seconds / 60);
    $seconds -= $minutes * 60;

    return $this->lz($hours) . ":" . $this->lz($minutes) . ":" . $this->lz($seconds);
  }

  private function getDayDetails($field, $par, $return) 
  {
    $day = new Sched_day;
    $result = $day->search(array($field => $par));
    foreach ($result as $key => $value) {
      return $value->$return;
    }
  }

  private function lz($num) 
  {
    return (strlen($num) < 2) ? "0{$num}" : $num;
  }

  private function random_color() 
  {
    return '#' . $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
  }

  private function random_color_part() 
  {
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
  }
}
