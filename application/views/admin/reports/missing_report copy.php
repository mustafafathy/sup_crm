<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/tables.css'); ?>">

<?php if ($_GET['account'] == 2) blank_page('SOLAR MISSING REPORT IS COMING SOON :)');?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>

<body>
    <div>
        <div class="content">
            <div class="row">
                <div class="">
                    <div class="tw-flex tw-justify-end tw-items-center tw-gap-x-4 tw-mb-4">
                        <?php echo form_open('', ['method' => 'post']); ?>
                        <label for="date_from">From:</label>
                        <input class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" type="date" id="date_from"
                            name="date_from"
                            value="<?php echo isset($_POST['date_from']) ? htmlspecialchars($_POST['date_from']) : $today; ?>">
                        <label for="date_to">To:</label>
                        <input class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" type="date" id="date_to"
                            name="date_to"
                            value="<?php echo isset($_POST['date_to']) ? htmlspecialchars($_POST['date_to']) : $today; ?>">
                        <button type="submit" class="btn btn-primary tw-w-20 tw-px-2 tw-py-1"><i
                                class="fa-solid fa-filter"></i> Filter</button>
                        <?php echo form_close(); ?>
                        <button id="exportBtn" class="btn btn-primary tw-w-64 tw-px-2 tw-py-1"><i
                                class="fa-solid fa-file-export"></i> Export</button>
                        <div id="exportModal" class="modal">
                            <div class="modal-content text-center">
                                <span class="close" id="closeModal">&times;</span>
                                <?php echo form_open('/admin/reports/missing_report/export', ['method' => 'post', 'id' => 'exportForm']); ?>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start
                                    Date:</label>
                                <input type="date" id="start_date" name="startDate" value="<?php echo $today; ?>"
                                    class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date:</label>
                                <input type="date" id="end_date" name="endDate" value="<?php echo $today; ?>"
                                    class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                                <label for="export_format" class="block text-sm font-medium text-gray-700">Export
                                    Format:</label>
                                <select id="export_format" name="exportType"
                                    class="tw-form-input tw-w-64 tw-px-2 tw-py-1 tw-text-sm">
                                    <option value="csv">CSV</option>
                                    <!--<option value="excel">Excel</option>
                                        <option value="pdf">PDF</option>-->
                                </select>
                                <button type="button" class="btn btn-primary tw-w-64 tw-px-2 tw-py-1"
                                    onclick="submitExportForm()"><i class="fa fa-download"></i> Generate Export</button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <button class="btn btn-primary tw-w-64 tw-px-2 tw-py-1" onclick="takeSnapshot()"><i
                                class="fa fa-camera"></i> Snapshot</button>
                    </div>


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
                    <div id="wrapper">
                        <table class="table-auto" id="status"
                            style="border-collapse: collapse; border-radius: 8px; overflow: auto;">
                            <thead>
                                <tr>
                                    <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">
                                        Campaign</th>
                                    <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">
                                        Status</th>
                                    <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">
                                        Target</th>
                                    <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">
                                        Weight</th>
                                    <th style="background-color:lightgray; padding: 1em; border: 1px solid black;">
                                        Missing</th>
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

                                        <tr>

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
                                        </tr>
                                    <?php } ?>

                                <?php endforeach; ?>
                                <tr>
                                    <th colspan="2"
                                        style="background-color:lightgray; padding: 1em; border: 1px solid black;"
                                        align="center">Total</th>
                                    <td style="padding: 1em; border: 1px solid black;"><?php echo $totalTarget; ?></td>
                                    <td style="padding: 1em; border: 1px solid black;">
                                        <?php echo (floor($totalWeight) == $totalWeight ? (int) $totalWeight : (float) $totalWeight); ?>
                                    </td>
                                    <td
                                        style="padding: 1em; border: 1px solid black; padding: 1em; border: 1px solid black; --tw-bg-opacity: 1;background-color: rgb(254 226 226 / var(--tw-bg-opacity));">
                                        <?php echo $totalMissing; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table><br>
                        <h4 style="text-align:center">
                            <font color="red">Total Missing:<span class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg"
                                    style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #DA605B; color: whitesmoke;"><?php echo $totalMissing; ?></span>
                            </font>
                            <font color="#50ce1e">Total Overachieved:<span
                                    class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg"
                                    style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #50ce1e; color: whitesmoke;"><?php echo $totalOverAchieved; ?></span>
                            </font>
                            <font color="#2F4058">Total Target:<span class="tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg"
                                    style="padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #2F4058; color: whitesmoke;"><?php echo $totalTarget; ?></span>
                            </font>
                        </h4>
                        <?php echo (count(array_filter($info, fn($value) => $value > 0)) > 0 ? '<div class="more-info-flex flex-wrap gap-4 tw-justify-center"></div>' : ''); ?><br>
                        <div class="flex flex-wrap gap-4 tw-justify-center"></div><br>
                        <div class="flex flex-wrap gap-4 tw-justify-center"></div>
                        <?php
                        //$totalloss = $ive_count + $disqualified_count + $pending_count + $glitch_count + $callback_count + $duplicate_count;
                        //echo "<button type='button' class='btn btn-danger'><h4>total lost $totalloss</h4></button><br>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
                                    // init_tail(); 
