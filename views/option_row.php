		<tr>
			<td><?=form_input(sprintf('entry_type_options[0][%s][value]', $i), $value)?></td>
			<td><?=form_input(sprintf('entry_type_options[0][%s][label]', $i), $label)?></td>
			<td>
                <?php foreach ($fields as $field_id => $field_name) : ?>
                <label class="entry_type_hide_fields">
                <?=form_checkbox(sprintf('entry_type_options[0][%s][hide_fields][]', $i), $field_id, in_array($field_id, $hide_fields))?>
                <?=$field_name?>
                </label>
                <?php endforeach; ?>
            </td>
			<td><a href="javascript:void(0);" class="entry_type_remove_row"></a></td>
		</tr>
