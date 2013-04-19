(function(w) {

	w.EntryType = {
    fields: [],
    change: function(event) {
      var i, value, $input, fieldId;
      if (event === undefined) {
        event = {target: ""};
      }
      $("div[id*=hold_field_]").not(event.target).filter(function(){
        return $(this).attr("id").match(/^hold_field_\d+$/);
      }).each(function(){
        $(this).show().width($(this).data("width"));
      });
      for (i = 0; i < EntryType.fields.length; i++) {
        $input = $(":input[name=\'"+EntryType.fields[i].fieldName+"\']");
        if ( $input.is(":radio") ) $input = $input.filter(":checked");
        value = EntryType.fields[i].callback !== null ? EntryType.fields[i].callback($input) : $input.val();
        for (fieldId in EntryType.fields[i].hideFields[value]) {
          $("div#hold_field_"+EntryType.fields[i].hideFields[value][fieldId]).hide();
        }
      }
    },
    addField: function(fieldName, hideFields, callback) {
			var $input = $(":input[name=\'"+fieldName+"\']");
      EntryType.fields.push({
        fieldName: fieldName,
        hideFields: hideFields || [],
        callback: callback || null
      });
      $input.change(EntryType.change);
      EntryType.change({target: $input});
    },
    setWidths: function(widths) {
			var fieldId;
      for (fieldId in widths) {
         $("div#hold_field_"+fieldId).data("width", widths[fieldId]);
      }
    }
	};

})(window);
