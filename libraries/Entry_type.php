<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

class Entry_type {

    private $field_names = array();
	
	public function __construct()
	{
		$this->EE =& get_instance();

        $this->EE->load->library('javascript');
	}

    public function init($channel_id)
    {
        if ($this->EE->session->cache('entry_type', 'display_field'))
        {
            return;
        }

        $this->EE->session->set_cache('entry_type', 'display_field', TRUE);

        $query = $this->EE->db->select('field_id, field_name')
                                ->join('channels', 'channels.field_group = channel_fields.group_id')
                                ->where('channel_id', $channel_id)
                                ->get('channel_fields');

        foreach ($query->result() as $row)
        {
            $this->field_names[$row->field_id] = REQ === 'CP' ? 'field_id_'.$row->field_id : $row->field_name;
        }

        $query->free_result();

        if (empty($this->field_names))
        {
            return;
        }
        
        //fetch field widths/visibility from publish layout
        $this->EE->load->model('member_model');
        
        $layout_group = is_numeric($this->EE->input->get_post('layout_preview')) ? $this->EE->input->get_post('layout_preview') : $this->EE->session->userdata('group_id');
        
        $layout_info = $this->EE->member_model->get_group_layout($layout_group, $channel_id);
    
        $widths = array();
        $invisible = array();
        
        if ( ! empty($layout_info))
        {
            foreach ($layout_info as $tab => $tab_fields)
            {
                foreach ($tab_fields as $field_name => $field_options)
                {
                    if (strncmp($field_name, 'field_id_', 9) === 0)
                    {
                        $field_id = substr($field_name, 9);

                        if (isset($field_options['width']))
                        {
                            $widths[$field_id] = $field_options['width'];
                        }

                        if (isset($field_options['visible']) && ! $field_options['visible'])
                        {
                            $invisible[] = $field_id;
                        }
                    }
                }
            }
        }

        if (REQ === 'CP')
        {
            $this->EE->cp->load_package_js('EntryType');
        }
        //for safecracker
        else
        {
            $this->EE->cp->add_to_head('<script type="text/javascript">'.file_get_contents(PATH_THIRD.'entry_type/javascript/EntryType.js').'</script>');
        }
        
        $this->EE->javascript->output('EntryType.setWidths('.json_encode($widths).');');
        
        $this->EE->javascript->output('EntryType.setInvisible('.json_encode($invisible).');');
    
        //show Entry Type-hidden fields when publish layouts are being edited
        //otherwise the publish layout sees the ET-hidden fields as intentionally
        //hidden and the layout will save with those fields hidden
        $this->EE->cp->add_to_head('<style type="text/css">#holder .entry-type-hidden { display: none !important; } #holder.toolbar-visible .entry-type-hidden { display: block !important; }</style>');

        $this->EE->javascript->output('
        $("#showToolbarLink > a").on("click", function() {
            $("#holder").toggleClass("toolbar-visible", $("#tab_menu_tabs").hasClass("ui-sortable"));
        });
        ');
    }

    public function add_field($field_name, $type_options)
    {
        $fields = array();

        foreach ($type_options as $value => $row)
        {
            foreach (explode('|', $value) as $val)
            {
                $fields[$val] = (isset($row['hide_fields'])) ? $row['hide_fields'] : array();
            }
        }

        if (is_numeric($field_name))
        {
            $field_name = isset($this->field_names[$field_name]) ? $this->field_names[$field_name] : 'field_id_'.$field_name;
        }

        if (method_exists($this, 'add_'.$field_name.'_field'))
        {
            return $this->{'add_'.$field_name.'_field'}($fields);
        }

        $callback = '';

        if (method_exists($this, 'callback_'.$field_name))
        {
            $callback = ', '.$this->{'callback_'.$field_name}();
        }

        $this->EE->javascript->output('EntryType.addField('.json_encode($field_name).', '.json_encode($fields).');');
    }

    protected function add_structure_parent_field($fields)
    {
        if ( ! $this->EE->session->cache(__CLASS__, __FUNCTION__))
        {
            $this->EE->session->set_cache(__CLASS__, __FUNCTION__, TRUE);

            $query = $this->EE->db->select('entry_id, parent_id')
                                    ->where('site_id', $this->EE->config->item('site_id'))
                                    ->get('structure');

            $structure_listings = array();

            foreach ($query->result() as $row)
            {
                $structure_listings[$row->entry_id] = $row->parent_id;
            }

            $query->free_result();

            $this->EE->javascript->set_global('structureListings', $structure_listings);
        }

        $callback = 'function() {
            var val = this.$input.val();
            
            while (EE.structureListings[val] !== undefined && EE.structureListings[val] != "0") {
                val = EE.structureListings[val];
            }

            return val;
        }';

        $this->EE->javascript->output('EntryType.addField("structure__parent_id", '.json_encode($fields).', '.$callback.');');
    }

