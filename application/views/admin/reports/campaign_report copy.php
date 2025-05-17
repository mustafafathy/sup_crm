<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>
<body>
<div>
    <div class="content">
        <div class="row">
            <div class="col-md-18">
                <div class="tw-flex tw-justify-end tw-items-center tw-gap-x-4 tw-mb-4">
    				<?php echo form_open('', ['method' => 'post']); ?>
        				<label for="date_from">From:</label>
        				<input class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" type="date" id="date_from" name="date_from" value="<?php echo isset($_POST['date_from']) ? htmlspecialchars($_POST['date_from']) : $today; ?>">
        				<label for="date_to">To:</label>
        				<input class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" type="date" id="date_to" name="date_to" value="<?php echo isset($_POST['date_to']) ? htmlspecialchars($_POST['date_to']) : $today; ?>">
        				<button type="submit" class="btn btn-primary tw-w-20 tw-px-2 tw-py-1"><i class="fa-solid fa-filter"></i> Filter</button>
    				<?php echo form_close(); ?>
					<button id="exportBtn" class="btn btn-primary tw-w-64 tw-px-2 tw-py-1"><i class="fa-solid fa-file-export"></i> Export</button>
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
                    <button class="btn btn-primary tw-w-64 tw-px-2 tw-py-1" onclick="takeSnapshot()"><i class="fa fa-camera"></i> Snapshot</button>
                </div>
                <?php if ($this->session->flashdata('error')): ?>
     				<div class="flash-message error tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4"><?php echo $this->session->flashdata('error'); ?></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('success')): ?>
    				<div class="flash-message success tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4"><?php echo $this->session->flashdata('success'); ?></div>
				<?php endif; ?>
<div id="wrapper" >
        
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
$info = ['qualified' => $qualified_count, 'pending' => $pending_count, 'pushed' => $pushed_count, 'cold' => $cold_count, 'warm' => $warm_count, 'hot' => $hot_count, 'flaming_hot' => $flaming_hot_count, 'other' => ($ive_count+$disqualified_count+$glitch_count+$callback_count+$duplicate_count)];
$colors = ['#FF6384', '#36A2EB', '#FFCE56', '#FF9F40', '#4BC0C0', '#9966FF'];
$totalMissing = 0;
$totalOverAchieved = 0;
$totalTarget = 0;

