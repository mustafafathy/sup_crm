<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>
<body>
<div id="wrapper">

    <div class="content">
<a href="<?php echo admin_url('task/add'); ?>" class="btn btn-primary">Add Task</a>

        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-end tw-items-center tw-gap-x-4 tw-mb-4">

<table class="table">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Start Date</th>
            <th>Due Date</th>
            <th>Priority</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr class="<?php echo $task['completed'] ? 'table-success' : ''; ?>">
                <td><?php echo $task['subject']; ?></td>
                <td><?php echo $task['start_date']; ?></td>
                <td><?php echo $task['due_date']; ?></td>
                <td><?php echo ucfirst($task['priority']); ?></td>
                <td><?php echo $this->task_model->get_staff_name($task['assigned_to']); ?></td> <!-- Fetch staff name -->
                <td><?php echo $task['completed'] ? 'Completed' : 'Pending'; ?></td>
                <td>
                    <a href="<?php echo admin_url('task/edit/' . $task['id']); ?>" class="btn btn-warning">Edit</a>
                    <a href="<?php echo admin_url('task/delete/' . $task['id']); ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


        </div>
        </div>
        </div>
        </div>