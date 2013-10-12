var EntryTypeFieldSettings;
(function() {
	EntryTypeFieldSettings = function(container, options) {

		options = $.extend({
			fieldName: "entry_type_options",
			sortable: true,
			rowTemplate: "",
			deleteConfirmMsg: "Are you sure you want to delete this Type?"
		}, options);

		var self = this;

		this.$container = $(container);
		this.$container.data("EntryTypeFieldSettings", this);
		this.$tbody = this.$container.find("tbody:first");
		this.addRow = function() {
			self.$tbody.append(options.rowTemplate.replace(/\{\{INDEX\}\}/g, self.$tbody.find("tr").length));
		};
		this.removeRow = function(index) {
			self.$tbody.find("tr").eq(index).remove();
			self.orderRows();
		};
		this.orderRows = function() {
			self.$tbody.find("tr").each(function(index){
				$(this).find(":input").each(function(){
					var match = $(this).attr("name").match(new RegExp("^"+options.fieldName+"\\[([a-z:\\d]+)\\]\\[\\d+\\]\\[(.*?)\\]$"));
					if (match) {
						$(this).attr("name", options.fieldName+"["+match[1]+"]["+index+"]["+match[2]+"]");
					}
				});
			});
		};

		this.$container.on("click", ".entry_type_add_row", self.addRow);
		this.$container.on("click", ".entry_type_remove_row", function(){
			if (confirm(options.deleteConfirmMsg)) {
				self.removeRow($(this).parents("tbody").find(".entry_type_remove_row").index(this));
			}
		});
		if (options.sortable) {
			this.$tbody.sortable({
				stop: function(e, ui) {
					self.orderRows();
				}
			}).children("tr").css({cursor:"move"});
		}

	};
})();