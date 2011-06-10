		<tr>
			<td><?=form_input(sprintf('entry_type_options[%s][type]', $i), $type)?></td>
			<td><?=form_multiselect(sprintf('entry_type_options[%s][hide_fields][]', $i), $fields, $hide_fields)?></td>
		</tr>
