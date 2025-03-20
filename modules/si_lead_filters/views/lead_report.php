<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$report_heading = '';
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$lead_id = $this->input->post('id', TRUE);
	if (isset($lead_id)) {
		$this->load->database();
		$status_value = $this->input->post('lead_status', TRUE);
		if (is_numeric($status_value) && $status_value > 0) {
			$old_status = '';
			$sql = "SELECT status FROM tblleads WHERE id = ?";
			$query = $this->db->query($sql, array($lead_id));
			if ($query->num_rows() > 0) {
				$this->db->select('name');
				$this->db->from('tblleads_status');
				$this->db->where('id', $query->row()->status);
				$query = $this->db->get();
				if ($query->num_rows() > 0)
					$old_status = $query->row()->name;
			}
			$sql = "UPDATE tblleads SET status = ? WHERE id = ?";
			$this->db->query($sql, array($status_value, $lead_id));
			if ($this->db->affected_rows() > 0) {
				$this->db->select('name');
				$this->db->from('tblleads_status');
				$this->db->where('id', $status_value);
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					$current_status = $query->row()->name;
					if ($current_status != $old_status && $old_status != '') {
						$_log_message = 'not_lead_activity_status_updated';
						$additional_data = serialize([
							get_staff_full_name(),
							$old_status,
							$current_status,
						]);

						hooks()->do_action('lead_status_changed', [
							'lead_id' => $lead_id,
							'old_status' => $old_status,
							'new_status' => $current_status,
						]);

						$log = [
							'date' => date('Y-m-d H:i:s'),
							'description' => $_log_message,
							'leadid' => $lead_id,
							'staffid' => get_staff_user_id(),
							'additional_data' => $additional_data,
							'full_name' => get_staff_full_name(get_staff_user_id()),
						];

						$this->db->insert(db_prefix() . 'lead_activity_log', $log);
					}

					$this->session->set_flashdata('success', "Status was changed successfully to " . $current_status . " for lead $lead_id.");
				}
			} else {
				$this->session->set_flashdata('error', "Status is the same and was not changed.");
			}
		}

		$lead_heat_value = $this->input->post('heat', TRUE);
		if (is_string($lead_heat_value)) {
			$this->db->select('options');
			$this->db->from('tblcustomfields');
			$this->db->where('id', 4);
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				$heatTemps = explode(",", $query->row()->options);
			} else {
				$heatTemps = [];
			}

			if (in_array($lead_heat_value, $heatTemps)) {
				$sql = "SELECT id FROM tblcustomfieldsvalues WHERE relid = ? AND fieldid = 4";
				$query = $this->db->query($sql, array($lead_id));
				if ($query->num_rows() > 0) {
					$sql = "UPDATE tblcustomfieldsvalues SET value = ? WHERE relid = ? AND fieldid = 4";
					$this->db->query($sql, array($lead_heat_value, $lead_id));
					if ($this->db->affected_rows() > 0) {
						$this->session->set_flashdata('success', "Lead temperature was changed successfully to $lead_heat_value for lead $lead_id.");
					} else {
						$this->session->set_flashdata('error', "Lead temperature is the same and was not changed $lead_heat_value.");
					}
				} else {
					$sql = "INSERT INTO tblcustomfieldsvalues (relid, fieldid, fieldto, value) VALUES (?, 4, 'leads', ?)";
					$this->db->query($sql, array($lead_id, $lead_heat_value));
					if ($this->db->affected_rows() > 0) {
						$this->session->set_flashdata('success', "Lead temperature was added successfully as $lead_heat_value for lead $lead_id.");
					} else {
						$this->session->set_flashdata('error', "Failed to add lead temperature $lead_heat_value.");
					}
				}
			} else {
				$this->session->set_flashdata('error', "Invalid input. Please select a valid lead temperature.");
			}
		}

		redirect(current_url());
	}
}
?>

