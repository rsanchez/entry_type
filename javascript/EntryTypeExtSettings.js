var EntryTypeExtSettings;
(function() {
	EntryTypeExtSettings = {
		load: function(target, view, appendTo, data, callback) {
			var $loading = $("<img>", {
				border: 0,
				src: EE.PATH_CP_GBL_IMG+"indicator.gif"
			});
			$(target).after($loading);
			$.get(
				EE.BASE+"&C=addons_extensions&M=extension_settings&file=entry_type&view="+view,
				data,
				function(data) {
					$(appendTo).append(data);
					$loading.remove();
					if (typeof callback === "function") {
						callback();
					}
				},
				"html"
			);
		},
		addRow: function() {
			EntryTypeExtSettings.load(this, "option_row_ext", "#entry_type_options > tbody", {
				channel_id: "",
				field_name: ""
			});
		},
		removeRow: function(index) {
			$("#entry_type_options > tbody").children("tr").eq(index).remove();
		}
	};

	$("#entry_type_options").on("change", ".entry_type_channel_select", function(event) {
		var $this = $(event.target);
		$this.parents("tr").find(".entry_type_field_select").toggle(Boolean($this.val())).trigger("change");
	}).on("change", ".entry_type_field_select", function() {
		var $this = $(this),
			fieldName = $(this).val(),
			$row = $(this).parents("tr"),
			channelId = $row.find(".entry_type_channel_select").val(),
			$fieldOptions = $row.find(".entry_type_field_options"),
			data = {
				channel_id: channelId,
				field_name: fieldName
			};

		if ( ! fieldName) {
			return;
		}

		$fieldOptions.html("").show();

		EntryTypeExtSettings.load($fieldOptions, "options_field_ext", $fieldOptions, data, function() {
			$.post(
				EE.BASE+"&C=addons_extensions&M=extension_settings&file=entry_type&view=option_field_row_ext",
				data,
				function(rowTemplate) {
					new EntryTypeFieldSettings($fieldOptions, {
						rowTemplate: rowTemplate,
						sortable: false,
						fieldName: "channel_"+channelId
					});
				},
				"text"
			);
		});
	});

	$("#entry_type_add_field").click(EntryTypeExtSettings.addRow);

	$("#entry_type_options .remove_field").live("click", function(){
		if (confirm("Are you sure you want to delete this field?")) {
			EntryTypeExtSettings.removeRow($(this).parents("tbody").find(".remove_field").index(this));
		}
	});
})();