<?php

namespace Tablesome\Workflow_Library\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Workflow_Library\Actions\GSheet_Load_From')) {
    class GSheet_Load_From extends Action
    {
        public $gsheet_api;
        public $gsheet_integration_handler;
        public $tablesomedb__rest_api;
        public $datatable;

        public function __construct()
        {
            // error_log('gsheet load from - construct');
            $this->gsheet_api = new \Tablesome\Workflow_Library\External_Apis\GSheet();
            $this->gsheet_integration_handler = new \Tablesome\Workflow_Library\Integrations\GSheet();
            $this->tablesomedb__rest_api = new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\TablesomeDB_Rest_Api();
            $this->datatable = new \Tablesome\Includes\Modules\Datatable\Datatable();
            add_action('tablesome_before_table_load', [$this, 'do_action'], 10, 1);

        }

        // TODO: get config from json instead of declaring in class file.
        public function get_config()
        {
            return array(
                'id' => 17,
                'name' => 'gsheet_load_from',
                'label' => __('Load from GSheet (beta)', 'tablesome'),
                'integration' => 'gsheet',
                'is_premium' => true,
            );
        }

        public function do_action($args = [])
        {

            // return;
            // $trigger_class = isset($args['trigger_class']) ? $args['trigger_class'] : '';
            // $trigger_instance = isset($args['trigger_instance']) ? $args['trigger_instance'] : '';

            $table_id = isset($args['post_id']) ? $args['post_id'] : 0;

            $should_update = $this->should_update($table_id);

            // if (!$should_update) {
            //     return;
            // }

            $action_meta = $this->get_action_meta($args);

            $get_rows_params = [];
            $get_rows_params['spreadsheet_id'] = isset($action_meta['spreadsheet_id']) ? $action_meta['spreadsheet_id'] : '';
            $get_rows_params['sheet_id'] = isset($action_meta['sheet_id']) ? $action_meta['sheet_id'] : '';
            $get_rows_params['sheet_name'] = $this->get_sheet_name($get_rows_params['spreadsheet_id'], $get_rows_params['sheet_id']);
            $get_rows_params['coordinates'] = isset($action_meta['coordinates']) ? $action_meta['coordinates'] : 'A1:Z1000';
            $get_rows_params['range'] = $get_rows_params['sheet_name'];

            // error_log('action_meta: ' . print_r($action_meta, true));

            // error_log('gsheet load from');
            // error_log('args: ' . print_r($args, true));
            // error_log('trigger_instance: ' . print_r($trigger_instance, true));

            // return;
            // Check last table update time and method (method = gsheet_api)

            // If last update time is less than 15 minutes ago, do nothing

            // If last update time is more than 15 minutes ago, update table
            $gsheet_data = $this->gsheet_api->get_rows($get_rows_params);
            $incoming_columns = $gsheet_data['values'][0];
            $columns = $this->transform_columns($incoming_columns);

            // error_log('columns: ' . print_r($columns, true));
            $column_count = count($columns);
            $incoming_rows = $gsheet_data['values'];
            unset($incoming_rows[0]);
            $rows = $this->transform_rows($incoming_rows, $column_count);
            // $rows = $gsheet_data['values'];

            // $columns = $this->filter_mapped_columns($columns, $action_meta);

            $params = [
                'columns' => $columns,
                'recordsData' => [
                    'records_inserted' => $rows,
                ],
                'mode' => 'editor',
                'table_id' => $args['post_id'],

            ];

            // error_log('load_from_gsheet params:' . print_r($params, true));

            $this->datatable->reset_entire_table_data($params);
            // return;

            //  $this->tablesomedb__rest_api->save_table($params);

            // Update last update time and method (method = gsheet_api)
            update_post_meta($table_id, 'last_update_time', time());
            update_post_meta($table_id, 'last_update_method', 'gsheet_api');

        }

        public function get_sheet_name($spreadsheet_id, $sheet_id)
        {
            $sheet_name = '';
            $spreadsheet = $this->gsheet_api->get_sheets_by_spreadsheet_id($spreadsheet_id);

            $sheets = isset($spreadsheet['sheets']) ? $spreadsheet['sheets'] : [];

            // error_log('sheets: ' . print_r($sheets, true));

            if (empty($sheets)) {
                return '';
            }

            foreach ($sheets as $sheet) {
                $sheet_properties = isset($sheet['properties']) ? $sheet['properties'] : [];

                if ($sheet_properties['sheetId'] == $sheet_id) {
                    $sheet_name = $sheet_properties['title'];
                    break;
                }
            }
            return $sheet_name;
        }

        public function get_action_meta($collection)
        {
            $chosen_trigger_id = 5;
            $chosen_action_id = 17;

            $other_cpt_model = new \Tablesome\Components\Table\Other_CPT_Model();
            $action_meta = $other_cpt_model->get_action_meta_abstract($collection, $chosen_trigger_id, $chosen_action_id);

            return $action_meta;
        }

        public function filter_mapped_columns($columns, $action_meta)
        {
            $filtered_columns = [];
            $map_fields = isset($action_meta['map_fields']) ? $action_meta['map_fields'] : [];

            // error_log('filter_mapped_columns');
            // error_log('map_fields: ' . print_r($map_fields, true));
            // error_log('columns: ' . print_r($columns, true));
            $table_id = isset($args['post_id']) ? $args['post_id'] : 0;

            foreach ($map_fields as $key => $single_map) {
                $source_field = isset($single_map['source_field']) ? $single_map['source_field'] : '';
                $destination_field = isset($single_map['destination_field']) ? $single_map['destination_field'] : '';

                $source_column_id = isset($source_field['id']) ? $source_field['id'] : '';

                error_log('source_column_id: ' . print_r($source_column_id, true));

                // $result = array_filter($columns, function ($column) use ($source_column_id) {
                //     return $column['id'] == $source_column_id;
                // });

                foreach ($columns as $key => $column) {
                    if (isset($column['id']) && $column['id'] == $source_column_id) {
                        $single_column = $column; // Return the subarray if value is found
                    }
                }

                // $single_column = $this->findSubarrayByValue($columns, 'id', $source_column_id);

                // $single_column = $result[0];

                // error_log('singl resulte_column: ' . print_r($result, true));
                error_log('single_column: ' . print_r($single_column, true));
                // $single_column['label'] = isset($destination_field['value']) ? $destination_field['value'] : $single_column['name'];

                array_push($filtered_columns, $single_column);

            }

            error_log('filtered_columns: ' . print_r($filtered_columns, true));

            return $filtered_columns;

        }

        public function findSubarrayByValue($mainArray, $subkey, $searchValue)
        {
            foreach ($mainArray as $key => $subArray) {
                if (isset($subArray[$subkey]) && $subArray[$subkey] === $searchValue) {
                    return $subArray; // Return the subarray if value is found
                }
            }
            return null; // Return null if value is not found in any subarray
        }

        public function should_update($table_id)
        {
            $should_update = false;
            $last_update_time = get_post_meta($table_id, 'last_update_time', true);
            $last_update_method = get_post_meta($table_id, 'last_update_method', true);

            error_log('last_update_time: ' . print_r($last_update_time, true));
            error_log('last_update_method: ' . print_r($last_update_method, true));

            // return true;

            $current_time = time();
            $time_diff = $current_time - (int) $last_update_time;
            $time_diff_in_minutes = $time_diff / 60;

            error_log('time_diff_in_minutes: ' . $time_diff_in_minutes);

            if ($time_diff_in_minutes > 15 || $last_update_method != 'gsheet_api') {
                $should_update = true;
            }

            error_log('should_update: ' . $should_update);
            return $should_update;

        }

        public function transform_rows($incoming_rows, $column_count)
        {
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $datatable = new \Tablesome\Includes\Modules\Datatable\Datatable();
            $rows = [];

            foreach ($incoming_rows as $key => $incoming_row) {
                $row = [
                    'record_id' => $key,
                    'content' => [],
                    'stateRecordID' => $key,
                ];

                for ($ii = 0; $ii < $column_count; $ii++) {

                    $cell = [
                        'value' => isset($incoming_row[$ii]) ? $incoming_row[$ii] : '',
                        'type' => 'text',
                        'html' => '',
                    ];

                    $row['content'][] = $cell;
                } // end foreach single row

                $row = $datatable->record->get_additional_data($row);
                $rows[] = $row;

            } // end foreach $incoming_rows

            return $rows;
        }

        public function transform_columns($incoming_columns)
        {
            $columns = [];
            foreach ($incoming_columns as $key => $incoming_column_name) {
                // $id = (int) $key + 1; // IDs should start from 1, google columns start with 1
                $column = [
                    'id' => $key,
                    'name' => isset($incoming_column_name) ? $incoming_column_name : '',
                    'format' => 'text',
                ];

                $columns[] = $column;
            }

            return $columns;

        }

    } // END CLASS

}
