<?php $display = $channel_id ? '' : ' style="display:none;"'; ?>
        <tr>
            <td valign="top"><?=form_dropdown('', $channels, $channel_id, 'class="entry_type_channel_select"')?></td>
            <td valign="top"><?=form_dropdown('', $global_fields, $field_name, 'class="entry_type_field_select"'.$display)?></td>
            <td valign="top"><div id="<?=$channel_id?>_<?=$field_name?>" class="entry_type_field_options"<?=$display?>><?=$this->load->view('options_field_ext', array('channel_id' => $channel_id, 'type_options' => $type_options, 'fields' => $channel_id ? $fields_by_id[$channel_id] : array(), 'value_options' => $field_name && $channel_id ? $value_options[$field_name][$channel_id] : array(), 'field_name' => $field_name), TRUE)?></div></td>
            <td valign="top"><a href="javascript:void(0);" class="remove_field"><?=img(array('border' => '0', 'src' => $this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'))?></a></td>
        </tr>
