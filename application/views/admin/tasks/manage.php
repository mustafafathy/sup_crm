<?php defined('BASEPATH') or exit('No direct script access allowed');

$tasks_summary = tasks_summary_data();
$user_ID = get_staff_user_id();

$tasks_list = [];
foreach ($tasks_summary as $summary) {
    $tasks_list[strtolower($summary['name'])] = [
        'total_tasks' => $summary['total_tasks'],
        'status_id' => $summary['status_id'],

        'name' => $summary['name'],
        'total_my_tasks' => $summary['total_my_tasks'],
        'class' => $summary['class'],

    ];
}
$CI = &get_instance();

$statuses = $CI->tasks_model->get_statuses();

?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
         
        <div class="full-flex tasks-section">
            <div class=" tw-items-center tasks_header">
                <div class="sub-flex page-section">
                    <h3 class="card-header">Tasks Summary</h3>

                    <div class="shrink tool">

                        <span class="tab-title"> Tasks Assigned to: </span>
                        <div class="tab-section full-flex tw-items-center " id="tab_tasks">


                            <div class="tab-switch tw-text-center left" id="tasks-switcher"
                                data-user="<?php echo e($user_ID); ?>" style="width: 1280px;max-width: 8000px;">
                                <div id="me_tab" data-id="<?php echo htmlspecialchars($user_ID); ?>" class="tab active"
                                    tab-direction="left">Me</div>
                                <div id="oth_tab" data-id="" class="tab " tab-direction="left">Others</div>



                                <div id="dep_tab" data-id="" class="tab" tab-direction="right">Department</div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>

            <div class="sub-flex shrink tw-justify-between">


                <?php if (staff_can('create', 'tasks')) { ?>
                    <a href="#" onclick="new_task(<?php if ($this->input->get('project_id')) {
                        echo "'" . admin_url('tasks/task?rel_id=' . $this->input->get('project_id') . '&rel_type=project') . "'";
                    } ?>); return false;" class="button-pr">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_task'); ?>
                    </a>
                <?php } ?>
                <a href="<?php echo admin_url(!$this->input->get('project_id') ? ('tasks/switch_kanban/' . $switch_kanban) : ('projects/view/' . $this->input->get('project_id') . '?group=project_tasks')); ?>"
                    class=" button-pr" data-toggle="tooltip" data-placement="top"
                    style="width: auto;height: inherit;margin-left:8px;"
                    data-title="<?php echo $switch_kanban == 1 ? _l('switch_to_list_view') : _l('leads_switch_to_kanban'); ?>">
                    <?php if ($switch_kanban == 1) { ?>
                        <i class="fa-solid fa-table-list"></i>
                    <?php } else { ?>
                        <i class="fa-solid fa-grip-vertical"></i>
                    <?php }
                    ; ?>
                </a>
            </div>





        </div>
        <div id="grid_tasks_all" class="grid_tasks tw-grid xs:tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-5   "
            style="display: none;">
            <?php foreach ($tasks_list as $task_key => $task_data): ?>
                <div data-value="<?php echo $task_data['name']; ?>" data-status="<?php echo $task_data['status_id']; ?>"
                    data-filter=""
                    class="card-element full-flex tw-flex-col tw-justify-between <?php echo $switch_kanban == 0 ? 'clickable-select' : '' ?> <?php echo $task_data['class']; ?>">
                    <?php echo htmlspecialchars($task_data['name']); ?>
                    <span id class="count">
                        <?php echo htmlspecialchars($task_data['total_tasks']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>


        <div id="grid_tasks_me" class="grid_tasks tw-grid xs:tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-5    ">
            <?php foreach ($tasks_list as $task_key => $task_data): ?>
                <div data-value="<?php echo $task_data['name']; ?>" data-status="<?php echo $task_data['status_id']; ?>"
                    data-filter="<?php echo get_staff_user_id(); ?>"
                    class="card-element full-flex tw-flex-col tw-justify-between <?php echo $switch_kanban == 0 ? 'clickable-select' : '' ?> <?php echo $task_data['class']; ?>">
                    <?php echo htmlspecialchars($task_data['name']); ?>
                    <span id class="count">
                        <?php echo $task_data['total_my_tasks']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="content">
        <?php
        if ($this->session->has_userdata('tasks_kanban_view') && $this->session->userdata('tasks_kanban_view') == 'true') { ?>
            <div class="kan-ban-tab" id="kan-ban-tab">
                <div class="row">
                    <div id="kanban-params">
                        <?php echo form_hidden('project_id', $this->input->get('project_id')); ?>
                    </div>
                    <div class="container-fluid">
                        <div id="kan-ban" class="tw-grid xs:tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-5"></div>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="panel_s">
                <div class="panel-body">
                    <a href="#" data-toggle="modal" data-target="#tasks_bulk_actions"
                        class="hide bulk-actions-btn table-btn"
                        data-table=".table-tasks"><?php echo _l('bulk_actions'); ?></a>
                    <div class="panel-table-full">
                        <?php $this->load->view('admin/tasks/_table', ['bulk_actions' => true]); ?>
                    </div>
                    <?php $this->load->view('admin/tasks/_bulk_actions'); ?>
                </div>
            </div>
        <?php } ?>
    </div>







</div>
</div>
<?php init_tail(); ?>
<script>
    taskid = '<?php echo e($taskid); ?>';
    $(function () {
        tasks_kanban();
    });
</script>
<style>
    /* #tasks_wrapper>div:nth-child(5) {
        display: none;
    }
*/
    #tasks_wrapper>div:nth-child(2) {
        display: none;
    }

    .modal-body #description {
        resize: none;
    }

    .modal-body label, .bold {
        font-weight: bold;
    }

    .card-element span {
        color: #fff;
    }

    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        background-color: #0B70A6;
    }

    #tasks>thead>tr>th {
        min-width: 180px;
        background-color: #0B70A6;
        /* border-bottom: 1px solid #eeeaf5; */
        box-sizing: border-box;
        height: 60px;
        text-align: center;
        font-size: 16px;
        color: #fff;
        font-family: 'Poppins';
        height: 100%;
        position: relative;
        padding: 12px;
        box-sizing: border-box;
        text-align: center;
        font-size: 16px;
        color: #fff;
        font-family: Poppins;
    }

    #kan-ban .panel-body .lead-name,
    #kan-ban .panel-body .task-name {
        background: transparent;
        padding-left: 25px;
    }

    li.lead-kan-ban.current-user-lead .panel-body,
    li.task.current-user-task .panel-body {
        /* background: #eff6ff !important;
        border: 1px solid #dbeafe !important; */
    }

    #kan-ban .panel-body {
        background-color: #FFFFFF !important;
    }

    .kan-ban-content {
        background: var(--background) !important;
        padding: 8px 0px;
    }

    #kan-ban>ul>li>div>div.kan-ban-content-wrapper>div>ul {
        background: #F4F6F6;
    }

    #kan-ban .panel-body .lead-name,
    #kan-ban .panel-body .task-name {
        background: transparent;
        padding-left: 0px;
        display: flex;
        flex-wrap: nowrap;
        flex-direction: row;
        align-content: center;
        justify-content: flex-start;
        align-items: flex-end;
        font-family: Poppins;
        font-size: 12px;
        font-weight: 400;
        line-height: 20px;
        letter-spacing: -0.01em;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        margin: 6px;
    }

    .card-date {
        color: #D4D4D4;
        font-family: Poppins;
        /* font-size: 8px; */
        font-weight: 400;
        line-height: 20px;
        letter-spacing: -0.01em;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
    }

    .card-footer-kan {
        padding-left: 8px;
        margin-top: 8px;
    }

    .card-content-kan {
        display: flex;
        width: fit-content;
        margin: 4px;
        cursor: grabbing;
        gap: 4px;
    }

    .empty-ban {
        background: var(--background) !important;
    }

    .btn-primary {
        background: #023047;
    }

    .current-user-task {}

    .modal-footer {
        /* display: flex; */
        padding: 15px;
        text-align: right;
        border-top: 0;
        flex-wrap: wrap;
        justify-content: space-evenly;
        align-content: center;
        align-items: baseline;
    }

    .modal-footer button {
        width: 150px;

    }

    .modal-footer .btn.btn-primary {
        background-color: #0B70A6;
    }

    /* .modal-footer .btn.btn-primary{
        background-color: #0B70A6;
    } */

    .card-element {
        border: none;
    }

    #kan-ban .panel-body {
        border-top: 0;
        padding: 15px 20px;
        margin: 8px 0px;
        padding: var(--spacing-md);
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        filter: drop-shadow(0px 1px 3px rgba(0, 0, 0, 0.08));
    }

    .shrink.tool {
        display: flex;
        flex-wrap: nowrap;
        flex-direction: row;
        align-content: center;
        justify-content: flex-start;
        align-items: center;
        padding: 20px;
        gap: 12px;
    }

    .status-card-tasks {
        position: relative;
    }

    .card-footer-kan .status-kan-content {
        border-radius: 20px;
    }

    hr {
        margin-top: 12px;
        margin-bottom: 0px;
        border: 0;
        border-top: 1px solid #eee;
        padding-bottom: 4px;
    }


    #_task .row {
        margin: 0px
    }

    #kan-ban {
        grid-template-columns: repeat(5, minmax(0, 1fr));
        display: grid;
        justify-content: space-evenly;
        justify-items: stretch;
        align-content: space-between;
        /* align-items: center; */
        gap: 16px;

    }

    .panel-body {
        --tw-bg-opacity: 1;
        border-radius: .375rem;
        position: relative;
        padding: 1.5rem !important;
        padding-top: 0px !important;
        margin-top: 0px !important;
        padding: 20px 20px 20px 20px;
        min-width: 186px !important;
        border-radius: 8px;
        clip-path: border-box;
    }

    .kan-ban-col {
        width: initial;
        margin-right: 0px;
        display: inline-block;
        float: left;
        /* padding: 6px; */
        border-radius: 8px;
    }

    .status.tasks-status {
        /* padding: 6px; */
        border-radius: 8px;
    }

    #tasks>thead>tr>th.sorting_disabled {
        max-width: 10px;
        min-width: 10px;
    }

    #tasks>thead>tr>th.sorting.sorting_asc,
    #tasks>thead>tr>th.sorting.sorting_desc {
        color: #000;
        border-bottom: 1px solid #fb8500;
    }

    #tasks>thead>tr>th.sorting {
        color: #fff;
    }

    #tasks>thead>tr>th:nth-child(2) {
        max-width: 80px;
        min-width: 80px;

    }

    table.dataTable thead th.sorting_asc:after,
    table.dataTable thead th.sorting_desc:after {
        background-color: #fb8500;
    }

    a {
        position: relative;
        font-size: 14px;
        font-family: Poppins;
        color: #000;
        text-align: left;
    }

    .button-pr:hover {
        color: #fb8500;

    }

    .button-pr {
        background-color: #0B70A6;
    }

    .flex-label {
        color: #3b82f6;
        display: flex;
        /* position: absolute; */
        border-radius: 4px;
        background-color: rgba(211, 211, 211, 0.2);
        /* height: 28px; */
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        padding: 4px 8px;
        box-sizing: border-box;
        gap: 10px;
        /* text-align: left; */
        font-size: 14px;
        /* color: #000; */
        font-family: Poppins;
        /* flex-wrap: wrap; */
        max-width: 184px;
        box-shadow: -5px 5px 3px #D3D3D333;
        filter: drop-shadow(0px 1px 3px #D3D3D333);
        align-content: center;
    }

    .form-inline .checkbox input[type=checkbox],
    .form-inline .radio input[type=radio] {
        cursor: pointer;
        width: 20px;
        height: 20px;
    }

    td .checkbox label:before {
        /* padding-left: 10px; */
        background: #ffffff;
        border-radius: 4px;
        /* border: thick; */
        /* min-height: 28px; */
        /* min-width: 28px; */
        /* display: inline; */
        /* line-height: 1.42857143; */
        /* align-items: center; */
        /* align-content: center; */
        border-color: #023047;
        border: solid;
        border-width: medium;
    }

    .task-name .count {
        font-size: 15px;
        font-weight: bold;
        /* margin-top: var(--spacing-xs); */
        padding: 0px 10px 4px 0px;
        border-radius: 500%;
        margin-top: 0px;
        cursor: grab;
    }

    div.panel-heading {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        color: #fff;
        display: flex;
        align-items: center;
        padding: 15px 25px;
        font-size: 12px !important;
        font-weight: 400;
        line-height: 20px;
        letter-spacing: -0.01em;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        /* gap: 38px; */
        border-radius: 6px;
        align-content: center;
        justify-content: flex-start;
        flex-wrap: nowrap;
        flex-direction: row;
    }



    div.panel-heading[data-status-id="5"] {
        background-color: #e15402;
    }



    div.panel-heading[data-status-id="4"] {
        background-color: #08a4a7;
    }



    div.panel-heading[data-status-id="3"] {
        background-color: #f8b202;
    }

    /* ul[data-col-status-id="5"] li {
        filter: drop-shadow(1px 1px 1px #e15402);
    }
    ul[data-col-status-id="4"] li {
        filter: drop-shadow(1px 1px 1px #08a4a7);
    }
    ul[data-col-status-id="2"] li {
        filter: drop-shadow(1px 1px 1px #fb8500);
    }
    ul[data-col-status-id="3"] li {
        filter: drop-shadow(1px 1px 1px #f8b202);
    }
    ul[data-col-status-id="1"] li {
        filter: drop-shadow(1px 1px 1px #32aac7);
    } */
    div.panel-heading[data-status-id="2"] {
        background-color: #fb8500;
    }



    div.panel-heading[data-status-id="1"] {
        background-color: #32aac7;
    }



    #tasks>tbody>tr>td {
        position: relative;
        display: table-cell;
        padding: 12px;
        box-sizing: border-box;
        text-align: left;
        font-size: 14px;
        color: #000;
        font-family: Poppins;
        text-align: -webkit-center;
        position: relative;
        font-size: 14px;
        font-family: Poppins;
        color: #000;
        place-content: center;
        max-width: 168px;
    }

    .panel-body {
        --tw-bg-opacity: 1;
        border-radius: .375rem;
        position: relative;
        padding: 1.5rem !important;
        padding-top: 0px !important;
        margin-top: 0px !important;
        padding: 20px 20px 20px 20px;
        min-width: 320px;
        border-radius: 8px;
        clip-path: border-box;
    }

    li.task.ui-sortable-handle div.panel-body {
        border: 1px solid #D3D3D3;

    }

    table.dataTable {
        clear: both;
        margin-top: 0px !important;
        margin-bottom: 6px !important;
        max-width: none !important;
        border-collapse: separate !important;
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        overflow: visible;

    }


    .content {
        margin: 10px 10px 10px 10px;
        padding: 10px 10px 10px 10px;
        min-width: 320px;
    }

    .table-section {
        padding: 00px 40px;
    }

    .complete {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        background-color: #e15402;
        /* font-size: 12px; */
        font-family: Poppins;
        color: #fff;
        display: flex;
        align-items: flex-start;
        padding: 15px 25px;
    }

    .notstarted {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        background-color: #32aac7;
        /* font-size: 12px; */
        font-family: Poppins;
        color: #fff;
        display: flex;
        align-items: flex-start;
        padding: 15px 25px;

    }

    .awaitingfeedback {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        background-color: #fb8500;
        /* font-size: 12px; */
        font-family: Poppins;
        color: #fff;
        display: flex;
        align-items: flex-start;
        padding: 15px 25px;
    }

    .testing {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        background-color: #f8b202;
        /* font-size: 12px; */
        font-family: Poppins;
        color: #fff;
        display: flex;
        align-items: flex-start;
        padding: 15px 25px;
    }

    .inprogress {
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        background-color: #08a4a7;
        /* font-size: 12px; */
        font-family: Poppins;
        color: #fff;
        display: flex;
        align-items: flex-start;
        padding: 15px 25px;
    }

    .checkbox label:after,
    .checkbox label:before {
        display: inline-block;
        left: 0;
        margin-left: -20px;
        position: absolute;
        /* padding-left: 10px; */
        background: transparent;
        border-radius: 4px;
        /* border: thick; */
        /* min-height: 28px; */
        /* min-width: 28px; */
        /* display: inline; */
        /* line-height: 1.42857143; */
        /* align-items: center; */
        /* align-content: center; */
        border-color: #ffffff;
        /* border: solid; */
        border-width: medium;
    }

    .grid_tasks .active {
        /* border: blue; */
        /* border-radius: 2px; */
        /* border-style: solid; */
        border: 2px solid #004c73;
    }

    table .row-options {
        /* padding: 12px 0px 0; */
        position: relative;
        left: -9999em;
        font-size: 12px;
        color: #cbd5e1;
        text-align: left;
    }

    .panel,
    .panel-body,
    .panel_s {
        background-color: transparent;
        border-width: 0px;
    }

    table.dataTable thead .sorting:after {
        top: 30px;
    }

    .page-section {}

    a>i {
        padding: 0 6px 0px 2px;
    }

    .clickable-select {
        cursor: pointer;
    }

    .clickable-select.active {}

    .tasks_header {
        min-width: 60%;
        width: -webkit-fill-available;
        padding-right: 20px;
    }
    .tasks_header2 {
        min-width: 80%;
        width: -webkit-fill-available;
        padding-right: 10px;
    }

    .tab-section {
        margin-right: 20px;
    }

                            	.priority{
                                max-width:140px;}
    .tasks-count {
        padding: 0px 10px;
        border-radius: 45%;
        background-color: #FAFAFA;
        color: black;
        margin: 0 10px;
        border: 1px solid #DEDEDE;
        padding: 2px 8px 2px 8px;
    }

    .kan-ban-content .kanban-empty h4 {
        color: #004c74;
        /* background: var(--background) !important; */
        background: transparent;
    }

    /* Table Styling */
    .table-tasks {
        width: 100%;
        border-spacing: 0px 8px;
        /* 0px horizontal, 8px vertical spacing */
        border-collapse: separate;
        background: var(--background) !important;
    }

    /* Table Row Shadow Effect */
    .table-tasks tr {
        /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
        /* Shadow effect */
        transition: box-shadow 0.2s ease-in-out;
        /* Smooth hover effect */
        height: 80px;
        border-bottom: 1px solid #D4D4D4;
        background: #ffffff;
    }

    .table-tasks tr:nth-child(even){
        background: #F9F9F9;
    }


    /* Table Cell Styling */
    .table-tasks td {
        padding: 12px;
    }

    /* Hover Effect (Optional) */
    .table-tasks tr:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        /* Enhanced shadow on hover */
    }

    /* Table Header Styling (Optional) */
    .table-tasks th {
        padding: 12px;
        /* background-color: #f87171; */
        /* Light red background */
        color: #fff;
        /* White text color */
        text-align: left;
        border: 1px solid #dc2626;
    }

    /* Base Table Styles */
    .table-tasks {
        width: 100%;
        border-spacing: 0px 8px;
        border-collapse: separate;
    }

    /* Table Cell Styling */
    .table-tasks td {
        padding: 12px;
        position: relative;
        /* To allow relative positioning of child elements */
        overflow: hidden;
        /* Hide overflowing elements during animation */
    }

    /* Name Styling */
    .main-tasks-table-href-name {
        display: block;
        /* Ensure it takes full width of the cell */
        position: relative;
        transition: transform 0.3s ease-in-out;
        /* Smooth upward transition */
        z-index: 1;
        /* Keep it above row-options initially */
    }

    /* Row Options Styling */
    .row-options {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 6px 12px;
        /* background-color: #f87171; Light red background */
        color: #fff;
        text-align: center;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        /* Slightly offset downward initially */
        transition: opacity 0.3s ease, transform 0.3s ease;
        /* Smooth appear animation */
        display: none;
        margin-top: -18px;
    }

    /* Hover Effect: Move Name Up and Show Options */
    .table-tasks td:hover .main-tasks-table-href-name {
        transform: translateY(-20px);
        /* Move name upwards */
    }

    .table-tasks td:hover .row-options {
        display: block;
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        /* Bring row-options to its original position */
    }
