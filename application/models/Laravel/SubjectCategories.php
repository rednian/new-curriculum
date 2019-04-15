<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/23/2018
 * Time: 8:08 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class SubjectCategories extends Eloquent
{

    protected $table = "subject_category"; // table name
    protected $primaryKey = 'sc_id';
    protected $fillable = ['school_year','semester', 'subj_id', 'c_id'];



}