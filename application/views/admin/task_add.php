<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>
<body>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
<form action="<?php echo admin_url('task/add'); ?>" method="post">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

    <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Due Date</label>
        <input type="date" name="due_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Priority</label>
        <select name="priority" class="form-control" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
    </div>
    <div class="form-group">
        <label>Task Owner</label>
        <select name="assigned_to" class="form-control" required>
            <?php foreach ($staff as $member): ?>
                <option value="<?php echo $member['staffid']; ?>">
                    <?php echo $member['firstname'] . ' ' . $member['lastname']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="form-check">
        <input type="checkbox" name="completed" class="form-check-input" value="1">
        <label class="form-check-label">Completed</label>
    </div>
    <button type="submit" class="btn btn-primary">Add Task</button>
</form>

    </div>
    </div>
    </div>
    </div>
