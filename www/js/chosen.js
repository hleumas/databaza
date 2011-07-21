$.extend({
        chosen: function(element) {
                    var opt         = this.parseOptions(element);
                    this.options    = opt.options;
                    this.selected   = opt.selected;
                    this.emptyLabel = opt.emptyLabel;
                    this.emptyValue = opt.emptyValue;

                    this.noMatch = element.attr('data-no-match')
                                || 'Žiadna položka nevyhovuje filtru';
                    this.prefix = element.attr('id');
                    this.createControl(this.prefix, element.attr('name'));
                    $(element).replaceWith(this.control);
                    this.select(this.selected);
                    this.setWidth(this.drop.width() + 10);
                    this.drop.hide();
        },
        scrollIntoView: function(element) {
            var c          = element.parent(),
                elemTop    = element.position().top,
                elemBottom = elemTop + element.outerHeight(); 
            if (elemTop < 0) {
                container.scrollTop(elemTop+c.scrollTop());
            } else if (elemBottom > c.height()) {
                container.scrollTop(elemBottom - c.height() + c.scrollTop());
            }
        }
});

$.chosen.prototype.parseOptions = function(select) {
    var options    = Array(),
        selected   = null,
        value      = select.val(),
        emptyValue = select.attr('data-nette-empty-value'),
        emptyLabel = '';

    var parseOption = function(data, elem) {
        var opt = {label: elem.text(), value: elem.val()};
        if (opt.value !== emptyValue) {
            opt.normalized = $.removeDiacritics(opt.label).toLowerCase();
            data.push(opt);
            return (opt.value === value);
        } else {
            emptyLabel = opt.label;
            return false;
        }
    };

    select.children('option, optgroup').each(function() {
        var elem = $(this);
        if (elem.is('option')) {
            selected = parseOption(options, elem)
                     ? '' + (options.length - 1) : selected;
        } else {
            var group = {label: elem.attr('label'), value: new Array()};
            var i = options.push(group) - 1;
            elem.children('option').each(function() {
                selected = parseOption(options[i].value, $(this))
                         ? i + '_' + (options[i].value.length - 1) : selected;
            });
        }
    });

    return {
        'options'   : options,
        'selected'  : selected,
        'emptyValue': emptyValue,
        'emptyLabel': emptyLabel
    };
};

$.chosen.prototype.filter = function(string) {
    string = $.removeDiacritics(string).toLowerCase();
    for (var i in this.options) {
        if (this.options[i].normalized === undefined) {
            var visible = false;
            for (var j in this.options[i].value) {
                found = (this.options[i].value[j].normalized.indexOf(string) != -1);
                visible = visible || found;
                this.show(i+'_'+j, found);
            }
            this.show(i, visible);
        } else {
            this.show(i, (this.options[i].normalized.indexOf(string) != -1));
        }
    }
    var noMatch = false;
    if (!this.drop.find('.highlighted').is(':visible')) {
        var first = this.drop.find('li.active-result:visible:first');
        if (first.length === 0) {
            noMatch = true;
        }
        this.highlight(this.drop.find('li.active-result:visible:first'));
    }
    if (noMatch) {
        this.noMatch.show();
    } else {
        this.noMatch.hide();
    }

};

$.chosen.prototype.show = function(index, show) {
    show = (show === undefined || show === null) ? true : show;
    var elem = $('#' + this.prefix + '_' + index);
    if (show) {
        elem.show();
    } else {
        elem.hide();
    }
};


$.chosen.prototype.createOption = function(id, label, group) {
    var li = $('<li></li>');
    var choose = this;
    li.text(label);
    li.attr('id', id);
    if (group) {
        li.addClass('group-result');
    } else {
        li.addClass('active-result');
        li.mouseenter(function (e) {choose.highlight($(this));});
        li.click(function(e) {choose.selectElem($(this)); choose.rollIn();});
    }
    return li;
};
$.chosen.prototype.createOptions = function(prefix) {
    var ul = $("<ul class='chzn-results'></ul>");
    for (var i in this.options) {
        var elem = this.options[i];
        if ($.isArray(elem.value)) {
            var li = this.createOption(prefix + '_' + i, elem.label, true);
            ul.append(li);
            for (var j in elem.value) {
                var li = this.createOption(prefix + '_' + i + '_' + j, elem.value[j].label, false);
                li.addClass('group-option');
                ul.append(li);
            }
        } else {
            var li = this.createOption(prefix + '_' + i, elem.label, false);
            ul.append(li);
        }
    }
    var noMatch = $('<li class="no-results"></li>');
    noMatch.text(this.noMatch);
    this.noMatch = noMatch;
    this.noMatch.hide();
    ul.append(this.noMatch);
    return ul;
};