?>
<div class="bg-auto" style="background-image: url('data:image/svg+xml;base64,PHN2ZyBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBzbGljZSIgdmlld0JveD0iMTAgMTAgODAgODAiPgogICAgPGRlZnM+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgICBAa2V5ZnJhbWVzIHJvdGF0ZSB7CgkJCQkJIDAlIHsKICAgICAgICAgICAgICAgICAgICB0cmFuc2Zvcm06IHJvdGF0ZSgwZGVnKTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIDEwMCUgewogICAgICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDM2MGRlZyk7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgLm91dC10b3AgewogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiByb3RhdGUgMjBzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybS1vcmlnaW46IDEzcHggMjVweDsKICAgICAgICAgICAgfQogICAgICAgICAgICAuaW4tdG9wIHsKICAgICAgICAgICAgICAgIGFuaW1hdGlvbjogcm90YXRlIDEwcyBsaW5lYXIgaW5maW5pdGU7CiAgICAgICAgICAgICAgICB0cmFuc2Zvcm0tb3JpZ2luOiAxM3B4IDI1cHg7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgLm91dC1ib3R0b20gewogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiByb3RhdGUgMjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybS1vcmlnaW46IDg0cHggOTNweDsKICAgICAgICAgICAgfQogICAgICAgICAgICAuaW4tYm90dG9tIHsKICAgICAgICAgICAgICAgIGFuaW1hdGlvbjogcm90YXRlIDE1cyBsaW5lYXIgaW5maW5pdGU7CiAgICAgICAgICAgICAgICB0cmFuc2Zvcm0tb3JpZ2luOiA4NHB4IDkzcHg7CiAgICAgICAgICAgIH0KICAgICAgICA8L3N0eWxlPgogICAgPC9kZWZzPgogICAgPHBhdGggZmlsbD0iIzliNWRlNSIgY2xhc3M9Im91dC10b3AiIGQ9Ik0zNy01QzI1LjEtMTQuNyw1LjctMTkuMS05LjItMTAtMjguNSwxLjgtMzIuNywzMS4xLTE5LjgsNDljMTUuNSwyMS41LDUyLjYsMjIsNjcuMiwyLjNDNTkuNCwzNSw1My43LDguNSwzNy01WiIvPgogICAgPHBhdGggZmlsbD0iI2YxNWJiNSIgY2xhc3M9ImluLXRvcCIgZD0iTTIwLjYsNC4xQzExLjYsMS41LTEuOSwyLjUtOCwxMS4yLTE2LjMsMjMuMS04LjIsNDUuNiw3LjQsNTBTNDIuMSwzOC45LDQxLDI0LjVDNDAuMiwxNC4xLDI5LjQsNi42LDIwLjYsNC4xWiIvPgogICAgPHBhdGggZmlsbD0iIzAwYmJmOSIgY2xhc3M9Im91dC1ib3R0b20iIGQ9Ik0xMDUuOSw0OC42Yy0xMi40LTguMi0yOS4zLTQuOC0zOS40LjgtMjMuNCwxMi44LTM3LjcsNTEuOS0xOS4xLDc0LjFzNjMuOSwxNS4zLDc2LTUuNmM3LjYtMTMuMywxLjgtMzEuMS0yLjMtNDMuOEMxMTcuNiw2My4zLDExNC43LDU0LjMsMTA1LjksNDguNloiLz4KICAgIDxwYXRoIGZpbGw9IiMwMGY1ZDQiIGNsYXNzPSJpbi1ib3R0b20iIGQ9Ik0xMDIsNjcuMWMtOS42LTYuMS0yMi0zLjEtMjkuNSwyLTE1LjQsMTAuNy0xOS42LDM3LjUtNy42LDQ3LjhzMzUuOSwzLjksNDQuNS0xMi41QzExNS41LDkyLjYsMTEzLjksNzQuNiwxMDIsNjcuMVoiLz4KPC9zdmc+');">
<table id="status" class="table-auto" style="overflow: auto;border-style: solid;">
    <thead>
        <tr>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Campaign</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Status</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Target</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Weight</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Missing</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Qualified</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Disqualified</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Pending</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">IVE</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Callback</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Duplicate</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Glitch</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Pushed</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Cold</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Warm</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Hot</th>
            <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">Flaming Hot</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lead_status_summary as $index => $row): ?>
        	<?php 
        		  $dtarget = $row['camp_target'];   
				  $weight = $row['weighted_score']; 
                  $activity = $row['camp_activity'];
				  $campName = $row['lead_source_name'];
				  $missing = max(0, $dtarget - $weight);
				  if($activity)
				  {
				  	$totalMissing = $totalMissing+$missing;
				  	if($dtarget-$weight < 0)
				  		$totalOverAchieved = $totalOverAchieved+1;
				  	$totalTarget = $totalTarget+$dtarget;
				  }
				  $campaigns[] = ['activity' => $activity, 'name' => $row['lead_source_name'], 'percentage' => min(100, (($row['weighted_score'] / $dtarget) * 100)), 'color' => isset($colors[$index]) ? $colors[$index] : $colors[0]]; ?>
            <?php if ($activity) { ?>
                  <tr>
                  
                <td style="padding: 1em; border: 1px solid black;"><?php echo ($activity ? $campName : "<span title='".$campName."'><del>".$campName."</del></span>").' '.($activity  ? "<span style='color:#009933'>A</span><span style='color:#0BA036'>c</span><span style='color:#17A83A'>t</span><span style='color:#23B03E'>i</span><span style='color:#2FB842'>v</span><span style='color:#3AC046'>e</span></span>" : '<p style="color:red">Inactive</p>'); ?></td>
        	    <td style="padding: 1em; border: 1px solid black;"><?php echo ($dtarget-$weight < 0 ? "<span style='background: transparent url(".site_url('resources')."/images/bg_menu.gif)'><span style='color:green'><i class='fa-solid fa-award'></i> Over Achieved</span></span>" : ($dtarget-$weight == 0 ? '<span style="background: transparent url('.site_url("resources").'/images/bg_menu.gif)"><font color="green"><span class="completed"> <i class="fa-solid fa-trophy"></i> Achieved</span></font>' : '<font color="red">Not Achieved</font>')); ?></td>
                <td style="padding: 1em; border: 1px solid black;">
                    <?php echo form_open('', ['method' => 'post', 'class' => 'tw-inline-flex tw-gap-2', 'autocomplete' => 'off']); ?>
                        <input type="hidden" name="id" value="<?php echo $row['lead_source_id']; ?>" />
                        <input type="hidden" name="name" value="<?php echo $row['lead_source_name']; ?>" />
                        <label for="editable_label_<?php echo $index; ?>" id="editable_label_<?php echo $index; ?>" data-id="<?php echo $index; ?>" contenteditable="true" type="number" min="1" max="1000" name="target" /><?php echo $dtarget; ?></label>
                        <input id="temp_input_<?php echo $index; ?>" style="display: none;" contenteditable="true" type="number" min="1" max="5000" value="<?php echo $dtarget; ?>" name="target"/> 
                  
                        <button type="submit" style="display: none; border-radius: 45px; color: #000; background-color: #fff;" id="edit_button_<?php echo $index; ?>" class="btn btn-primary tw-w-10 tw-px-2 tw-py-1"><i class="fas fa-edit"></i></button>
                    <?php echo form_close(); ?>
                </td>
        		<td style="padding: 1em; border: 1px solid black;"><?php echo (floor($weight) == $weight ? (int) $weight : (float) $weight); ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $missing; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_qualified']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_disqualified']; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_pending']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_ive']; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_callback']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_duplicate']; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_glitch']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_pushed']; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_cold']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_warm']; ?></td>
                <td style="padding: 1em; border: 1px solid black;"><?php echo $row['status_hot']; ?></td>
                <td  style="padding: 1em; border: 1px solid black;"><?php echo $row['status_flaming_hot']; ?></td>
            </tr>
                        <?php } ?>

        <?php endforeach; ?>
        <tr>
        <th style="background-color:lightgray; padding: 1em; border: 1px solid black" colspan="5" align="center">Total</th>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $qualified_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $disqualified_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $pending_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $ive_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $callback_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $duplicate_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $glitch_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $pushed_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $cold_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $warm_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $hot_count; ?></td>
            <td style="background-color:lightgray; padding: 1em; border: 1px solid black;"><?php echo $flaming_hot_count; ?></td>
        </tr>
    </tbody>
