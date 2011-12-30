<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Entry_type_ft extends EE_Fieldtype
{
	public $info = array(
		'name' => 'Entry Type',
		'version' => '1.0.3',
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
		$options = array();
		$widths = array();
		$invisible = array();

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
			
			//fetch field widths from publish layout
			$this->EE->load->model('member_model');
			
			$layout_group = (is_numeric($this->EE->input->get_post('layout_preview'))) ? $this->EE->input->get_post('layout_preview') : $this->EE->session->userdata('group_id');
			
			$layout_info = $this->EE->member_model->get_group_layout($layout_group, $this->EE->input->get_post('channel_id'));
			
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
			
			$this->EE->cp->add_to_head('
			<script type="text/javascript">
			EE.entryType = {
				fields: {},
				invisible: '.$this->EE->javascript->generate_json($invisible, TRUE).',
				widths: '.$this->EE->javascript->generate_json($widths).',
				change: function() {
					var value;
					$("div[id*=hold_field_]").not("#hold_field_"+$(this).data("fieldId")).filter(function(){
						return $(this).attr("id").match(/^hold_field_\d+$/);
					}).each(function(){
						var match = $(this).attr("id").match(/^hold_field_(\d+)$/);
						$(this).width($(this).data("width"));
						if ($.inArray(match[1], EE.entryType.invisible) === -1) {
							$(this).show();
						}
					});
					for (fieldName in EE.entryType.fields) {
						value = $(":input[name=\'"+fieldName+"\']").val();
						for (fieldId in EE.entryType.fields[fieldName][value]) {
							$("div#hold_field_"+EE.entryType.fields[fieldName][value][fieldId]).hide();
						}
					}
				},
				addField: function(data) {
					this.fields[data.fieldName] = data.fields;
					$(":input[name=\'"+data.fieldName+"\']").data("fieldId", data.fieldId).change(EE.entryType.change).trigger("change");
				},
				init: function() {
					for (fieldId in EE.entryType.widths) {
						$("div#hold_field_"+fieldId).data("width", EE.entryType.widths[fieldId]);
					}
				}
				
			};
			</script>');
			
			$this->EE->javascript->output("EE.entryType.init();");
		}

		$this->EE->javascript->output('EE.entryType.addField('.$this->EE->javascript->generate_json(array('fieldName' => $this->field_name, 'fieldId' => $this->field_id, 'fields' => $fields), TRUE).');');
		
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
		$this->EE->lang->loadfile('entry_type', 'entry_type');
		
		$this->EE->load->helper(array('array', 'html'));
		
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
				if ( ! is_array($show_fields))
				{
					$show_fields = array();
				}
				
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
			lang('field_type'),
			form_dropdown('entry_type_fieldtype', $types, element('fieldtype', $data))
		));

		$this->EE->table->add_row(array(
			lang('types'),
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