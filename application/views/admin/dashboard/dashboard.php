<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
// Today's Date
$today_date = date('Y-m-d'); // Today's date

// Weekly Date (start of the week)
$week_start_date = date('Y-m-d', strtotime('monday this week')); // Start of the current week

// Get leads summary and format as a list
$month_start_date = date('Y-m-01'); // Start of the current month
$summary = get_leads_summary($month_start_date);
$statusList = [];
foreach ($summary as $status) {
    $statusList[strtolower($status['name'])] = [
        'name' => $status['name'],
        'total' => $status['total'],
    ];
}
$role = _getCurrentUserRole();
$staff_id = $current_user->staffid;
// Get tasks summary and format as a list
$tasks_summary = tasks_summary_data();
$tasks_list = [];
foreach ($tasks_summary as $summary) {
    $tasks_list[strtolower($summary['name'])] = [
        'total_tasks' => $summary['total_tasks'],
        'name' => $summary['name'],
        'total_my_tasks' => $summary['total_my_tasks'],
    ];
}
// $user_lead = user_leads();
// $total_leads_today = total_leads_today();
// $total_leads_today = $total_leads_today;
// $role = getCurrentUserRole();

$user_leads_monthly = user_leads_monthly($staff_id,$month_start_date);

$user_leads_monthly = end($user_leads_monthly);
// $user_leads_daily = user_leads_daily($current_user);

// $user_leads_daily = end($user_leads_daily);

$cold_count = $user_leads_monthly->cold_count;
$warm_count = $user_leads_monthly->warm_count;
$hot_count = $user_leads_monthly->hot_count;
$flaming_hot_count = $user_leads_monthly->flaming_hot_count;

// $campaigns_target = campaigns_target();

// $target = $campaigns_target->total_target;
// $missing = (int) $campaigns_target - (int) $total_leads_today; // Calculate missing targets
// if ($missing < 0) {
//     $missing = 0;
// }
// Fetch the top-performing agents
$today_top_agents = get_leads_performance($today_date);
$least_performing_today = end($today_top_agents);
$top_agents_today = array_slice($today_top_agents, 0, 2);
$weekly_top_agents = get_leads_performance($week_start_date);
$least_performing_week = end($weekly_top_agents);
$top_agents_week = array_slice($weekly_top_agents, 0, 2);

// Monthly Date (first day of the month)
$monthly_top_agents = get_leads_performance($month_start_date);
$least_performing_month = end($monthly_top_agents);
$top_agents_month = array_slice($monthly_top_agents, 0, 2);

// Call the function to get today's lead status summary
$lead_status_summary = total_leads_eval();

// Initialize totals for further calculations
$totalMissing = 0;
$totalAchieved = 0;
$totalTarget = 0;

// Loop through the lead status summary to process each row
foreach ($lead_status_summary as $index => $row) {
    $campaignTarget = $row['camp_target'];
    $weightedScore = $row['weighted_score'];
    $isActive = $row['camp_activity'];

    // Calculate the missing target, ensuring it doesn’t go below zero
    $missingScore = max(0, $campaignTarget - $weightedScore);

    // Only include active campaigns in calculations
    if ($isActive) {
        $totalMissing += $missingScore;

        // Check if the weighted score exceeds the campaign target
        if ($campaignTarget < $weightedScore) {
            $totalAchieved += 1;
        }

        // Sum up the total target for active campaigns
        $totalTarget += $campaignTarget;
    }

    // Optional: Display or process each row data as needed
}


$base = $this->baseUrl;
$tasks_link = '/admin/Tasks'; // view all tasks
$performance_link = 'reports/agent_report' ; // Agent report
$missing_link = 'missingreport/report'; // Missing report 
$leads_temprature_link = 'reports/campaign_report'; // campaign report

// $top_agents = get_leads_performance();
if ($role == 1) {
    echo '<style>#tab_tasks { display: none; }</style>';
    echo '<style>.view-details { visibility: hidden; }</style>';
    echo '<style>.tabs_tasks .view-details { visibility: initial; }</style>';
}

