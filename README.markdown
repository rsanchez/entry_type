# Entry Type #

A dropdown fieldtype that shows/hides other fields on the publish page.

This can be used to create a Tumblr-like experience. For example, you first add a list of "types" to the Entry Type field: Link, Video, Image, etc. Then you create custom field(s) for each of those types for that field group. Then you can associate certain fields in that group to types, and when you select a type in the dropdown, those associated fields will show, and the others will hide.

@TODO add screencast here

## Installation

* Copy the /system/expressionengine/third_party/entry_type/ folder to your /system/expressionengine/third_party/ folder

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