<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/14/2019
 * Time: 9:39 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class SchedSubj extends Eloquent
{

    protected $table = "sched_subj"; // table name
    protected $primaryKey = 'ss_id';
    public $timestamps = false;
    protected $fillable = ['year_lvl', 'sy', 'sem', 'subj_id', 'avs_status', 'employee_id', 'bs_id', 'temp_id', 'key'];

    public function subjSchedDay()
    {
        return $this->hasMany(SubjSchedDay::class,'ss_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subjects::class, 'subj_id');
    }
}