<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="">
    <div class="" id="vueApp">
            <div class="">
                <?php if ($task->billed == 1) { ?>
                    <?php echo '<p class="no-margin">' . _l('task_is_billed', '<a href="' . admin_url('invoices/list_invoices/' . $task->invoice_id) . '" target="_blank">' . e(format_invoice_number($task->invoice_id))) . '</a></p>'; ?>
                <?php } ?>
                <?php if ($task->is_public == 0) { ?>
                    <div class="full-flex tw-justify-center tasks-section tw-mt-3 ">
                        <div class=" tw-items-center tw-px-3 tw-mx-3 tasks_header2">
                            <div class="sub-flex page-section tw-pb-0">
                                <h3 class="card-header ">Tickets Summary</h3>

                                <div class="shrink tool ">

                                    <!-- <span class="tab-title"> Tasks Assigned to: </span> -->
                                    <div class=" full-flex tw-items-between  " id="tab_tasks">


                                        <div class=" tw-text-center  left" id="tasks-switcher"
                                            data-user="<?php echo e($user_ID); ?>" style="width:60vw;max-width: 8000px;">



                                            <!-- <div id="dep_tab" data-id="" class="tab" tab-direction="right">Department</div> -->
                                        </div>
                                    </div>
                                </div>



                            </div>
                        </div>

                        <div class="sub-flex shrink tw-justify-between">

                            <div>
                                <a href="<?php echo admin_url('tickets/add'); ?>"
                                    class="btn startbtn button-pr ">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
                                    <?php echo _l('new_ticket'); ?>
                                </a>
                            </div>


                        </div>





                    </div>

                <?php } ?>
            </div>

    </div>
    <div class=" ">
        <div class=" ">
            <!-- <div class="weekly-ticket-opening no-shadow tw-mb-10" style="display:none;">
                                <h4 class="tw-font-semibold tw-mb-8 tw-flex tw-items-center tw-text-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="tw-w-5 tw-h-5 tw-mr-1.5 tw-text-neutral-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>

                                
                                </h4>
                                <div class="relative" style="max-height:350px;">
                                    <canvas class="chart" id="weekly-ticket-openings-chart" height="350"></canvas>
                                </div>
                            </div> -->

            <?php hooks()->do_action('before_render_tickets_list_table'); ?>
            <?php $this->load->view('admin/tickets/summary', [
                'hrefAttrs' => function ($status) use ($table) {
                    return '@click.prevent="extra.ticketsRules = ' . app\services\utilities\Js::from($table->findRule('status')->setValue([(int) $status['ticketstatusid']])) . '"';
                }
            ]); ?>
            <hr class="hr-panel-separator" />
            <a href="#" data-toggle="modal" data-target="#tickets_bulk_actions"
                class="bulk-actions-btn table-btn hide"
                data-table=".table-tickets"><?php echo _l('bulk_actions'); ?></a>
            <div class="clearfix"></div>
            <div class="panel-table-full content">
                <?php echo AdminTicketsTableStructure('', true); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bulk_actions" id="tickets_bulk_actions" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="checkbox checkbox-primary merge_tickets_checkbox">
                    <input type="checkbox" name="merge_tickets" id="merge_tickets">
                    <label for="merge_tickets"><?php echo _l('merge_tickets'); ?></label>
                </div>
                <?php if (can_staff_delete_ticket()) { ?>
                    <div class="checkbox checkbox-danger mass_delete_checkbox">
                        <input type="checkbox" name="mass_delete" id="mass_delete">
                        <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                    </div>
                    <hr class="mass_delete_separator" />
                <?php } ?>
                <div id="bulk_change">
                    <?php echo render_select('move_to_status_tickets_bulk', $statuses, ['ticketstatusid', 'name'], 'ticket_single_change_status'); ?>
                    <?php echo render_select('move_to_department_tickets_bulk', $departments, ['departmentid', 'name'], 'department'); ?>
                    <?php echo render_select('move_to_priority_tickets_bulk', $priorities, ['priorityid', 'name'], 'ticket_priority'); ?>
                    <div class="form-group">
                        <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                        <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value=""
                            data-role="tagsinput">
                    </div>
                    <?php if (get_option('services') == 1) { ?>
                        <?php echo render_select('move_to_service_tickets_bulk', $services, ['serviceid', 'name'], 'service'); ?>
                    <?php } ?>
                </div>
                <div id="merge_tickets_wrapper">
                    <div class="form-group">
                        <label for="primary_ticket_id">
                            <span class="text-danger">*</span> <?php echo _l('primary_ticket'); ?>
                        </label>
                        <select id="primary_ticket_id" class="selectpicker" name="primary_ticket_id" data-width="100%"
                            data-live-search="true"
                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" required>
                            <option></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="primary_ticket_status">
                            <span class="text-danger">*</span> <?php echo _l('primary_ticket_status'); ?>
                        </label>
                        <select id="primary_ticket_status" class="selectpicker" name="primary_ticket_status"
                            data-width="100%" data-live-search="true"
                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" required>
                            <?php foreach ($statuses as $status) { ?>
                                <option value="<?php echo e($status['ticketstatusid']); ?>"><?php echo e($status['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <a href="#" class="btn btn-primary"
                    onclick="tickets_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    var chart;
    var chart_data = <?php echo $weekly_tickets_opening_statistics; ?>;

    function init_tickets_weekly_chart() {
        if (typeof(chart) !== 'undefined') {
            chart.destroy();
        }
        // Weekly ticket openings statistics
        chart = new Chart($('#weekly-ticket-openings-chart'), {
            type: 'line',
            data: chart_data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                        }
                    }]
                }
            }
        });
    }
</script>
</body>

</html>