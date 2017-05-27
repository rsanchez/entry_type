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
<?=$this->load->view('option_row', array('i' => (string) $i, 'value' => $value, 'label' => $data['label'], 'hide_fields' => $data['hide_fields'], 'fields' => $fields), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
	<thead>
		<tr>
			<td colspan="4">
				<a href="javascript:void(0);" class="btn entry_type_add_row"><i class="icon plus"></i><?=lang('add_type')?></a>
			</td>
		</tr>
	</thead>
</table>