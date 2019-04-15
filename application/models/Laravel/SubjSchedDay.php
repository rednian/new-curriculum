<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/14/2019
 * Time: 2:36 PM
 */

use \Illuminate\Database\Eloquent\Model as Eloquent;

class SubjSchedDay extends Eloquent
{

    protected $table = "subj_sched_day"; // table name
    protected $primaryKey = 'ssd_id';
    public $timestamps = false;
    protected $fillable = ['time_start', 'time_end', 'sd_id', 'ss_id', 'type', 'rl_id', 'user_id'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'rl_id');
    }

    public function day()
    {
        return $this->belongsTo(SchedDay::class, 'sd_id');
    }
}