?>
</body>

</html>
<style>
    #status {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    #status td,
    #status th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    #status tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #status tr:hover {
        background-color: #ddd;
    }

    #status th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
    }

    h4,
    h4 {
        font-size: 18px;
        padding-right: 30px;
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

<script src="<?php echo site_url('resources/js/chart_js.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/plugin_datalabs.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/html2canvas.min.js'); ?>"></script>
<script>
    const modal = document.getElementById('exportModal');
    const exportBtn = document.getElementById('exportBtn');
    const closeModal = document.getElementById('closeModal');
    const exportForm = document.getElementById('exportForm');

    exportBtn.onclick = function () {
        modal.style.display = 'block';
    }

    closeModal.onclick = function () {
        modal.style.display = 'none';
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

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
            link.download = 'missing-report-snapshot.png';
            link.click();
        });
    }
    */

    async function takeSnapshot() {
        try {
            const canvas = await html2canvas(document.getElementById('wrapper'));
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
    //     	if(info.qualified < 1 && info.pending  < 1 && info.other < 1) {
    //         	return;
    //         }

    //     	let canvas = document.createElement('canvas');
    // 		canvas.classList.add('more-info-circle-chart');
    // 		chartContainer.appendChild(canvas);

    // 		new Chart(canvas, {
    // 			type: 'doughnut',
    // 			data: {
    // 				labels: ['Qualified', 'Pending', 'Other'],
    // 				datasets: [{
    // 					data: [info.qualified, info.pending, info.other],
    // 					backgroundColor: ['#23ba3d', '#28b8da', '#FFFF00'],
    // 					borderColor: ['#ffffff', '#ffffff', '#ffffff'],
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

    document.addEventListener('DOMContentLoaded', function () {
        const chartContainer = document.querySelector('.flex');
        const campaigns = <?php echo json_encode($campaigns); ?>;

        campaigns.forEach(campaign => {
            if (campaign.activity >= 1) {

                const canvas = document.createElement('canvas');
                canvas.classList.add('circle-chart');
                chartContainer.appendChild(canvas);

                const missing = parseFloat(campaign.missing);

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Missing', campaign.name],
                        datasets: [{
                            data: [missing, campaign.target - missing],
                            backgroundColor: [campaign.color, '#E0E0E0'],
                            borderColor: [campaign.color, '#E0E0E0'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        plugins: {
                            datalabels: {
                                display: true,
                                color: '#333333',
                                formatter: (value) => {
                                    const missing = value;
                                    return missing > 0 ? missing : '';
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
                                    label: function (tooltipItem) {
                                        return tooltipItem.raw;
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