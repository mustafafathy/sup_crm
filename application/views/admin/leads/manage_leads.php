<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$CI =& get_instance();
$CI->load->database();
$CI->load->library('session');

?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <a href="#" onclick="init_lead(undefined, undefined, <?= $this->session->userdata('category') ?>); return false;"
                        class="btn btn-primary mright5 pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_lead'); ?>
                    </a>
                    <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>"
                            class="btn btn-primary pull-left display-block hidden-xs">
                            <i class="fa-solid fa-upload tw-mr-1"></i>
                            <?php echo _l('import_leads'); ?>
                        </a>
                    <?php } ?>
                    <div class="row">
                        <div class="col-sm-5 ">
                            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                                data-title="<?php echo _l('leads_summary'); ?>" data-placement="top"
                                onclick="slideToggle('.leads-overview'); return false;"><i
                                    class="fa fa-bar-chart"></i></a>
                            <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>"
                                class="btn btn-default mleft5 hidden-xs" data-toggle="tooltip" data-placement="top"
                                data-title="<?php echo $switch_kanban == 1 ? _l('leads_switch_to_kanban') : _l('switch_to_list_view'); ?>">
                                <?php if ($switch_kanban == 1) { ?>
                                    <i class="fa-solid fa-grip-vertical"></i>
                                <?php } else { ?>
                                    <i class="fa-solid fa-table-list"></i>
                                <?php }
                                ?>
                            </a>

                            <!-- // Hide Lead in Employee Role -->
                            <?php

                            function hidePanelTableFull() // for hide the table and page data with role condition
                            {
                                $role = getCurrentUserRole();

                                if (($role == 1) || ($role == 7)) {
                                    echo '<style>.panel-table-full { display: none; }</style>';
                                    echo '<style>#kan-ban-tab { display: none; }</style>';

                                }
                            }
                            // Call the function where you need it, e.g., in your header or main layout file
                            hidePanelTableFull();
                            ?>
                            <!-- // End of Hide Lead in Employee Role -->

                        </div>

                        <div class="col-sm-6 col-xs-12 pull-right leads-search">
                            <?php
                            // if (count($categories) > 1) {  // Check if there are more than 1 category
                            //     // Start rendering the select element manually
                            //     // echo '<label for="searchCategorySelect">PipeLine</label>';
                            //     echo '<select onchange="leads_kanban();" name="searchCategory" id="searchCategorySelect" class="form-control" style="right: 0; width: 100%; float: right;">';
                            //     // Loop through categories to populate options
                            //     foreach ($categories as $key => $category) {
                            //         // Check if the category should be pre-selected from session
                            //         if (isset($_SESSION['account_category'])) {
                            //             // Compare session value with the category key (not category name)
                            //             $selected = ($_SESSION['account_category'] == $key) ? 'selected' : '';
                            //         } else {
                            //             // Default value if session is not set
                            //             $selected = ($category == 'solar') ? 'selected' : '';
                            //         }

                            //         // Echo each option
                            //         echo '<option value="' . $key . '" ' . $selected . '>' . $category . '</option>';
                            //     }


                            //     echo '</select>';
                            // }
                            if ($this->session->userdata('leads_kanban_view') == 'true') { // Kanban search input
                            
                                ?>
                                <div data-toggle="tooltip" data-placement="top"
                                    data-title="<?php echo _l('search_by_tags'); ?>">
                                    <?php
                                    echo render_input(
                                        'search',
                                        '', // Value of the search input
                                        '', // Label for the input
                                        'search', // Input type
                                        ['data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')], // Attributes
                                        [], // Additional attributes
                                        'no-margin' // Additional CSS classes
                                    );
                                    ?>
                                </div>
                                <?php
                            } else { // Handle the case when Kanban view is not enabled
                                ?>
                                <div id="vueApp" class="tw-inline pull-right">
                                    <app-filters id="<?php echo $table->id(); ?>" view="<?php echo $table->viewName(); ?>"
                                        :rules="<?php echo app\services\utilities\Js::from($this->input->get('status') ? $table->findRule('status')->setValue([$this->input->get('status')]) : []); ?>"
                                        :saved-filters="<?php echo $table->filtersJs(); ?>"
                                        :available-rules="<?php echo $table->rulesJs(); ?>">
                                    </app-filters>
                                </div>
                                <?php
                            }
                            ?>

                            <?php echo form_hidden('sort_type'); ?>
                            <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="hide leads-overview tw-mt-2 sm:tw-mt-4 tw-mb-4 sm:tw-mb-0">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <?php echo _l('leads_summary'); ?>
                        </h4>
                        <div class="tw-flex tw-flex-wrap tw-flex-col lg:tw-flex-row tw-w-full tw-gap-3 lg:tw-gap-6">
                            <?php
                            foreach ($summary as $status) { ?>
                                <div
                                    class="lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-items-center last:tw-border-r-0">
                                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                                        <?php
                                        if (isset($status['percent'])) {
                                            echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                        } else {
                                            // Is regular status
                                            echo $status['total'];
                                        }
                                        ?>
                                    </span>
                                    <span style="color:<?php echo e($status['color']); ?>"
                                        class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>">
                                        <?php echo e($status['name']); ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <div class="<?php echo $isKanBan ? '' : 'panel_s'; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body'; ?>">
                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="kanban-leads-sort">
                                        <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                                        <a href="#" onclick="leads_kanban_sort('dateadded'); return false"
                                            class="dateadded">
                                            <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>     <?php echo _l('leads_sort_by_datecreated'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('leadorder');return false;"
                                            class="leadorder">
                                            <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>     <?php echo _l('leads_sort_by_kanban_order'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('lastcontact');return false;"
                                            class="lastcontact">
                                            <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>     <?php echo _l('leads_sort_by_lastcontact'); ?>
                                        </a>
                                    </div>
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { // todo add a table refresh
//                                 if (count($categories) > 1) {
//                                     // Check if there are more than 1 category
//                                     // Start rendering the select element manually
//                                     // echo '<label for="searchCategorySelect">PipeLine</label>';
//                                     echo '<select name="searchCategory" id="searchCategorySelect" class="form-control" style="right: 0; width: 40%; float: right;">';
//                                     // Loop through categories to populate options
//                                     foreach ($categories as $key => $category) {
//                                         // Check if the category should be pre-selected from session
//                                         if (isset($_SESSION['account_category'])) {
//                                             // Compare session value with the category key (not category name)
//                                             $selected = ($_SESSION['account_category'] == $key) ? 'selected' : '';
//                                         } else {
//                                             // Default value if session is not set
//                                             $selected = ($category == 'solar') ? 'selected' : '';
//                                         }
                            
                                //                                         // Echo each option
//                                         echo '<option value="' . $key . '" ' . $selected . '>' . $category . '</option>';
//                                     }
                            

                                //                                     echo '</select>';
//                                 }
                                ?>
                                <div class="row" id="leads-table">
                                    <div class="col-md-12">
                                        <a href="#" data-toggle="modal" data-table=".table-leads"
                                            data-target="#leads_bulk_actions"
                                            class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                                        <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1"
                                            role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (staff_can('delete', 'leads')) { ?>
                                                            <div class="checkbox checkbox-danger">
                                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                                <label
                                                                    for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                                            </div>
                                                            <hr class="mass_delete_separator" />
                                                        <?php } ?>
                                                        <div id="bulk_change">
                                                            <div class="form-group">
                                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                                    <input type="checkbox" name="leads_bulk_mark_lost"
                                                                        id="leads_bulk_mark_lost" value="1">
                                                                    <label for="leads_bulk_mark_lost">
                                                                        <?php echo _l('lead_mark_as_lost'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <?php echo render_select('move_to_status_leads_bulk', $statuses, ['id', 'name'], 'ticket_single_change_status'); ?>
                                                            <?php
                                                            echo render_select('move_to_source_leads_bulk', $sources, ['id', 'name'], 'lead_source');
                                                            echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                                            echo render_select('assign_to_leads_bulk', $staff, ['staffid', ['firstname', 'lastname']], 'leads_dt_assigned');
                                                            ?>
                                                            <div class="form-group">
                                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                                <input type="text" class="tagsinput" id="tags_bulk"
                                                                    name="tags_bulk" value="" data-role="tagsinput">
                                                            </div>
                                                            <hr />
                                                            <div class="form-group no-mbot">
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                        id="leads_bulk_public" value="public">
                                                                    <label for="leads_bulk_public">
                                                                        <?php echo _l('lead_public'); ?>
                                                                    </label>
                                                                </div>
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                        id="leads_bulk_private" value="private">
                                                                    <label for="leads_bulk_private">
                                                                        <?php echo _l('private'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                            data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                        <a href="#" class="btn btn-primary"
                                                            onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->
                                        <?php
                                        $table_data = [];
                                        $_table_data = [
                                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                            [
                                                'name' => _l('the_number_sign'),
                                                'th_attrs' => ['class' => 'toggleable number', 'id' => 'th-number'],
                                            ]
                                        ];
                                        /*$_table_data = 
                                         [
                                           'name'     => _l('leads_dt_name'),
                                           'th_attrs' => ['class' => 'toggleable', 'id' => 'th-name'],
                                         ],
                                        */
                                        $custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);
                                        foreach ($custom_fields as $field) {
                                            if ($field['id'] == 7) {
                                                $_table_data[] = [
                                                    'name' => $field['name'],
                                                    'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                                ];
                                                break;
                                            }
                                        }
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_assigned'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-assigned'],
                                        ];
                                        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                            $_table_data[] = [
                                                'name' => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                                'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                                            ];
                                        }
                                        /*
                                        $_table_data[] = [
                                         'name'     => _l('lead_company'),
                                         'th_attrs' => ['class' => 'toggleable', 'id' => 'th-company'],
                                        ];
                                        $_table_data[] = [
                                         'name'     => _l('leads_dt_email'),
                                         'th_attrs' => ['class' => 'toggleable', 'id' => 'th-email'],
                                        ];
                                        */
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_phonenumber'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-phone'],
                                        ];
                                        /*
                                        $_table_data[] = [
                                           'name'     => _l('leads_dt_lead_value'),
                                           'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-value'],
                                          ];
                                        */
                                        $_table_data[] = [
                                            'name' => _l('tags'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-tags'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_status'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-status'],
                                        ];
                                        // $_table_data[] = [
                                        //     'name' => _l('leads_category'),
                                        //     'th_attrs' => ['class' => 'toggleable', 'id' => 'th-category'],
                                        // ];
                                        $_table_data[] = [
                                            'name' => _l('leads_source'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-source'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_last_contact'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last-contact'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_datecreated'),
                                            'th_attrs' => ['class' => 'date-created toggleable', 'id' => 'th-date-created'],
                                        ];
                                        foreach ($_table_data as $_t) {
                                            array_push($table_data, $_t);
                                        }
                                        foreach ($custom_fields as $field) {
                                            if ($field['id'] == 7)
                                                continue;

                                            array_push($table_data, [
                                                'name' => $field['name'],
                                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                            ]);
                                        }
                                        $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                                        ?>
                                        <div class="panel-table-full">
                                            <?php
                                            $category = $this->input->get('searchCategory');

                                            if (empty($category)) {
                                                $category = $this->session->userdata('account_category');
                                            }

                                            // Check if category exists
                                            if (!empty($category)) {
                                                // If category is set, add it to the WHERE clause with SQL escaping
                                                $where[] = 'AND (category = ' . $this->db->escape($category) . ')';
                                            } else {
                                                // Handle the case where the category is not set (if needed)
                                                $category = null; // Default value, or handle the logic for no category
                                            }
                                            render_datatable(
                                                $table_data,
                                                'leads',
                                                ['customizable-table number-index-2'],
                                                [
                                                    'id' => 'leads',
                                                    'data-last-order-identifier' => 'leads',
                                                    'data-default-order' => get_table_last_order('leads'),
                                                ]
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script id="hidden-columns-table-leads" type="text/json">
<?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>

<script type="text/javascript">
    function updateDynamicTitle(category) {
        // Update the text or title on the page dynamically
        var dynamicTitle = document.getElementsByClassName('modal-title');
        dynamicTitle.innerHTML = "Current Category: " + category;
    }
</script>
<script>
    function updateCategoryDisplay() {
        // Get the selected value from the select element
        var selectedCategoryId = document.getElementById('searchCategorySelect').value;

        // Define a JavaScript object with category data
        var categories = {
            '1': 'Property',
            '2': 'Solar',
            // '3': 'Technology',
            // '4': 'Finance'
        };

        // Get the category name (string) based on the selected value
        var selectedCategoryName = categories[selectedCategoryId].innerHTML;

        // Update the displayed category string dynamically
        document.getElementById('categoryDisplay').innerHTML = '' + selectedCategoryName;
    }

    // Call the function on page load to display the default selected category
    document.addEventListener('DOMContentLoaded', function () {
        updateCategoryDisplay(); // Ensure the correct category is displayed on load
    });
</script>


<script>
    var openLeadID = '<?php echo e($leadid); ?>';
    // var selectedCategory = document.getElementById('searchCategorySelect').value;
    // updateDynamicTitle(selectedCategory);

    $(function () {
        leads_kanban();
        $('#leads_bulk_mark_lost').on('change', function () {
            $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
            $('#move_to_status_leads_bulk').selectpicker('refresh')
        });
        $('#move_to_status_leads_bulk').on('change', function () {
            if ($(this).selectpicker('val') != '') {
                $('#leads_bulk_mark_lost').prop('disabled', true);
                $('#leads_bulk_mark_lost').prop('checked', false);
            } else {
                $('#leads_bulk_mark_lost').prop('disabled', false);
            }
        });
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
<script>
    // If category is set from session data (or fallback to 1), assign it to the hidden field
    document.addEventListener('DOMContentLoaded', function () {
        var category = "<?php echo $category; ?>"; // PHP passed category value

        if (category) {
            // Set the hidden input value to the session category
            document.getElementById('hiddenCategoryInput').value = category;
        }
    });

    // Update the hidden input when the category changes dynamically
    document.getElementById('searchCategorySelect').addEventListener('change', function () {
        // Get the selected value and update the hidden input field
        var selectedCategory = this.value;
        document.getElementById('hiddenCategoryInput').value = selectedCategory;
    });
</script>
<script>
$(document).ready(function() {
    // Initialize the DataTable
    var table = $('#leads').DataTable();

    // Add event listener for sorting
    table.on('order', function () {
        var order = table.order();  // Get the current ordering

        // The order array contains the column index and sorting direction
        var columnIndex = order[0][0];  // Index of the column being sorted
        var sortDirection = order[0][1];  // Sorting direction ('asc' or 'desc')

        // Show the sorting direction (ascending or descending) on the element with id th-number
        $('#th-number').html(
            '#'
        );
    });

    // Set initial ordering (for example, to the first column and descending)
    table.order([1, 'desc']).draw();
});

// $(document).ready(function() {
//     var table = $('#leads').DataTable({
//         "order": [[ $('#th-number').index(), "desc" ]] // Ensure the column index is determined from the table
//     });
// });
</script>


</body>

</html>