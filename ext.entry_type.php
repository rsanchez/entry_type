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
	public $version			= '2.0.0';
	
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

        $this->EE->load->library('entry_type');

        $this->EE->entry_type->init($row['channel_id'], $row['field_group']);

        $type_options = array(
            'a' => array (
                'label' => 'Text',
                'hide_fields' => array ('2'),
            ),
            'b' => array (
                'label' => 'Image',
                'hide_fields' => array ('1'),
            ),
        );

        $this->EE->entry_type->add_field('structure__template_id', $type_options);

        return $row;
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
	}	
	
	// ----------------------------------------------------------------------

    //@TODO finish the extensions settings
    public function settings_form($settings)
    {
        $this->EE->load->library('entry_type');

        $this->EE->load->helper('array');

        $vars['all_fields'] = array('' => 'Choose a field');
        $fields_by_group = array();
        $fields_labels = array();

        $all_fields = $this->EE->entry_type->all_fields();

        foreach ($all_fields as $group_id => $fields)
        {
            $field_by_group[$group_id] = array();

            foreach ($fields as $field)
            {
                if ( ! isset($vars['all_fields'][$field['group_name']]))
                {
                    $vars['all_fields'][$field['group_name']] = array();
                }

                $vars['all_fields'][$field['group_name']][$field['field_id']] = $field['field_label'];

                $fields_by_group[$group_id][] = $field['field_id'];
                $field_labels[$field['field_id']] = $field['field_label'];
            }
        }

        $vars['fields'] = array();

        if ( ! empty($this->settings['fields']))
        {
            foreach ($this->settings['fields'] as $field_id => $settings)
            {
                $vars['fields'][$field_id] = $this->EE->entry_type->field_settings($this->settings['group_id'], $settings, $field_id, $field_id);
            }
        }
        else
        {
            $vars['fields'][''] = $this->EE->entry_type->field_settings(NULL, array(), 0, 1);
        }

        $this->EE->javascript->output('
        (function(fieldsByGroup, fieldLabels) {

            function getFieldGroup(fieldId) {
                for (groupId in fieldsByGroup) {
                    if ($.inArray(fieldId, fieldsByGroup[groupId]) !== -1) {
                        return groupId;
                    }
                }
                return null;
            }

            function getSiblingFields(fieldId) {
                var siblings = {};
                for (groupId in fieldsByGroup) {
                    if ($.inArray(fieldId, fieldsByGroup[groupId]) !== -1) {
                        $.each(fieldsByGroup[groupId], function(i, v) {
                            if (v != fieldId) {
                                siblings[v] = fieldLabels[v];
                            }
                        });
                    }
                }
                return siblings;
            }

            function updateSelect($select, options) {
                var select = "<select multiple=\'multiple\' name=\'"+$select.attr("name")+"\'>";

                $.each(options, function(value, text) {
                    select += "<option value=\'"+value+"\'>"+text+"</option>";
                });

                select += "</select>";

                $select.replaceWith(select);
            }

            $(".entry_type_field_select").live("change", function() {
                var $this = $(this),
                    $row = $this.parents("tr"),
                    $options = $row.find(".entry_type_options"),
                    $inputs = $row.find(":input[name^=entry_type_options]"),
                    fieldId = $this.val(),
                    groupId = getFieldGroup(fieldId),
                    options = {};

                if (fieldId) {
                    $options.show();

                    updateSelect($options.find("select[name*=hide_fields]"), getSiblingFields(fieldId));

                    $inputs.each(function() {
                        var $input = $(this),
                            name = $input.attr("name");

                        $input.attr("name", name.replace(/^entry_type_options\[[^\]]+\]/, "entry_type_options["+fieldId+"]"));
                    });
                } else {
                    $options.hide();
                }
            });
        })('.$this->EE->javascript->generate_json($fields_by_group, TRUE).', '.$this->EE->javascript->generate_json($field_labels).');
        ');

        return $this->EE->load->view('options_ext', $vars, TRUE);
    }

    public function save_settings($data)
    {
        if ( ! isset($data['entry_type_options']))
        {
            return;
        }
        
        $settings['type_options'] = array();
        
        if (isset($data['entry_type_options']) && is_array($data['entry_type_options']))
        {
            foreach ($data['entry_type_options'] as $row)
            {
                if ( ! isset($row['value']))
                {
                    continue;
                }
                
                $value = $row['value'];
                
                unset($row['value']);
                
                if (empty($row['label']))
                {
                    $row['label'] = $value;
                }
                
                $settings['type_options'][$value] = $row;
            }
        }
        
        $settings['blank_hide_fields'] = (isset($data['entry_type_blank_hide_fields'])) ? $data['entry_type_blank_hide_fields'] : array();
        
        $settings['fieldtype'] = (isset($data['entry_type_fieldtype'])) ? $data['entry_type_fieldtype'] : 'select';
        
        return $settings;
    }
}

/* End of file ext.entry_type.php */
/* Location: /system/expressionengine/third_party/entry_type/ext.entry_type.php */