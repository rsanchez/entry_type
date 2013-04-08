(function(w) {

	w.EntryType = {
    fields: {},
    callbacks: {},
    change: function(event) {
      var value, $input, fieldName, fieldId;
      if (event === undefined) {
        event = {target: ""};
      }
      $("div[id*=hold_field_]").not(event.target).filter(function(){
        return $(this).attr("id").match(/^hold_field_\d+$/);
      }).each(function(){
        $(this).show().width($(this).data("width"));
      });
      for (fieldName in EntryType.fields) {
        $input = $(":input[name=\'"+fieldName+"\']");
        if ( $input.is(":radio") ) $input = $input.filter(":checked");
        value = EntryType.callbacks[fieldName] !== undefined ? EntryType.callbacks[fieldName]($input) : $input.val();
        for (fieldId in EntryType.fields[fieldName][value]) {
          $("div#hold_field_"+EntryType.fields[fieldName][value][fieldId]).hide();
        }
      }
    },
    addField: function(fieldName, fields, callback) {
			var $input = $(":input[name=\'"+fieldName+"\']");
      EntryType.fields[fieldName] = fields;
      if (callback !== undefined) {
        EntryType.callbacks[fieldName] = callback;
      }
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