</table><div/><div class="leads-overview tw-mt-2 sm:tw-mt-4 tw-mb-4 sm:tw-mb-0 tw-text-center tw-w-full">
            <a class="btn btn-default btn-with-tooltip" id="leads_tooltip">
            <i class="fa fa-bar-chart"></i> Leads Summary</a>
            
            <div class="tw-flex tw-flex-wrap tw-flex-col lg:tw-flex-row tw-justify-center tw-gap-3 lg:tw-gap-6">
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $pending_count; ?></span>
                <span style="color:#28b8da;">Pending</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $qualified_count; ?></span>
                <span style="color:#7cb342">Qualified</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $disqualified_count; ?></span>
                <span style="color:#fc2d42">Disqualified</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $ive_count; ?></span>
                <span style="color:#ffcc22">IVE</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $pushed_count; ?></span>
                <span style="color:#2961ff">Pushed</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $callback_count; ?></span>
                <span style="color:#da2890">Callback</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $duplicate_count; ?></span>
                <span style="color:#fc2f42">Duplicate</span>
          </div>
          <div id="leads_summary_data" class="hidden animated lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-justify-center tw-items-center last:tw-border-r-0">
                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"><?php echo $glitch_count; ?></span>
                <span style="color:#ffccbb">Glitch</span>
    	  </div></div></div><h4 style="text-align:center"><font color="red">Total Missing:<span class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg" style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #DA605B; color: whitesmoke;"><?php echo $totalMissing; ?></span></font> <font color="#50ce1e" >Total Overachieved:<span class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg" style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #50ce1e; color: whitesmoke;"><?php echo $totalOverAchieved; ?></span></font> <font color="#2F4058">Total Target:<span class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg" style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #2F4058; color: whitesmoke;"><?php echo $totalTarget; ?></span></font></h4><?php echo (count(array_filter($info, fn($value) => $value > 0)) > 0 ? '<div class="more-info-flex flex-wrap gap-4 tw-justify-center"></div>' : ''); ?><br><div class="flex flex-wrap gap-4 tw-justify-center"></div>
