<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/15/2019
 * Time: 9:05 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class BlockSection extends Eloquent
{

    protected $table = "block_section"; // table name
    protected $primaryKey = 'bs_id';
    protected $fillable = ['sec_code', 'activation', 'description', 'year_lvl', 'semister', 'sy', 'pl_id', 'cur_id'];
}