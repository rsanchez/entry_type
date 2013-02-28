var EntryTypeFieldSettings;
(function() {
	EntryTypeFieldSettings = function(container, rowTemplate) {

		var self = this;

		this.$container = $(container);
		this.$tbody = this.$container.find(".entry_type_options tbody");
		this.deleteConfirmMsg = "Are you sure you want to delete this Type?";
		this.rowTemplate = rowTemplate;
		this.addRow = function() {
			self.$tbody.append(self.rowTemplate.replace(/\{\{INDEX\}\}/g, self.$tbody.find("tr").length));
		};
		this.removeRow = function(index) {
			self.$tbody.find("tr").eq(index).remove();
			self.orderRows();
		};
		this.orderRows = function() {
			self.$tbody.find("tr").each(function(index){
				$(this).find(":input").each(function(){
					var match = $(this).attr("name").match(/^entry_type_options\[(\d+)\]\[\d+\]\[(.*?)\]$/);
					if (match) {
						$(this).attr("name", "entry_type_options["+match[1]+"]["+index+"]["+match[2]+"]");
					}
				});
			});
		};

		this.$container.find(".entry_type_add_row").click(self.addRow);
		this.$container.find(".entry_type_remove_row").live("click", function(){
			if (confirm(self.deleteConfirmMsg)) {
				self.removeRow($(this).parents("tbody").find(".entry_type_remove_row").index(this));
			}
		});
		this.$tbody.sortable({
			stop: function(e, ui) {
				self.orderRows();
			}
		}).children("tr").css({cursor:"move"});

	};
})();