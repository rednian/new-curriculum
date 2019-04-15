<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	/**
	* 
	*/
	class Sched_subj extends MY_Model{

		const DB_TABLE = 'sched_subj';
    	const DB_TABLE_PK = 'ss_id';
    	
    	public $ss_id;
    	public $sy;
    	public $sem;
    	public $subj_id;
    	public $bs_id;
    	public $avs_status;
    	public $employee_id;
        public $year_lvl;
        public $temp_id;
        public $key;


        public function existingSchedule($data = [])
        {
            $this->db->select('*')->from('sched_subj')
                ->join('subj_sched_day', 'subj_sched_day.ss_id = sched_subj.ss_id')
                ->where('sched_subj.avs_status','active')
                ->where('sched_subj.ss_id',$data['ss_id']);
            $query = $this->db->get();

            return $query->result();
        }


        public function schedule($data = [])
        {
            $this->db->select('
                block_section.sec_code,
                sched_subj.sem,
                sched_subj.sy,
                sched_subj.year_lvl,
                subject.subj_name,
                subj_sched_day.type,
                subj_sched_day.time_start,
                subj_sched_day.time_end,
                room_list.room_code,
                room_list.room_name,
                sched_subj.ss_id,
                GROUP_CONCAT(sched_day.abbreviation SEPARATOR "") AS day
            ')->from('sched_subj')
            ->join('subject', 'subject.subj_id = sched_subj.subj_id')
            ->join('block_section', 'block_section.bs_id = sched_subj.bs_id')
            ->join('subj_sched_day', 'subj_sched_day.ss_id = sched_subj.ss_id')
            ->join('room_list', 'room_list.rl_id = subj_sched_day.rl_id')
            ->join('sched_day', 'sched_day.sd_id = subj_sched_day.sd_id')
            ->where(['sched_subj.sy'=>$data['sy']])
            ->where(['sched_subj.sem'=>$data['semester']])
            // ->where(['sched_subj.year_lvl'=>$data['year_level']])
            ->where(['sched_subj.subj_id'=>$data['subj_id']])
            ->group_by(['sched_subj.ss_id','block_section.sec_code','subj_sched_day.type','subj_sched_day.type'])
            ->order_by('block_section.sec_code');

            $query = $this->db->get();

            return $query->result();
        }

        public function get_schedule($room_code  = false){
            $this->toJoin = array(
              'room_list' => 'sched_subj',
              'sched_day' => 'sched_subj',
              'subject'   => 'sched_subj',
            );
            
            if (!empty($room_code)) {
              $this->db->where('room_list.room_code', $room_code);
            }

            return $this->get();
            
        }

        public function get_by_block($data =array()){
            
            $this->toJoin = array('Subject'=>'sched_subj');

            $this->db->where(array('bs_id'=>$data['id'], 'type'=>$data['type']));
            return $this->get();
        }

      
		
	}