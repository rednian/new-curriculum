<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Block_section extends MY_Model
{

    const DB_TABLE = 'block_section';
    const DB_TABLE_PK = 'bs_id';

    public $bs_id;
    public $sec_code;
    public $activation;
    public $description;
    public $year_lvl;
    public $semister;
    public $sy;
    public $pl_id;
    public $cur_id;

    public function getOffSem($semester = null, $sy = null)
    {
        $this->db->where('pl_id',0)
            ->where('semister', $semester)
            ->where('sy', $sy)
            ->order_by('sec_code');

        return $this->get();
    }
    public function program(array $data)
    {
        $this->db->select('*')
            ->from('block_section')
            ->join('program_list', 'program_list.pl_id = block_section.pl_id','left');
            if(!empty($data['sy']) && !empty($data['semester'])){
                $this->db->where('block_section.semister', $data['semester']);
                $this->db->where('block_section.sy', $data['sy']);
            }
            else{
                $this->db->where('block_section.bs_id', $data['id']);
            }
        $q = $this->db->get();

        return $q->result();
    }

    public function schoolYear(){
        $this->db->group_by('sy');
        $this->db->order_by('bs_id','DESC');
        return $this->get();
    }

    public function get_last_row()
    {
        $this->db->limit(1);
        $this->db->order_by('bs_id', 'DESC');
        return $this->get();
    }

    public function all()
    {
        $this->sqlQueries['join_type'] = 'LEFT';
        $this->toJoin = array(
            'program_list' => 'block_section'
        );
        return $this->get();

    }
    	
	}