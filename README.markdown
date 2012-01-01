# Entry Type #

A dropdown fieldtype that shows/hides other fields on the publish page.

This can be used to create a Tumblr-like experience. For example, you first add a list of "types" to the Entry Type field: Link, Video, Image, etc. Then you create custom field(s) for each of those types for that field group. Then you can associate certain fields in that group to types, and when you select a type in the dropdown, those associated fields will show, and the others will hide.

@TODO add screencast here

## Installation

* Download the addon and rename the folder to entry_title
* Copy to system/expressionengine/third_party

## Tags

	{your_field_name}

The short name for the entry type selected.

	{your_field_name:label}

The label for the entry type selected.

	{if {your_field_name:selected option="link"}}

Check whether the specified option is selected. (Use the short name).

	{your_field_name all_options="yes"}
		{option} {option_name} {label} {selected}
	{/your_field_name}

List all your options.

## Examples

Displaying different content based on entry type.

	{exp:channel:entries}
	{title}<br />
    {your_entry_type_field:label}<br />
	{if your_entry_type_field == 'link'}
		{link_url}
	{if:else your_entry_type_field == 'video'}
		{video}
	{if:elseif your_entry_type_field == 'image'}
		{image}
	{/if}
	{/exp:channel:entries}


Use with [Switchee](https://github.com/croxton/Switchee):

	
	{exp:channel:entries}
	{title}<br />
	{exp:switchee variable = "{your_entry_type_field}" parse="inward"}
	
		{case value="link"}
		{link_url}
		{/case}
	
		{case value="video"}
		{video}
		{/case}

		{case value="image"}
		{image}
		{/case}
	
	{/exp:switchee}
	{/exp:channel:entries}