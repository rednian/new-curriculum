<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/15/2019
 * Time: 9:08 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class BlockSectionSubject extends Eloquent
{

    protected $table = "block_section_subjects"; // table name
    protected $primaryKey = 'bss_id';
    public $timestamps = false;
    
    protected $fillable = ['bs_id', 'subj_id', 'type', 'remaining_hour', 'status'];
}