<?php
//$totalloss = $ive_count + $disqualified_count + $pending_count + $glitch_count + $callback_count + $duplicate_count;
//echo "<button type='button' class='btn btn-danger'><h4>total lost $totalloss</h4></button><br>";
?>
          
</div>
<style>
 .ball {
  position: absolute;
  border-radius: 100%;
  opacity: 0.7;
}
#status {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

  
$bubble-count: 50;
$sway-type: "sway-left-to-right", "sway-right-to-left";

@function random_range($min, $max) {
  $rand: random();
  $random_range: $min + floor($rand * (($max - $min) + 1));
  @return $random_range;
}

@function sample($list) {
  @return nth($list, random(length($list)));
}

.bubbles {
  position: relative;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.bubble {
  position: absolute;
  left: var(--bubble-left-offset);
  bottom: -75%;
  display: block;
  width: var(--bubble-radius);
  height: var(--bubble-radius);
  border-radius: 50%;
  animation: float-up var(--bubble-float-duration) var(--bubble-float-delay) ease-in infinite;

  &::before {
    position: absolute;
    content: '';
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: hsla(183, 94%, 76%, 0.3);
    border-radius: inherit;
    animation: var(--bubble-sway-type) var(--bubble-sway-duration) var(--bubble-sway-delay) ease-in-out alternate infinite;
  }

  @for $i from 0 through $bubble-count {
    &:nth-child(#{$i}) {
      --bubble-left-offset: #{random_range(0vw, 100vw)};
      --bubble-radius: #{random_range(1vw, 10vw)};
      --bubble-float-duration: #{random_range(6s, 12s)};
      --bubble-sway-duration: #{random_range(4s, 6s)};
      --bubble-float-delay: #{random_range(0s, 4s)};
      --bubble-sway-delay: #{random_range(0s, 4s)};
      --bubble-sway-type: #{sample($sway-type)};
    }
  }
}

@keyframes float-up {
  to {
    transform: translateY(-175vh);
  }
}

@keyframes sway-left-to-right {
  from {
    transform: translateX(-100%);
  }

  to {
    transform: translateX(100%);
  }
}

@keyframes sway-right-to-left {
  from {
    transform: translateX(100%);
  }

  to {
    transform: translateX(-100%);
  }
}


#status td, #status th {
  border: 1px solid #ddd;
  padding: 8px;
}

#status tr:nth-child(even){background-color: #f2f2f2;}

#status tr:hover {background-color: #ddd;}

#status th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}                        
            
h4, h4 {
    font-size: 18px;
    padding-right: 30px;
}

.flash-message {
    opacity: 0;
    height: 0;
    margin: 0;
    padding: 0;
    border: 1px solid transparent;
    transition: all 0.5s ease;
    overflow: hidden;
}

