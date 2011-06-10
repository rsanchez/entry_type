<table width="100%" id="entry_type_options" class="mainTable">
	<thead>
		<tr>
			<th><?=lang('type')?></th>
			<th><?=lang('hide_fields')?></th>
		</tr>
	</thead>
	<tbody>
		<?php /*
		<tr>
			<td><?=lang('blank')?></td>
			<td><?=form_multiselect('entry_type_blank_hide_fields[]', $fields, $blank_hide_fields)?></td>
		</tr>
		*/ ?>
<?php $i = 0; ?>
<?php foreach ($options as $type => $hide_fields) : ?>
<?=$this->load->view('option_row', array('i' => (string) $i, 'type' => $type, 'hide_fields' => $hide_fields, 'fields' => $fields), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_row">+ Add Type</a></p>