</style>
<script>

    function updateDisplay(showMeCards) {
        // Cache task grid elements and jQuery selectors for better performance
        const meCards = document.getElementById("grid_tasks_me");
        const otherCards = document.getElementById("grid_tasks_all");
        const taskStatusFilter = $('#tasks-switcher');
        const userId = <?php echo json_encode($user_ID); ?>; // Securely encode PHP variable
        const switchKanban = <?php echo json_encode($switch_kanban); ?>; // Kanban mode flag

        // Toggle visibility of "My Tasks" and "All Tasks"
        meCards.style.display = showMeCards ? "grid" : "none";
        otherCards.style.display = showMeCards ? "none" : "grid";

        // Set or clear the `data-user` attribute based on the selected view
        const userFilterValue = showMeCards ? userId : null;
        taskStatusFilter.attr('data-user', userFilterValue);

        // Debugging logs for tracing the toggle status and filter
        console.log(`Show My Tasks: ${showMeCards}`);
        console.log(`Task Status Filter Data User: ${taskStatusFilter.attr('data-user')}`);

        // Check if Kanban mode is enabled
        if (switchKanban) {
            // Log the filter application in Kanban view
            console.log('Kanban view reloaded with filter:', userFilterValue);

            // Reload the Kanban view and check for empty columns
            tasks_kanban();
            check_kanban_empty_col("ul.tasks-status");
        }
    }





    // Event listeners for "My Tasks" and "All Tasks" tabs
    document.getElementById("me_tab").addEventListener("click", () => updateDisplay(true));
    document.getElementById("oth_tab").addEventListener("click", () => updateDisplay(false));

    // Initialize task switching with the user ID fetched from PHP
    // updateDisplay(true); // Show "My Tasks" by default
    // DOMContentLoaded event for initializing tab switching
    document.addEventListener("DOMContentLoaded", function () {
        const tabContainer = document.querySelector("#tasks-switcher");
        const tabs = tabContainer.querySelectorAll(".tab");

        tabs.forEach((tabElement) => {
            tabElement.addEventListener("click", function () {
                if (tabElement.classList.contains("active")) return;

                // Get the animation direction (left/right)
                const direction = tabElement.getAttribute("tab-direction");

                // Update tab animation and active state
                tabContainer.classList.remove("left", "right");
                tabContainer.classList.add(direction);

                tabContainer.querySelector(".tab.active").classList.remove("active");
                tabElement.classList.add("active");
            });
        });
    });