    protected function add_structure_depth_field($fields)
    {
        $callback = 'function() {
            var val = this.$input.val(),
                label = this.$input.find("option:selected").text();
                depth = (val == 0) ? 0 : label.split("--").length;
            
            return depth;
        }';

        $this->EE->javascript->output('EntryType.addField("structure__parent_id", '.json_encode($fields).', '.$callback.');');
    }
    
    public function fields($group_id, $exclude_field_id = FALSE)
    {
        $all_fields = $this->all_fields();

        if ( ! isset($all_fields[$group_id]))
        {
            return array();
        }
        
        $fields = array();

        foreach ($all_fields[$group_id] as $field_id => $field)
        {
            $fields[$field_id] = $field['field_label'];
        }
        
        if ($exclude_field_id)
        {
            foreach ($fields as $field_id => $field_label)
            {
                if ($exclude_field_id == $field_id)
                {
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
        
        if (is_null($cache))
        {
            $query = $this->EE->db->select('field_id, field_name, field_label, field_groups.group_id, group_name')
                                    ->where('channel_fields.site_id', $this->EE->config->item('site_id'))
                                    ->join('field_groups', 'field_groups.group_id = channel_fields.group_id')
                                    ->order_by('field_groups.group_id, field_order')
                                    ->get('channel_fields');
    
            $cache = array();
    
            foreach ($query->result_array() as $row)
            {
                if ( ! isset($cache[$row['group_id']]))
                {
                    $cache[$row['group_id']] = array();
                }

                $cache[$row['group_id']][$row['field_id']] = $row;
            }
            
            $query->free_result();
        }

        return $cache;
    }

    public function field_settings($field_group = FALSE, $settings = array(), $field_id = FALSE, $key = FALSE)
    {
        $this->EE->lang->loadfile('entry_type', 'entry_type');
        
        $this->EE->load->helper(array('array', 'html'));
        
        $this->EE->cp->add_js_script(array('ui' => array('sortable')));

        if ($field_group)
        {
            $vars['fields'] = $this->fields($field_group, $field_id);
        }
        else
        {
            $vars['fields'] = array();

            foreach ($this->all_fields() as $group_id => $fields)
            {
                foreach ($fields as $field_id => $field)
                {
                    $vars['fields'][$field_id] = $field['field_label'];
                }
            }
        }
        
        if (empty($settings['type_options']))
        {
            $vars['type_options'] = array(
                '' => array(
                    'hide_fields' => array(),
                    'label' => '',
                ),
            );
        }
        else
        {
            foreach ($settings['type_options'] as $value => $option)
            {
                if ( ! isset($option['hide_fields']))
                {
                    $settings['type_options'][$value]['hide_fields'] = array();
                }
                
                if ( ! isset($option['label']))
                {
                    $settings['type_options'][$value]['label'] = $value;
                }
            }
            
            $vars['type_options'] = $settings['type_options'];
        }

        $row_view = $key ? 'option_row_ext' : 'option_row';

        $options_view = $key ? 'options_ext' : 'options';

        $row_template = preg_replace('/[\r\n\t]/', '', $this->EE->load->view($row_view, array('key' => $key, 'i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => array(), 'fields' => $vars['fields']), TRUE));

        $this->EE->cp->load_package_js('EntryTypeFieldSettings');

        $this->EE->javascript->output('
        (function() {
            var fieldSettings = new EntryTypeFieldSettings("#ft_entry_type", '.json_encode($row_template).');
            fieldSettings.deleteConfirmMsg = '.json_encode(lang('confirm_delete_type')).';
        })();
        ');

        return $this->EE->load->view($options_view, $vars, TRUE);
    }
}

/* End of file Entry_type.php */
/* Location: /system/expressionengine/third_party/entry_type/libraries/Entry_type.php */
