<?php
$size = count($fields);

if ($size < 5)
{
    $size = '';
}
else if ($size > 20)
{
    $size = 'size="20"';
}
else
{
    $size = 'size="'.$size.'"';
}
?>
		<tr>
			<td><?=form_multiselect(sprintf('%s[%s][%s][value][]', 'channel_'.$channel_id, $field_name, $i), $value_options, $value, $size)?></td>
			<td><?=form_multiselect(sprintf('%s[%s][%s][hide_fields][]', 'channel_'.$channel_id, $field_name, $i), $fields, $hide_fields, $size)?></td>
			<td><a href="javascript:void(0);" class="entry_type_remove_row"><?=img(array('border' => '0', 'src' => $this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'))?></a></td>
		</tr>
