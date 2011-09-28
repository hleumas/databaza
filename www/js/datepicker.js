$.extend({
    createPicker: function (elem) {
        var el = $(elem);
        var value = el.val();
        var format = 'd.m.yy';
        var date = (value ? $.datepicker.parseDate(format, value) : null);

        var minDate = el.attr("data-min") || null;
        if (minDate) minDate = $.datepicker.parseDate(format, minDate);
        var maxDate = el.attr("data-max") || null;
        if (maxDate) maxDate = $.datepicker.parseDate(format, maxDate);

        // input.attr("type", "text") throws exception
        if (el.attr("type") == "date") {
            var tmp = $("<input type='text'/>");
            $.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value".split(","), function(i, attr)  {
                tmp.attr(attr, el.attr(attr));
            });
            el.replaceWith(tmp);
            el = tmp;
        }
        el.datepicker({
            minDate: minDate,
            maxDate: maxDate,
            changeYear: true,
            yearRange: '-30:+2',
        });
        el.val($.datepicker.formatDate(el.datepicker("option", "dateFormat"), date));
    }});
