<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Entry_type_ft extends EE_Fieldtype
{
	public $info = array(
		'name'		=> 'Entry Type',
		'version'	=> '1.0.0'
	);

	public $has_array_data = FALSE;
	
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

	public function display_field($data)
	{
		$fields = array();

		//$options = array('' => '---');

		if (empty($this->settings['hide_fields']))
		{
			$this->settings['hide_fields'] = array();
		}

		foreach ($this->settings['hide_fields'] as $name => $field_ids)
		{
			$fields[$name] = $field_ids;
			$options[$name] = $name;
		}
		
		if ( ! isset($this->EE->session->cache['entry_type']['display_field']))
		{
			$this->EE->session->cache['entry_type']['display_field'] = TRUE;
			
			if (REQ !== 'CP')
			{
				$this->EE->cp->add_to_head('<script type="text/javascript">EE.entry_type = {};</script>');
			}
			else
			{
				$this->EE->javascript->set_global('entry_type', array());
			}
			
			//add all publish fields initial widths as data attribute
			$this->EE->javascript->output("$('div.publish_field').each(function(){
				$(this).attr('data-width', $(this).css('width'));
			});");
		}

		$this->EE->javascript->output('EE.entry_type["'.$this->field_name.'"] = '.$this->EE->javascript->generate_json($fields, TRUE).';');

		$this->EE->javascript->output("
			$(':input[name=\"".$this->field_name."\"]').change(function(){
				if ( ! $(this).val()) {
					return;
				}
				var value = $(this).val();
				$('div[id*=hold_field_]').not('#hold_field_".$this->field_id."').filter(function(){
					return $(this).attr('id').match(/^hold_field_\d+$/);
				}).each(function(){
					$(this).show().width($(this).attr('data-width'));
				});
				for (i in EE.entry_type['".$this->field_name."'][value]) {
					$('div#hold_field_'+EE.entry_type['".$this->field_name."'][value][i]).hide();
				}
			}).trigger('change');
		");
		
		if (isset($this->settings['fieldtype']) && $fieldtype = $this->EE->api_channel_fields->setup_handler($this->settings['fieldtype'], TRUE))
		{
			$fieldtype->field_name = $this->field_name;
			$fieldtype->field_id = $this->field_id;
			$fieldtype->settings = $this->fieldtypes[$this->settings['fieldtype']];
			$fieldtype->settings['field_list_items'] = $fieldtype->settings['options'] = $options;
			
			return $fieldtype->display_field($data);
		}

		return form_dropdown($this->field_name, $options, $data);
	}

	public function display_settings($data)
	{
		$this->EE->load->helper('array');
		
		$this->EE->cp->add_js_script(array('ui' => array('sortable')));
		
		$this->EE->load->model('field_model');

		$query = $this->EE->field_model->get_fields($this->EE->input->get('group_id', TRUE));

		$vars['fields'] = array();

		foreach ($query->result() as $row)
		{
			if ($this->EE->input->get('field_id') == $row->field_id)
			{
				continue;
			}
			
			$vars['fields'][$row->field_id] = $row->field_label;
		}
		
		//backwards compat
		if (isset($data['options']))
		{
			$data['hide_fields'] = array();
			
			foreach ($data['options'] as $type => $show_fields)
			{
				$data['hide_fields'][$type] = array();
				
				foreach (array_keys($vars['fields']) as $field_id)
				{
					if ( ! in_array($field_id, $show_fields))
					{
						$data['hide_fields'][$type][] = $field_id;
					}
				}
			}
		}
		
		$vars['options'] = (isset($data['hide_fields'])) ? $data['hide_fields'] : array('' => array());
		
		$vars['blank_hide_fields'] = (isset($data['blank_hide_fields'])) ? $data['blank_hide_fields'] : array();
		
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
			'Field Type',
			form_dropdown('entry_type_fieldtype', $types, element('fieldtype', $data))
		));

		$this->EE->table->add_row(array(
			'Types',
			$this->EE->load->view('options', $vars, TRUE)
		));

		$row_template = preg_replace('/[\r\n\t]/', '', $this->EE->load->view('option_row', array('i' => '{{INDEX}}', 'type' => '', 'hide_fields' => array(), 'fields' => $vars['fields']), TRUE));

		$this->EE->javascript->output($this->EE->load->view('js', array('row_template' => str_replace("'", "\'", $row_template)), TRUE));
	}

	public function save_settings($data)
	{
		if ( ! isset($data['entry_type_options']))
		{
			return;
		}

		$settings['hide_fields'] = array();

		foreach ($data['entry_type_options'] as $i => $option)
		{
			$settings['hide_fields'][$option['type']] = (isset($option['hide_fields'])) ? $option['hide_fields'] : array();
		}
		
		$settings['blank_hide_fields'] = (isset($data['entry_type_blank_hide_fields'])) ? $data['entry_type_blank_hide_fields'] : array();
		
		$settings['fieldtype'] = (isset($data['entry_type_fieldtype'])) ? $data['entry_type_fieldtype'] : 'select';

		return $settings;
	}
}

/* End of file ft.entry_type.php */
/* Location: ./system/expressionengine/third_party/entry_type/ft.entry_type.php */