<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Entry_type_ft extends EE_Fieldtype
{
	public $info = array(
		'name'		=> 'Entry Type',
		'version'	=> '1.0.0'
	);

	public $has_array_data = FALSE;
	
	function Entry_type_ft()
	{
		parent::EE_Fieldtype();
		
		/** ----------------------------------------
		/**	 Prepare Cache
		/** ----------------------------------------*/

		if (! isset($this->EE->session->cache['entry_type']))
		{
			$this->EE->session->cache['entry_type'] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache['entry_type'];
	}

	public function display_field($data)
	{
		$fields = array();

		$options = array('' => '---');

		if (empty($this->settings['options']))
		{
			$this->settings['options'] = array();
		}

		foreach ($this->settings['options'] as $name => $field_ids)
		{
			$fields[$name] = $field_ids;
			$options[$name] = $name;
		}

		$this->EE->javascript->set_global('entry_type', array());

		$this->EE->javascript->output('EE.entry_type["'.$this->field_name.'"] = '.$this->EE->javascript->generate_json($fields, TRUE).';');

		if ( ! $data)
		{
			//hide em all
			$this->EE->javascript->output("
				$('div[id*=hold_field_]').not('#hold_field_".$this->field_id."').filter(function(){
					return $(this).attr('id').match(/^hold_field_\d+$/);
				}).hide();
			");
		}

		$this->EE->javascript->output("
			$('select[name=".$this->field_name."]').change(function(){
				if ( ! $(this).val()) {
					return;
				}
				var value = $(this).val();
				$('div[id*=hold_field_]').not('#hold_field_".$this->field_id."').filter(function(){
					return $(this).attr('id').match(/^hold_field_\d+$/);
				}).hide();
				for (i in EE.entry_type['".$this->field_name."'][value]) {
					$('div#hold_field_'+EE.entry_type['".$this->field_name."'][value][i]).show().width('100%');
				}
			});
			
			$('select[name=".$this->field_name."]').trigger('change');
		");
		
		$exists = $this->EE->db->get_where('fieldtypes', array('name' => 'pt_pill'));
		$field_id = '';
		
		// If PT Pill is installed use it's style because it's purdy.
		if($exists->num_rows() == 1)
		{
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().'styles/pt_pill.css" />');
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().'scripts/pt_pill.js"></script>');

			$field_id = str_replace(array('[', ']'), array('_', ''), $this->field_name);
			$this->EE->cp->add_to_foot('<script type="text/javascript">new ptPill(jQuery("#'.$field_id.'"));</script>');
		}

		return form_dropdown($this->field_name, $options, $data, 'id="'.$field_id.'"');
	}
	
	/**
	 * Theme URL
	 */
	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
			$this->cache['theme_url'] = $theme_folder_url.'third_party/pt_pill/';
		}

		return $this->cache['theme_url'];
	}

	public function display_settings($data)
	{
		$options = (isset($data['options'])) ? $data['options'] : array('' => array());

		$this->EE->load->model('field_model');

		$query = $this->EE->field_model->get_fields($this->EE->input->get('group_id', TRUE));

		$fields = array();

		foreach ($query->result() as $row)
		{
			if ($this->EE->input->get('field_id') == $row->field_id)
			{
				continue;
			}
			
			$fields[$row->field_id] = $row->field_label;
		}

		$this->EE->table->add_row(array(
			'Types',
			$this->EE->load->view('options', array('fields' => $fields, 'options' => $options), TRUE)
		));

		$row_template = preg_replace('/[\r\n\t]/', '', $this->EE->load->view('option_row', array('i' => '{{INDEX}}', 'type' => '', 'show_fields' => array(), 'fields' => $fields), TRUE));

		$this->EE->javascript->output($this->EE->load->view('js', array('row_template' => str_replace("'", "\'", $row_template)), TRUE));
	}

	public function save_settings($data)
	{
		if ( ! isset($data['entry_type_options']))
		{
			return;
		}

		$settings = array();

		foreach ($data['entry_type_options'] as $i => $option)
		{
			$settings[$option['type']] = (isset($option['show_fields'])) ? $option['show_fields'] : array();
		}

		return array('options' => $settings);
	}
}

/* End of file ft.entry_type.php */
/* Location: ./system/expressionengine/third_party/entry_type/ft.entry_type.php */