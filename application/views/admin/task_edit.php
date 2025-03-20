<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>
<body>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
<form action="<?php echo admin_url('task/edit/' . $task['id']); ?>" method="post">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

    <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control" value="<?php echo $task['subject']; ?>" required>
    </div>
    <div class="form-group">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?php echo $task['start_date']; ?>" required>
    </div>
    <div class="form-group">
        <label>Due Date</label>
        <input type="date" name="due_date" class="form-control" value="<?php echo $task['due_date']; ?>" required>
    </div>
    <div class="form-group">
        <label>Priority</label>
        <select name="priority" class="form-control" required>
            <option value="low" <?php echo ($task['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
            <option value="medium" <?php echo ($task['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
            <option value="high" <?php echo ($task['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
        </select>
    </div>
    <div class="form-group">
        <label>Assigned To</label>
        <select name="assigned_to" class="form-control" required>
            <?php foreach ($staff as $member): ?>
                <option value="<?php echo $member['staffid']; ?>" <?php echo ($task['assigned_to'] == $member['staffid']) ? 'selected' : ''; ?>>
                    <?php echo $member['firstname'] . ' ' . $member['lastname']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required><?php echo $task['description']; ?></textarea>
    </div>
    <div class="form-check">
        <input type="checkbox" name="completed" class="form-check-input" value="1" <?php echo ($task['completed'] == 1) ? 'checked' : ''; ?>>
        <label class="form-check-label">Completed</label>
    </div>
    <button type="submit" class="btn btn-warning">Update Task</button>
</form>

    </div>
    </div>
    </div>
    </div>
