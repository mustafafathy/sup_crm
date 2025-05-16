<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('app_admin_head', 'leads_app_admin_head_data');

function leads_app_admin_head_data()
{
    ?>
    <script>
        var leadUniqueValidationFields = <?php echo json_decode(json_encode(get_option('lead_unique_validation'))); ?>;
        var leadAttachmentsDropzone;
    </script>
    <?php
}

/**
 * Check if the user is lead creator
 * @since  Version 1.0.4
 * @param  mixed  $leadid leadid
 * @param  mixed  $staff_id staff id (Optional)
 * @return boolean
 */

function is_lead_creator($lead_id, $staff_id = '')
{
    if (!is_numeric($staff_id)) {
        $staff_id = get_staff_user_id();
    }

    return total_rows(db_prefix() . 'leads', [
        'addedfrom' => $staff_id,
        'id' => $lead_id,
    ]) > 0;
}

/**
 * Lead consent URL
 * @param  mixed $id lead id
 * @return string
 */
function lead_consent_url($id)
{
    return site_url('consent/l/' . get_lead_hash($id));
}

/**
 * Lead public form URL
 * @param  mixed $id lead id
 * @return string
 */
function leads_public_url($id)
{
    return site_url('forms/l/' . get_lead_hash($id));
}

/**
 * Get and generate lead hash if don't exists.
 * @param  mixed $id  lead id
 * @return string
 */
function get_lead_hash($id)
{
    $CI = &get_instance();
    $hash = '';

    $CI->db->select('hash');
    $CI->db->where('id', $id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    if ($lead) {
        $hash = $lead->hash;
        if (empty($hash)) {
            $hash = app_generate_hash() . '-' . app_generate_hash();
            $CI->db->where('id', $id);
            $CI->db->update(db_prefix() . 'leads', ['hash' => $hash]);
        }
    }

    return $hash;
}


function get_leads_performance($from_date = null)
{
    // Set default `from_date` to the first day of the current month if not provided
    if ($from_date === null) {
        $from_date = date('Y-m-01');
    }

    $CI = &get_instance();

    $sql = "SELECT 
    s.staffid,
    s.target,
    CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
    COALESCE(COUNT(l.id), 0) AS lead_count
FROM 
    tblstaff s
LEFT JOIN 
    tblleads l ON s.staffid = l.assigned AND (l.dateadded IS NULL OR l.dateadded  >= ?)
WHERE 
    s.role = 1
GROUP BY 
    s.staffid
ORDER BY 
    lead_count DESC;
";


    // Execute the query with parameter binding for `from_date`
    return $CI->db->query($sql, [$from_date])->result();
}
/**
 * Get leads summary
 * @return array
 */
function get_leads_summary($from_date = null)
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $statuses = $CI->leads_model->get_status();

    $totalStatuses = count($statuses);
    $has_permission_view = staff_can('view', 'leads');
    $sql = '';
    $whereNoViewPermission = '(addedfrom = ' . get_staff_user_id() . ' OR assigned=' . get_staff_user_id() . ' OR is_public = 1)';

    // Add lost status to statuses array
    $statuses[] = [
        'lost' => true,
        'name' => _l('lost_leads'),
        'color' => '#fc2d42',
    ];

    // Build the SQL query
    foreach ($statuses as $status) {
        $sql .= ' SELECT COUNT(*) as total, SUM(lead_value) as value FROM ' . db_prefix() . 'leads';

        // Handle lost and junk statuses
        if (isset($status['lost'])) {
            $sql .= ' WHERE lost=1';
        } elseif (isset($status['junk'])) {
            $sql .= ' WHERE junk=1';
        } else {
            $sql .= ' WHERE status=' . intval($status['id']); // Ensure it's an integer
        }

        // Apply date filter if provided
        if ($from_date !== null) {
            $sql .= ' AND dateadded >= "' . $CI->db->escape_str($from_date) . '"'; // Sanitize the date input
        }

        // Add permission check
        if (!$has_permission_view) {
            $sql .= ' AND ' . $whereNoViewPermission;
        }

        // Append UNION ALL for the next status
        $sql .= ' UNION ALL ';
    }

    // Remove the last UNION ALL
    $sql = rtrim($sql, ' UNION ALL ');

    // Execute the query
    $result = $CI->db->query($sql)->result();

    // Get total leads count for percentage calculation
    $total_leads = $CI->db->count_all_results(db_prefix() . 'leads');

    // Prepare the results with percentages
    foreach ($statuses as $key => $status) {
        if (isset($status['lost']) || isset($status['junk'])) {
            $statuses[$key]['percent'] = ($total_leads > 0 ? number_format(($result[$key]->total * 100) / $total_leads, 2) : 0);
        }

        $statuses[$key]['total'] = isset($result[$key]) ? $result[$key]->total : 0; // Ensure we avoid undefined index
        $statuses[$key]['value'] = isset($result[$key]) ? $result[$key]->value : 0; // Same as above
    }

    return $statuses;
}



