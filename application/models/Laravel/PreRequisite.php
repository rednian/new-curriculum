<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/28/2018
 * Time: 9:58 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class PreRequisite extends Eloquent
{

    protected $table = "pre_requisite"; // table name
    protected $primaryKey = 'prr_id';
    public $timestamps = false;
    protected $fillable = ['subj_id', 'cs_id'];

    public function subject()
    {
        return $this->belongsTo(Subjects::class, 'subj_id');
    }

}