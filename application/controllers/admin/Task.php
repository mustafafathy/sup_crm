<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('task_model');
    }

    public function index()
    {
        $data['tasks'] = $this->task_model->get_all_tasks();
        $this->load->view('admin/tasks_view', $data);
    }

    public function add()
    {
        $data['staff'] = $this->task_model->get_all_staff(); // Fetch staff list

        if ($this->input->post()) {
            $postData = $this->input->post();
            $taskData = array(
                'subject'     => $postData['subject'],
                'start_date'  => $postData['start_date'],
                'due_date'    => $postData['due_date'],
                'priority'    => $postData['priority'],
                'assigned_to' => $postData['assigned_to'], // Assigned staff ID
                'description' => $postData['description'],
                'completed'   => isset($postData['completed']) ? 1 : 0
            );

            $this->task_model->add_task($taskData);
            redirect(admin_url('task'));
        }

        $this->load->view('admin/task_add', $data);
    }

    public function edit($id)
    {
        $data['task'] = $this->task_model->get_task($id);
        $data['staff'] = $this->task_model->get_all_staff(); // Fetch staff list

        if ($this->input->post()) {
            $postData = $this->input->post();
            $taskData = array(
                'subject'     => $postData['subject'],
                'start_date'  => $postData['start_date'],
                'due_date'    => $postData['due_date'],
                'priority'    => $postData['priority'],
                'assigned_to' => $postData['assigned_to'], // Assigned staff ID
                'description' => $postData['description'],
                'completed'   => isset($postData['completed']) ? 1 : 0
            );

            $this->task_model->update_task($id, $taskData);
            redirect(admin_url('task'));
        }

        $this->load->view('admin/task_edit', $data);
    }

    public function delete($id)
    {
        $this->task_model->delete_task($id);
        redirect(admin_url('task'));
    }
}
