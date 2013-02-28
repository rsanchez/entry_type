var EntryTypeExtSettings;
(function() {
	EntryTypeExtSettings = {
		rowTemplate: "",
		addRow: function() {
			$("#entry_type_options tbody").append(EntryTypeExtSettings.rowTemplate.replace(/\{\{INDEX\}\}/g, $("#entry_type_options tbody tr").length));
		},
		removeRow: function(index) {
			$("#entry_type_options tbody tr").eq(index).remove();
		}
	};
	
	$("#entry_type_add_field").click(EntryTypeExtSettings.addRow);
	$("#entry_type_options .remove_field").live("click", function(){
		if (confirm("Are you sure you want to delete this field?")) {
			EntryTypeExtSettings.removeRow($(this).parents("tbody").find(".remove_field").index(this));
		}
	});
})();