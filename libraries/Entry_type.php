<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Entry_type
{
    private $field_names = array();

    public function init($channel_id)
    {
        if (ee()->session->cache("entry_type", "display_field")) {
            return;
        }

        if (!$channel_id) {
            return;
        }

        ee()->session->set_cache("entry_type", "display_field", true);

        $channel = ee("Model")
            ->get("Channel")
            ->filter("channel_id", $channel_id)
            ->first();

        $fields = $channel->getAllCustomFields();

        foreach ($fields as $row) {
            $this->field_names[$row->field_id] = REQ === 'CP' ? 'field_id_'.$row->field_id : $row->field_name;
        }

        $query->free_result();

        if (empty($this->field_names)) {
            return;
        }

        //fetch field widths/visibility from publish layout
        $layout_group = is_numeric(ee()->input->get_post('layout_preview')) ? ee()->input->get_post('layout_preview') : ee()->session->userdata('group_id');

        ee()->load->model('layout_model');

        $layout_info = ee()->layout_model->get_layout_settings(array(
            'site_id' => ee()->config->item('site_id'),
            'channel_id' => $channel_id,
            //'member_group' => $layout_group,
        ));

        $invisible = array();

        if (!empty($layout_info)) {
            foreach ($layout_info as $tab => $tab_fields) {
                foreach ($tab_fields as $field_name => $field_options) {
                    if (strncmp($field_name, 'field_id_', 9) === 0) {
                        $field_id = substr($field_name, 9);

                        if (isset($field_options['visible']) && !$field_options['visible']) {
                            $invisible[] = $field_id;
                        }
                    }
                }
            }
        }

        if (REQ === 'CP') {
            ee()->cp->load_package_js('EntryType');
        } else {
            //for channel form
            ee()->cp->add_to_head('<script type="text/javascript">'.file_get_contents(PATH_THIRD.'entry_type/javascript/EntryType.js').'</script>');
        }

        ee()->cp->add_to_head('<style type="text/css">.entry-type-hidden { position: absolute !important; left: -9999px !important; }</style>');

        ee()->load->library('javascript');

        ee()->javascript->output('EntryType.setInvisible('.json_encode($invisible).');');
    }

    public function add_field($field_name, $type_options)
    {
        $fields = array();

        foreach ($type_options as $value => $row) {
            foreach (explode('|', $value) as $val) {
                $fields[$val] = (isset($row['hide_fields'])) ? $row['hide_fields'] : array();
            }
        }

        if (is_numeric($field_name)) {
            $field_name = isset($this->field_names[$field_name]) ? $this->field_names[$field_name] : 'field_id_'.$field_name;
        }

        if (method_exists($this, 'add_'.$field_name.'_field')) {
            return $this->{'add_'.$field_name.'_field'}($fields);
        }

        $callback = '';

        if (method_exists($this, 'callback_'.$field_name)) {
            $callback = ', '.$this->{'callback_'.$field_name}();
        }

        ee()->javascript->output('EntryType.addField('.json_encode($field_name).', '.json_encode($fields).');');
    }

    public function fields($group_id, $exclude_field_id = false)
    {
        $all_fields = $this->all_fields();

        if (!isset($all_fields[$group_id])) {
            return array();
        }

        $fields = array();

        foreach ($all_fields[$group_id] as $field_id => $field) {
            $fields[$field_id] = $field['field_label'];
        }

        if ($exclude_field_id) {
            foreach ($fields as $field_id => $field_label) {
                if ($exclude_field_id == $field_id) {
                    unset($fields[$field_id]);

                    break;
                }
            }
        }

        return $fields;
    }

    public function all_fields()
    {
        static $cache;

        if (is_null($cache)) {
            $query = ee()->db->select('field_id, field_name, field_label, field_groups.group_id, group_name')
                ->where('channel_fields.site_id', ee()->config->item('site_id'))
                ->join('field_groups', 'field_groups.group_id = channel_fields.group_id')
                ->order_by('field_groups.group_id, field_order')
                ->get('channel_fields');

            $cache = array();

            foreach ($query->result_array() as $row) {
                if (!isset($cache[$row['group_id']])) {
                    $cache[$row['group_id']] = array();
                }

                $cache[$row['group_id']][$row['field_id']] = $row;
            }

            $query->free_result();
        }

        return $cache;
    }

    public function field_settings($field_group = false, $settings = array(), $field_id = false, $key = false)
    {
        ee()->lang->loadfile('entry_type', 'entry_type');

        ee()->load->helper(array('array', 'html'));

        ee()->cp->add_js_script(array('ui' => array('sortable')));

        if ($field_group) {
            $vars['fields'] = $this->fields($field_group, $field_id);
        } else {
            $vars['fields'] = array();

            foreach ($this->all_fields() as $group_id => $fields) {
                foreach ($fields as $field_id => $field) {
                    $vars['fields'][$field_id] = $field['field_label'];
                }
            }
        }

        if (empty($settings['type_options'])) {
            $vars['type_options'] = array(
                '' => array(
                    'hide_fields' => array(),
                    'label' => '',
                ),
            );
        } else {
            foreach ($settings['type_options'] as $value => $option) {
                if (!isset($option['hide_fields'])) {
                    $settings['type_options'][$value]['hide_fields'] = array();
                }

                if (!isset($option['label'])) {
                    $settings['type_options'][$value]['label'] = $value;
                }
            }

            $vars['type_options'] = $settings['type_options'];
        }

        $row_view = $key ? 'option_row_ext' : 'option_row';

        $options_view = $key ? 'options_ext' : 'options';

        $row_template = preg_replace('/[\r\n\t]/', '', ee()->load->view($row_view, array('key' => $key, 'i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => array(), 'fields' => $vars['fields']), true));

        ee()->cp->load_package_js('EntryTypeFieldSettings');

        ee()->load->library('javascript');

        ee()->javascript->output('
        (function() {
            var fieldSettings = new EntryTypeFieldSettings("#ft_entry_type", '.json_encode($row_template).');
            fieldSettings.deleteConfirmMsg = '.json_encode(lang('confirm_delete_type')).';
        })();
        ');

        return ee()->load->view($options_view, $vars, true);
    }
}
