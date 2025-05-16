<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- <link rel="stylesheet" href="<?php echo base_url('assets/tables.css'); ?>"> -->

<?php if ($_GET['account'] == 2) blank_page('SOLAR MISSING REPORT IS COMING SOON :)'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>

<body>
    <div id="wrapper">
        <?php
        $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : $today;
        $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : $today;

        // Database credentials
        $hostname = "localhost";
        $username = "crm";
        $password = "SgMGLDSKLq9FTGV";
        $database = "crm";

        // Connect to the database
        $mysqli = new mysqli($hostname, $username, $password, $database);

        // Check connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // Query to get lead source name and count of statuses for the current date, including the total count for each source
        $query = "
            SELECT 
            ls.name as lead_source_name,
            ls.active as camp_activity,
            ls.target as camp_target,
            COUNT(tl.id) as total_leads,
            SUM(CASE WHEN ts.name = 'Qualified' THEN 1 ELSE 0 END) as status_qualified,
            SUM(CASE WHEN ts.name = 'Disqualified' THEN 1 ELSE 0 END) as status_disqualified,
            SUM(CASE WHEN ts.name = 'Pending' THEN 1 ELSE 0 END) as status_pending,
            SUM(CASE WHEN ts.name = 'IVE' THEN 1 ELSE 0 END) as status_ive,
            SUM(CASE WHEN ts.name = 'Callback' THEN 1 ELSE 0 END) as status_callback,
            SUM(CASE WHEN ts.name = 'Duplicate' THEN 1 ELSE 0 END) as status_duplicate,
            SUM(CASE WHEN ts.name = 'Glitch' THEN 1 ELSE 0 END) as status_glitch,
            SUM(CASE WHEN ts.name = 'Pushed' THEN 1 ELSE 0 END) as status_pushed,
            SUM(CASE WHEN ts.id = 1 or ts.id = 2 or ts.id = 8 THEN 
            CASE 
            WHEN cf.value = 'Cold' THEN 1
            WHEN cf.value = 'Warm' THEN 1
            WHEN cf.value = 'Hot' THEN 1
            WHEN cf.value = 'Flaming Hot' THEN 1
            ELSE 1
            END
            ELSE 0 END) as weighted_score,
            SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS status_cold,
            SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS status_warm,
            SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS status_hot,
            SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS status_flaming_hot
            FROM 
            tblleads_sources ls
            LEFT JOIN 
            tblleads tl ON ls.id = tl.source AND (
            (tl.last_status_change IS NOT NULL AND (tl.status = 1 OR tl.status = 8) AND DATE(tl.last_status_change) BETWEEN ? AND ?)
            OR
            DATE(tl.dateadded) BETWEEN ? AND ?
            )
            LEFT JOIN 
            tblleads_status ts ON tl.status = ts.id
            LEFT JOIN
            tblcustomfieldsvalues cf ON tl.id = cf.relid AND cf.fieldid = 4
            WHERE ls.account = 1
            GROUP BY 
            ls.name
            ";

        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            die('Prepare failed: ' . $mysqli->error);
        }

        $stmt->bind_param('ssss', $date_from, $date_to, $date_from, $date_to);
        $stmt->execute();
        $result = $stmt->get_result();

        $lead_status_summary = [];
        while ($row = $result->fetch_assoc()) {
            $lead_status_summary[] = $row;
        }

        $query2 = "
        SELECT 
        SUM(CASE WHEN ls.name = 'Qualified' THEN 1 ELSE 0 END) AS qualified_count,
        SUM(CASE WHEN ls.name = 'Disqualified' THEN 1 ELSE 0 END) AS disqualified_count,
        SUM(CASE WHEN ls.name = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN ls.name = 'IVE' THEN 1 ELSE 0 END) AS ive_count,
        SUM(CASE WHEN ls.name = 'Callback' THEN 1 ELSE 0 END) AS callback_count,
        SUM(CASE WHEN ls.name = 'Duplicate' THEN 1 ELSE 0 END) AS duplicate_count,
        SUM(CASE WHEN ls.name = 'Glitch' THEN 1 ELSE 0 END) AS glitch_count,
        SUM(CASE WHEN ls.name = 'Pushed' THEN 1 ELSE 0 END) as pushed_count,
        SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS cold_count,
        SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS warm_count,
        SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS hot_count,
        SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS flaming_hot_count
        FROM 
        tblleads AS l
        JOIN 
        tblleads_status AS ls ON l.status = ls.id
        LEFT JOIN
        tblcustomfieldsvalues AS cf ON l.id = cf.relid AND cf.fieldid = 4
        WHERE
        (
        (l.last_status_change IS NOT NULL AND (l.status = 1 OR l.status = 8) AND DATE(l.last_status_change) BETWEEN ? AND ?)
        OR
        DATE(l.dateadded) BETWEEN ? AND ?
        )
        ";

        $stmt2 = $mysqli->prepare($query2);
        if (!$stmt2) {
            die('Prepare failed: ' . $mysqli->error);
        }

        $stmt2->bind_param('ssss', $date_from, $date_to, $date_from, $date_to);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $row = $result2->fetch_assoc();
        $qualified_count = $row['qualified_count'] ?? 0;
        $ive_count = $row['ive_count'] ?? 0;
        $disqualified_count = $row['disqualified_count'] ?? 0;
        $pending_count = $row['pending_count'] ?? 0;
        $glitch_count = $row['glitch_count'] ?? 0;
        $callback_count = $row['callback_count'] ?? 0;
        $duplicate_count = $row['duplicate_count'] ?? 0;
        $pushed_count = $row['pushed_count'] ?? 0;
        $cold_count = $row['cold_count'] ?? 0;
        $warm_count = $row['warm_count'] ?? 0;
        $hot_count = $row['hot_count'] ?? 0;
        $flaming_hot_count = $row['flaming_hot_count'] ?? 0;

        // Close the database connection
        $stmt->close();
        $mysqli->close();

        $campaigns = [];
        $info = ['qualified' => $qualified_count, 'pending' => $pending_count, 'cold' => $cold_count, 'warm' => $warm_count, 'hot' => $hot_count, 'flaming_hot' => $flaming_hot_count, 'other' => ($ive_count + $disqualified_count + $glitch_count + $callback_count + $duplicate_count + $pushed_count)];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#FF9F40', '#4BC0C0', '#9966FF'];
        $totalTarget = 0;
        $totalWeight = 0;
        $totalMissing = 0;
        $totalOverAchieved = 0;

        ?>
        <div class="content-wrapper">
            <div class="grid grid-cols-8" style="gap: 10px;">
                <div class="reports-header col-span-6">
                    <h3>RE Summary</h3>
                </div>
                <div class="col-span-2 reports-header-actions grid grid-cols-2 tw-justify-between">
                    <button>
                        <i class="fa-solid fa-up-right-from-square"></i>
                        Export
                    </button>
                    <button>
                        <i class="fa-solid fa-camera"></i>
                        Screenshoot
                    </button>
                </div>
            </div>

            <div class="reports-filter-grid grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-8" style="display: grid;">
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(101, 186, 225, 1);">
                    Pending
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(50, 170, 199, 1);">
                    Qualified
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(8, 164, 167, 1);">
                    Disqualified
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(241, 202, 128, 1);">
                    IVE
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(248, 178, 2, 1);">
                    Pushed
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(251, 133, 0, 1);">
                    Callback
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(225, 84, 2, 1);">
                    Duplicated
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(205, 51, 37, 1);">
                    Glitch
                    <span id="" class="count">
                        0
                    </span>
                </div>
            </div>
        </div>
        <div class="content-wrapper tickets-container grid lg:grid-cols-2 xl:grid-cols-3 gap-5">
            <?php foreach ($lead_status_summary as $index => $row): ?>
                <?php
                $dtarget = $row['camp_target'];
                $weight = $row['weighted_score'];
                $activity = $row['camp_activity'];
                $campName = $row['lead_source_name'];
                $missing = max(0, $dtarget - $weight);
                if ($activity) {
                    $totalTarget = $totalTarget + $dtarget;
                    $totalWeight = $totalWeight + $weight;
                    $totalMissing = $totalMissing + $missing;
                    if ($dtarget - $weight < 0)
                        $totalOverAchieved = $totalOverAchieved + 1;
                }
                $campaigns[] = ['activity' => $activity, 'name' => $row['lead_source_name'], 'missing' => $missing, 'target' => $dtarget, 'color' => isset($colors[$index]) ? $colors[$index] : $colors[0]];
                ?>
                <?php if ($activity) { ?>
                    <div class="ticket" id="ticket_<?php echo $row['lead_source_id']; ?>">
                        <div class="flex items-center justify-between p-4 ticket-header">
                            <h3><?php echo ($activity ? $campName : "<h3 title='" . $campName . "'><del>" . $campName . "</del></h3>") ?></h3>
                            <div><?php echo ($activity ? "active" : '<span class="inactive-ticket">inactive</span>') ?></div>
                        </div>
                        <div class="ticket-status">
                            <h3 class="m-2">status</h3>
                            <?php echo ($dtarget - $weight < 0 ? "<div style='background: transparent url(" . site_url('resources') . "/images/bg_menu.gif)'><span style='color:green'><i class='fa-solid fa-award'></i><div>Over Achieved</div></span></div>" : ($dtarget - $weight == 0 ? '<div style="background: transparent url(' . site_url("resources") . '/images/bg_menu.gif)"><span class="completed" style="color:#34A770;"> <i class="fa-solid fa-trophy"></i><div>Achieved</div></span></div>' : '<div>Not Achieved</div>')); ?>
                        </div>
                        <div class="ticket-body">
                            <div class="main-states grid grid-cols-3 gap-3 justify-between">
                                <div class="text-center">
                                    <div class="title">
                                        target
                                    </div>
                                    <div class="value">
                                        <?php echo $dtarget; ?>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="title">
                                        weight
                                    </div>
                                    <div class="value">
                                        <?php echo (floor($weight) == $weight ? (int) $weight : (float) $weight); ?>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="title">
                                        missing
                                    </div>
                                    <div class="value">
                                        <?php echo $missing; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ticket-footer flex items-center py-3 w-full mt-8">
                            <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto">view more</button>
                        </div>
                    </div>
                    <!-- <tr>

                        <td style="padding: 1em; border: 1px solid black;">
                            <?php echo ($activity ? $campName : "<span title='" . $campName . "'><del>" . $campName . "</del></span>") . ' ' . ($activity ? "<span style='color:#009933'>A</span><span style='color:#0BA036'>c</span><span style='color:#17A83A'>t</span><span style='color:#23B03E'>i</span><span style='color:#2FB842'>v</span><span style='color:#3AC046'>e</span></span>" : '<p style="color:red">Inactive</p>'); ?>
                        </td>
                        <td style="padding: 1em; border: 1px solid black;">
                            <?php echo ($dtarget - $weight < 0 ? "<span style='background: transparent url(" . site_url('resources') . "/images/bg_menu.gif)'><span style='color:green'><i class='fa-solid fa-award'></i> Over Achieved</span></span>" : ($dtarget - $weight == 0 ? '<span style="background: transparent url(' . site_url("resources") . '/images/bg_menu.gif)"><font color="green"><span class="completed"> <i class="fa-solid fa-trophy"></i> Achieved</span></font>' : '<font color="red">Not Achieved</font>')); ?>
                        </td>

                        <td style="padding: 1em; border: 1px solid black;"><?php echo $dtarget; ?></td>
                        <td style="padding: 1em; border: 1px solid black;">
                            <?php echo (floor($weight) == $weight ? (int) $weight : (float) $weight); ?>
                        </td>
                        <td
                            style="padding: 1em; border: 1px solid black; padding: 1em; border: 1px solid black; padding: 1em; border: 1px solid black; --tw-bg-opacity: 1;background-color: rgb(254 226 226 / var(--tw-bg-opacity));">
                            <?php echo $missing; ?>
                        </td>
                    </tr> -->
                <?php } ?>

            <?php endforeach; ?>
            <!-- <div class="ticket" id="ticket_1">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                    <div class="more-states hidden">
                        <table class="w-full states-table">
                            <tbody>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" disabled class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_1')">view more</button>
                </div>
            </div> -->
        </div>
    </div>
