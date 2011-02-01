		<tr>
			<td><?=form_input(sprintf('entry_type_options[%s][option_value]', $index), $option_value)?></td>
			<td><?=form_input(sprintf('entry_type_options[%s][option_name]', $index), $option_name)?></td>
			<td><?=form_multiselect(sprintf('entry_type_options[%s][show_fields][]', $index), $fields, $show_fields)?></td>
		</tr>
