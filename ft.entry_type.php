<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Entry_type_ft extends EE_Fieldtype
{
	public $info = array(
		'name' => 'Entry Type',
		'version' => '1.0.4',
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
		
		$fields = array();
		
		$options = array();
		
		$widths = array();

		foreach ($this->settings['type_options'] as $value => $row)
		{
			$fields[$value] = $row['hide_fields'];
			
			$options[$value] = ($row['label']) ? $row['label'] : $value;
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
						if (strncmp($field_name, 'field_id_', 9) === 0 && isset($field_options['width']))
						{
							$widths[substr($field_name, 9)] = $field_options['width'];
						}
					}
				}
			}
			
			$this->EE->cp->add_to_head('
			<script type="text/javascript">
			EE.entryType = {
				fields: {},
				widths: '.$this->EE->javascript->generate_json($widths).',
				change: function() {
					var value, input;
					$("div[id*=hold_field_]").not("#hold_field_"+$(this).data("fieldId")).filter(function(){
						return $(this).attr("id").match(/^hold_field_\d+$/);
					}).each(function(){
						$(this).show().width($(this).data("width"));
					});
					for (fieldName in EE.entryType.fields) {
						input = $(":input[name=\'"+fieldName+"\']");
						if ( input.is(":radio") ) input = input.filter(":checked");
						value = input.val();
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
				
				foreach (array_keys($vars['fields']) as $field_id)
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
		}
		
		$this->settings = $settings;
	}

	public function display_settings($settings)
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
		
		$this->convert_old_settings($settings);
		
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
			$vars['type_options'] = $settings['type_options'];
		}
		
		$vars['blank_hide_fields'] = (isset($settings['blank_hide_fields'])) ? $settings['blank_hide_fields'] : array();
		
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
			$this->EE->load->view('options', $vars, TRUE)
		));

		$row_template = preg_replace('/[\r\n\t]/', '', $this->EE->load->view('option_row', array('i' => '{{INDEX}}', 'value' => '', 'label' => '', 'hide_fields' => array(), 'fields' => $vars['fields']), TRUE));

		$this->EE->javascript->output('
			EE.entryTypeSettings = {
				rowTemplate: '.$this->EE->javascript->generate_json($row_template).',
				addRow: function() {
					$("#entry_type_options tbody").append(EE.entryTypeSettings.rowTemplate.replace(/{{INDEX}}/g, $("#entry_type_options tbody tr").length));
				},
				removeRow: function(index) {
					$("#entry_type_options tbody tr").eq(index).remove();
					EE.entryTypeSettings.orderRows();
				},
				orderRows: function() {
					$("#entry_type_options tbody tr").each(function(index){
						$(this).find(":input").each(function(){
							var match = $(this).attr("name").match(/^entry_type_options\[\d+\]\[(.*?)\]$/);
							if (match) {
								$(this).attr("name", "entry_type_options["+index+"]["+match[1]+"]");
							}
						});
					});
				}
			};
			
			$("#entry_type_add_row").click(EE.entryTypeSettings.addRow);
			$(".entry_type_remove_row").live("click", function(){
				if (confirm("'.lang('confirm_delete_type').'")) {
					EE.entryTypeSettings.removeRow($(this).parents("tbody").find(".entry_type_remove_row").index(this));
				}
			});
			$("#entry_type_options tbody").sortable({
				stop: function(e, ui) {
					EE.entryTypeSettings.orderRows();
				}
			}).children("tr").css({cursor:"move"});
		');
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

/* End of file ft.entry_type.php */
/* Location: ./system/expressionengine/third_party/entry_type/ft.entry_type.php */