</script>
<script>
updateDisplay(true);
    $(document).ready(function () {
        // Click event for sorting table headers
        $('#tasks > thead > tr > th.sorting').on('click', function () {
            const clickedElement = $(this);
            const newColor = '#0B70A6'; // Default color for sorted headers

            // Change the background color of all sorting headers
            $('#tasks > thead > tr > th').css('background-color', newColor);
        	// Remove Borders form all cards
        	$('div.card-element').removeClass('active');
        });
    });
    $(document).ready(function () {
        // Click event for elements with .clickable-select in the task grid
        $('.grid_tasks').on('click', '.clickable-select', function () {
            const clickedElement = $(this);

            // Toggle active state and reset table headers if clicked again
            if (clickedElement.hasClass('active')) {
                clickedElement.removeClass('active');
                $('#tasks > thead > tr > th').css('background-color', '#0B70A6');
                reload_tasks_tables();
                return;
            }

            // Apply the clicked element's background color to the table headers
            const selectedColor = clickedElement.css('background-color');
            $('#tasks > thead > tr > th').css('background-color', selectedColor);

            // Prepare filters based on status and data attributes
            const statusId = clickedElement.data('status');
            const filter = clickedElement.data('filter');
            const filters = {
                search: String(statusId),
                selector: 'span.flex-label.table-cell.status-cell-content',
                data_filter: String(filter),
                filter_selector: 'div.table-assignee > a > img',
            };

            <?php if ($switch_kanban != 1) { ?>
                reload_or_filter_tasks_tables(filters); // Apply filters or reload tasks
            <?php } ?>

            // Update active state
            $('.clickable-select').removeClass('active');
            clickedElement.addClass('active');
        });
    });
    // Apply background styling to empty Kanban lists
    $('.text-center.not-sortable.mtop30.kanban-empty.ui-sortable-handle')
        .parent('ul')
        .css('background', '#E8F7FF !important');

    // Optional: Custom filter for task status
    // $('#taskStatusFilter').on('change', function () {
    //     const selectedStatus = $(this).val();
    //     const filters = {
    //         index: table_tasks.find("th.status").index(),
    //         search: selectedStatus,
    //     };
    //     reload_or_filter_tasks_tables(filters);
    // });

    // Helper function to select option by value
    function selectOptionByValue(selectId, value) {
        const selectElement = document.getElementById(selectId);
        selectElement.value = value;
    }

</script>



</body>

</html>