		<tr>
			<td><?=form_input(sprintf('entry_type_options[%s][type]', $i), $type)?></td>
			<td><?=form_multiselect(sprintf('entry_type_options[%s][hide_fields][]', $i), $fields, $hide_fields)?></td>
			<td><label><?=form_radio('entry_type_default_type', $type, ($type == $default_type) ? TRUE : FALSE)?> <?=lang('yes')?></label></td>
			<td><a href="javascript:void(0);" class="entry_type_remove_row"><?=img(array('border' => '0', 'src' => $this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'))?></a></td>
		</tr>
