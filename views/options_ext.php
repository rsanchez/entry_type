<table width="100%" id="entry_type_options" class="mainTable">
	<thead>
		<tr>
			<th>Channel</th>
			<th>Field</th>
			<th>Settings</th>
			<th style="width:1%;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($settings as $channel_id => $row) : ?>
	<?php foreach ($row as $field_name => $type_options) : ?>
		<?=$this->load->view('option_row_ext', array('channel_id' => $channel_id, 'field_name' => $field_name, 'type_options' => $type_options, 'channels' => $channels, 'global_fields' => $global_fields, 'fields_by_id' => $fields_by_id, 'value_options' => $value_options))?>
    <?php endforeach; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_field">Add Field</a></p>