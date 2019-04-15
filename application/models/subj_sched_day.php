<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subj_sched_day extends MY_Model {
    
    const DB_TABLE = 'subj_sched_day';
    const DB_TABLE_PK = 'ssd_id';

    public $ssd_id;
    public $sd_id;
    public $ss_id;
    public $time_start;
    public $time_end;
    public $type;
    public $rl_id;
    public $user_id;


    public function schedule()
    {
        $this->toJoin = ['sched_subj'=>'subj_sched_day'];
        return $this;
    }

    public function get_schedule($room_code = null, $sy = null, $semester = null){
    	$this->toJoin = array(
    	    "Room_list" => "Subj_sched_day", 
    	    "Sched_day" => "Subj_sched_day", 
    	    "Sched_subj" => "Subj_sched_day", 
            "Subject" => "Sched_subj",
    	    "Block_section" => "Sched_subj"
    	);
        $this->db->where('room_list.room_code', $room_code)
            ->where('sched_subj.sy',$sy)
            ->where('sched_subj.sem',$semester)
            ->group_by(['Subj_sched_day.time_start','Subj_sched_day.time_end','Subj_sched_day.sd_id']);
    	return $this->get();
        }
}
  