.flash-message.error {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

.flash-message.success {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6;
}

.modal {
	display: none;
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	overflow: auto;
	background-color: rgba(0,0,0,0.4);
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

.circle-chart {
	width: 20% !important;
	height: auto !important;
	max-width: 250px;
}

.flex {
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
}

.more-info-circle-chart {
	width: 14% !important;
	height: auto !important;
	max-width: 240px;
}

.more-info-flex {
	display: flex;
	flex-wrap: wrap;
}
</style>
			<!--<div class="col-md-6 animated fadeIn">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo _l('report_leads_sources_conversions'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <canvas class="leads-sources-report" height="150" id="leads-sources-report"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animated fadeIn">
                <div class="panel_s">
                 	<div class="panel-heading">
                        <h3 class="panel-title"><?php echo _l('report_leads_monthly_conversions'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                    <?php
                            echo '<select name="month" class="selectpicker" data-none-selected-text="' . _l('dropdown_non_selected_tex') . '">' . PHP_EOL;
                            for ($m = 1; $m <= 12; $m++) {
                                $_selected = '';
                                if ($m == date('m')) {
                                    $_selected = ' selected';
                                }
                                echo '  <option value="' . $m . '"' . $_selected . '>' . _l(date('F', mktime(0, 0, 0, $m, 1))) . '</option>' . PHP_EOL;
                            }
                      echo '</select>' . PHP_EOL;
                      ?>
                            </div>
                        </div>
                        <div class="relative" style="max-height:400px;">
                            <canvas class="leads-monthly chart mtop20" id="leads-monthly" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>
</div>
<?php 
    // init_tail(); 
    ?>
</body>
</html>
<script src="<?php echo site_url('resources/js/chart_js.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/plugin_datalabs.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/html2canvas.min.js'); ?>"></script>
<script>
                      
    document.addEventListener('DOMContentLoaded', function() {
        var messages = document.querySelectorAll('.flash-message');
        if (messages.length > 0) {
            messages.forEach(function(message) {
                message.style.opacity = '1';
                message.style.height = 'auto';
                message.style.margin = '10px 0';
                message.style.padding = '10px';
            });

            setTimeout(function() {
                messages.forEach(function(message) {
                    message.style.opacity = '0';
                    message.style.height = '0';
                    message.style.margin = '0';
                    message.style.padding = '0';
                });
            }, 3000);
        }
    });

	const modal = document.getElementById('exportModal');
	const exportBtn = document.getElementById('exportBtn');
	const closeModal = document.getElementById('closeModal');
	const exportForm = document.getElementById('exportForm');
	
const toggleButton = document.getElementById('leads_tooltip');
const elementsToHide = document.querySelectorAll('[id="leads_summary_data"]');

toggleButton.addEventListener('click', () => {
  elementsToHide.forEach(element => {
    if (element.classList.contains('hidden')) {
    element.classList.add('fadeInDown');  
    element.classList.remove('hidden');
    	

    } else {
      element.classList.add('hidden');
    	element.classList.remove('fadeInDown');

    }
  });
});

                                                     
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
	function showButton(id) {
  		const editButton = document.getElementById(`edit_button_${id}`);
        if (editButton) {
    		editButton.style.display = 'inline-block';
  		} else {
    		console.warn(`Edit button with ID ${id} not found.`);
  			}
		}
	const labels = document.querySelectorAll('label[data-id]');
		labels.forEach(label => {
  		label.addEventListener('blur', function() {
        	const id = this.dataset.id;
        	const ele = document.getElementById(`temp_input_${id}`);
        	ele.value = label.textContent
            console.log('debug:label#temp_input_${id}', label.textContent);
    		showButton(id);
  			});
		});
        // label.addEventListener('blur', function () {
        //     console.log('debug:label:editable_label', label.textContent);
        // showButton();
        // });
	function submitExportForm() {
		const startDate = document.getElementById('start_date').value;
		const endDate = document.getElementById('end_date').value;
		const exportFormat = document.getElementById('export_format').value;

		if (!startDate || !endDate) {
			alert('Please select both start and end dates.');
			return;
		}

		if (new Date(startDate) > new Date(endDate)) {
			alert('Start date must be before or equal to end date.');
			return;
		}

		if (!exportFormat) {
			alert('Please select an export format.');
			return;
		}

		exportForm.submit();
	}

	/*
    function takeSnapshot() {
		html2canvas(document.getElementById('status')).then(function(canvas) {
			var link = document.createElement('a');
			link.href = canvas.toDataURL('image/png');
			link.download = 'campaign-report-snapshot.png';
			link.click();
		});
	}
	*/

	async function takeSnapshot() {
        try {
            const canvas = await html2canvas(document.getElementsByTagName('wrapper'));
            const dataUrl = canvas.toDataURL('image/png');
            const blob = await (await fetch(dataUrl)).blob();
            const item = new ClipboardItem({ 'image/png': blob });
            await navigator.clipboard.write([item]);
            alert('Snapshot copied to clipboard!');
        } catch (error) {
            console.error('Failed to copy snapshot to clipboard:', error);
            alert('Failed to copy snapshot to clipboard.');
        }
    }

// 	document.addEventListener('DOMContentLoaded', function() {
// 		const chartContainer = document.querySelector('.more-info-flex');
// 		const info = <?php echo json_encode($info); ?>;
//     	if(info.qualified < 1 && info.pending  < 1 && info.pushed  < 1 && info.other < 1) {
//         	return;
//         }
    
//     	let canvas = document.createElement('canvas');
// 		canvas.classList.add('more-info-circle-chart');
// 		chartContainer.appendChild(canvas);

// 		new Chart(canvas, {
// 			type: 'doughnut',
// 			data: {
// 				labels: ['Qualified', 'Pending', 'Pushed', 'Other'],
// 				datasets: [{
// 					data: [info.qualified, info.pending, info.pushed, info.other],
// 					backgroundColor: ['#23ba3d', '#28b8da', '#2961ff', '#FFFF00'],
// 					borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
// 					borderWidth: 1
// 				}]
// 			},
// 			options: {
// 				responsive: false,
//             	cutout: '40%',
// 				plugins: {
// 					datalabels: {
// 						display: true,
// 						color: '#333333',
// 						formatter: (value) => value > 0 ? value : '',
// 						font: {
// 							weight: 'bold',
// 							size: 12
// 						},
// 						anchor: 'center',
// 						align: 'center',
// 						offset: 0
// 					},
// 					tooltip: {
// 						callbacks: {
// 							label: function(tooltipItem) {
// 								return tooltipItem.raw;
// 							}
// 						}
// 					},
// 					legend: {
// 						display: true,
// 						position: 'top',
// 						align: 'center',
// 						labels: {
// 							boxWidth: 12,
// 							padding: 12,
// 							font: {
// 								size: 12
// 							}
// 						}
// 					}
// 				}
// 			},
// 			plugins: [ChartDataLabels]
// 		});
    
//     	if(info.cold > 0 || info.warm > 0 || info.hot > 0 || info.flaming_hot > 0)
//     	{
// 			canvas = document.createElement('canvas');
// 			canvas.classList.add('more-info-circle-chart');
// 			chartContainer.appendChild(canvas);
// 			new Chart(canvas, {
// 				type: 'doughnut',
// 				data: {
// 					labels: ['Cold', 'Warm', 'Hot', 'Flaming Hot'],
// 					datasets: [{
// 						data: [info.cold, info.warm, info.hot, info.flaming_hot],
// 						backgroundColor: ['#a0d8ef', '#f7e86f', '#f76c6c', '#ff3b3b'],
// 						borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
// 						borderWidth: 1
// 					}]
// 				},
// 				options: {
// 					responsive: false,
// 					cutout: '40%',
// 					plugins: {
// 						datalabels: {
// 							display: true,
// 							color: '#333333',
// 							formatter: (value) => value > 0 ? value : '',
// 							font: {
// 								weight: 'bold',
// 								size: 12
// 							},
// 							anchor: 'center',
// 							align: 'center',
// 							offset: 0
// 						},
// 						tooltip: {
// 							callbacks: {
// 								label: function(tooltipItem) {
// 									return tooltipItem.raw;
// 								}
// 							}
// 						},
// 						legend: {
// 							display: true,
// 							position: 'top',
// 							align: 'center',
// 							labels: {
// 								boxWidth: 12,
// 								padding: 12,
// 								font: {
// 									size: 12
// 								}
// 							}
// 						}
// 					}
// 				},
// 				plugins: [ChartDataLabels]
// 			});
//     	}
// 	});

	document.addEventListener('DOMContentLoaded', function() {
		const chartContainer = document.querySelector('.flex');
		const campaigns = <?php echo json_encode($campaigns); ?>;

		campaigns.forEach(campaign => {
        if (campaign.activity >= 1){
			const canvas = document.createElement('canvas');
			canvas.classList.add('circle-chart');
			chartContainer.appendChild(canvas);

			const percentage = parseFloat(campaign.percentage);
			
			new Chart(canvas, {
				type: 'doughnut',
				data: {
					labels: [campaign.name, 'Missing'],
					datasets: [{
						data: [percentage, 100 - percentage],
						backgroundColor: [campaign.color, '#E0E0E0'],
						borderColor: [campaign.color, '#E0E0E0'],
						borderWidth: 1
					}]
				},
				options: {
					responsive: false,
            		cutout: '50%',
					plugins: {
						datalabels: {
							display: true,
							color: '#333333',
							formatter: (value) => {
								const percentage = Math.round(value);
								return percentage > 0 ? `${percentage}%` : '';
							},
							font: {
								weight: 'bold',
								size: 14
							},
							anchor: 'center',
							align: 'center',
							offset: 0
						},
						tooltip: {
							callbacks: {
								label: function(tooltipItem) {
									return `${Math.round(tooltipItem.raw)}%`;
								}
							}
						},
						legend: {
							display: true,
							position: 'top',
							align: 'center',
							labels: {
								boxWidth: 12,
								padding: 12,
								font: {
									size: 13
								}
							}
						}
					}
				},
				plugins: [ChartDataLabels]
			});
        }
        });
        
	});
</script>
    
<!--  
<script>
var MonthlyLeadsChart;
$(function() {
    $.get(admin_url + 'reports/leads_monthly_report/' + $('select[name="month"]').val(), function(response) {
        var ctx = $('#leads-monthly').get(0).getContext('2d');
        MonthlyLeadsChart = new Chart(ctx, {
            'type': 'bar',
            data: response,
            options: {
                responsive: true,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                        }
                    }]
                },
            },
        });
    }, 'json');
    $('select[name="month"]').on('change', function() {
        MonthlyLeadsChart.destroy();
        $.get(admin_url + 'reports/leads_monthly_report/' + $('select[name="month"]').val(), function(
            response) {
            var ctx = $('#leads-monthly').get(0).getContext('2d');
            MonthlyLeadsChart = new Chart(ctx, {
                'type': 'bar',
                data: response,
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
                    },
                },
            });
        }, 'json');
    });

    new Chart($("#leads-this-week"), {
        type: 'pie',
        data: <?php echo $leads_this_week_report; ?>,
        option: {
            responsive: true
        }
    });

    new Chart($('#leads-sources-report'), {
        type: 'bar',
        data: <?php echo $leads_sources_report; ?>,
        options: {
            responsive: true,
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                    }
                }]
            },
        },
    });
});
// 
// 
// 
// Some random colors
const colors = ["#3CC157", "#2AA7FF", "#1B1B1B", "#FCBC0F", "#F85F36"];

const numBalls = 50;
const balls = [];

for (let i = 0; i < numBalls; i++) {
  let ball = document.createElement("div");
  ball.classList.add("ball");
  ball.style.background = colors[Math.floor(Math.random() * colors.length)];
  ball.style.left = `${Math.floor(Math.random() * 100)}vw`;
  ball.style.top = `${Math.floor(Math.random() * 100)}vh`;
  ball.style.transform = `scale(${Math.random()})`;
  ball.style.width = `${Math.random()}em`;
  ball.style.height = ball.style.width;
  
  balls.push(ball);
  document.body.append(ball);
}

// Keyframes
balls.forEach((el, i, ra) => {
  let to = {
    x: Math.random() * (i % 2 === 0 ? -11 : 11),
    y: Math.random() * 12
  };

  let anim = el.animate(
    [
      { transform: "translate(0, 0)" },
      { transform: `translate(${to.x}rem, ${to.y}rem)` }
    ],
    {
      duration: (Math.random() + 1) * 2000, // random duration
      direction: "alternate",
      fill: "both",
      iterations: Infinity,
      easing: "ease-in-out"
    }
  );
});

</script>
-->