<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="<?php echo site_url('resources/js/html2canvas.min.js'); ?>"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<?php if ($_GET['account'] == 2) blank_page('SOLAR MISSING REPORT IS COMING SOON :)'); ?>
<?php init_head(); ?>
<?php $today = date('m-d-Y'); ?>

<body>
    <div id="wrapper">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->load->database();
            $id = $this->input->post('id', TRUE);
            $target_value = $this->input->post('target', TRUE);
            $camp_name = $this->input->post('name', TRUE);
            if (isset($id)) {
                if (is_numeric($target_value) && $target_value > 0) {
                    $sql = "UPDATE tblleads_sources SET target = ? WHERE id = ?";
                    $this->db->query($sql, array($target_value, $id));
                    if ($this->db->affected_rows() > 0) {
                        $this->session->set_flashdata('success', "Target was updated successfully for $camp_name.");
                    } else {
                        $this->session->set_flashdata('error', "Target is the same and was not changed for $camp_name.");
                    }
                } else {
                    $this->session->set_flashdata('error', "Invalid input. Please enter a number greater than 0.");
                }
                redirect(current_url());
            }
        }

        $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : $today;
        $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : $today;

        // Database credentials
        // $hostname = "localhost";
        // $username = "crm2";
        // $password = "IIf7Cw20hMcJBAE";
        // $database = "crm2";

        // CRM 1
        // Database credentials
        $hostname = "localhost";
        $username = "crm";
        $password = "SgMGLDSKLq9FTGV";
        $database = "crm";

        # todo change this with application/config/app-config.php configuration 

        // Connect to the database
        $mysqli = new mysqli($hostname, $username, $password, $database);

        // Check connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // Query to get lead source name and count of statuses for the current date, including the total count for each source
        $query = "
    SELECT
    	ls.id as lead_source_id,
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
        tblleads tl ON ls.id = tl.source AND 
        (
    		(tl.last_status_change IS NOT NULL AND (tl.status = 1 OR tl.status = 8) AND DATE(tl.last_status_change) BETWEEN ? AND ?)
    		OR
    		DATE(tl.dateadded) BETWEEN ? AND ?
		)
    LEFT JOIN 
        tblleads_status ts ON tl.status = ts.id
    LEFT JOIN
    	tblcustomfieldsvalues cf ON tl.id = cf.relid AND cf.fieldid = 4
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
        $info = ['qualified' => $qualified_count, 'pending' => $pending_count, 'pushed' => $pushed_count, 'cold' => $cold_count, 'warm' => $warm_count, 'hot' => $hot_count, 'flaming_hot' => $flaming_hot_count, 'other' => ($ive_count + $disqualified_count + $glitch_count + $callback_count + $duplicate_count)];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#FF9F40', '#4BC0C0', '#9966FF'];
        $totalMissing = 0;
        $totalOverAchieved = 0;
        $totalTarget = 0;

        ?>
        <div class="content-wrapper">
            <div class="grid grid-cols-8" style="gap: 10px;">
                <div class="reports-header col-span-5">
                    <h3>RE Summary</h3>
                </div>
                <div class="col-span-3 reports-header-actions col-span-2 grid grid-cols-3 tw-justify-between">
                    <input class="form-control" type="text" id="date_range" name="date_range" style="height: 100%;"
                        value="<?php echo isset($_POST['date_from']) ? (new DateTime(htmlspecialchars($_POST['date_from'])))->format('m-d-Y') : $today; ?> - <?php echo isset($_POST['date_to']) ? (new DateTime(htmlspecialchars($_POST['date_to'])))->format('m-d-Y') : $today; ?>">
                    <div id="date_filter_form" class="hidden">
                        <?php echo form_open('/admin/reports/campaign_report', ['method' => 'post', 'id' => 'dateFilterForm']); ?>
                        <label for="date_from"></label>
                        <input type="text" id="date_from" name="date_from" class="hidden" placeholder="Start Date" value="<?php echo isset($_POST['date_from']) ? htmlspecialchars($_POST['date_from']) : $today; ?>">
                        <label for="date_to"></label>
                        <input type="text" id="date_to" name="date_to" class="hidden" placeholder="End Date" value="<?php echo isset($_POST['date_to']) ? htmlspecialchars($_POST['date_to']) : $today; ?>">
                        <input type="submit">
                        <?php echo form_close(); ?>
                    </div>
                    <button id="exportBtn">
                        <i class="fa-solid fa-up-right-from-square"></i>
                        Export
                    </button>
                    <button onclick="takeSnapshot()">
                        <i class="fa-solid fa-camera"></i>
                        Screenshoot
                    </button>
                </div>
            </div>

            <div id="exportModal" class="modal">
                <div class="modal-content text-center">
                    <span class="close" id="closeModal">&times;</span>
                    <?php echo form_open('/admin/reports/campaign_report/export', ['method' => 'post', 'id' => 'exportForm']); ?>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date:</label>
                    <input type="date" id="start_date" name="startDate" value="<?php echo $today; ?>" class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date:</label>
                    <input type="date" id="end_date" name="endDate" value="<?php echo $today; ?>" class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                    <label for="export_format" class="block text-sm font-medium text-gray-700">Export Format:</label>
                    <select id="export_format" name="exportType" class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                        <option value="csv">CSV</option>
                        <!--<option value="excel">Excel</option>
										<option value="pdf">PDF</option>-->
                    </select>
                    <button type="button" class="btn btn-primary tw-w-64 tw-px-2 tw-py-1" onclick="submitExportForm()"><i class="fa fa-download"></i> Generate Export</button>
                    <?php echo form_close(); ?>
                </div>
            </div>

            <div class="reports-filter-grid grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-8" style="display: grid;">
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(101, 186, 225, 1);">
                    Pending
                    <span id="" class="count">
                        <?php echo $pending_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(50, 170, 199, 1);">
                    Qualified
                    <span id="" class="count">
                        <?php echo $qualified_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(8, 164, 167, 1);">
                    Disqualified
                    <span id="" class="count">
                        <?php echo $disqualified_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(241, 202, 128, 1);">
                    IVE
                    <span id="" class="count">
                        <?php echo $ive_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(248, 178, 2, 1);">
                    Pushed
                    <span id="" class="count">
                        <?php echo $pushed_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(251, 133, 0, 1);">
                    Callback
                    <span id="" class="count">
                        <?php echo $callback_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(225, 84, 2, 1);">
                    Duplicated
                    <span id="" class="count">
                        <?php echo $duplicate_count; ?>
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(205, 51, 37, 1);">
                    Glitch
                    <span id="" class="count">
                        <?php echo $glitch_count; ?>
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
                    $totalMissing = $totalMissing + $missing;
                    if ($dtarget - $weight < 0)
                        $totalOverAchieved = $totalOverAchieved + 1;
                    $totalTarget = $totalTarget + $dtarget;
                }
                $campaigns[] = ['activity' => $activity, 'name' => $row['lead_source_name'], 'percentage' => min(100, (($row['weighted_score'] / $dtarget) * 100)), 'color' => isset($colors[$index]) ? $colors[$index] : $colors[0]]; ?>
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
                                    <div class="value" contenteditable="true">
                                        <?php echo $dtarget; ?>
                                        <div class="hidden">
                                            <?php echo form_open('', ['method' => 'post', 'autocomplete' => 'off']); ?>
                                            <input type="hidden" name="id" value="<?php echo $row['lead_source_id']; ?>" />
                                            <input type="hidden" name="name" value="<?php echo $row['lead_source_name']; ?>" />
                                            <input id="temp_input_<?php echo $index; ?>" style="display: none;" contenteditable="true" type="number" min="1" max="5000" value="<?php echo $dtarget; ?>" name="target" />

                                            <button type="submit" style="display: none; border-radius: 45px; color: #000; background-color: #fff;" id="edit_button_<?php echo $index; ?>" class="btn btn-primary tw-w-10 tw-px-2 tw-py-1"><i class="fas fa-edit"></i></button>
                                            <?php echo form_close(); ?>
                                        </div>
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
                            <div class="more-states hidden">
                                <table class="w-full states-table">
                                    <tbody>
                                        <tr class="state-row">
                                            <td class="state-title">qualified</td>
                                            <td class="state-value"><?php echo $row['status_qualified']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">disqualified</td>
                                            <td class="state-value"><?php echo $row['status_disqualified']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">pending</td>
                                            <td class="state-value"><?php echo $row['status_pending']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">IVE</td>
                                            <td class="state-value"><?php echo $row['status_ive']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">callback</td>
                                            <td class="state-value"><?php echo $row['status_callback']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">duplicate</td>
                                            <td class="state-value"><?php echo $row['status_duplicate']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">glitch</td>
                                            <td class="state-value"><?php echo $row['status_glitch']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">pushed</td>
                                            <td class="state-value"><?php echo $row['status_pushed']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">cold</td>
                                            <td class="state-value"><?php echo $row['status_cold']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">warm</td>
                                            <td class="state-value"><?php echo $row['status_warm']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">hot</td>
                                            <td class="state-value"><?php echo $row['status_hot']; ?></td>
                                        </tr>
                                        <tr class="state-row">
                                            <td class="state-title">flaming hot</td>
                                            <td class="state-value"><?php echo $row['status_flaming_hot']; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="ticket-footer flex items-center py-3 w-full mt-8">
                            <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_<?php echo $row['lead_source_id']; ?>')">view more</button>
                        </div>
                    </div>
                    <!-- <td style="padding: 1em; border: 1px solid black;">
                        <?php echo form_open('', ['method' => 'post', 'class' => 'tw-inline-flex tw-gap-2', 'autocomplete' => 'off']); ?>
                        <input type="hidden" name="id" value="<?php echo $row['lead_source_id']; ?>" />
                        <input type="hidden" name="name" value="<?php echo $row['lead_source_name']; ?>" />
                        <label for="editable_label_<?php echo $index; ?>" id="editable_label_<?php echo $index; ?>" data-id="<?php echo $index; ?>" contenteditable="true" type="number" min="1" max="1000" name="target" /><?php echo $dtarget; ?></label>
                        <input id="temp_input_<?php echo $index; ?>" style="display: none;" contenteditable="true" type="number" min="1" max="5000" value="<?php echo $dtarget; ?>" name="target" />

                        <button type="submit" style="display: none; border-radius: 45px; color: #000; background-color: #fff;" id="edit_button_<?php echo $index; ?>" class="btn btn-primary tw-w-10 tw-px-2 tw-py-1"><i class="fas fa-edit"></i></button>
                        <?php echo form_close(); ?>
                        </td> -->
                <?php } ?>

            <?php endforeach; ?>
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

    .ticket-header .inactive-ticket {
        background-color: red;
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
        cursor: pointer;
    }

    .ticket-footer button:hover {
        transform: translateY(-1px);
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
        z-index: 1000;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 60%;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script>
    const dateFilterForm = document.getElementById('dateFilterForm')
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    const modal = document.getElementById('exportModal');
    const exportBtn = document.getElementById('exportBtn');
    const closeModal = document.getElementById('closeModal');
    const exportForm = document.getElementById('exportForm');

    $(function() {
        $('input[name="date_range"]').daterangepicker({
            opens: 'left'
        }, function(start, end, label) {
            dateFilterFormSubmit(start, end)

            console.log("A new date selection was made: " + label + " " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });
    });

    function dateFilterFormSubmit(dateFrom, dateTo) {
        dateFromInput.value = dateFrom;
        dateToInput.value = dateTo;

        dateFilterForm.submit();
    }

    exportBtn.onclick = function() {
        modal.style.display = 'block';
    }

    closeModal.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function submitExportForm() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const exportFormat = document.getElementById('export_format');

        if (!startDate.value || !endDate.value) {
            alert('Please select both start and end dates.');
            return;
        }

        if (new Date(startDate.value) > new Date(endDate.value)) {
            alert('Start date must be before or equal to end date.');
            return;
        }

        if (!exportFormat.value) {
            alert('Please select an export format.');
            return;
        }

        exportForm.submit();
    }
</script>
<script>
    function ticketToggler(ticketId) {
        const targetedTicket = document.getElementById(ticketId);
        const toggleButton = document.getElementById(ticketId).getElementsByTagName("button")[0];
        const ticketTable = document.getElementById(ticketId).getElementsByClassName("more-states")[0];

        if (ticketTable) {
            targetedTicket.classList.toggle("row-span-2");
            ticketTable.classList.toggle("hidden");
            if (toggleButton.innerText === "View Less") {
                toggleButton.innerText = "view more";
            } else {
                toggleButton.innerText = "view less";
            }
            console.log(toggleButton.innerText)
        }
    }

    async function takeSnapshot() {
        try {
            const canvas = await html2canvas(document.getElementById('wrapper'));
            const dataUrl = canvas.toDataURL('image/png');
            const blob = await (await fetch(dataUrl)).blob();
            const item = new ClipboardItem({
                'image/png': blob
            });
            await navigator.clipboard.write([item]);
            alert('Snapshot copied to clipboard!');
        } catch (error) {
            console.error('Failed to copy snapshot to clipboard:', error);
            alert('Failed to copy snapshot to clipboard.');
        }
    }
</script>