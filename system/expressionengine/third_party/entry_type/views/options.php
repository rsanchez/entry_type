<table width="100%" id="entry_type_options">
	<thead>
		<tr>
			<th><?=lang('type')?></th>
			<th><?=lang('show_fields')?></th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($options as $type => $show_fields) : ?>
<?=$this->load->view('option_row', array('i' => (string) $i, 'type' => $type, 'show_fields' => $show_fields, 'fields' => $fields), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_row">+ Add Type</a></p>