$.chosen.prototype.createControl = function(prefix, name) {
    var choose = this;

    /** Create hidden field */
    this.hidden = $('<input type="hidden" />').attr('name', name);

    /** Create roller */
    this.current = $('<span></span>');
    this.roller = $('<a class="chzn-single"></a>')
                .append(this.current)
                .append($('<div><b></b></div>'))
                .click(function(e) {
        if (choose.isRolled()) {
            choose.rollIn();
        } else {
            choose.rollOut();
        }
    });

    /** Crate search input */
    this.search = $('<input type=text />').change(function() {
        choose.filter($(this).val());
    }).keyup(function() {
        $(this).change();
    }).click(function() {
        $(this).change();
    });

    /** Create dropdown */
    this.drop = $('<div class="chzn-drop"></div>')
              .append($('<div class="chzn-search"></div>').append(this.search))
              .append(this.createOptions(prefix));

    /** Create control */
    this.control = $('<div class="chzn-container chzn-container-single"></div>')
                 .attr('id', prefix)
                 .append(this.hidden)
                 .append(this.roller)
                 .append(this.drop)
                 .keydown(function(e){
        if (e.keyCode === 38) {
            if (choose.isRolled()) {
                choose.moveUp();
            } else {
                choose.rollOut();
            }
            e.preventDefault();
        } else if (e.keyCode === 40) {
            if (choose.isRolled()) {
                choose.moveDown();
            } else {
                choose.rollOut();
            }
            e.preventDefault();
        } else if (e.keyCode === 27) {
            choose.rollIn()
            e.preventDefault();
            e.stopImmediatePropagation();
        } else if (e.keyCode === 13) {
            choose.selectElem(choose.drop.find('.highlighted'));
            choose.rollIn();
            e.preventDefault();
        }
    });
};

$.chosen.prototype.setWidth = function(width) {
    this.control.width(width);
    //this.roller.width(width-10);
    this.search.width(width-22);
    this.drop.width(width-2);
};

$.chosen.prototype.highlight = function(elem) {
    if (elem.length === 1) {
        this.drop.find('.highlighted').removeClass('highlighted');
        elem.addClass('highlighted');
        $.scrollIntoView(elem);
    }
};

$.chosen.prototype.isRolled = function() {
    return this.control.hasClass('chzn-container-active');
};
$.chosen.prototype.rollOut = function() {
    if (!this.isRolled()) {
        this.control.addClass('chzn-container-active');
        this.roller.addClass('chzn-single-with-drop');
        this.drop.show();
        this.highlight(this.drop.find('#' + this.prefix + '_' + this.selected));
        this.search.focus();
    }
};

$.chosen.prototype.rollIn = function() {
    if (this.isRolled()) {
        this.control.removeClass('chzn-container-active');
        this.roller.removeClass('chzn-single-with-drop');
        this.drop.hide();
    }
};

$.chosen.prototype.moveUp = function() {
    this.highlight(this.drop.find('.highlighted').prevAll('li.active-result:visible:first'));
};

$.chosen.prototype.moveDown = function() {
    this.highlight(this.drop.find('.highlighted').nextAll('li.active-result:visible:first'));
};

$.chosen.prototype.setValue = function(value) {
    value = (value === null || value === undefined) ? this.emptyValue : value;
    this.hidden.val(value);
};

$.chosen.prototype.select = function(id) {
    try {
        if (id === null || id === undefined) {
            throw 'empty';
        }
        index = id.split('_', 2);
        var elem = this.options[index[0]];
        if (index.length >= 2) {
            elem = elem.value[index[1]];
        }
        this.current.text(elem.label);
        this.setValue(elem.value);
        this.selected = id;
    } catch (err) {
        this.setValue(null);
        this.current.text(this.emptyLabel);
        this.selected = null;
    }
    
};
$.chosen.prototype.selectElem = function(elem) {
    if (elem.length === 1) {
        this.select(elem.attr('id').substring(this.prefix.length + 1));
    }
};

$(function() {
$('select.chosen').livequery(function(){a = new $.chosen($(this));});
});