// function leads_performance():
// {


// }

/**
 * Render lead status select field with ability to create inline statuses with + sign
 * @param  array  $statuses         current statuses
 * @param  string  $selected        selected status
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @param  boolean $exclude_default whether to exclude default Client status
 * @return string
 */
function render_leads_status_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [], $exclude_default = false)
{
    foreach ($statuses as $key => $status) {
        if ($status['isdefault'] == 1) {
            if ($exclude_default == false) {
                $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
            } else {
                unset($statuses[$key]);
            }

            break;
        }
    }

    if (is_admin() || get_option('staff_members_create_inline_lead_status') == '1') {
        return render_select_with_input_group($name, $statuses, ['id', 'name'], $lang_key, $selected, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_lead_status_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a></div>', $select_attrs);
    }

    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

/**
 * Render lead source select field with ability to create inline source with + sign
 * @param  array   $sources         current sourcees
 * @param  string  $selected        selected source
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @return string
 */
function render_leads_source_select($sources, $selected = '', $lang_key = '', $name = 'source', $select_attrs = [])
{
    if (is_admin() || get_option('staff_members_create_inline_lead_source') == '1') {
        echo render_select_with_input_group($name, $sources, ['id', 'name'], $lang_key, $selected, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_lead_source_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a></div>', $select_attrs);
    } else {
        echo render_select($name, $sources, ['id', 'name'], $lang_key, $selected, $select_attrs);
    }
}

/**
 * Load lead language
 * Used in public GDPR form
 * @param  string $lead_id
 * @return string return loaded language
 */
function load_lead_language($lead_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();

    // Lead not found or default language already loaded
    if (!$lead || empty($lead->default_language)) {
        return false;
    }

    $language = $lead->default_language;

    if (!file_exists(APPPATH . 'language/' . $language)) {
        return false;
    }

    $CI->lang->is_loaded = [];
    $CI->lang->language = [];

    $CI->lang->load($language . '_lang', $language);
    load_custom_lang_file($language);
    $CI->lang->set_last_loaded_language($language);

    return true;
}
function _getCurrentUserRole()
{
    global $CI;

    // Get the current user ID from session
    $userId = $CI->session->userdata('staff_user_id');
    if (!$userId) {
        return null; // No user is logged in
    }

    // Query to get the user role
    $CI->db->select('role');
    $CI->db->from('tblstaff');
    $CI->db->where('staffid', $userId);
    $query = $CI->db->get();

    if ($query->num_rows() > 0) {
        $user = $query->row();
        return $user->role; // Return the user role
    } else {
        return null; // User not found
    }
}


function campaigns_target()
{
    $sql = "SELECT SUM(ls.target) AS total_target FROM tblleads_sources AS ls";
    $CI = &get_instance();
    $query = $CI->db->query($sql);
    return $query->row()->total_target; // Using row() to retrieve a single result row
}

function total_leads_eval($date_from = null, $date_to = null)
{
    // Set default values to today’s date if parameters are not provided
    $date_from = $date_from ?? date('Y-m-d');
    $date_to = $date_to ?? date('Y-m-d');

    $sql = "SELECT 
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
        SUM(CASE WHEN ts.id IN (1, 2, 8) THEN 
            CASE 
                WHEN cf.value = 'Cold' THEN 1
                WHEN cf.value = 'Warm' THEN 1
                WHEN cf.value = 'Hot' THEN 2
                WHEN cf.value = 'Flaming Hot' THEN 3
                ELSE 1
            END
        ELSE 0 END) as weighted_score,
        SUM(CASE WHEN cf.value = 'Cold' THEN 1 ELSE 0 END) AS status_cold,
        SUM(CASE WHEN cf.value = 'Warm' THEN 1 ELSE 0 END) AS status_warm,
        SUM(CASE WHEN cf.value = 'Hot' THEN 1 ELSE 0 END) AS status_hot,
        SUM(CASE WHEN cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS status_flaming_hot
    FROM 
        tblleads_sources ls
    LEFT JOIN 
        tblleads tl ON ls.id = tl.source AND (
            (tl.last_status_change IS NOT NULL AND (tl.status = 1 OR tl.status = 8) AND DATE(tl.last_status_change) = ?)
            OR
            DATE(tl.dateadded) = ?
        )
    LEFT JOIN 
        tblleads_status ts ON tl.status = ts.id
    LEFT JOIN
        tblcustomfieldsvalues cf ON tl.id = cf.relid AND cf.fieldid = 4
    WHERE ls.account = 1
    GROUP BY 
        ls.name";

    // Get CodeIgniter instance
    $CI = &get_instance();

    // Execute the query with bound parameters
    $query = $CI->db->query($sql, [$date_from, $date_from]);

    // Fetch and return the result
    return $query->result_array(); // Return the full result array
}

function user_leads_daily($current_user, $from_date = null)
{
    // Set default `from_date` to the first day of the current month if not provided
    if ($from_date === null) {
        $from_date = date('Y-m-d');
    }
    $role = _getCurrentUserRole();
    $CI = &get_instance();
    if ((is_admin($current_user)) or ($role >= 2)) {

        // Define SQL query

        $sql = "SELECT 
                sum(s.target) AS target,

                SUM(ls.active) as camp_activity,
                sum(ls.target) as camp_target


                -- COUNT(l.id) AS achieved,

                FROM tblleads l
                    LEFT JOIN 
                        tblstaff s ON s.staffid = l.assigned 
                    LEFT JOIN 
                         tblleads_status ts ON l.status = ts.id
                    LEFT JOIN
                        tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
                    LEFT JOIN
                        tblleads_sources ls ON ls.id = l.source 


                WHERE 
                    l.dateadded >= ?";

        // Execute the query with parameter binding for `from_date` and `user_id`
        return $CI->db->query($sql, [$from_date])->result();
    } else {
        $sql = "SELECT 
                s.staffid,
                s.target,
                CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
                COUNT(l.id) AS achieved,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS cold_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS warm_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS hot_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS flaming_hot_count
                FROM tblstaff s
                    LEFT JOIN 
                        tblleads l ON s.staffid = l.assigned 

                    LEFT JOIN
                        tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
                WHERE 
                    s.admin = 0 

                    AND l.dateadded >= ?
                    AND s.staffid = ?";

        // Execute the query with parameter binding for `from_date` and `user_id`
        return $CI->db->query($sql, [$from_date, $current_user])->result();

    }
}

function user_leads_monthly($current_user, $from_date = null)
{
    // Set default `from_date` to the first day of the current month if not provided
    if ($from_date === null) {
        $from_date = date('Y-m-01');
    }

    $role = _getCurrentUserRole(); // Retrieve current user role
    $CI = &get_instance(); // Get the CodeIgniter instance

    // Admin or higher role query
    if (is_admin($current_user) || $role >= 2) {
        $sql = "SELECT 
                SUM(s.target) AS target,
                COUNT(l.id) AS achieved,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS cold_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS warm_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS hot_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS flaming_hot_count
                FROM tblstaff s
                    LEFT JOIN tblleads l ON s.staffid = l.assigned 
                    LEFT JOIN tblleads_status ts ON l.status = ts.id
                    LEFT JOIN tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
                WHERE l.dateadded >= ?";

        // Execute the query with parameter binding for `from_date`
        return $CI->db->query($sql, [$from_date])->result();
    } else if (!is_admin($current_user)) {
        // User-specific query for lower roles
        $sql = "SELECT 
                s.staffid,
                s.target AS target,
                -- CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
                COUNT(l.id) AS achieved,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Cold' THEN 1 ELSE 0 END) AS cold_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Warm' THEN 1 ELSE 0 END) AS warm_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Hot' THEN 1 ELSE 0 END) AS hot_count,
                SUM(CASE WHEN cf.value IS NOT NULL AND cf.value = 'Flaming Hot' THEN 1 ELSE 0 END) AS flaming_hot_count
                FROM tblstaff s
                    LEFT JOIN tblleads l ON s.staffid = l.assigned 
                    LEFT JOIN tblcustomfieldsvalues cf ON l.id = cf.relid AND cf.fieldid = 4
                WHERE l.dateadded >= ? AND s.staffid = ?";

        // Execute the query with parameter binding for user_id and `from_date`
        return $CI->db->query($sql, [$from_date, $current_user])->result();
    }

}
