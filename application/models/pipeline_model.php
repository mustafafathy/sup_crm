<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pipeline_model extends CI_Model
{
    private $table = 'pipeline';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();  // Load the database library
    }

    // Create a new pipeline
    public function create_pipeline($data)
    {
        // Data validation can be added here if needed
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Read all pipelines
    public function get_all_pipelines()
    {
        $query = $this->db->get($this->table);
        return $query->result_array();  // Return as an array
    }

    // Read a pipeline by its ID
    public function get_pipeline_by_id($id)
    {
        $query = $this->db->get_where($this->table, ['id' => $id]);
        return $query->row_array();  // Return a single row
    }

    // Update a pipeline by its ID
    public function update_pipeline($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    // Delete a pipeline by its ID
    public function delete_pipeline($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    // Trigger to run before inserting a pipeline (can be expanded)
    public function before_insert_trigger($data)
    {
        // Example trigger before insert: Automatically timestamp the record
        $data['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    // Trigger to run before updating a pipeline
    public function before_update_trigger($data)
    {
        // Example trigger before update: Automatically timestamp the update
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    // Create pipeline with triggers
    public function create_pipeline_with_trigger($data)
    {
        $data = $this->before_insert_trigger($data); // Trigger before inserting
        return $this->create_pipeline($data);
    }

    // Update pipeline with triggers
    public function update_pipeline_with_trigger($id, $data)
    {
        $data = $this->before_update_trigger($data); // Trigger before updating
        return $this->update_pipeline($id, $data);
    }
}

