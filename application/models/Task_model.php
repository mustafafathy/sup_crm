<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_tasks()
    {
        return $this->db->get('tbl_tasks')->result_array();
    }

    public function get_task($id)
    {
        return $this->db->where('id', $id)->get('tbl_tasks')->row_array();
    }

    public function add_task($data)
    {
        $this->db->insert('tbl_tasks', $data);
    }

    public function update_task($id, $data)
    {
        $this->db->where('id', $id)->update('tbl_tasks', $data);
    }

    public function delete_task($id)
    {
        $this->db->where('id', $id)->delete('tbl_tasks');
    }

    public function get_all_staff()
    {
        $this->db->select('staffid, firstname, lastname');
        return $this->db->get('tblstaff')->result_array();
    }



	public function get_staff_name($staff_id)
{
    $this->db->select('firstname, lastname');
    $this->db->where('staffid', $staff_id);
    $staff = $this->db->get('tblstaff')->row_array();
    
    return $staff ? $staff['firstname'] . ' ' . $staff['lastname'] : 'Unassigned';
}
}