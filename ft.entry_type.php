<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Entry_type_ft extends EE_Fieldtype
{
	public $info = array(
		'name' => 'Entry Type',
		'version' => '1.0.6',
	);

	public $has_array_data = TRUE;
	
	protected $fieldtypes = array(
		'select' => array(
			'field_text_direction' => 'ltr',
			'field_pre_populate' => 'n',
			'field_pre_field_id' => FALSE,
			'field_pre_channel_id' => FALSE,
			'field_list_items' => FALSE,
		),
		'radio' => array(
			'field_text_direction' => 'ltr',
			'field_pre_populate' => 'n',
			'field_pre_field_id' => FALSE,
			'field_pre_channel_id' => FALSE,
			'field_list_items' => FALSE,
		),
		'pt_pill' => array(),
	);
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if ($tagdata && isset($params['all_options']) && $params['all_options'] === 'yes')
		{
			return $this->replace_all_options($data, $params, $tagdata);
		}
		
		return $data;
	}
	
	public function replace_label($data, $params = array(), $tagdata = FALSE)
	{
		$this->convert_old_settings();
		
		foreach ($this->settings['type_options'] as $value => $option)
		{
			if ($data == $value)
			{
				return ( ! empty($option['label'])) ? $option['label'] : $value;
			}
		}
		
		return $data;
	}
	
	public function replace_selected($data, $params = array(), $tagdata = FALSE)
	{
		if ( ! isset($params['option']))
		{
			return 0;
		}
		
		$this->convert_old_settings();
		
		return (int) ($data == $params['option']);
	}
	
	public function replace_all_options($data, $params = array(), $tagdata = FALSE)
	{
		$this->convert_old_settings();
		
		$vars = array();
		
		foreach ($this->settings['type_options'] as $value => $option)
		{
			$label = ( ! empty($option['label'])) ? $option['label'] : $value;
			
			$vars[] = array(
				'value' => $value,
				'option' => $value,
				'option_value' => $value,
				'option_name' => $label,
				'option_label' => $label,
				'label' => $label,
				'selected' => (int) ($data == $value),
			);
		}
		
		if ( ! $vars)
		{
			$vars[] = array();
		}
		
		return $this->EE->TMPL->parse_variables($tagdata, $vars);
	}

	public function display_field($data)
	{
		$this->convert_old_settings();
		
		$options = array();

		foreach ($this->settings['type_options'] as $value => $row)
		{
			$options[$value] = ( ! empty($row['label'])) ? $row['label'] : $value;
		}

		$this->EE->load->library('entry_type');

		$this->EE->entry_type->init($this->EE->input->get_post('channel_id'));

		$this->EE->entry_type->add_field($this->field_name, $this->settings['type_options']);
		
		if ( ! empty($this->settings['fieldtype']))
		{
			$method = 'display_field_'.$this->settings['fieldtype'];
			
			if (method_exists($this, $method))
			{
				return $this->$method($options, $data);
			}
			else if ($fieldtype = $this->EE->api_channel_fields->setup_handler($this->settings['fieldtype'], TRUE))
			{
				$fieldtype->field_name = $this->field_name;
				$fieldtype->field_id = $this->field_id;
				$fieldtype->settings = $this->fieldtypes[$this->settings['fieldtype']];
				$fieldtype->settings['field_list_items'] = $fieldtype->settings['options'] = $options;
				
				return $fieldtype->display_field($data);
			}
		}

		return $this->display_field_select($options, $data);
	}
	
	private function display_field_radio($options, $current_value = '')
	{
		$output = form_fieldset('');

		foreach($options as $value => $label)
		{
			$output .= form_label(form_radio($this->field_name, $value, $value == $current_value).NBS.$label);
		}
		
		$output .= form_fieldset_close();
		
		return $output;
	}
	
	private function display_field_select($options, $current_value = '')
	{
		return form_dropdown($this->field_name, $options, $current_value);
	}
	
	private function convert_old_settings($settings = NULL)
	{
		if (is_null($settings))
		{
			$settings = $this->settings;
		}
		
		//backwards compat
		if (isset($settings['options']))
		{
			$settings['hide_fields'] = array();
			
			foreach ($settings['options'] as $type => $show_fields)
			{
				if ( ! is_array($show_fields))
				{
					$show_fields = array();
				}
				
				$settings['hide_fields'][$type] = array();
				
				foreach (array_keys($this->fields()) as $field_id)
				{
					if ( ! in_array($field_id, $show_fields))
					{
						$settings['hide_fields'][$type][] = $field_id;
					}
				}
			}
		}
		
		// more backwards compat
		if (isset($settings['hide_fields']))
		{
			$settings['type_options'] = array();
			
			foreach ($settings['hide_fields'] as $value => $hide_fields)
			{
				$settings['type_options'][$value] = array(
					'hide_fields' => $hide_fields,
					'label' => $value,
				);
			}
			
			unset($settings['hide_fields']);
			unset($this->settings['hide_fields']);
		}
		
		unset($settings['fields']);
		unset($this->settings['fields']);
		
		$this->settings = array_merge($this->settings, $settings);
	}
	
	protected function fields($group_id = FALSE, $exclude_field_id = FALSE)
	{
		static $cache;
		
		if ($group_id === FALSE)
		{
			if (isset($this->settings['group_id']))
			{
				$group_id = $this->settings['group_id'];
			}
			else
			{
				return array();
			}
		}
		
		if ($exclude_field_id === FALSE && isset($this->field_id) && is_numeric($this->field_id))
		{
			$exclude_field_id = $this->field_id;
		}
		
		if ( ! isset($cache[$group_id]))
		{
			$this->EE->load->model('field_model');
	
			$query = $this->EE->field_model->get_fields($group_id);
	
			$cache[$group_id] = array();
	
			foreach ($query->result() as $row)
			{
				$cache[$group_id][$row->field_id] = $row->field_label;
			}
			
			$query->free_result();
		}
		
		$fields = $cache[$group_id];
		
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

	public function display_settings($settings)
	{
		$this->EE->load->library('entry_type');

		$this->EE->load->helper('array');

		$this->convert_old_settings($settings);

		$this->settings['group_id'] = $this->EE->input->get('group_id');
		
		$this->field_id = $this->EE->input->get('field_id');
		
		$this->EE->load->library('api');
		
		$this->EE->api->instantiate('channel_fields');
		
		$this->EE->api_channel_fields = new Api_channel_fields;
		
		$all_fieldtypes = $this->EE->api_channel_fields->fetch_all_fieldtypes();
		
		$types = array();
		
		foreach ($all_fieldtypes as $row)
		{
			$type = strtolower(str_replace('_ft', '', $row['class']));
			
			if (array_key_exists($type, $this->fieldtypes))
			{
				$types[$type] = $row['name'];
			}
		}
		
		$this->EE->table->add_row(array(
			lang('field_type'),
			form_dropdown('entry_type_fieldtype', $types, element('fieldtype', $settings))
		));

		$this->EE->table->add_row(array(
			lang('types'),
			$this->EE->entry_type->field_settings($this->settings['group_id'], $this->settings, $this->field_id),
		));
	}

	public function save_settings($data)
	{
		if ( ! isset($data['entry_type_options'][0]))
		{
			return;
		}
		
		$settings['type_options'] = array();
		
		if (is_array($data['entry_type_options'][0]))
		{
			foreach ($data['entry_type_options'][0] as $row)
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

/* End of file ft.entry_type.php */
/* Location: ./system/expressionengine/third_party/entry_type/ft.entry_type.php */