<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/6/2018
 * Time: 10:35 AM
 */

class SchoolSemester extends MY_Model
{
    const DB_TABLE ='school_semester';
    const DB_TABLE_PK = 'ss_id';

    public $ss_id;
    public $date_end;
    public $semester;
    public $date_start;
    public $created_at;
    public $updated_at;
    public $school_year;

}