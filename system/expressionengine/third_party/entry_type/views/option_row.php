		<tr>
			<td><?=form_input(sprintf('entry_type_options[%s][type]', $i), $type)?></td>
			<td><?=form_multiselect(sprintf('entry_type_options[%s][show_fields][]', $i), $fields, $show_fields)?></td>
		</tr>
