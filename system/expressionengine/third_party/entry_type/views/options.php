<table width="100%" id="entry_type_options">
	<thead>
		<tr>
			<th><?=lang('option_value')?></th>
			<th><?=lang('option_name')?></th>
			<th><?=lang('show_fields')?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($options as $index => $option) : ?>
	<?=$this->load->view('option_row', array_merge(array('index' => $index), $option), TRUE)?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_row">+ Add Row</a></p>