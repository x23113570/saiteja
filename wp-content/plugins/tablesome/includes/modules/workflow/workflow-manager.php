<?php

namespace Tablesome\Includes\Modules\Workflow;

use Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Workflow_Manager')) {
    class Workflow_Manager
    {

        public static $instance = null;

        public $library;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        public function init()
        {

            $this->library = get_tablesome_workflow_library();

            // if (pauple_is_feature_active('gsheet_action')) {
            //     $this->library->actions['gsheet_add_row'] = new GSheet_Add_Row();
            //     $this->library->integrations['gsheet'] = new GSheet();
            // }

            $this->register_trigger_hooks();
            // add_action("load_editor");

            add_filter("tablesome_form_submission_data", [self::$instance, "add_attachment_to_submission_data"]);

            Event_Log::get_instance();
        }

        public function register_trigger_hooks()
        {

            foreach ($this->library->triggers as $key => $trigger) {
                $trigger->init($this->library->actions);
                $config = $trigger->get_config();

                if (!isset($config['hooks'])) {
                    continue;
                }

                foreach ($config['hooks'] as $hook) {

                    if (isset($hook['hook_type']) && $hook['hook_type'] == "filter") {
                        add_filter($hook['name'], array($trigger, $hook['callback_name']), $hook['priority'], $hook['accepted_args']);
                    } else {
                        add_action($hook['name'], array($trigger, $hook['callback_name']), $hook['priority'], $hook['accepted_args']);
                    }

                }

            }
        }

        public function get_trigger_prop_value_by_id($trigger_id, $prop_name)
        {
            $value = '';
            foreach ($this->library->triggers as $trigger) {
                $config = $trigger->get_config();
                if (isset($config['trigger_id']) && $config['trigger_id'] == $trigger_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_prop_value_by_id($action_id, $prop_name)
        {
            $value = '';
            foreach ($this->library->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_integration_label_by_id($action_id)
        {
            $label = '';
            foreach ($this->library->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $integration = $config['integration'];
                    $label = $this->library->integrations[$integration]->get_config()['integration_label'];
                    break;
                }
            }
            return $label;
        }

        public function get_external_data_by_integration($integration)
        {
            if (!isset($this->library->integrations[$integration])) {
                return [];
            }

            $class = $this->library->integrations[$integration];

            if ($integration == 'notion') {
                return $class->notion_api->get_all_databases(array('excluded_props' => 'fields,archived'));
            } else if ($integration == 'mailchimp') {
                return $class->get_all_audiences(array('can_add_fields' => false, 'can_add_tags' => false));
            } else if ($integration == 'hubspot') {
                return $class->get_static_lists();
            } else if ($integration == 'gsheet') {
                return $class->get_spreadsheets();
            }

        }

        public function get_external_data_fields_by_id($integration, $document_id)
        {
            if (!isset($this->library->integrations[$integration]) || empty($document_id)) {
                return [];
            }

            $class = $this->library->integrations[$integration];

            if ('notion' == $integration) {
                $database = $class->notion_api->get_database_by_id($document_id);
                return $class->notion_api->get_formatted_fieds($database);
            } else if ($integration == 'mailchimp') {
                return $class->get_all_fields_from_audience($document_id);
            } else if ($integration == 'hubspot') {
                return $class->get_fields();
            } else if ($integration == 'gsheet') {
                return $class->get_sheets_by_spreadsheet_id($document_id);
            } else if ($integration == 'slack' && $document_id == "channels") {
                return $class->slack_api->get_channels();
            } else if ($integration == 'slack' && $document_id == "users") {
                return $class->slack_api->get_users();
            }
        }

        public function get_posts_by_integration($integration)
        {
            $trigger_classs = isset($this->library->triggers[$integration]) ? $this->library->triggers[$integration] : null;
            if (is_null($trigger_classs)) {
                return [];
            }
            $posts = $trigger_classs->get_posts();
            return $posts;
        }

        public function get_post_fields_by_id($integration, $document_id)
        {
            // error_log('$this->library->triggers : ' . print_r($this->library->triggers, true));
            // error_log('$integration : ' . $integration);
            $trigger_classs = isset($this->library->triggers[$integration]) ? $this->library->triggers[$integration] : null;
            if (is_null($trigger_classs)) {
                return [];
            }

            $fields = $trigger_classs->get_post_fields($document_id);
            // error_log('get_post_fields_by_id $fields : ' . print_r($fields, true));
            return $fields;
        }

        public function add_attachment_to_submission_data($submission_data)
        {
            $file_types = ["upload", "file-upload", "fileupload", "post_image", 'input_image', 'input_file'];
            if (isset($submission_data) && !empty($submission_data)) {
                // error_log(' before submission_data : ' . print_r($submission_data, true));

                foreach ($submission_data as $field_key => $field) {
                    if (in_array($field["type"], $file_types) && !empty($field["value"])) {
                        $file_url = self::$instance->get_single_url_from_value($field["value"]);
                        // error_log(' file_url : ' . print_r($file_url, true));

                        $field["value"] = self::$instance->upload_file_from_url($file_url);
                        $field["type"] = "file";
                    }

                    $submission_data[$field_key] = $field;
                }

                // error_log(' after submission_data : ' . print_r($submission_data, true));
                return $submission_data;
            }

            return $submission_data;
        }

        public function get_single_url_from_value($value)
        {
            $url = "";
            $is_comma_separated = false;
            $is_linebreak_separated = false;

            if (!empty($value)) {
                $comma_separated_values = explode(",", $value);
                $linebreak_separated_values = explode("\n", $value);

                $is_comma_separated = is_array($comma_separated_values) && count($comma_separated_values) > 1;
                $is_linebreak_separated = is_array($linebreak_separated_values) && count($linebreak_separated_values) > 1;

                if ($is_comma_separated) {
                    $value = $comma_separated_values[0];
                } else if ($is_linebreak_separated) {
                    $value = $linebreak_separated_values[0];
                }

                $url = trim($value);
            }

            return $url;
        }

        public function upload_file_from_url($url, $title = null)
        {
            require_once ABSPATH . "/wp-load.php";
            require_once ABSPATH . "/wp-admin/includes/image.php";
            require_once ABSPATH . "/wp-admin/includes/file.php";
            require_once ABSPATH . "/wp-admin/includes/media.php";

            // Download url to a temp file
            $tmp = download_url($url);
            if (is_wp_error($tmp)) {
                return false;
            }

            // Get the filename and extension ("photo.png" => "photo", "png")
            $filename = pathinfo($url, PATHINFO_FILENAME);
            $extension = pathinfo($url, PATHINFO_EXTENSION);

            // An extension is required or else WordPress will reject the upload
            if (!$extension) {
                // Look up mime type, example: "/photo.png" -> "image/png"
                $mime = mime_content_type($tmp);
                $mime = is_string($mime) ? sanitize_mime_type($mime) : false;

                // Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
                $mime_extensions = array(
                    // mime_type         => extension (no period)
                    'text/plain' => 'txt',
                    'text/csv' => 'csv',
                    'application/msword' => 'doc',
                    'image/jpg' => 'jpg',
                    'image/jpeg' => 'jpeg',
                    'image/gif' => 'gif',
                    'image/png' => 'png',
                    'video/mp4' => 'mp4',
                );

                if (isset($mime_extensions[$mime])) {
                    // Use the mapped extension
                    $extension = $mime_extensions[$mime];
                } else {
                    // Could not identify extension
                    @unlink($tmp);
                    return false;
                }
            }

            // Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
            $args = array(
                'name' => "$filename.$extension",
                'tmp_name' => $tmp,
            );

            // Do the upload
            $attachment_id = media_handle_sideload($args, 0, $title);

            // Cleanup temp file
            @unlink($tmp);

            // Error uploading
            if (is_wp_error($attachment_id)) {
                return false;
            }

            // Success, return attachment ID (int)
            return (int) $attachment_id;
        }
    }

}