</body>

</html>
<style>
    .content-wrapper {
        margin: 43px;
        min-width: 320px;
    }

    .reports-header {
        background-color: #FFFFFF;
        padding: 12px 20px;
        border-radius: 4px;
        margin-bottom: 12px;
    }

    .reports-header h3 {
        color: rgba(0, 0, 0, 1);
        font-size: 18px;
        font-weight: 500;
        font-family: "Poppins";
        margin: 0 !important;
    }

    .reports-header-actions {
        margin-bottom: 12px;
        gap: 10px;
    }

    .reports-header-actions button {
        color: #FFFFFF;
        background-color: rgba(11, 112, 163, 1);
        border-radius: 4px;
        font-size: 16px;
        font-weight: 500;
    }

    .reports-filter-grid {
        padding: 0;
        gap: 10px;
    }

    .card-element {
        color: #FFFFFF;
    }

    .tickets-container {
        font-family: 'Poppins';
        text-transform: capitalize;
        transition: all .3s ease-in;
    }

    .ticket {
        min-width: 317px;
        background-color: #FFFFFF;
        border-radius: 12px;
        border: 1px solid rgba(222, 222, 222, 1);
        box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
        transition: all .1s ease-in;
        position: relative;
    }

    .ticket:hover,
    .ticket.row-span-2 {
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    .ticket-header {
        border-bottom: 1px solid #DEDEDE;
        padding: 11px 16px;
    }

    .ticket-header h3 {
        font-size: 20px;
        font-weight: 500;
        line-height: 100%;
        letter-spacing: -1px;
    }

    .ticket-header div {
        font-weight: 400;
        font-size: 14px;
        line-height: 100%;
        letter-spacing: 0;
        padding: 9px 13px;
        color: #34A770;
        background-color: #DCF3EB;
        border-radius: 6px;
    }

    .ticket-status {
        margin: 31px;
        line-height: 100%;
        letter-spacing: -1px;
        text-align: center;
    }

    .ticket-status h3 {
        color: #625F68;
        font-weight: 400;
        font-size: 16px;
        margin: 7px !important;
    }

    .ticket-status div {
        color: #A8A6AC;
        font-weight: 400;
        font-size: 16px;
    }

    .ticket-body {
        margin-bottom: 80px;
    }

    .main-states {
        padding: 16px;
    }

    .main-states>div {
        background-color: #F9F9F9;
        border: 1px solid #BCBCBC;
        border-radius: 6px;
        padding: 7px;
    }

    .ticket-body .title {
        color: rgba(0, 0, 0, 0.4);
        font-weight: 400;
        font-size: 14px;
        margin: 2px;
    }

    .ticket-body .value {
        font-weight: 600;
        font-size: 16px;
    }

    .more-states {
        max-height: calc(11 * 32.5px);
        overflow-y: auto;
        margin-top: 17px;
    }

    .state-row .state-title {
        color: #625F68;
        padding: 4px 16px;
    }

    .state-row .state-value {
        color: #A8A6AC;
        padding: 4px 12px;
    }

    .states-table tr:nth-child(even) {
        background-color: #F9F9F9;
        border-top: 0.5px solid rgba(188, 188, 188, 0.8);
        border-bottom: 0.5px solid rgba(188, 188, 188, 0.8);
    }

    .ticket-footer {
        border-top: 1px solid #DEDEDE;
        position: absolute;
        bottom: 0;
        padding: 12px 0;
        justify-content: center;
        margin-top: 32px;
    }

    .ticket-footer button {
        background-color: rgba(11, 112, 163, 1);
        background-color: rgba(11, 112, 163, 1);
        color: #FFFFFF;
        font-size: 16px !important;
        font-weight: 600;
        text-transform: capitalize;
        border-radius: 4px;
        padding: 7px 36px;
        line-height: 24px;
        letter-spacing: 1;
        box-shadow: #0A0D120D 0px 1px 2px 0px;
        /* cursor: pointer; */
    }

    .ticket-footer button:hover {
        /* transform: translateY(-1px); */
        cursor: not-allowed;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script>
    function ticketToggler(ticketId) {
        const targetedTicket = document.getElementById(ticketId);
        const toggleButton = document.getElementById(ticketId).getElementsByTagName("button")[0];
        const ticketTable = document.getElementById(ticketId).getElementsByClassName("more-states")[0];
        //  targetedTicket.classList.toggle("ticket-active");
        if (ticketTable) {
            targetedTicket.classList.toggle("row-span-2");
            toggleButton.innerText = "view less";
            ticketTable.classList.toggle("hidden");
        }
    }
</script>