<link href="<?php echo module_dir_url('si_lead_filters', 'assets/css/si_lead_filters_style.css'); ?>" rel="stylesheet" />
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<?php echo form_open($this->uri->uri_string() . ($this->input->get('filter_id') ? '?filter_id=' . $this->input->get('filter_id') : ''), "id=si_form_lead_filter"); ?>
						<h4 class="pull-left"><?php echo _l('si_lf_submenu_lead_filters'); ?> <small
								class="text-success"><?php echo htmlspecialchars($saved_filter_name); ?></small></h4>
						<div class="btn-group pull-right mleft4 btn-with-tooltip-group" data-toggle="tooltip"
							data-title="<?php echo _l('si_lf_filter_templates'); ?>" data-original-title="" title="">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
								aria-haspopup="true" aria-expanded="true"><i class="fa fa-list"></i>
							</button>
							<ul class="row dropdown-menu width400">
								<?php
								if (!empty($filter_templates)) {
									foreach ($filter_templates as $row) {
										echo "<li><a href='leads_quality?filter_id=$row[id]'>$row[filter_name]</a></li>";
									}
								} else
									echo '<li><a >' . _l('si_lf_no_filter_template') . '</a></li>';
								?>
							</ul>
						</div>
						<button type="submit" data-toggle="tooltip" data-title="<?php echo _l('si_lf_apply_filter'); ?>"
							class=" pull-right btn btn-info mleft4"><?php echo _l('filter'); ?></button>
						<a href="leads_quality" class=" pull-right btn btn-info mleft4"><?php echo _l('new'); ?></a>
						<div class="clearfix"></div>
						<hr />
						<div class="row">
							<?php if (has_permission('leads', '', 'view')) { ?>
								<div class="col-md-2 border-right">
									<label for="rel_type" class="control-label"><?php echo _l('Agents'); ?></label>
									<?php echo render_select('member', $members, array('staffid', array('firstname', 'lastname')), '', $staff_id, array('data-none-selected-text' => _l('All Agents')), array(), 'no-margin'); ?>
								</div>
								
							<?php } ?>

							<div class="col-md-2 text-center1 border-right">
								<label for="status" class="control-label"><?php echo _l('lead_status'); ?></label>
								<?php
								echo render_select('status[]', $lead_statuses, array('id', 'name'), '', $statuses, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false); ?>

							</div>
							<!--start sources select -->
							<div class="col-md-2  border-right">
								<label for="rel_type" class="control-label"><?php echo _l('lead_source'); ?></label>
								<?php
								echo render_select('source[]', $lead_sources, array('id', 'name'), '', $sources, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false); ?>
							</div>
							<!--end sources select-->
							<!--start country select -->
							<div class="col-md-2  border-right">
								<label for="rel_type" class="control-label"><?php echo _l('lead_country'); ?></label>
								<?php
								$lead_countries[] = array('id' => -1, 'name' => _l('si_lf_unknown'));
								echo render_select('countries[]', $lead_countries, array('id', 'name'), '', $countries, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false); ?>
							</div>
							<!--end counry select-->
							<!--start tags -->
							<div class="col-md-2 text-center1 border-right">
								<label for="rel_type" class="control-label"><?php echo _l('tags'); ?></label>
								<?php
								echo render_select('tags[]', get_tags(), array('id', 'name'), '', $tags, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false); ?>
							</div>
							<!--end tags-->
							<!--start other_type select -->
							<div class="col-md-2 border-right form-group">
								<label for="date_by" class="control-label"><span
										class="control-label"><?php echo _l('si_lf_filter_by_type'); ?></span></label>
								<select name="type" id="type" class="selectpicker no-margin" data-width="100%">
									<option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
									<option value="lost" <?php echo ($type == 'lost' ? 'selected' : '') ?>>
										<?php echo _l('lead_lost'); ?></option>
									<option value="junk" <?php echo ($type == 'junk' ? 'selected' : '') ?>>
										<?php echo _l('lead_junk'); ?></option>
									<option value="public" <?php echo ($type == 'public' ? 'selected' : '') ?>>
										<?php echo _l('lead_public'); ?></option>
									<option value="not_assigned" <?php echo ($type == 'not_assigned' ? 'selected' : '') ?>>
										<?php echo _l('leads_not_assigned'); ?></option>
								</select>
							</div>
							<!--end other_type select-->
						</div>
						<?php
						if (count($hide_columns) == 0)
							$hide_columns = ['company', 'email', 'country', 'dateadded', 'lastcontact', 'is_public', 'tags', 'leads_whatsapp_enable'];
						?>
						<div class="row">
							<!--start hide_export_columns select -->
							<div class="col-md-2 border-right form-group">
								<label for="hide_columns" class="control-label"><span
										class="control-label"><?php echo _l('si_lf_hide_export_columns'); ?></span></label>
								<select name="hide_columns[]" id="hide_columns" class="selectpicker no-margin"
									data-width="100%" multiple>
									<option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
									<option value="leads_owner_s_name" <?php echo (in_array('leads_owner_s_name', $hide_columns) ? 'selected' : '') ?>>
										<?php echo "Owner's Name" ?></option>
									<!-- <option value="name" <?php echo (in_array('name', $hide_columns) ? 'selected' : '') ?>><?php echo _l('leads_dt_name'); ?></option> -->
									<option value="phonenumber" <?php echo (in_array('phonenumber', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('leads_dt_phonenumber'); ?></option>
									<option value="status" <?php echo (in_array('status', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('leads_dt_status'); ?></option>
									<option value="company" <?php echo (in_array('company', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('lead_company'); ?></option>
									<option value="email" <?php echo (in_array('email', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('leads_dt_email'); ?></option>
									<option value="country" <?php echo (in_array('country', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('lead_country'); ?></option>
									<?php
									$custom_fields = get_custom_fields('leads', ['show_on_table' => 1,]);
									foreach ($custom_fields as $field) {
										if ($field['slug'] == 'leads_owner_s_name')
											continue;

										echo "<option value='" . $field['slug'] . "' " . (in_array($field['slug'], $hide_columns) ? 'selected' : '') . ">" . $field['name'] . "</option>";
									}
									?>
									<option value="source" <?php echo (in_array('source', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('lead_add_edit_source'); ?></option>
									<option value="dateadded" <?php echo (in_array('dateadded', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('si_lf_created_date'); ?></option>
									<option value="lastcontact" <?php echo (in_array('lastcontact', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('si_lf_last_contacted_date'); ?></option>
									<option value="is_public" <?php echo (in_array('is_public', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('lead_public'); ?></option>
									<option value="assigned" <?php echo (in_array('assigned', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('leads_dt_assigned'); ?></option>
									<option value="tags" <?php echo (in_array('tags', $hide_columns) ? 'selected' : '') ?>>
										<?php echo _l('tags'); ?></option>
								</select>
							</div>
							<!--end hide_export_columns select-->
							<!--start filter_by select -->
							<div class="col-md-2 border-right form-group">
								<label for="date_by" class="control-label"><span
										class="control-label"><?php echo _l('si_lf_lead_filter_by_date'); ?></span></label>
								<select name="date_by" id="date_by" class="selectpicker no-margin" data-width="100%">
									<option value="dateadded"><?php echo _l('si_lf_created_date'); ?></option>
									<option value="lastcontact" <?php echo ($date_by != '' && $date_by == 'lastcontact' ? 'selected' : '') ?>>
										<?php echo _l('si_lf_last_contacted_date'); ?></option>
								</select>
							</div>
							<!--end filter_by select-->
							<div class="col-md-2 form-group border-right" id="searchCategoryLabel">
								<label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
								<select class="selectpicker" name="report_months" id="report_months" data-width="100%"
									data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
									<option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
									<option value="this_month"><?php echo _l('this_month'); ?></option>
									<option value="1"><?php echo _l('last_month'); ?></option>
									<option value="this_year"><?php echo _l('this_year'); ?></option>
									<option value="last_year"><?php echo _l('last_year'); ?></option>
									<option value="3"
										data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>">
										<?php echo _l('report_sales_months_three_months'); ?></option>
									<option value="6"
										data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>">
										<?php echo _l('report_sales_months_six_months'); ?></option>
									<option value="12"
										data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>">
										<?php echo _l('report_sales_months_twelve_months'); ?></option>
									<option value="custom"><?php echo _l('period_datepicker'); ?></option>
								</select>
								<?php
								if ($report_months !== '') {
									$report_heading .= ' for ' . _l('period_datepicker') . " ";
									switch ($report_months) {
										case 'this_month':
											$report_heading .= date('01-m-Y') . " To " . date('t-m-Y');
											break;
										case '1':
											$report_heading .= date('01-m-Y', strtotime('-1 month')) . " To " . date('t-m-Y', strtotime('-1 month'));
											break;
										case 'this_year':
											$report_heading .= date('01-01-Y') . " To " . date('31-12-Y');
											break;
										case 'last_year':
											$report_heading .= date('01-01-Y', strtotime('-1 year')) . " To " . date('31-12-Y', strtotime('-1 year'));
											break;
										case '3':
											$report_heading .= date('01-m-Y', strtotime('-2 month')) . " To " . date('t-m-Y');
											break;
										case '6':
											$report_heading .= date('01-m-Y', strtotime('-5 month')) . " To " . date('t-m-Y');
											break;
										case '12':
											$report_heading .= date('01-m-Y', strtotime('-11 month')) . " To " . date('t-m-Y');
											break;
										case 'custom':
											$report_heading .= $report_from . " To " . $report_to;
											break;
										default:
											$report_heading .= 'All Time';
									}
								}
								?>
							</div>
							<div class="col-md-2 form-group border-right" id="searchCategory">
							<label for="searchCategorySelect" class="control-label"><span
										class="control-label">Pipeline</span></label>

							<?php 
									if (count($categories) > 1) {
										// Check if there are more than 1 category
										// Start rendering the select element manually
										echo '<select name="searchCategory" id="searchCategorySelect" class="form-control" onchange="submit();" style="background: skyblue;">';
										// Loop through categories to populate options
										foreach ($categories as $key => $category) {
											// Check if the category should be pre-selected from session
											if (isset($_SESSION['account_category'])) {
												// Compare session value with the category key (not category name)
												$selected = ($_SESSION['account_category'] == $key) ? 'selected' : '';
											}

											// Echo each option
                                        echo '<option value="' . $key . '" ' . $selected . '>' . $category . '</option>';
										}


										echo '</select>';
									}
								?>
							</div>

							<div id="date-range" class="col-md-4 hide mbot15" id="date_by_wrapper">
								<div class="row">
									<div class="col-md-6">
										<label for="report_from"
											class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
										<div class="input-group date">
											<input type="text" class="form-control datepicker" id="report_from"
												name="report_from" value="<?php echo htmlspecialchars($report_from); ?>"
												autocomplete="off">
											<div class="input-group-addon">
												<i class="fa fa-calendar calendar-icon"></i>
											</div>
										</div>
									</div>
									<div class="col-md-6 border-right">
										<label for="report_to"
											class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
										<div class="input-group date">
											<input type="text" class="form-control datepicker" id="report_to"
												name="report_to" autocomplete="off">
											<div class="input-group-addon">
												<i class="fa fa-calendar calendar-icon"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!--end date time div-->
							<div class="col-md-12">
								<div class="checklist relative">
									<div class="checkbox checkbox-success checklist-checkbox" data-toggle="tooltip"
										title="" data-original-title="<?php echo _l('si_lf_save_filter_template'); ?>">
										<input type="checkbox" id="si_lf_save_filter" name="save_filter" value="1"
											title="<?php echo _l('si_lf_save_filter_template'); ?>" <?php echo ($this->input->get('filter_id') ? 'checked' : '') ?>>
										<label for=""><span
												class="hide"><?php echo _l('si_lf_save_filter_template'); ?></span></label>
										<textarea id="si_lf_filter_name" name="filter_name" rows="1"
											placeholder="<?php echo _l('si_lf_filter_template_name'); ?>" <?php echo ($this->input->get('filter_id') ? '' : 'disabled="disabled"') ?>
											maxlength='100'><?php echo ($this->input->get('filter_id') ? $saved_filter_name : ''); ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<?php echo form_close(); ?>
					</div>
				</div>
				<div class="panel_s">
					<?php if ($this->session->flashdata('error')): ?>
						<div class="flash-message error tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4">
							<?php echo $this->session->flashdata('error'); ?></div>
					<?php endif; ?>
					<?php if ($this->session->flashdata('success')): ?>
						<div class="flash-message success tw-flex tw-justify-center tw-items-center tw-space-x-4 tw-mb-4">
							<?php echo $this->session->flashdata('success'); ?></div>
					<?php endif; ?>
					<div class="panel-body">
						<?php
						foreach ($overview as $month => $data) {
							if (count($data) == 0) {
								continue;
							} ?>
							<h4 class="bold text-success"><?php echo htmlspecialchars($month); ?>
							</h4>
							<table class="table tasks-overview dt-table scroll-responsive">
								<caption class="si_lf_caption"><?php echo htmlspecialchars($month . $report_heading); ?>
								</caption>
								<thead>
									<tr>
										<th>#</th>
										<th
											class="<?php echo (in_array('leads_owner_s_name', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo "Owner's Name"; ?></th>
										<!-- <th class="<?php echo (in_array('name', $hide_columns) ? 'not-export' : '') ?>"><?php echo _l('leads_dt_name'); ?></th>-->
										<th class="<?php echo (in_array('assigned', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('leads_dt_assigned'); ?></th>
										<th class="<?php echo (in_array('phonenumber', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('leads_dt_phonenumber'); ?></th>
										<th class="<?php echo (in_array('status', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('leads_dt_status'); ?></th>
										<th class="<?php echo (in_array('company', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('lead_company'); ?></th>
										<th class="<?php echo (in_array('email', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('leads_dt_email'); ?></th>
										<th class="<?php echo (in_array('country', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('lead_country'); ?></th>
										<?php
										$custom_fields = get_custom_fields('leads', ['show_on_table' => 1,]);
										foreach ($custom_fields as $field) {
											if ($field['slug'] == 'leads_owner_s_name')
												continue;

											echo '<th class="' . (in_array($field['slug'], $hide_columns) ? 'not-export' : '') . '">' . $field['name'] . '</th>';
										}
										?>
										<th class="<?php echo (in_array('source', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('lead_add_edit_source'); ?></th>
										<th class="<?php echo (in_array('dateadded', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('si_lf_created_date'); ?></th>
										<th class="<?php echo (in_array('lastcontact', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('si_lf_last_contacted_date'); ?></th>
										<th class="<?php echo (in_array('is_public', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('lead_public'); ?></th>
										<th class="<?php echo (in_array('tags', $hide_columns) ? 'not-export' : '') ?>">
											<?php echo _l('tags'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($data as $lead) { ?>
										<tr>
											<td><?php echo htmlspecialchars($lead['id']); ?></td>
											<td><?php echo get_custom_field_value($lead['id'], 7, 'leads', false) ?></td>
											<td data-order="<?php echo htmlspecialchars($lead['staff_name']); ?>">
												<?php
												if ($lead['assigned'] > 0) { ?>
													<a href="<?php echo admin_url('leads/index/' . $lead['id']); ?>"
														onclick="init_lead(<?php echo htmlspecialchars($lead['id']); ?>); return false;"><?php echo htmlspecialchars($lead['staff_name']); ?></a>
													<a data-toggle="tooltip"
														data-title="<?php echo htmlspecialchars($lead['staff_name']) ?>"
														href="<?php echo admin_url('profile/' . $lead['assigned']) ?>"><?php echo staff_profile_image($lead['assigned'], ['staff-profile-image-small',]) ?></a>
												<?php } ?>
												<br><a target="_blank"
													href="<?php echo admin_url('leads/index/' . $lead['id'] . "?edit=true"); ?>"
													onclick="init_lead(<?php echo htmlspecialchars($lead['id']); ?>); return true;">Edit</a>
											</td>
											<!-- <td data-order="<?php echo htmlspecialchars($lead['name']); ?>"><a href="<?php echo admin_url('leads/index/' . $lead['id']); ?>" onclick="init_lead(<?php echo htmlspecialchars($lead['id']); ?>); return false;"><?php echo htmlspecialchars($lead['name']); ?></a>
									<br><a target="_blank" href="<?php echo admin_url('leads/index/' . $lead['id'] . "?edit=true"); ?>" onclick="init_lead(<?php echo htmlspecialchars($lead['id']); ?>); return true;">Edit</a>
									</td> -->
											<td><?php echo htmlspecialchars($lead['phonenumber']); ?></td>
											<td>
												<?php
												$selectedColor = '';
												$this->db->select('name, id, color');
												$this->db->from('tblleads_status');
												$query = $this->db->get();
												foreach ($query->result_array() as $status) {
													if ($status['id'] == $lead['status']) {
														$selectedColor = $status['color'];
													}
												}
												echo form_open('', ['method' => 'post']) . '<input type="hidden" name="id" value="' . $lead['id'] . '"><select style="color:' . $selectedColor . '; border-color: ' . $selectedColor . '" class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" name="lead_status" onchange="submit()">';
												foreach ($query->result_array() as $status) {
													echo '<option value="' . $status['id'] . '"' . ($status['id'] == $lead['status'] ? ' selected="selected"' : '') . ' style="color: ' . $status['color'] . '">' . $status['name'] . '</option>';
												}
												echo '</select>' . form_close();
												?>
											</td>
											<td><?php echo htmlspecialchars($lead['company']); ?></td>
											<td><?php echo htmlspecialchars($lead['email']); ?></td>
											<td><?php echo htmlspecialchars(get_country_name($lead['country'])); ?></td>
											<?php
											foreach ($custom_fields as $field) {
												if ($field['slug'] == 'leads_owner_s_name')
													continue;

												$current_value = get_custom_field_value($lead['id'], $field['id'], 'leads', false);
												if ($field['id'] == 4) {
													$selectedColor = '#f7e86f';
													$this->db->select('options');
													$this->db->from('tblcustomfields');
													$this->db->where('id', 4);
													$query = $this->db->get();
													if ($query->num_rows() > 0) {
														$heatTemps = explode(",", $query->row()->options);
													} else {
														$heatTemps = [];
													}
													$colors = ['Cold' => '#a0d8ef', 'Warm' => '#f7e86f', 'Hot' => '#f76c6c', 'Flaming Hot' => '#ff3b3b'];
													foreach ($heatTemps as $tempHeat) {
														if ($tempHeat == $current_value) {
															$selectedColor = $colors[$tempHeat];
														}
													}
													echo '<td>' . form_open('', ['method' => 'post']) . '<input type="hidden" name="id" value="' . $lead['id'] . '"><select style="color:' . $selectedColor . '; border-color: ' . $selectedColor . '" class="tw-form-input tw-w-20 tw-px-2 tw-py-1 tw-text-sm" name="heat" onchange="submit()">';
													foreach ($heatTemps as $heat)
														echo '<option style="color: ' . $colors[$heat] . '" value="' . $heat . '"' . ($heat == $current_value || (empty($current_value) && $heat == "Warm") ? ' selected="selected"' : '') . '>' . $heat . (empty($current_value) && $heat == "Warm" ? ' (Default)' : '') . '</option>';
													echo '</select>' . form_close() . '</td>';
												} else
													echo '<td>' . (($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') && $current_value != '' ? date('d-m-Y H:i:s A', strtotime($current_value)) : $current_value) . '</td>';
											}
											?>
											<td><?php echo htmlspecialchars($lead['source_name']); ?></td>
											<td data-order="<?php echo htmlspecialchars($lead['dateadded']); ?>">
												<?php echo _d($lead['dateadded']); ?></td>
											<td data-order="<?php echo htmlspecialchars($lead['lastcontact']); ?>">
												<?php echo _d($lead['lastcontact']); ?></td>
											<td data-order="<?php echo htmlspecialchars($lead['is_public']); ?>">
												<?php echo ($lead['is_public'] ? _l('lead_is_public_yes') : _l('lead_is_public_no')); ?>
											</td>
											<td><?php echo render_tags(prep_tags_input(get_tags_in($lead['id'], 'lead'))); ?>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
							<hr />
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
</body>

</html>
<style>
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
</style>
<script src="<?php echo module_dir_url('si_lead_filters', 'assets/js/si_lead_filters_lead_report.js'); ?>"></script>
<script>
	(function ($) {
		"use strict";
		<?php if ($report_months !== '') { ?>
			$('#report_months').val("<?php echo htmlspecialchars($report_months); ?>");
			$('#report_months').change();
		<?php }
		if ($report_from !== '') {
			?>
			$('#report_from').val("<?php echo htmlspecialchars($report_from); ?>");
			<?php
		}
		if ($report_to !== '') {
			?>
			$('#report_to').val("<?php echo htmlspecialchars($report_to); ?>");
			<?php
		}
		?>
	})(jQuery);

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

</script>



<script>
    // Listen for change events on the combo box
    document.getElementById('searchCategorySelect').addEventListener('change', function () {
        // Get the selected option value
        var selectedCategory = this.options[this.selectedIndex].value; // Get the category key (value)

        // Send the selected category to the CodeIgniter controller via AJAX
        $.ajax({
            url: '<?php echo base_url("admin/leads/saveCategorySession"); ?>', // CodeIgniter controller/method URL
            type: 'POST',
            data: { searchCategory: selectedCategory },
            success: function (response) {
                console.log('Category saved in session: ' + response);

            },
            error: function () {
                console.error('Failed to save category in session.');
                // console.log('Category saved in session: ' + <?php echo base_url("admin/leads/saveCategorySession"); ?>);

            }

        });
    });
</script>
