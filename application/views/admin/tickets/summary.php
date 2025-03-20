<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class=" ">

    <?php
    $statuses = $this->tickets_model->get_ticket_status();
    ?>

    <div class="ticketpanel tw-ml-5 tw-mb-0 tw-m-0  tw-grid xs:tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-6 tw-gap-3 ">

        <?php
        $where = '';
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'AND department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }
        }

        foreach ($statuses as $status) {
            $_where = '';
            if ($where == '') {
                $_where = 'status=' . $status['ticketstatusid'];
            } else {
                $_where = 'status=' . $status['ticketstatusid'] . ' ' . $where;
            }
            if (isset($project_id)) {
                $_where = $_where . ' AND project_id=' . $project_id;
            }
            $_where = $_where . ' AND merged_ticket_id IS NULL'; ?>


            <a class=" card-element  full-flex tw-flex tw-flex-col tw-align-center  tw-justify-between <?php echo $switch_kanban == 0 ? 'clickable-select' : '' ?> <?php echo $task_data['class']; ?>" style="background-color:<?php echo e($status['statuscolor']); ?>" href="#" data-cview="ticket_status_<?php echo e($status['ticketstatusid']); ?> "
                <?php echo ($hrefAttrs ?? null instanceof Closure) ? $hrefAttrs($status) : ''; ?>>


                <span class="count2  tw-pt-3 tw-ps-3" style="color: white;">
                    <?php echo e(ticket_status_translate($status['ticketstatusid'])); ?>
                </span>
                <span class=" count2 tw-font-semibold  tw-text-lg" style="color: white;">
                    <?php echo total_rows(db_prefix() . 'tickets', $_where); ?>
                </span>
            </a>

        <?php
        } ?>
    </div>

</div>
</div