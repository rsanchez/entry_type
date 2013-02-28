<table width="100%" class="entry_type_options mainTable">
	<thead>
		<tr>
			<th><?=lang('type_value')?></th>
			<th><?=lang('type_label')?></th>
			<th><?=lang('hide_fields')?></th>
			<th style="width:1%;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($type_options as $value => $data) : ?>
<?=$this->load->view('option_row', array('key' => 0, 'i' => (string) $i, 'value' => $value, 'label' => $data['label'], 'hide_fields' => $data['hide_fields'], 'fields' => $fields), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" class="entry_type_add_row"><?=lang('add_type')?></a></p>