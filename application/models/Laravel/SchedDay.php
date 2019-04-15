<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/14/2019
 * Time: 2:34 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class SchedDay extends Eloquent
{

    protected $table = "sched_day"; // table name
    protected $primaryKey = 'sd_id';
    public $timestamps = false;
    protected $fillable = ['abbreviation', 'composition'];
}