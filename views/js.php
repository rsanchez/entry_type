$.entry_type = {
	row_template: '<?=$row_template?>',
	add_row: function() {
		$("#entry_type_options tbody").append($.entry_type.row_template.replace(/{{INDEX}}/g, $("#entry_type_options tbody tr").length));
	},
	remove_row: function(index) {
		$("#entry_type_options tbody tr").eq(index).remove();
		$.entry_type.order_rows();
	},
	order_rows: function() {
		$("#entry_type_options tbody tr").each(function(index){
			$(this).find(":input").each(function(){
				var match = $(this).attr("name").match(/^entry_type_options\[\d+\]\[(.*?)\]$/);
				if (match) {
					$(this).attr("name", "entry_type_options["+index+"]["+match[1]+"]");
				}
			});
		});
	}
};

$("#entry_type_add_row").click($.entry_type.add_row);
$(".entry_type_remove_row").live("click", function(){
	if (confirm("<?=lang('confirm_delete_type')?>")) {
		$.entry_type.remove_row($(this).parent().index());
	}
});
$("#entry_type_options tbody").sortable({
	stop: function(e, ui) {
		$.entry_type.order_rows();
	}
}).children("tr").css({cursor:"move"});