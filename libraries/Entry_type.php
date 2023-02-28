<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Entry_type
{
    /**
     * @var array
     */
    private $field_names = [];

    /**
     * @param int $channelId
     */
    public function init(int $channelId)
    {
        if (ee()->session->cache('entry_type', 'display_field')) {
            return;
        }

        ee()->session->set_cache('entry_type', 'display_field', true);

        $channels = ee('Model')->get('Channel')
            ->with('CustomFields')
            ->filter('channel_id', $channelId)
            ->all();

        /** @var Channel $channel */
        foreach ($channels as $channel) {
            $customFields = $channel->getAllCustomFields();

            foreach ($customFields as $customField) {
                $this->field_names[$customField->field_id] = REQ === 'CP' ? 'field_id_'.$customField->field_id : $customField->field_name;
            }
        }

        if (empty($this->field_names)) {
            return;
        }

        //fetch field widths/visibility from publish layout
        //$layout_group = is_numeric(ee()->input->get_post('layout_preview')) ? ee()->input->get_post('layout_preview') : ee()->session->userdata('group_id');

        ee()->load->model('layout_model');

        $layout_info = ee()->layout_model->get_layout_settings(array(
            'site_id' => ee()->config->item('site_id'),
            'channel_id' => $channelId,
            //'member_group' => $layout_group,
        ));

        $invisible = [];

        if (!empty($layout_info)) {
            foreach ($layout_info as $tab_fields) {
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

    /**
     * @param string $field_name
     * @param array $type_options
     * @return mixed
     */
    public function add_field($field_name, array $type_options = [])
    {
        $fields = [];

        foreach ($type_options as $value => $row) {
            foreach (explode('|', $value) as $val) {
                $fields[$val] = (isset($row['hide_fields'])) ? $row['hide_fields'] : [];
            }
        }

        if (is_numeric($field_name)) {
            $field_name = isset($this->field_names[$field_name]) ? $this->field_names[$field_name] : 'field_id_'.$field_name;
        }

        if (method_exists($this, 'add_'.$field_name.'_field')) {
            return $this->{'add_'.$field_name.'_field'}($fields);
        }

        // @todo - what is this used for?
        $callback = '';

        if (method_exists($this, 'callback_'.$field_name)) {
            $callback = ', '.$this->{'callback_'.$field_name}();
        }

        ee()->javascript->output('EntryType.addField('.json_encode($field_name).', '.json_encode($fields).');');
    }

    /**
     * @param int  $group_id
     * @param bool $exclude_field_id
     * @return array
     */
    public function fields($group_id, $exclude_field_id = false)
    {
        $all_fields = $this->all_fields();

        if (!isset($all_fields[$group_id])) {
            return [];
        }

        $fields = [];

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
            /** @var CI_DB_result $query */
            $query = ee()->db->select('field_id, field_name, field_label, field_groups.group_id, group_name')
                ->where('channel_fields.site_id', ee()->config->item('site_id'))
                ->join('field_groups', 'field_groups.group_id = channel_fields.group_id')
                ->order_by('field_groups.group_id, field_order')
                ->get('channel_fields');

            $cache = [];

            foreach ($query->result_array() as $row) {
                if (!isset($cache[$row['group_id']])) {
                    $cache[$row['group_id']] = [];
                }

                $cache[$row['group_id']][$row['field_id']] = $row;
            }

            $query->free_result();
        }

        return $cache;
    }

    public function field_settings($field_group = false, $settings = [], $field_id = false, $key = false)
    {
        ee()->lang->loadfile('entry_type', 'entry_type');
        ee()->load->helper(['array', 'html']);
        ee()->cp->add_js_script(['ui' => ['sortable']]);

        if ($field_group) {
            $vars['fields'] = $this->fields($field_group, $field_id);
        } else {
            $vars['fields'] = [];

            foreach ($this->all_fields() as $fields) {
                foreach ($fields as $field_id => $field) {
                    $vars['fields'][$field_id] = $field['field_label'];
                }
            }
        }

        if (empty($settings['type_options'])) {
            $vars['type_options'] = [
                '' => [
                    'hide_fields' => [],
                    'label' => '',
                ],
            ];
        } else {
            foreach ($settings['type_options'] as $value => $option) {
                if (!isset($option['hide_fields'])) {
                    $settings['type_options'][$value]['hide_fields'] = [];
                }

                if (!isset($option['label'])) {
                    $settings['type_options'][$value]['label'] = $value;
                }
            }

            $vars['type_options'] = $settings['type_options'];
        }

        $row_view = $key ? 'option_row_ext' : 'option_row';

        $options_view = $key ? 'options_ext' : 'options';

        $row_template = preg_replace('/[\r\n\t]/', '', ee()->load->view($row_view, array('key' => $key, 'i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => [], 'fields' => $vars['fields']), true));

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
