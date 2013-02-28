		<tr>
			<td>
                <?=form_input(sprintf('entry_type_options[%s][%s][value]', $key, $i), $value)?>
                <?=form_label('Use text instead of value'.NBS.form_checkbox('', ''))?>
            </td>
			<td><?=form_multiselect(sprintf('entry_type_options[%s][%s][hide_fields][]', $key, $i), $fields, $hide_fields)?></td>
			<td><a href="javascript:void(0);" class="entry_type_remove_row"><?=img(array('border' => '0', 'src' => $this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'))?></a></td>
		</tr>
