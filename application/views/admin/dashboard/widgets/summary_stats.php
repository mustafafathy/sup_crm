<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget relative" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('quick_stats'); ?>">
    <div class="widget-dragger"></div>
    <div class="row">
        <?php
         $initial_column = 'col-lg-3';
         if (!is_staff_member() && ((staff_cant('view', 'invoices') && staff_cant('view_own', 'invoices') && (get_option('allow_staff_view_invoices_assigned') == 0
           || (get_option('allow_staff_view_invoices_assigned') == 1 && !staff_has_assigned_invoices()))))) {
             $initial_column = 'col-lg-6';
         } elseif (!is_staff_member() || (staff_cant('view', 'invoices') && staff_cant('view_own', 'invoices') && (get_option('allow_staff_view_invoices_assigned') == 1 && !staff_has_assigned_invoices()) || (get_option('allow_staff_view_invoices_assigned') == 0 && (staff_cant('view', 'invoices') && staff_cant('view_own', 'invoices'))))) {
             $initial_column = 'col-lg-4';
         }
      ?>
        <?php if (staff_can('view',  'invoices') || staff_can('view_own',  'invoices') || (get_option('allow_staff_view_invoices_assigned') == '1' && staff_has_assigned_invoices())) { ?>
        <div class="quick-stats-invoices col-xs-12 col-md-6 col-sm-6 <?php echo e($initial_column); ?> tw-mb-2 sm:tw-mb-0">
            <div class="top_stats_wrapper">
                <?php
                  $total_invoices                          = total_rows(db_prefix() . 'invoices', 'status NOT IN (5,6)' . (staff_cant('view', 'invoices') ? ' AND ' . get_invoices_where_sql_for_staff(get_staff_user_id()) : ''));
                  $total_invoices_awaiting_payment         = total_rows(db_prefix() . 'invoices', 'status NOT IN (2,5,6)' . (staff_cant('view', 'invoices') ? ' AND ' . get_invoices_where_sql_for_staff(get_staff_user_id()) : ''));
                  $percent_total_invoices_awaiting_payment = $total_invoices > 0 ? (($total_invoices_awaiting_payment * 100) / $total_invoices) : 0;
                  $percent_total_invoices_awaiting_payment = number_format($percent_total_invoices_awaiting_payment > 0 && $percent_total_invoices_awaiting_payment < 1 ? ceil($percent_total_invoices_awaiting_payment) : $percent_total_invoices_awaiting_payment, 2)
                  ?>
                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                        
                        <span class="tw-truncate">
                            <?php echo _l('invoices_awaiting_payment'); ?>
                        </span>
                    </div>
                    <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0">
                        <?php echo e($total_invoices_awaiting_payment); ?> /
                        <?php echo e($total_invoices); ?>
                    </span>
                </div>

                
            </div>
        </div>
        <?php } ?>
        <?php if (is_staff_member()) { ?>
        <div class="quick-stats-leads col-xs-12 col-md-6 col-sm-6 <?php echo e($initial_column); ?> tw-mb-2 sm:tw-mb-0">
            <div class="top_stats_wrapper">
                <?php
                  $where = '';
                  if (!is_admin()) {
                      $where .= '(addedfrom = ' . get_staff_user_id() . ' OR assigned = ' . get_staff_user_id() . ')';
                  }
                  // Junk leads are excluded from total
                  $total_leads = total_rows(db_prefix() . 'leads', ($where == '' ? 'junk=0' : $where .= ' AND junk =0'));
                  if ($where == '') {
                      $where .= 'status=1';
                  } else {
                      $where .= ' AND status =1';
                  }
                  $total_leads_converted         = total_rows(db_prefix() . 'leads', $where);
                  $percent_total_leads_converted = ($total_leads > 0 ? number_format(($total_leads_converted * 100) / $total_leads, 2) : 0);
                  ?>
                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                        
                        <span class="tw-truncate">
                            <?php echo _l('leads_converted_to_client'); ?>
                        </span>
                    </div>
                    <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0">
                        <?php echo e($total_leads_converted); ?> /
                        <?php echo e($total_leads); ?>
                    </span>
                </div>

               
            </div>
        </div>
        <?php } ?>
        <div class="quick-stats-projects col-xs-12 col-md-6 col-sm-6 <?php echo e($initial_column); ?> tw-mb-2 sm:tw-mb-0">
            <div class="top_stats_wrapper">
                <?php
                  $_where         = '';
                  $project_status = get_project_status_by_id(2);
                  if (staff_cant('view', 'projects')) {
                      $_where = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
                  }
                  $total_projects               = total_rows(db_prefix() . 'projects', $_where);
                  $where                        = ($_where == '' ? '' : $_where . ' AND ') . 'status = 2';
                  $total_projects_in_progress   = total_rows(db_prefix() . 'projects', $where);
                  $percent_in_progress_projects = ($total_projects > 0 ? number_format(($total_projects_in_progress * 100) / $total_projects, 2) : 0);
                  ?>
                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-font-medium tw-inline-flex tw-items-center text-neutral-500 tw-truncate">
                       
                        <span class="tw-truncate">
                            <?php echo e(_l('projects') . ' ' . $project_status['name']); ?>
                        </span>
                    </div>
                    <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0">
                        <?php echo e($total_projects_in_progress); ?> /
                        <?php echo e($total_projects); ?>
                    </span>
                </div>

              
            </div>
        </div>
        <div class="quick-stats-tasks col-xs-12 col-md-6 col-sm-6 <?php echo e($initial_column); ?>">
            <div class="top_stats_wrapper">
                <?php
                  $_where = '';
                  if (staff_cant('view', 'tasks')) {
                      $_where = db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ')';
                  }
                  $total_tasks                = total_rows(db_prefix() . 'tasks', $_where);
                  $where                      = ($_where == '' ? '' : $_where . ' AND ') . 'status != ' . Tasks_model::STATUS_COMPLETE;
                  $total_not_finished_tasks   = total_rows(db_prefix() . 'tasks', $where);
                  $percent_not_finished_tasks = ($total_tasks > 0 ? number_format(($total_not_finished_tasks * 100) / $total_tasks, 2) : 0);
                  ?>
                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                        
                        <span class="tw-truncate">
                            <?php echo _l('tasks_not_finished'); ?>
                        </span>
                    </div>
                    <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0">
                        <?php echo e($total_not_finished_tasks); ?> / <?php echo e($total_tasks); ?>
                    </span>
                </div>

            </div>
        </div>
    </div>
</div>