elseif(!(is_admin($staff_id)) and ($role == 7))
{
    echo '<style>.leads_temp .view-details { visibility: hidden; }</style>';

}

elseif(!(is_admin($staff_id)) and ($role == 5))
{

}
elseif (is_admin($staff_id) or ($role == 3)) {

}else{

}




?>



<div id="wrapper">
    <div class>
        <div class="tw-mx-auto">
            <?php $this->load->view('admin/includes/alerts'); ?>
            <?php hooks()->do_action('before_start_render_dashboard_content'); ?>
            <div class="summary-widget card">


                <div class="summary-header">
                    <!-- Header Section -->

                    <div class="tab-section full-flex tw-items-center tw-gap-4">


                        <div class="tab-switch tw-text-center left" id="summary-switcher" style="width: 650px;">
                            <div id="re_tab" class="tab active" tab-direction="left">RE Summary</div>
                            <div id="so_tab" class="tab " tab-direction="right">Solar Summary</div>
                        </div>
                    </div>
                </div>
                <!-- Status Bar -->

                <div class="summary-section">
                    <div class="re-summary">
                        <div class="status-bar tw-grid-cols-6 sm:tw-grid-cols-6 lg:tw-grid-cols-9 tw-gap-4">
                            <div class="summary-item card-1">
                                Pending<br><span
                                    class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['pending']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-2">
                                Qualified<br><span
                                    class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['qualified']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-3">
                                Disqualified<br><span
                                    class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['disqualified']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-4">
                                IVE<br><span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['ive']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-5">
                                Pushed<br><span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['pushed']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-6">
                                Callback<br><span
                                    class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['callback']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-7">
                                Duplicate<br><span
                                    class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['duplicate']['total'] : '--' ?></span>
                            </div>
                            <div class="summary-item card-8">
                                Glitch<br><span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['glitch']['total'] : '--' ?></span>
                            </div>
                            <!-- <div class="summary-item re-lost-leads">
                Lost Leads<br><span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? $statusList['lost leads']['total'] : '--' ?></span>
            </div> -->
                        </div>
                    </div>
                    <div class="solar-summary" style="display: none;">
                        <div class="status-bar tw-grid-cols-6 sm:tw-grid-cols-6 lg:tw-grid-cols-8 tw-gap-4">
                            <div class="summary-item card-1">
                                Appts Booked<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['booked']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-2">
                                In-Home<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['in-home']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-3">
                                Virtual<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['virtual']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-4">
                                Rescheduled<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['rescheduled']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-5">
                                Phone Call<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['phone call']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-6">
                                Show Up<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['show up']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-7">
                                Missing<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['missing']['total'] ?? 0 : '--'?></span>
                            </div>
                            <div class="summary-item card-8">
                                IVE<br><span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? $statusList['ive']['total'] ?? 0 : '--'?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="dashboard-section">

                <!-- Widgets Section -->
                <div class="card tw-col-span-2 tw-bg-white tw-shadow-lg  ">
                    <div class>
                        <div class="full-flex tw-justify-between tw-items-center tabs_tasks">
                            <h3 class="card-header">Tasks Summary</h3>
                            <div class="tab-section full-flex tw-items-center tw-gap-4" id="tab_tasks">

                                <span class="tab-title">Tasks Assigned to:</span>
                                <div class="tab-switch tw-text-center left" id="tasks-switcher" style="width: 650px;">
                                    <div id="me_tab" class="tab active" tab-direction="left">Me
                                    </div>
                                    <div id="oth_tab" class="tab" tab-direction="right">Others</div>

                                </div>
                            </div>
                            <a href="<?php echo($tasks_link)?> " class="view-details">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                        </div>



                        <div id="grid_tasks_all" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-4 lg:tw-grid-cols-5 "
                            style="display: none;">
                            <?php foreach ($tasks_list as $task_key => $task_data): ?>
                                <div
                                    class="tw-flex-1 card-element tw-bg-gray-100  tw-text-center full-flex tw-flex-col tw-justify-between">
                                    <?php echo htmlspecialchars($task_data['name']); ?>
                                    <span id class="count">
                                        <?php echo htmlspecialchars($task_data['total_tasks']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>


                        <div id="grid_tasks_me" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-4 lg:tw-grid-cols-5  ">
                            <?php foreach ($tasks_list as $task_key => $task_data): ?>
                                <div
                                    class="tw-flex-1 card-element tw-bg-gray-100  tw-text-center full-flex tw-flex-col tw-justify-between">
                                    <?php echo htmlspecialchars($task_data['name']); ?>
                                    <span id class="count">
                                        <?php echo htmlspecialchars($task_data['total_my_tasks']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>


                </div>

                <div class="widget card tw-bg-white tw-shadow-lg  " style="">

                    <div class="full-flex tw-justify-between tw-items-center">
                        <h3 class="card-header">HR Overview</h3>
                        <a href="#" class="view-details tw-hidden">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                    </div>

                    <div class="widget-content full-flex ">
                        <div
                            class="card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Annual Requests<span class="count">--</span>
                        </div>
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            HeadCount<span class="count">--</span>
                        </div>
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Action Plan<span class="count">--</span>
                        </div>
                    </div>

                </div>



                <div class="card tw-col-span-2 tw-bg-white tw-shadow-lg  ">
                    <div class="full-flex tw-justify-between tw-items-center">
                        <h3 class="card-header">Performance</h3>
                        <div class="tab-section full-flex tw-items-center tw-gap-4">


                            <div class="tab-switch tw-text-center left" id="performance-switcher"
                                style="width: 1280px;max-width: 8000px;">
                                <div id="today_tab" class="tab " tab-direction="left">Today`s</div>
                                <div id="week_tab" class="tab" tab-direction="left">This Week</div>
                                <div id="month_tab" class="tab active" tab-direction="right">This Month</div>
                            </div>
                        </div><a href="<?php echo($performance_link)?>" class="view-details">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="performance tw-grid-cols-3 tw-gap-4" id="monthly">
                        <div class="agent full-flex tw-flex-col tw-items-center"
                            style="border-radius: 14px;border-color: #E15402;">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($least_performing_month->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>
                                <div class="internal-card">
                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($least_performing_month->staff_name); ?></span>
                                    <span class="">Least Performance</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #E15402;">
                                <span class="internal-title">Least Performance</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_month[0]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>
                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_month[0]->staff_name); ?></span>

                                    <span class="">Top Agent</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_month[1]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>

                                <div class="internal-card">
                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_month[1]->staff_name); ?></span>

                                    <span class="">Second Place</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                    </div>
                    <div class="performance tw-grid-cols-3 tw-gap-4" id="weekly" style="display: none;">
                        <div class="agent full-flex tw-flex-col tw-items-center"
                            style="border-radius: 14px;border-color: #E15402;">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($least_performing_week->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>

                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($least_performing_week->staff_name); ?></span>

                                    <span class="">Least Performance</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #E15402;">
                                <span class="internal-title">Least Performance</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_week[0]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>
                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_week[0]->staff_name); ?></span>


                                    <span class="">Top Agent</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_week[1]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>

                                <div class="internal-card">
                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_week[1]->staff_name); ?></span>

                                    <span class="">Second Place</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                    </div>
                    <div class="performance tw-grid-cols-3 tw-gap-4" id="daily" style="display: none;">
                        <div class="agent full-flex tw-flex-col tw-items-center"
                            style="border-radius: 14px;border-color: #E15402;">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($least_performing_today->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>

                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($least_performing_today->staff_name); ?></span>

                                    <span class="">Least Performance</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #E15402;">
                                <span class="internal-title">Least Performance</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_today[0]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>
                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_today[0]->staff_name); ?></span>


                                    <span class="">Top Agent</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                        <div class="agent full-flex tw-flex-col tw-items-center">
                            <!-- Container for Image and Name -->
                            <div class="flex-card-img">
                                <?php echo staff_profile_image($top_agents_today[1]->staffid, [
                                    'img',
                                    'img-responsive',
                                    'staff-performance-image-small',
                                    'tw-w-12',
                                    'tw-h-12',
                                    'tw-rounded-full',
                                    'tw-mr-2'
                                ]); ?>
                                <div class="internal-card">

                                    <span
                                        class="internal-name"><?php echo htmlspecialchars($top_agents_today[1]->staff_name); ?></span>

                                    <span class="">Second Place</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="tw-w-full tw-text-center tw-text-white tw-py-1 tw-rounded-b-lg"
                                id="agent_title_card" style="background: #219EBC;">
                                <span class="internal-title">Top Agent</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card tw-bg-white tw-shadow-lg  " style="">
                    <div class="full-flex tw-justify-between tw-items-center">
                        <h3 class="card-header">RE Today’s Missing</h3>
                        <a href="<?php echo($missing_link)?>" class="view-details">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                    </div>

                    <div class="widget-content full-flex ">
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Target<span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? ($totalTarget) : '--' ?></span>
                        </div>
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Achieved<span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? ($totalAchieved) : '--' ?></span>
                        </div>
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Missing<span class="count"><?= in_array(get_staff_account_type(), [1,3]) ? ($totalMissing) : '--' ?></span>
                        </div>
                    </div>
                </div>


                <div class="container-custom">

                    <div class="card tw-bg-white tw-shadow-lg  ">
                        <div class="full-flex tw-justify-between tw-items-center leads_temp">
                            <h3 class="card-header">Leads Temperature</h3>
                            <a href="<?php echo($leads_temprature_link)?>" class="view-details">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="full-flex tw-grid-cols-4" id="leads_temp">
                            <div
                                class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                Cold<span class="count"><?php echo ($cold_count) ?></span>
                            </div>
                            <div
                                class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                Warm<span class="count"><?php echo ($warm_count) ?></span>
                            </div>
                            <div
                                class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                Hot<span class="count"><?php echo ($hot_count) ?></span>
                            </div>
                            <div
                                class="card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                Flaming Hot<span class="count"><?php echo ($flaming_hot_count) ?></span>
                            </div>
                        </div>

                    </div>




                    <div class="card tw-bg-white tw-shadow-lg  ">
                        <div class="full-flex tw-justify-between tw-items-center">
                            <h3 class="card-header">Appts Type</h3>
                            <a href="#" class="view-details tw-hidden">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                        </div>

                        <div class="widget-content full-flex ">
                            <div
                                class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                Virtual<span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? 0 : '--' ?></span>
                            </div>
                            <div
                                class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                                In-Home<span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? 0 : '--' ?></span>
                            </div>
                        </div>
                    </div>



                </div>

                <div class="card tw-bg-white tw-shadow-lg  " style="">

                    <div class="full-flex tw-justify-between tw-items-center">
                        <h3 class="card-header">Solar Today’s Missing</h3>
                        <a href="#" class="view-details tw-hidden">Full Details <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="widget-content full-flex ">
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Target<span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? 0 : '--' ?></span>
                        </div>
                        <div
                            class="tw-flex-1 card-element tw-bg-gray-100   tw-text-center full-flex tw-flex-col tw-justify-between">
                            Missing<span class="count"><?= in_array(get_staff_account_type(), [2,3]) ? 0 : '--' ?></span>
                        </div>
                    </div>
                </div>
            </div>


        </div>




        <?php hooks()->do_action('after_dashboard'); ?>
    </div>
</div>
</div>
<script>
    app.calendarIDs = '<?php echo json_encode($google_ids_calendars); ?>';
    console.log(<?php $top_agents ?>)
</script>
<?php init_tail(); ?>
<?php $this->load->view('admin/utilities/calendar_template'); ?>
<?php $this->load->view('admin/dashboard/dashboard_preload'); ?>
</body>

</html>