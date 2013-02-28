<table width="100%" id="entry_type_options" class="mainTable">
	<thead>
		<tr>
			<th>Field</th>
			<th>Settings</th>
			<th style="width:1%;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($fields as $field_id => $field_settings) : ?>
        <tr>
            <td><?=form_dropdown('', $all_fields, $field_id, 'class="entry_type_field_select"')?><br><br><p>OR enter a form field name:</p><?=form_input('', ($field_id && ! is_numeric($field_id)) ? $field_id : '', 'class="entry_type_field_name"')?></td>
            <td><?=$field_settings?></td>
            <td><a href="javascript:void(0);" class="remove_field"><?=img(array('border' => '0', 'src' => $this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'))?></a></td>
        </tr>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_field">Add Field</a></p>