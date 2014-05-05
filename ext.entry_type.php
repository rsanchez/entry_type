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
 
// ------------------------------------------------------------------------

/**
 * Entry Type Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Rob Sanchez
 * @link		
 */

class Entry_type_ext {
	
	public $settings 		= array();
	public $description		= 'Conditionally shows/hide fields on the publish page.';
	public $docs_url		= '';
	public $name			= 'Entry Type';
	public $settings_exist	= 'y';
	public $version			= '1.1.1';
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{
		return array(
			
		);
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'publish_form_channel_preferences',
			'hook'		=> 'publish_form_channel_preferences',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * publish_form_channel_preferences
	 *
	 * @param 
	 * @return 
	 */
	public function publish_form_channel_preferences($row)
	{
        if ($this->EE->extensions->last_call !== FALSE)
        {
            $row = $this->EE->extensions->last_call;
        }

        if (empty($row['field_group']))
        {
            return $row;
        }

        if ( ! isset($this->settings[$row['channel_id']]))
        {
            return $row;
        }

        $this->EE->load->library('entry_type');

        $this->EE->entry_type->init($row['channel_id'], $row['field_group']);

        foreach ($this->settings[$row['channel_id']] as $field_name => $type_options)
        {
            $this->EE->entry_type->add_field($field_name, $type_options);
        }

        return $row;
    }

    private function is_structure_installed()
    {
        return isset($this->EE->extensions->extensions['entry_submission_end'][10]['Structure_ext']);
    }

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		ee()->db->where('class', __CLASS__);
    		ee()->db->update(
                	'extensions',
                	array('version' => $this->version)
    		);
	}

    private function ajax_option_row_ext($vars)
    {
        $vars['type_options'] = array('' => array('value' => '', 'hide_fields' => array()));

        $vars['channel_id'] = $this->EE->input->get_post('channel_id');

        $vars['field_name'] = $this->EE->input->get_post('field_name');

        $vars['value_options'] = isset($vars['value_options'][$vars['field_name']][$vars['channel_id']]) ? $vars['value_options'][$vars['field_name']][$vars['channel_id']] : array();

        //get the fields by field group or 
        $vars['fields'] = isset($vars['fields_by_id'][$vars['channel_id']]) ? $vars['fields_by_id'][$vars['channel_id']] : array();

        exit($this->EE->load->view('option_row_ext', $vars, TRUE));
    }

    private function ajax_options_field_ext($vars)
    {
        $vars['type_options'] = array('' => array('value' => '', 'hide_fields' => array()));

        $vars['channel_id'] = $this->EE->input->get_post('channel_id');

        $vars['field_name'] = $this->EE->input->get_post('field_name');

        $vars['value_options'] = isset($vars['value_options'][$vars['field_name']][$vars['channel_id']]) ? $vars['value_options'][$vars['field_name']][$vars['channel_id']] : array();

        //get the fields by field group or 
        $vars['fields'] = isset($vars['fields_by_id'][$vars['channel_id']]) ? $vars['fields_by_id'][$vars['channel_id']] : array();

        exit($this->EE->load->view('options_field_ext', $vars, TRUE));
    }

    private function ajax_option_field_row_ext($vars)
    {
        $vars['hide_fields'] = array();

        $vars['value'] = '';

        $vars['i'] = '{{INDEX}}';

        $vars['channel_id'] = $this->EE->input->get_post('channel_id');

        $vars['field_name'] = $this->EE->input->get_post('field_name');

        $vars['value_options'] = isset($vars['value_options'][$vars['field_name']][$vars['channel_id']]) ? $vars['value_options'][$vars['field_name']][$vars['channel_id']] : array();

        //get the fields by field group or 
        $vars['fields'] = isset($vars['fields_by_id'][$vars['channel_id']]) ? $vars['fields_by_id'][$vars['channel_id']] : array();

        exit($this->EE->load->view('option_field_row_ext', $vars, TRUE));
    }

