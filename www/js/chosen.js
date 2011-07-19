$.extend({
        chosen: function(element) {
                    this.data = new Array();
                    this.selected = null;
                    this.value = element.val();
                    this.label = null;
                    this.name = element.attr('name');
                    this.parseOptions(element);
                    this.prefix = element.attr('id');
                    this.control = this.createControl(this.prefix);
                    $(element).replaceWith(this.control);
                    this.setWidth(this.drop.width() + 10);
                    this.drop.hide();
        },
        scrollIntoView: function(element) {
            container = element.parent();
            var scrollPosition = container.scrollTop();
            var containerBottom = container.height(); 
            var elemTop = element.position().top;
            var elemBottom = elemTop + element.outerHeight(); 
            if (elemTop < 0) {
                container.scrollTop(elemTop+scrollPosition);
            } else if (elemBottom > containerBottom) {
                container.scrollTop(elemBottom - containerBottom + scrollPosition);
            }
        }
});

$.chosen.prototype.parseOptions = function (select) {
    var data = this.data;
    var choose = this;
    select.children('option, optgroup').each(function() {
        var elem = $(this);
        if (elem.is('option')) {
            var opt = {label: elem.text(), value: elem.val()};
            opt.normalized = $.removeDiacritics(opt.label).toLowerCase();
            var i = data.push(opt) - 1;
            if (opt.value == choose.value) {
                choose.selected = i;
            }
        } else if (elem.is('optgroup')) {
            var group = {label: elem.attr('label'), value: new Array()};
            var i = data.push(group) - 1;
            elem.children('option').each(function() {
                var opt = $(this);
                opt = {label: opt.text(), value: opt.val()};
                opt.normalized = $.removeDiacritics(opt.label).toLowerCase();
                var j = group['value'].push(opt) - 1;
                if (opt.value == choose.value) {
                    choose.selected = i + '_' + j;
                    choose.label = opt.label;
                }
            });
        }
    });
};

$.chosen.prototype.filter = function(string) {
    string = $.removeDiacritics(string).toLowerCase();
    for (var i in this.data) {
        if (this.data[i].normalized == undefined) {
            var visible = false;
            for (var j in this.data[i].value) {
                if (this.data[i].value[j].normalized.indexOf(string) == -1) {
                    this.hide(i+'_'+j);
                } else {
                    visible = true;
                    this.show(i+'_'+j);
                }
            }
            if (visible) {
                this.show(i);
            } else {
                this.hide(i);
            }
        } else {
            if (this.data[i].normalized.indexOf(string) == -1) {
                this.hide(i);
            } else {
                this.show(i);
            }
        }
    }
    if (!this.drop.find('.highlighted').is(':visible')) {
        this.highlight(this.drop.find('li.active-result:visible:first'));
    }

};

$.chosen.prototype.hide = function(index) {
    $('#' + this.prefix + '_' + index).hide();
};

$.chosen.prototype.show = function(index) {
    $('#' + this.prefix + '_' + index).show();
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
        li.click(function(e) {choose.select($(this)); choose.rollIn();});
    }
    return li;
};
$.chosen.prototype.createOptions = function (prefix) {
    var ul = $("<ul class='chzn-results'></ul>");
    var data = this.data;
    var choose = this;
    for (var i in data) {
        var elem = data[i];
        if ($.isArray(elem.value)) {
            var li = this.createOption(prefix + '_' + i, elem.label, true);
            ul.append(li);
            for (var j in elem.value) {
                var subelem = elem.value[j];
                var subli = this.createOption(prefix + '_' + i + '_' + j, subelem.label, false);
                subli.addClass('group-option');
                ul.append(subli);
            }
        } else {
            var li = this.createOption(prefix + '_' + i, elem.label, false);
            ul.append(li);
        }
    }
    return ul;
};

$.chosen.prototype.createControl = function (prefix) {
    var container = $('<div class="chzn-container chzn-container-single"></div>');
    container.attr('id', prefix);
    this.hidden = $('<input type="hidden" />');
    this.hidden.attr('name', this.name);
    this.hidden.val(this.value);
    container.append(this.hidden);
    this.roller = $('<a class="chzn-single"></a>');
    this.current = $('<span></span>');
    this.current.text(this.label);
    this.roller.append(this.current);
    this.roller.append($('<div><b></b></div>'));
    var choose = this;
    this.roller.click(function(e) {
        if (choose.isRolled()) {
            choose.rollIn();
        } else {
            choose.rollOut();
        }
    });
    container.append(this.roller);
    this.drop = $('<div class="chzn-drop"></div>');
    var search = $('<div class="chzn-search"></div>');
    this.search = $('<input type=text />');
    search.append(this.search);
    this.search.change(function() {
        choose.filter($(this).val());
    });
    this.search.keyup(function() {
        $(this).change();
        console.log('pruser');
    });
    this.search.click(function() {
        $(this).change();
    });
    this.options = this.createOptions(prefix);
    this.drop.append(search);
    this.drop.append(this.options);
    container.append(this.drop);
    container.keydown(function(e){
        if (e.keyCode == 38) {
            if (choose.isRolled()) {
                choose.moveUp();
            } else {
                choose.rollOut();
            }
        } else if (e.keyCode == 40) {
            if (choose.isRolled()) {
                choose.moveDown();
            } else {
                choose.rollOut();
            }
        } else if (e.keyCode == 27) {
            choose.rollIn()
            e.preventDefault();
            e.stopImmediatePropagation();
        } else if (e.keyCode == 13) {
            choose.select(choose.drop.find('.highlighted'));
            choose.rollIn();
            e.preventDefault();
        }
    });

    return container;
};

$.chosen.prototype.setWidth = function(width)
{
    this.control.width(width);
    //this.roller.width(width-10);
    this.search.width(width-22);
    this.drop.width(width-2);
};

$.chosen.prototype.highlight = function (elem) {
    if (elem.length != 1) {
        return;
    }
    this.options.find('.highlighted').removeClass('highlighted');
    elem.addClass('highlighted');
    $.scrollIntoView(elem);
};

$.chosen.prototype.isRolled = function() {
    if (this.control.hasClass('chzn-container-active')) {
        return true;
    } else {
        return false;
    }
};
$.chosen.prototype.rollOut = function() {
    if (this.isRolled()) {
        return;
    }
    this.control.addClass('chzn-container-active');
    this.roller.addClass('chzn-single-with-drop');
    this.drop.show();
    this.highlight(this.drop.find('#' + this.prefix + '_' + this.selected));
    this.search.focus();
};

$.chosen.prototype.rollIn = function() {
    if (!this.isRolled()) {
        return;
    }
    this.control.removeClass('chzn-container-active');
    this.roller.removeClass('chzn-single-with-drop');
    this.drop.hide();
};

$.chosen.prototype.moveUp = function() {
    this.highlight(this.drop.find('.highlighted').prevAll('li.active-result:visible:first'));
};

$.chosen.prototype.moveDown = function() {
    this.highlight(this.drop.find('.highlighted').nextAll('li.active-result:visible:first'));
};

$.chosen.prototype.select = function(elem) {
    if (elem.length != 1) {
        return;
    }
    this.selected = elem.attr('id').substring(this.prefix.length + 1);
    var split = this.selected.indexOf('_');
    if (split == -1) {
        this.value = this.data[this.selected].value;
    } else {
        this.value = this.data[this.selected.substr(0, split)].value[this.selected.substr(split+1)].value;
    }
    this.hidden.val(this.value);
    this.label = elem.text();
    this.current.text(this.label);
};

$.chosen.prototy

$(function() {
$('select.chosen').livequery(function(){a = new $.chosen($(this));});
});

