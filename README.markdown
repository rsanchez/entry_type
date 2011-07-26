# Entry Type #

A dropdown fieldtype that shows/hides other fields on the publish page.

This can be used to create a Tumblr-like experience. For example, you first add a list of "types" to the Entry Type field: Link, Video, Image, etc. Then you create custom field(s) for each of those types for that field group. Then you can associate certain fields in that group to types, and when you select a type in the dropdown, those associated fields will show, and the others will hide. As of version 1.0.2, there is support for multiple Entry Type fields in one channel.

If you want to have a blank option, you can added "None" as one of your types. The first type in order will be the default type.

@TODO add screencast here

## Installation

* Download the addon and rename the folder to entry_title
* Copy to system/expressionengine/third_party

## Usage

	{exp:channel:entries}
	{title}<br />
	{if your_entry_type_field == 'Link'}
		{link_url}
	{if:else your_entry_type_field == 'Video'}
		{video}
	{if:elseif your_entry_type_field == 'Image'}
		{image}
	{/if}
	{/exp:channel:entries}


Use with [Switchee](https://github.com/croxton/Switchee):

	
	{exp:channel:entries}
	{title}<br />
	{exp:switchee variable = "{your_entry_type_field}" parse="inward"}
	
		{case value="Link"}
		{link_url}
		{/case}
	
		{case value="Video"}
		{video}
		{/case}

		{case value="Image"}
		{image}
		{/case}
	
	{/exp:switchee}
	{/exp:channel:entries}