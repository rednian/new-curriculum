<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	/**
	* 
	*/
	class InstructorModel extends MY_Model{

        private $dtrps = null;

        public function __construct()
        {
             $this->dtrps = $this->load->database('dtrps', true);
        }

        public function get_instructor(){

            $this->dtrps->select('*')
                ->from('employees')
                ->join('employment', 'employment.employee_id = employees.employee_id')
                ->join('departments', 'employment.department_id = departments.department_id');
            $query = $this->dtrps->get();

			return $query->result();
		}

        public function get_list(){

            $this->dtrps->select('*')
                ->from('employees')
                ->join('employment', 'employment.employee_id = employees.employee_id')
                ->join('departments', 'employment.department_id = departments.department_id')
                ->order_by('department_name', 'DESC');
            $query = $this->dtrps->get();

            return $query->result();
        }
    	
	}