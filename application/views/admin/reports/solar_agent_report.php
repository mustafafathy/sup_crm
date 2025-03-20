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
                                <?php echo form_open('/admin/reports/agent_report/export', ['method' => 'post', 'id' => 'exportForm']); ?>
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
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="flash-message error tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4">
                            <?php echo $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="flash-message success tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4">
                            <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $this->load->database();
                            $staff_id = $this->input->post('id', TRUE);
                            $target_value = $this->input->post('target', TRUE);
                            if (isset($staff_id)) {
                                $this->db->select('firstname, lastname');
                                $this->db->from('tblstaff');
                                $this->db->where('staffid', $staff_id);
                                $query = $this->db->get();
                                if ($query->num_rows() > 0) {
                                    $row = $query->row();
                                    $user_firstname = $row->firstname;
                                    $user_lastname = $row->lastname;
                                    $user_name = $user_firstname . ' ' . $user_lastname;
                                } else {
                                    $user_name = 'Unknown';
                                }
                                if (is_numeric($target_value) && $target_value > 0) {
                                    $sql = "UPDATE tblstaff SET target = ? WHERE staffid = ?";
                                    $this->db->query($sql, array($target_value, $staff_id));
                                    if ($this->db->affected_rows() > 0) {
                                        $this->session->set_flashdata('success', "Target was updated successfully for $user_name.");
                                    } else {
                                        $this->session->set_flashdata('error', "Target is the same and was not changed for $user_name.");
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

                        // Connect to the database
                        $mysqli = new mysqli($hostname, $username, $password, $database);
                        // Check connection
                        if ($mysqli->connect_error) {
                            die("Connection failed: " . $mysqli->connect_error);
                        }

                        $query3 = "
    SELECT 
        s.staffid,
        s.target,
        CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
        COUNT(l.id) AS lead_count,
        SUM(CASE WHEN ts.id = 1 or ts.id = 2 or ts.id = 8 THEN 
        CASE 
            WHEN cf.value = 'Cold' THEN 1
            WHEN cf.value = 'Warm' THEN 1
            WHEN cf.value = 'Hot' THEN 2
            WHEN cf.value = 'Flaming Hot' THEN 3
            ELSE 1
        END
    	ELSE 0 END) as weighted_score,
        SUM(CASE WHEN ts.name = 'Qualified' THEN 1 ELSE 0 END) AS qualified_count,
        SUM(CASE WHEN ts.name = 'Disqualified' THEN 1 ELSE 0 END) AS disqualified_count,
        SUM(CASE WHEN ts.name = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN ts.name = 'IVE' THEN 1 ELSE 0 END) AS ive_count,
        SUM(CASE WHEN ts.name = 'Callback' THEN 1 ELSE 0 END) AS callback_count,
        SUM(CASE WHEN ts.name = 'Duplicate' THEN 1 ELSE 0 END) AS duplicate_count,
        SUM(CASE WHEN ts.name = 'Glitch' THEN 1 ELSE 0 END) AS glitch_count,
        SUM(CASE WHEN ts.name = 'Pushed' THEN 1 ELSE 0 END) as pushed_count,
    	SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS cold_count,
    	SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS warm_count,
    	SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS hot_count,
    	SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS flaming_hot_count
    FROM 
        tblstaff s
    LEFT JOIN 
        tblleads l ON s.staffid = l.assigned AND (
    		(l.last_status_change IS NOT NULL AND (l.status = 1 OR l.status = 8) AND DATE(l.last_status_change) BETWEEN ? AND ?)
    		OR
    		DATE(l.dateadded) BETWEEN ? AND ?
		)
    LEFT JOIN 
        tblleads_status ts ON l.status = ts.id
    LEFT JOIN
    	tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
    WHERE s.admin = 0 AND s.role < 3
    GROUP BY 
        s.staffid
";

                        $stmt = $mysqli->prepare($query3);
                        if (!$stmt) {
                            die('Prepare failed: ' . $mysqli->error);
                        }

                        $stmt->bind_param('ssss', $date_from, $date_to, $date_from, $date_to);
                        $stmt->execute();
                        $result3 = $stmt->get_result();

                        $staff_leads = [];
                        while ($row = $result3->fetch_assoc()) {
                            $staff_leads[] = $row;
                        }
                        // Close the database connection
                        $stmt->close();
                        $mysqli->close();
                        // End of PHP script
                        ?>
                        <div id="wrapper" style="">

                        <table id="status" class="table-auto">
                            <thead>
                                <tr>
                                    <th style="background-color:lightgray">Agent Name</th>
                                    <th style="background-color:lightgray">Lead Count</th>
                                    <th style="background-color:lightgray">Qualified</th>
                                    <th style="background-color:lightgray">Disqualified</th>
                                    <th style="background-color:lightgray">Pending</th>
                                    <th style="background-color:lightgray">IVE</th>
                                    <th style="background-color:lightgray">Callback</th>
                                    <th style="background-color:lightgray">Duplicate</th>
                                    <th style="background-color:lightgray">Glitch</th>
                                    <th style="background-color:lightgray">Pushed</th>
                                    <th style="background-color:lightgray">Cold</th>
                                    <th style="background-color:lightgray">Warm</th>
                                    <th style="background-color:lightgray">Hot</th>
                                    <th style="background-color:lightgray">Flaming Hot</th>
                                    <th style="background-color:lightgray">Lead Weight</th>
                                    <th style="background-color:lightgray">Zone</th>
                                    <th style="background-color:lightgray">Monthly Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_leads as $row): ?>
                                    <tr>
                                        <?php
                                        $t = $row['lead_count'];
                                        $wc = $row['weighted_score'];
                                        $target = $row['target'];
                                        $staffid = $row['staffid'];
                                        ?>
                                        <td>
                                            <div
                                                style=" background-size: cover; background-repeat: no-repeat; background-position: center center; padding-left: 1rem !important; align-items: center; text-align: -webkit-center;">
                                                <?php echo htmlspecialchars($row['staff_name']); ?>
                                                <?php echo staff_profile_image($staffid, ['img', 'img-responsive', 'staff-profile-image-small', 'tw-ring-1 tw-ring-offset-2 tw-ring-primary-500 tw-mx-1 tw-mt-2.5'], 'thumb'); ?>
                                            </div>
                                        </td>
                                        <td style=""><?php echo $t; ?></td>
                                        <td><?php echo $row['qualified_count']; ?></td>
                                        <td style=""><?php echo $row['disqualified_count']; ?></td>
                                        <td><?php echo $row['pending_count']; ?></td>
                                        <td style=""><?php echo $row['ive_count']; ?></td>
                                        <td><?php echo $row['callback_count']; ?></td>
                                        <td style=""><?php echo $row['duplicate_count']; ?></td>
                                        <td><?php echo $row['glitch_count']; ?></td>
                                        <td style=""><?php echo $row['pushed_count']; ?></td>
                                        <td><?php echo $row['cold_count']; ?></td>
                                        <td style=""><?php echo $row['warm_count']; ?></td>
                                        <td><?php echo $row['hot_count']; ?></td>
                                        <td style=""><?php echo $row['flaming_hot_count']; ?></td>
                                        <td><?php echo floor($wc) == $wc ? (int) $wc : (float) $wc; ?></td>
                                        <td style=""><?php
                                        if ($t < ceil($target * 0.9)) {
                                            echo "<span class='tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg' style='padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #DA605B; color: whitesmoke;'>Dangerous</span>";
                                        } elseif ($t > ceil($target * 1.36)) {
                                            echo "<span class='tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg' style='padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #50ce1e; color: whitesmoke;'>Top</font>";
                                        } else {
                                            echo "<span class='tw-font-bold tw-mr-2 rtl:tw-ml-2 tw-text-lg' style='padding: 8px 10px; border-radius: 10px; font-weight: 600; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,.25); margin: 0 10px; background: #2F4058; color: whitesmoke;'>Safe</font>";
                                        }
                                        ?></td>
                                        <td style="width: 150px;">
                                            <?php echo form_open('', ['method' => 'post', 'class' => 'tw-inline-flex tw-gap-2', 'autocomplete' => 'off']); ?>
                                            <input type="hidden" name="id" value="<?php echo $row['staffid']; ?>" />
                                            <label for="editable_label_<?php echo $row['staffid']; ?>"
                                                id="editable_label_<?php echo $row['staffid']; ?>"
                                                data-id="<?php echo $row['staffid']; ?>" contenteditable="true"
                                                type="number" min="1" max="1000"
                                                name="target" /><?php echo $target; ?></label>
                                            <input id="temp_input_<?php echo $row['staffid']; ?>" style="display: none;"
                                                contenteditable="true" type="number" min="1" max="5000"
                                                value="<?php echo $target; ?>" name="target" />
                                            <button type="submit"
                                                style="display: none; border-radius: 45px; color: #000; background-color: #fff;"
                                                id="edit_button_<?php echo $row['staffid']; ?>"
                                                class="btn btn-primary tw-w-10 tw-px-2 tw-py-1"><i
                                                    class="fas fa-edit"></i></button>
                                            <?php echo form_close(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
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

            <?
            // php init_tail(); 
            ?>
</body>

</html>
<script>
    (function () {
        new Chart($('#leads-staff-report'), {
            data: <?php echo $leads_staff_report; ?>,
            type: 'bar',
            options: { responsive: true, maintainAspectRatio: false }
        });
    })();
</script>
<script src="<?php echo site_url('resources/js/html2canvas.min.js'); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var messages = document.querySelectorAll('.flash-message');
        if (messages.length > 0) {
            messages.forEach(function (message) {
                message.style.opacity = '1';
                message.style.height = 'auto';
                message.style.margin = '10px 0';
                message.style.padding = '10px';
            });

            setTimeout(function () {
                messages.forEach(function (message) {
                    message.style.opacity = '0';
                    message.style.height = '0';
                    message.style.margin = '0';
                    message.style.padding = '0';
                });
            }, 3000);
        }
    });
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
        label.addEventListener('blur', function () {
            const id = this.dataset.id;
            const ele = document.getElementById(`temp_input_${id}`);
            ele.value = label.textContent
            console.log('debug:label#temp_input_${id}', label.textContent);
            showButton(id);
        });
    });
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
            link.download = 'agent-report-snapshot.png';
            link.click();
        });
    }
    */

    async function takeSnapshot() {
        try {
            const canvas = await html2canvas(document.getElementById('status'));
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
</script>