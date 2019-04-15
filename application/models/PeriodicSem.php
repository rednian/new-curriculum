<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/6/2018
 * Time: 10:32 AM
 */

class PeriodicSem extends MY_Model
{
    const DB_TABLE ='periodic_sem';
    const DB_TABLE_PK = 'ps_id';

    public $ps_id;
    public $ss_id;
    public $date_end;
    public $date_start;
    public $created_at;
    public $updated_at;
    public $periodic_id;

    public function periodicSemeser()
    {
        $this->db->select('*, 
                             school_semester.date_start AS semester_start, 
                             school_semester.date_end AS semester_end, 
                             periodic_sem.date_start AS period_start, 
                             periodic_sem.date_end AS period_end'
        )
            ->from('periodic_sem')
            ->join('school_semester', 'periodic_sem.ss_id = school_semester.ss_id')
            ->join('periodic', 'periodic_sem.periodic_id = periodic.periodic_id');
            $query = $this->db->get();

        return $query->result();
    }
}