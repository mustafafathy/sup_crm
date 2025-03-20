<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Report_Export extends AdminController {
    public function export_campaign_report() {
    	if (staff_cant('view', 'reports')) {
            access_denied('reports');
        }
    
        $exportType = $this->input->post('exportType');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
    
    	if(!isset($exportType)) {
			$exportType = 'csv';
        }

    	$todayDate = date('Y-m-d');
    	if(!isset($startDate)) {
			$startDate = $todayDate;
        }

    	if(!isset($startDate)) {
			$endDate = $todayDate;
        }

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
                        WHEN cf.value = 'Hot' THEN 2
                        WHEN cf.value = 'Flaming Hot' THEN 3
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

        $result = $this->db->query($query, [$startDate, $endDate, $startDate, $endDate]);
        $data = $result->result_array();
    
    	$formattedData = [];
        foreach ($data as $row) {
            $dtarget = $row['camp_target'];
            $weight = $row['weighted_score'];
            $formattedData[] = [
                $row['lead_source_name'] . ' (' . ($row['camp_activity'] ? 'Active' : 'Inactive') . ')',
                ($dtarget - $weight < 0 ? 'Over Achieved' : ($dtarget - $weight == 0 ? 'Achieved' : 'Not Achieved')),
                $dtarget,
                floor($weight) == $weight ? (int) $weight : (float) $weight,
                max(0, $dtarget - $weight),
                $row['status_qualified'],
                $row['status_disqualified'],
                $row['status_pending'],
                $row['status_ive'],
                $row['status_callback'],
                $row['status_duplicate'],
                $row['status_glitch'],
                $row['status_pushed'],
                $row['status_cold'],
                $row['status_warm'],
                $row['status_hot'],
                $row['status_flaming_hot'],
            ];
        }
    
        $query2 = "
            SELECT 
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
                tblleads AS l
            JOIN 
                tblleads_status AS ts ON l.status = ts.id
            LEFT JOIN
                tblcustomfieldsvalues AS cf ON l.id = cf.relid AND cf.fieldid = 4
            WHERE
        		(
    				(l.last_status_change IS NOT NULL AND (l.status = 1 OR l.status = 8) AND DATE(l.last_status_change) BETWEEN ? AND ?)
    				OR
    				DATE(l.dateadded) BETWEEN ? AND ?
				)
        ";

        $result2 = $this->db->query($query2, [$startDate, $endDate, $startDate, $endDate]);
        $totalCounts = $result2->row_array();

		$formattedCount = [
            'Total', '', '', '', '',
            $totalCounts['qualified_count'] ?? 0,
            $totalCounts['disqualified_count'] ?? 0,
            $totalCounts['pending_count'] ?? 0,
            $totalCounts['ive_count'] ?? 0,
            $totalCounts['callback_count'] ?? 0,
            $totalCounts['duplicate_count'] ?? 0,
            $totalCounts['glitch_count'] ?? 0,
            $totalCounts['pushed_count'] ?? 0,
            $totalCounts['cold_count'] ?? 0,
            $totalCounts['warm_count'] ?? 0,
            $totalCounts['hot_count'] ?? 0,
            $totalCounts['flaming_hot_count'] ?? 0,
        ];

    	$this->exportCampaignReportToCSV($formattedData, $formattedCount, $startDate, $endDate);
        /*switch ($exportType) {
            case 'csv':
                $this->exportCampaignReportToCSV($formattedData, $formattedCount, $startDate, $endDate);
                break;
            case 'excel':
                $this->exportCampaignReportToExcel($formattedData, $formattedCount, $startDate, $endDate);
                break;
            case 'pdf':
                $this->exportCampaignReportToPDF($formattedData, $formattedCount, $startDate, $endDate);
                break;
            default:
                show_error('Invalid export type selected.');
                break;
        }*/
    }

    private function exportCampaignReportToCSV($data, $total, $start, $end) {
        $filename = 'campaign_report_' . date('d/m/Y') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $file = fopen('php://output', 'w');

        fputcsv($file, ["Export generated from ".$start." to ".$end]);
        $header = [
            "Campaign", "Status", "Target", "Weight", "Missing",
            "Qualified", "Disqualified", "Pending", "IVE", "Callback",
            "Duplicate", "Glitch", "Pushed", "Cold", "Warm", "Hot", "Flaming Hot"
        ];
        fputcsv($file, $header);
    
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
    
        fputcsv($file, $total);

        fclose($file);
        exit;
    }

    private function exportCampaignReportToExcel($data, $total, $start, $end) {
    }

    private function exportCampaignReportToPDF($data, $total, $start, $end) {
    }

    public function export_agent_report() {
        if (staff_cant('view', 'reports')) {
            access_denied('reports');
        }
    
        $exportType = $this->input->post('exportType');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
    
    	if(!isset($exportType)) {
			$exportType = 'csv';
        }

    	$todayDate = date('Y-m-d');
    	if(!isset($startDate)) {
			$startDate = $todayDate;
        }

    	if(!isset($startDate)) {
			$endDate = $todayDate;
        }

        $query = "
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
				tblleads l ON s.staffid = l.assigned AND 
                (
    				(l.last_status_change IS NOT NULL AND (l.status = 1 OR l.status = 8) AND DATE(l.last_status_change) BETWEEN ? AND ?)
    				OR
    				DATE(l.dateadded) BETWEEN ? AND ?
				)
			LEFT JOIN 
				tblleads_status ts ON l.status = ts.id
			LEFT JOIN
				tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
            WHERE s.admin = 0 AND s.role < 3
			GROUP BY s.staffid
		";

        $result = $this->db->query($query, [$startDate, $endDate, $startDate, $endDate]);
        $data = $result->result_array();
    
    	$formattedData = [];
        foreach ($data as $row) {
        	$t = $row['lead_count'];
        	$wc = $row['weighted_score'];
			$target = $row['target'];
            $formattedData[] = [
                $row['staff_name'],
                $t,
                $row['qualified_count'],
                $row['disqualified_count'],
                $row['pending_count'],
                $row['ive_count'],
                $row['callback_count'],
                $row['duplicate_count'],
                $row['glitch_count'],
                $row['pushed_count'],
                $row['cold_count'],
                $row['warm_count'],
                $row['hot_count'],
                $row['flaming_hot_count'],
                floor($wc) == $wc ? (int) $wc : (float) $wc,
                ($t < ceil($target*0.9) ? "Dangerous" : ($t > ceil($target*1.36) ? "Top" : "Safe")),
            	$target
            ];
        }

    	$this->exportAgentReportToCSV($formattedData, $startDate, $endDate);
        /*switch ($exportType) {
            case 'csv':
                $this->exportAgentReportToCSV($formattedData, $startDate, $endDate);
                break;
            case 'excel':
                $this->exportAgentReportToExcel($formattedData, $startDate, $endDate);
                break;
            case 'pdf':
                $this->exportAgentReportToPDF($formattedData, $startDate, $endDate);
                break;
            default:
                show_error('Invalid export type selected.');
                break;
        }*/
    }

    private function exportAgentReportToCSV($data, $start, $end) {
        $filename = 'agent_report_' . date('d/m/Y') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $file = fopen('php://output', 'w');

        fputcsv($file, ["Export generated from ".$start." to ".$end]);
        $header = [
            "Agent Name", "Lead Count", "Qualified", "Disqualified", "Pending",
            "IVE", "Callback", "Duplicate", "Glitch", "Pushed",
            "Cold", "Warm", "Hot", "Flaming Hot", "Lead Weight", "Zone", "Monthly Target"
        ];
        fputcsv($file, $header);
    
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
        exit;
    }

    private function exportAgentReportToExcel($data, $start, $end) {
    }

    private function exportAgentReportToPDF($data, $start, $end) {
    }

    public function export_missing_report() {
    	if (staff_cant('view', 'missing_report')) {
            access_denied('missing_report');
        }
    
        $exportType = $this->input->post('exportType');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
    
    	if(!isset($exportType)) {
			$exportType = 'csv';
        }

    	$todayDate = date('Y-m-d');
    	if(!isset($startDate)) {
			$startDate = $todayDate;
        }

    	if(!isset($startDate)) {
			$endDate = $todayDate;
        }

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
                        WHEN cf.value = 'Hot' THEN 2
                        WHEN cf.value = 'Flaming Hot' THEN 3
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

        $result = $this->db->query($query, [$startDate, $endDate, $startDate, $endDate]);
        $data = $result->result_array();
    
    	$formattedData = [];
    	$totalTarget = 0;
    	$totalWeight = 0;
    	$totalMissing = 0;
        foreach ($data as $row) {
            $dtarget = $row['camp_target'];
        	$activity = $row['camp_activity'];
            $weight = $row['weighted_score'];
        	$missing = max(0, $dtarget - $weight);
        	if($activity)
        	{
        		$totalTarget = $totalTarget+$dtarget;
        		$totalWeight = $totalWeight+$weight;
        		$totalMissing = $totalMissing+$missing;
        	}
            $formattedData[] = [
                $row['lead_source_name'] . ' (' . ($activity ? 'Active' : 'Inactive') . ')',
                ($dtarget - $weight < 0 ? 'Over Achieved' : ($dtarget - $weight == 0 ? 'Achieved' : 'Not Achieved')),
                $dtarget,
                floor($weight) == $weight ? (int) $weight : (float) $weight,
                $missing,
            ];
        }
    
    	$formattedCount = [
            'Total', '',
            $totalTarget,
            floor($totalWeight) == $totalWeight ? (int) $totalWeight : (float) $totalWeight,
            $totalMissing,
        ];

    	$this->exportMissingReportToCSV($formattedData, $formattedCount, $startDate, $endDate);
        /*switch ($exportType) {
            case 'csv':
                $this->exportMissingReportToCSV($formattedData, $formattedCount, $startDate, $endDate);
                break;
            case 'excel':
                $this->exportMissingReportToExcel($formattedData, $formattedCount, $startDate, $endDate);
                break;
            case 'pdf':
                $this->exportMissingReportToPDF($formattedData, $formattedCount, $startDate, $endDate);
                break;
            default:
                show_error('Invalid export type selected.');
                break;
        }*/
    }

    private function exportMissingReportToCSV($data, $total, $start, $end) {
        $filename = 'missing_report_' . date('d/m/Y') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $file = fopen('php://output', 'w');

        fputcsv($file, ["Export generated from ".$start." to ".$end]);
        $header = [
            "Campaign", "Status", "Target", "Weight", "Missing"
        ];
        fputcsv($file, $header);
    
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
    
    	fputcsv($file, $total);

        fclose($file);
        exit;
    }

    private function exportMissingReportToExcel($data, $total, $start, $end) {
    }

    private function exportMissingReportToPDF($data, $total, $start, $end) {
    }
}