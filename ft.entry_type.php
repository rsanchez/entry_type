<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Entry_type_ft extends EE_Fieldtype
{
    /**
     * @var array
     */
    public $info = [
        'name' => 'Entry Type',
        'version' => '4.0.0',
    ];

    /**
     * @var bool
     */
    public $has_array_data = true;

    /**
     * @var array
     */
    protected $fieldtypes = [
        'select' => [
            'field_text_direction' => 'ltr',
            'field_pre_populate' => 'n',
            'field_pre_field_id' => false,
            'field_pre_channel_id' => false,
            'field_list_items' => false,
        ],
        'radio' => [
            'field_text_direction' => 'ltr',
            'field_pre_populate' => 'n',
            'field_pre_field_id' => false,
            'field_pre_channel_id' => false,
            'field_list_items' => false,
        ],
        'fieldpack_pill' => [],
    ];

    /**
     * @param mixed $data
     * @param array $params
     * @param bool  $tagdata
     * @return string
     */
    public function replace_tag($data, $params = [], $tagdata = false)
    {
        if ($tagdata && isset($params['all_options']) && $params['all_options'] === 'yes') {
            return $this->replace_all_options($data, $params, $tagdata);
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param array $params
     * @param bool  $tagdata
     * @return string
     */
    public function replace_label($data, $params = [], $tagdata = false)
    {
        foreach ($this->settings['type_options'] as $value => $option) {
            if ($data == $value) {
                return (!empty($option['label'])) ? $option['label'] : $value;
            }
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param array $params
     * @param bool  $tagdata
     * @return string
     */
    public function replace_selected($data, $params = [], $tagdata = false)
    {
        if (!isset($params['option'])) {
            return 0;
        }

        return (int) ($data == $params['option']);
    }

    /**
     * @param mixed $data
     * @param array $params
     * @param bool  $tagdata
     * @return string
     */
    public function replace_all_options($data, $params = [], $tagdata = false)
    {
        $vars = [];

        foreach ($this->settings['type_options'] as $value => $option) {
            $label = (!empty($option['label'])) ? $option['label'] : $value;

            $vars[] = [
                'value' => $value,
                'option' => $value,
                'option_value' => $value,
                'option_name' => $label,
                'option_label' => $label,
                'label' => $label,
                'selected' => (int) ($data == $value),
            ];
        }

        if (!$vars) {
            $vars[] = [];
        }

        return ee()->TMPL->parse_variables($tagdata, $vars);
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function display_field($data)
    {
        $options = [];

        foreach ($this->settings['type_options'] as $value => $row) {
            $options[$value] = (!empty($row['label'])) ? $row['label'] : $value;
        }

        ee()->load->library('entry_type');

        $channel_id = ee()->input->get_post('channel_id');

        if (REQ === 'CP' && ee()->uri->segment(3) === 'create') {
            $channel_id = ee()->uri->segment(4);
        }

        if (!$channel_id) {
            $entry_id = ee()->input->get_post('entry_id');

            if (REQ === 'CP' && ee()->uri->segment(4) === 'entry') {
                $entry_id = ee()->uri->segment(5);
            }

            if ($entry_id) {
                /** @var CI_DB_result $query */
                $query = ee()->db->select('channel_id')
                    ->where('entry_id', $entry_id)
                    ->get('channel_titles');

                $channel_id = $query->row('channel_id');

                $query->free_result();
            } else {
                /** @var CI_DB_result $query */
                $query = ee()->db->select('channel_id')
                    ->limit(1)
                    ->get('channels');

                $channel_id = $query->row('channel_id');

                $query->free_result();
            }
        }

        ee()->entry_type->init($channel_id);
        ee()->entry_type->add_field($this->field_name, $this->settings['type_options']);

        if (!empty($this->settings['fieldtype'])) {
            $method = 'display_field_'.$this->settings['fieldtype'];

            if (method_exists($this, $method)) {
                return $this->$method($options, $data);
            } elseif ($fieldtype = ee()->api_channel_fields->setup_handler($this->settings['fieldtype'], true)) {
                $fieldtype->field_name = $this->field_name;
                $fieldtype->field_id = $this->field_id;
                $fieldtype->settings = $this->fieldtypes[$this->settings['fieldtype']];
                $fieldtype->settings['field_list_items'] = $fieldtype->settings['options'] = $options;

                return $fieldtype->display_field($data);
            }
        }

        return $this->display_field_select($options, $data);
    }

    /**
     * @param array  $options
     * @param string $current_value
     * @return string
     */
    private function display_field_radio(array $options = [], $current_value = '')
    {
        $output = '';

        foreach($options as $value => $label) {
            $output .= form_label(form_radio($this->field_name, $value, $value == $current_value).NBS.$label);
        }

        return $output;
    }

    /**
     * @param array  $options
     * @param string $current_value
     * @return string
     */
    private function display_field_select(array $options = [], $current_value = '')
    {
        return form_dropdown($this->field_name, $options, $current_value);
    }

    /**
     * Given a fieldId, find out what channel(s) it's assigned to and get all the custom fields for those channels.
     * @param int  $fieldId
     * @param bool $excludeFieldId
     */
    protected function fieldsByChannel(int $fieldId, bool $excludeFieldId = false)
    {
        /** @var CI_DB_result $result */
        $result = ee()->db
            ->select('channel_id')
            ->where('field_id', $fieldId)
            ->group_by('channel_id')
            ->get('channels_channel_fields');

        $channelIds = array_column($result->result_array(), 'channel_id');

        if (empty($channelIds)) {
            return [];
        }

        $channels = ee('Model')->get('Channel')
            ->with('CustomFields')
            ->filter('channel_id', 'IN', $channelIds)
            ->all();

        $fields = [];

        /** @var Channel $channel */
        foreach ($channels as $channel) {
            $customFields = $channel->getAllCustomFields();

            foreach ($customFields as $customField) {
                $fields[$customField->field_id] = $customField->field_label;
            }
        }

        if ($excludeFieldId) {
            foreach ($fields as $field_id => $field_label) {
                if ($excludeFieldId == $field_id) {
                    unset($fields[$field_id]);

                    break;
                }
            }
        }

        return $fields;
    }

    /**
     * @param int|bool $group_id
     * @param bool $exclude_field_id
     * @return array|mixed
     */
    protected function fieldsByGroup($group_id = false, bool $exclude_field_id = false)
    {
        static $cache;

        if ($group_id === false) {
            if (isset($this->settings['group_id'])) {
                $group_id = $this->settings['group_id'];
            } else {
                return [];
            }
        }

        if ($exclude_field_id === false && isset($this->field_id) && is_numeric($this->field_id)) {
            $exclude_field_id = $this->field_id;
        }

        if (!isset($cache[$group_id])) {
            ee()->load->model('field_model');

            /** @var CI_DB_result $query */
            $query = ee()->field_model->get_fields($group_id);

            $cache[$group_id] = [];

            foreach ($query->result() as $row) {
                $cache[$group_id][$row->field_id] = $row->field_label;
            }

            $query->free_result();
        }

        $fields = $cache[$group_id];

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

    /**
     * @param array $settings
     * @return array|string
     */
    public function display_settings($settings = [])
    {
        if (!$this->field_id) {
            return ['field_options_entry_type' => [
                'label' => 'field_options',
                'group' => 'entry_type',
                'settings' => [
                    'This field must first be created and assigned to a Field Group or Channel, then edit the field to change it\'s options.'
                ]
            ]];
        }

        ee()->load->library('entry_type');
        ee()->load->helper('array');

        $action = ee()->uri->segment(3);

        if ($action === 'create') {
            $this->settings['group_id'] = ee()->uri->segment(4);

            $this->field_id = null;
        } else {
            $this->field_id = ee()->uri->segment(4);

            /** @var CI_DB_result $query */
            $query = ee()->db->select('group_id')
                ->from('channel_field_groups_fields')
                ->where('field_id', $this->field_id)
                ->get();

            $this->settings['group_id'] = $query->row('group_id');
        }

        ee()->load->library('api');
        ee()->legacy_api->instantiate('channel_fields');

        $all_fieldtypes = ee()->api_channel_fields->fetch_all_fieldtypes();

        $types = [];

        foreach ($all_fieldtypes as $row) {
            $type = strtolower(str_replace('_ft', '', $row['class']));

            if (array_key_exists($type, $this->fieldtypes)) {
                $types[$type] = $row['name'];
            }
        }

        ee()->lang->loadfile('entry_type', 'entry_type');
        ee()->load->helper(['array', 'html']);
        ee()->cp->add_js_script(['ui' => ['sortable']]);

        $vars['fields'] = [];

        if (!empty($this->settings['group_id'])) {
            $vars['fields'] = $this->fieldsByGroup($this->settings['group_id'], $this->field_id);
        } elseif (!empty($this->settings['field_id'])) {
            $vars['fields'] = $this->fieldsByChannel($this->settings['field_id'], $this->field_id);
        }

        if (empty($vars['fields'])) {
            return ['field_options_entry_type' => [
                'label' => 'field_options',
                'group' => 'entry_type',
                'settings' => [
                    'This field must first be assigned to a Field Group or Channel, then edit the field to change it\'s options.'
                ]
            ]];
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

        $options = [
            'rowTemplate' => preg_replace('/[\r\n\t]/', '', ee()->load->view('option_row', ['key' => 0, 'i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => [], 'fields' => $vars['fields']], true)),
            'deleteConfirmMsg' => lang('confirm_delete_type'),
        ];

        ee()->cp->load_package_js('EntryTypeFieldSettings');
        ee()->cp->load_package_css('EntryTypeFieldSettings');
        ee()->javascript->output('new EntryTypeFieldSettings(".entry_type_options", '.json_encode($options).');');

        return ['field_options_entry_type' => [
            'label' => 'field_options',
            'group' => 'entry_type',
            'settings' => [
                [
                    'title' => lang('field_type'),
                    'fields' => [
                        'entry_type_fieldtype' => [
                            'type' => 'select',
                            'choices' => $types,
                            'value' => isset($settings['fieldtype']) ? $settings['fieldtype'] : null,
                        ],
                    ],
                ],
                [
                    'title' => lang('types'),
                    'fields' => [
                        'entry_type_type_options' => [
                            'type' => 'html',
                            'content' => ee()->load->view('options', $vars, true),
                            'class' => 'options',
                        ],
                    ],
                ],
            ],
        ]];
    }

    public function save_settings($data)
    {
        $settings['type_options'] = [];

        if (isset($data['entry_type_options'][0]) && is_array($data['entry_type_options'][0])) {
            foreach ($data['entry_type_options'][0] as $row) {
                if (!isset($row['value'])) {
                    continue;
                }

                $value = $row['value'];

                unset($row['value']);

                if (empty($row['label'])) {
                    $row['label'] = $value;
                }

                $settings['type_options'][$value] = $row;
            }
        }

        $settings['blank_hide_fields'] = (isset($data['entry_type_blank_hide_fields'])) ? $data['entry_type_blank_hide_fields'] : [];

        $settings['fieldtype'] = (isset($data['entry_type_fieldtype'])) ? $data['entry_type_fieldtype'] : 'select';

        return $settings;
    }

    public function update($version = '')
    {
        return true;
    }
}
