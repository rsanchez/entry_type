# Entry Type

An ExpressionEngine 4 add-on for hiding publish fields on a conditional basis. The Entry Type fieldtype creates a dropdown field which can hide other publish fields depending on the value chosen.

Use the [`ee2`](/rsanchez/entry_type/tree/ee2) branch for an EE2 compatible version. Structure/Pages module compatibility has been removed in the EE3 version.

Use the [`ee3`](/rsanchez/entry_type/tree/ee3) branch for an EE3 compatible version.

## Installation

* Requires PHP 5.3
* Download the addon and rename the folder to `entry_type`
* Copy to `system/user/addons`
* Install the addon

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
