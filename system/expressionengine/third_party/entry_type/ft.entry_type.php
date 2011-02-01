<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'fieldtypes/ft.select'.EXT;

class Entry_type_ft extends Select_ft
{
	public $info = array(
		'name'		=> 'Entry Type',
		'version'	=> '1.0.0'
	);

	public $has_array_data = TRUE;
	
	public $default_options = array(
		'option_value' => '',
		'option_name' => '',
		'show_fields' => array()
	);

	public function display_settings($data)
	{
		$view_data['options'] = (isset($data['options'])) ? $data['options'] : array($this->default_options);
		
		$this->EE->load->model('field_model');
		
		$query = $this->EE->field_model->get_fields($this->EE->input->get('group_id', TRUE));
		
		$view_data['fields'] = array();
		
		foreach ($query->result() as $row)
		{
			if ($this->EE->input->get('field_id') == $row->field_id)
			{
				continue;
			}
			
			$view_data['fields'][$row->field_id] = $row->field_label;
		}
		
		$this->EE->table->add_row(array(
			array('data' => $this->EE->load->view('options', $view_data, TRUE), 'colspan' => 2)
		));
		
		$row_template = preg_replace('/[\r\n\t]/', '', $this->EE->load->view('option_row', array_merge(array('index' => '{{INDEX}}'), $this->default_options), TRUE));
		
		$this->EE->javascript->output($this->EE->load->view('js', array('row_template' => str_replace("'", "\'", $row_template)), TRUE));
	}
	
	public function save_settings($data)
	{
		if ( ! isset($data['entry_type_options']))
		{
			return;
		}
		
		foreach ($data['entry_type_options'] as $i => $option)
		{
			foreach ($this->default_options as $key => $value)
			{
				if ( ! isset($option[$key]))
				{
					$data['entry_type_options'][$i][$key] = $value;
				}
			}
		}
		
		return array('options' => $data['entry_type_options']);
	}
}

/* End of file ft.entry_type.php */
/* Location: ./system/expressionengine/third_party/entry_type/ft.entry_type.php */