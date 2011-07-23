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
                c.scrollTop(elemTop+c.scrollTop());
            } else if (elemBottom > c.height()) {
                c.scrollTop(elemBottom - c.height() + c.scrollTop());
            }
        },
        escapeHTML: function(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
});

$.chosen.prototype.parseOptions = function(select) {
    var options    = [],
        selected   = null,
        value      = select.val(),
        emptyValue = select.attr('data-nette-empty-value'),
        emptyLabel = '';

    function parseOption(data, elem) {
        var opt = {label: elem.text(), value: elem.val(), html: elem.html()};
        if (opt.value !== emptyValue) {
            opt.normalized = stripAccent(opt.label).toLowerCase();
            data.push(opt);
            return (opt.value === value);
        } else {
            emptyLabel = opt.label;
            return false;
        }
    }

    select.children('option, optgroup').each(function() {
        var elem = $(this);
        if (elem.is('option')) {
            selected = parseOption(options, elem)
                     ? (options.length - 1).toString() : selected;
        } else {
            var group = {label: elem.attr('label'), value: []};
            group.html = $.escapeHTML(group.label);
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
    var i, j, visible, found;
    string = stripAccent(string).toLowerCase();
    for (i = 0; i < this.options.length; i++) {
        if (this.options[i].normalized === undefined) {
            visible = false;
            for (j = 0; j < this.options[i].value.length; j++) {
                found = (this.options[i].value[j].normalized.indexOf(string) !== -1);
                visible = visible || found;
                this.show(i+'_'+j, found);
            }
            this.show(i, visible);
        } else {
            this.show(i, (this.options[i].normalized.indexOf(string) !== -1));
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


$.chosen.prototype.createOption = function(id, html, classes) {
    return '<li id="' + id + '" class="' + classes + '">' + html + '</li>';
};
$.chosen.prototype.createOptions = function(prefix) {
    var ul_content = [],
        i = 0,
        j = 0;
    for (i = 0; i < this.options.length; i++) {
        var elem = this.options[i];
        if ($.isArray(elem.value)) {
            ul_content.push(this.createOption(prefix + '_' + i, elem.html, 'group-result'));
            for (j = 0; j < elem.value.length; j++) {
                ul_content.push(this.createOption(prefix + '_' + i + '_' + j, elem.value[j].html, 'active-result group-option'));
            }
        } else {
            ul_content.push(this.createOption(prefix + '_' + i, elem.html, 'active-result'));
        }
    }
    var ul = $("<ul class='chzn-results'>" + ul_content.join('') + '</ul>');
    var noMatch = $('<li class="no-results"></li>');
    noMatch.text(this.noMatch);
    this.noMatch = noMatch;
    this.noMatch.hide();

    var choose = this;
    var lis = $('li.active-result');
    lis.mouseenter(function () {choose.highlight($(this));});
    lis.click(function() {choose.selectElem($(this)); choose.rollIn();});

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
                .click(function() {
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
            choose.rollIn();
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
    var index, elem;
    try {
        if (id === null || id === undefined) {
            throw 'empty';
        }
        index = id.split('_', 2);
        elem = this.options[index[0]];
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
$('select.chosen').livequery(function(){new $.chosen($(this))});
});
