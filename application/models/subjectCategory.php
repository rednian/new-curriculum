<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/21/2018
 * Time: 10:21 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class subjectCategory extends MY_Model
{

    const DB_TABLE = 'category_subject';
    const DB_TABLE_PK = 'cs_id';

    public $cs_id;
    public $category_name;
    public $created_at;
    public $updated_at;
}