<table width="100%" id="entry_type_options" class="mainTable">
	<thead>
		<tr>
			<th><?=lang('type')?></th>
			<th><?=lang('Hide Fields')?></th>
			<th><?=lang('Default')?></th>
			<th style="width:1%;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php $i = 0; ?>
<?php foreach ($options as $type => $hide_fields) : ?>
<?=$this->load->view('option_row', array('i' => (string) $i, 'type' => $type, 'hide_fields' => $hide_fields, 'fields' => $fields), TRUE)?>
<?php $i++; ?>
<?php endforeach; ?>
	</tbody>
</table>
<p><a href="javascript:void(0);" id="entry_type_add_row">+ Add Type</a></p>