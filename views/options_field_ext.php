<table width="100%" class="entry_type_options mainTable">
	<thead>
		<tr>
			<th><?=lang('Value')?></th>
			<th><?=lang('hide_fields')?></th>
			<th style="width:1%;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($type_options as $value => $data) : ?>
<?=$this->load->view('option_field_row_ext', array('channel_id' => $channel_id, 'field_name' => $field_name, 'i' => (string) $i, 'value' => $value, 'hide_fields' => $data['hide_fields'], 'fields' => $fields, 'value_options' => $value_options), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" class="entry_type_add_row"><?=lang('add_type')?></a></p>