(function(w) {

    $("form.ajax-validate fieldset").each(function() {
        var $fieldset = $(this);
        var $input = $fieldset.find('[name^=field_id_]:first').each(function() {
            var match = this.name.match(/^field_id_(\d+)/);
            $fieldset.attr("id", 'hold_field_' + match[1]);
        });
    });

    var $holdFields = $("fieldset[id^=hold_field_]").filter(function() {
        var match = this.id.match(/^hold_field_(\d+)$/);

        if (match) {
            $(this).addClass('entry-type-field-' + match[1]);

            return true;
        }

        return false;
    });

    var $tabs = $(".tab-wrap .tabs").find("li");

    // add grid fields
    var $gridHoldFields = $('.fieldset-faux').filter(function() {
        var $gridField = $(this).find('.grid-field');

        if ($gridField.length) {
            var match = $gridField.attr('id').match(/^field_id_(\d+)$/);

            if (match) {
                $(this).addClass('entry-type-field-' + match[1]);

                return true;
            }
        }

        return false;
    });

    // Selector is very specific b/c the <fieldset> on React fields has no class, and we don't want to select
    // a field inside of a Grid or Bloqs field on accident. We can't use .filter() b/c we need to find the child
    // field, then find it's parent fieldset, which we append classes to.
    var $reactHoldFields = [];
    $('.tab > fieldset > .field-instruct + .field-control > div[data-input-value]').each(function () {
        var $field = $(this);
        var $fieldset = $field.closest('fieldset');
        var match = $field.attr('data-input-value').match(/^field_id_(\d+)/);

        if (match) {
            $fieldset
                .addClass('entry-type-field-' + match[1]);

            $reactHoldFields.push($fieldset);
        }
    });

    $holdFields = $holdFields
        .add($gridHoldFields)
        .add($reactHoldFields)
    ;

    w.EntryType = {
        fields: [],
        change: function(event) {
            var value;

            $holdFields.not(this).each(function() {
                var $this = $(this);
                if (!$this.data("invisible")) {
                    $this.removeClass("entry-type-hidden");
                }
            });

            for (var i = 0; i < EntryType.fields.length; i++) {
                if (EntryType.fields[i].callback !== null) {
                    value = $.proxy(EntryType.fields[i].callback, EntryType.fields[i])();

                    if (value === null) {
                        continue;
                    }
                } else {
                    value = (EntryType.fields[i].$input.is(":radio")) ? EntryType.fields[i].$input.filter(":checked").val() : EntryType.fields[i].$input.val();
                }

                for (var fieldId in EntryType.fields[i].hideFields[value]) {
                    $(".entry-type-field-" + EntryType.fields[i].hideFields[value][fieldId]).addClass("entry-type-hidden");
                }
            }

            $tabs.each(function() {
                var tabNum = $(this).find("a").attr("rel"),
                    $tab = $(this),
                    $tabContents = $("div.tab." + tabNum),
                    $visibleFields = $tabContents.find("fieldset, .fieldset-faux, .alert").filter(function() {
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
                $input: $(":input[name=\'" + fieldName + "\']")
            };

            if (!field.$input.data("entryType")) {
                field.$input.change(EntryType.change).data("entryType", true);
            }

            EntryType.fields.push(field);

            $.proxy(EntryType.change, field.$input)();
        },
        setInvisible: function(invisible) {
            for (var i in invisible) {
                $(".entry-type-field-" + invisible[i]).data("invisible", true);
            }
        }
    };

})(window);