    public function settings_form()
    {
        $this->EE->lang->load('content');

        $this->EE->load->helper(array('html', 'form'));

        $query = $this->EE->db->select('group_name, template_id, template_name')
                                ->join('template_groups', 'templates.group_id = template_groups.group_id')
                                ->where('templates.site_id', $this->EE->config->item('site_id'))
                                ->order_by('group_order ASC, template_name ASC')
                                ->get('templates');

        $templates = array();

        foreach ($query->result() as $row)
        {
            if (  ! isset($templates[$row->group_name]))
            {
                $templates[$row->group_name] = array();
            }

            $templates[$row->group_name][$row->template_id] = $row->template_name;
        }

        $template_field_name = $this->is_structure_installed() ? 'structure__template_id' : 'pages__pages_template_id';

        $vars['value_options'] = array(
            'status' => array(),
            $template_field_name => array(),
        );

        $vars['fields_by_id'] = array('' => array());

        $channels_by_field_group = array();

        $vars['channels'] = array(
            '' => 'Choose a channel',
        );

        $query = $this->EE->db->select('channel_id, field_group, status_group, channel_title')
                                ->where('site_id', $this->EE->config->item('site_id'))
                                ->get('channels');

        foreach ($query->result() as $row)
        {
            $vars['channels'][$row->channel_id] = $row->channel_title;

            $vars['fields_by_id'][$row->channel_id] = array();

            $channels_by_field_group[$row->field_group][] = $row->channel_id;

            $channels_by_status_group[$row->status_group][] = $row->channel_id;

            $vars['value_options'][$template_field_name][$row->channel_id] = $templates;

            $vars['value_options']['status'][$row->channel_id] = array('open' => lang('open'), 'closed' => lang('closed'));
        }

        $query->free_result();

        $query = $this->EE->db->select('status, group_id')
                                ->where('site_id', $this->EE->config->item('site_id'))
                                ->order_by('status_order')
                                ->get('statuses');

        $vars['statuses_by_id'] = array();

        foreach ($query->result() as $row)
        {
            if (isset($channels_by_status_group[$row->group_id]))
            {
                foreach ($channels_by_status_group[$row->group_id] as $_channel_id)
                {
                    $vars['value_options']['status'][$_channel_id][$row->status] = lang($row->status);
                }
            }
        }

        $query->free_result();

        $query = $this->EE->db->select('group_id, group_name')
                                ->where('site_id', $this->EE->config->item('site_id'))
                                ->get('field_groups');

        foreach ($query->result() as $row)
        {
            $row->fields = array();

            $vars['fields_by_id']['group_'.$row->group_id] = array();
        }

        $query->free_result();

        $query = $this->EE->db->select('field_id, group_id, field_label')
                                ->where('site_id', $this->EE->config->item('site_id'))
                                ->get('channel_fields');

        foreach ($query->result() as $row)
        {
            $vars['fields_by_id']['group_'.$row->group_id][$row->field_id] = $row->field_label;
            
            if (isset($channels_by_field_group[$row->group_id]))
            {
                foreach ($channels_by_field_group[$row->group_id] as $_channel_id)
                {
                    $vars['fields_by_id'][$_channel_id][$row->field_id] = $row->field_label;
                }
            }
        }

        $query->free_result();

        $vars['global_fields'] = array(
            '' => 'Choose a field',
            'status' => 'Status',
            $this->is_structure_installed() ? 'structure__template_id' : 'pages__pages_template_id' => 'Template',
        );

        if ($this->is_structure_installed())
        {
            $parent_options = array(0 => 'NONE');

            require_once PATH_THIRD.'structure/sql.structure.php';

            $sql = new Sql_structure();

            foreach ($sql->get_data() as $entry_id => $data)
            {
                //$parent_options[$entry_id] = str_repeat("--", $data['depth']).$data['title'];
                if ($data['depth'] == 0)
                {
                    $parent_options[$entry_id] = $data['title'];
                }
            }

            for ($i = 0; $i <= 10; $i++)
            {
                $depth_options[$i] = 'Depth: '.$i;
            }

            foreach ($vars['channels'] as $channel_id => $channel_title)
            {
                $vars['value_options']['structure_parent'][$channel_id] = $parent_options;
                $vars['value_options']['structure_depth'][$channel_id] = $depth_options;
            }

            $this->EE->load->remove_package_path(PATH_THIRD.'structure/');

            unset($sql);

            $vars['global_fields']['structure_parent'] = 'Structure Parent Entry';

            $vars['global_fields']['structure_depth'] = 'Structure Page Depth';
        }

        $this->settings = $this->get_settings();

        if (empty($this->settings))
        {
            /*
            $vars['settings'] = array(
                'group_1' => array(
                    'status' => array(
                        'open' => array(
                            'hide_fields' => array(),
                        )
                    )
                )
            );
            */

            $vars['settings'] = array(
                '' => array(
                    '' => array(
                        '' => array(
                            'hide_fields' => array(),
                        )
                    )
                )
            );
        }
        else
        {
            $vars['settings'] = $this->settings;
        }

        if (method_exists($this, 'ajax_'.$this->EE->input->get('view')))
        {
            return call_user_func(array($this, 'ajax_'.$this->EE->input->get('view')), $vars);
        }

        $this->EE->load->library('javascript');

        $this->EE->cp->load_package_js('EntryTypeFieldSettings');
        $this->EE->cp->load_package_js('EntryTypeExtSettings');

        foreach ($vars['settings'] as $channel_id => $row)
        {
            foreach ($row as $field_name => $type_options)
            {
                $value_options = isset($vars['value_options'][$field_name][$channel_id]) ? $vars['value_options'][$field_name][$channel_id] : array();

                $options = array(
                    'rowTemplate' => preg_replace('/[\r\n\t]/', '', $this->EE->load->view('option_field_row_ext', array('channel_id' => $channel_id, 'field_name' => $field_name, 'i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => array(), 'fields' => $vars['fields_by_id'][$channel_id], 'value_options' => $value_options), TRUE)),
                    'sortable' => false,
                    'fieldName' => 'channel_'.$channel_id,
                );

                $this->EE->javascript->output('
                    new EntryTypeFieldSettings('.json_encode('#'.$channel_id.'_'.$field_name).', '.json_encode($options).');
                ');
            }
        }

        return form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=entry_type')
                .$this->EE->load->view('options_ext', $vars, TRUE)
                .form_submit('', lang('submit'), 'class="submit"')
                .form_close();
    }

    private function get_settings()
    {
        $query = $this->EE->db->select('settings')
                                ->where('class', __CLASS__)
                                ->limit(1)
                                ->get('extensions');

        $settings = $query->num_rows() > 0 ? @unserialize($query->row('settings')) : FALSE;

        $query->free_result();

        return is_array($settings) ? $settings : array();
    }

    public function save_settings()
    {
        $settings = array();

        foreach ($_POST as $channel_id => $row)
        {
            if (strncmp('channel_', $channel_id, 8) !== 0)
            {
                continue;
            }

            $channel_id = substr($channel_id, 8);

            $settings[$channel_id] = array();

            foreach ($row as $field_name => $type_options)
            {
                $options_by_value = array();

                $settings[$channel_id][$field_name] = array();

                foreach ($type_options as $options)
                {
                    if ( ! isset($options['value']))
                    {
                        continue;
                    }

                    $value = $options['value'];

                    if (is_array($value))
                    {
                        $value = implode('|', $value);
                    }

                    unset($options['value']);

                    if ( ! isset($options['hide_fields']))
                    {
                        $options['hide_fields'] = array();
                    }

                    $serialized = serialize($options);

                    if (isset($options_by_value[$serialized]))
                    {
                        unset($settings[$channel_id][$field_name][$options_by_value[$serialized]]);

                        $options_by_value[$serialized] .= '|'.$value;

                        $settings[$channel_id][$field_name][$options_by_value[$serialized]] = $options;

                    }
                    else
                    {
                        $options_by_value[$serialized] = $value;

                        $settings[$channel_id][$field_name][$value] = $options;
                    }
                }

                if (empty($settings[$channel_id][$field_name]))
                {
                    unset($settings[$channel_id][$field_name]);
                }
            }
        }

        $this->EE->db->update('extensions', array(
            'settings' => serialize($settings),
        ), array(
            'class' => __CLASS__,
        ));

        $this->EE->functions->redirect(BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=entry_type');
    }
}

/* End of file ext.entry_type.php */
/* Location: /system/expressionengine/third_party/entry_type/ext.entry_type.php */
