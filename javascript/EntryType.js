(function(w) {

  $("form.settings fieldset").each(function() {
    var $fieldset = $(this);
    var $input = $fieldset.find('[name^=field_id_]:first').each(function() {
      var match = this.name.match(/^field_id_(\d+)/);
      $fieldset.attr("id", 'hold_field_'+match[1]);
    });
  });

  var $holdFields = $("fieldset[id^=hold_field_]").filter(function(){
        return this.id.match(/^hold_field_\d+$/);
      }),
      $tabs = $(".tab-wrap .tabs").find("li");

	w.EntryType = {
    fields: [],
    change: function(event) {
      var i, j, $input, value, fieldId;
      $holdFields.not(this).each(function(){
        var $this = $(this);
        if ( !$this.data("invisible")) {
          $this.removeClass("entry-type-hidden");
        }
      });
      for (i = 0; i < EntryType.fields.length; i++) {

        if (EntryType.fields[i].callback !== null) {
          value = $.proxy(EntryType.fields[i].callback, EntryType.fields[i])();

          if (value === null) {
            continue;
          }
        } else {
          value = (EntryType.fields[i].$input.is(":radio")) ? EntryType.fields[i].$input.filter(":checked").val() : EntryType.fields[i].$input.val();
        }

        for (fieldId in EntryType.fields[i].hideFields[value]) {
          $("#hold_field_"+EntryType.fields[i].hideFields[value][fieldId]).addClass("entry-type-hidden");
        }
      }
      $tabs.each(function() {
        var tabNum = $(this).find("a").attr("rel"),
            $tab = $(this),
            $tabContents = $("div.tab."+tabNum),
            $visibleFields = $tabContents.find("fieldset").filter(function() {
              return $(this).css("display") !== "none";
            });

        $tab.toggle($visibleFields.length > 0);
      });
    },
    addField: function(fieldName, hideFields, callback) {
			var field = {
        fieldName: fieldName,
        hideFields: hideFields || [],
        callback: callback || null,
        $input: $(":input[name=\'"+fieldName+"\']")
      };

      if ( ! field.$input.data("entryType")) {
        field.$input.change(EntryType.change).data("entryType", true);
      }

      EntryType.fields.push(field);

      $.proxy(EntryType.change, field.$input)();
    },
    setInvisible: function(invisible) {
      for (var i in invisible) {
        $("#hold_field_"+invisible[i]).data("invisible", true);
      }
    }
	};

})(window);
