# Entry Type #

An ExpressionEngine add-on for hiding publish fields on a conditional basis. The Entry Type fieldtype creates a dropdown field which can hide other publish fields depending on the value chosen. The Entry Type extension allows you to hide specific publish fields depending on the entry's status, Pages/Structure template, or Structure page depth.

![Entry Type](https://raw.github.com/rsanchez/entry_type/master/images/entry-type.gif)

## Installation

* Download the addon and rename the folder to `entry_type`
* Copy to `system/expressionengine/third_party`
* Install the extension and fieldtype

## Fieldtype Tags

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

## Fieldtype Settings

-  Short Name - the value of the field, for use in templates and conditionals
-  Label - the label for the value
-  Hide Fields - choose the fields to hide when the specified value is chosen

## Extension Settings

-  Channel - choose one of your channels
-  Field - choose either Status or Template
-  Settings: Value - choose the status or template value that will trigger this "type"
-  Settings: Hide Fields - choose the fields to hide when the specified value is chosen

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