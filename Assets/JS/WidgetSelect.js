/*!
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

function getValueTypeParent(parent) {
    var term = '';

    if (parent.is('input')) {
        term = parent.val();
    } else if (parent.is('select')) {
        term = parent.find('option:selected').val();
    } else if (parent.is('checkbox') && parent.prop("checked")) {
        term = parent.val();
    } else if (parent.is('radio')) {
        term = parent.find(':checked').val();
    } else if (parent.is('textarea')) {
        term = parent.val();
    }

    return term;
}

function widgetSelectGetData(select) {
    select.html('');

    var data = {
        action: 'select',
        formname: $('input[name="activetab"]').val(),
        term: getValueTypeParent($('[name="'+select.attr('parent')+'"]')),
        field: select.attr("data-field"),
        fieldcode: select.attr("data-fieldcode"),
        fieldfilter: select.attr("data-fieldfilter"),
        fieldtitle: select.attr("data-fieldtitle"),
        source: select.attr("data-source"),
    };

    $.ajax({
        method: "POST",
        url: window.location.href,
        data: data,
        dataType: "json",
        success: function (results) {
            results.forEach(function (element) {
                var selected = (element.key == select.attr('value')) ? 'selected' : '';
                select.append('<option value="'+element.key+'" '+selected+'>'+element.value+'</option>');
            });
        },
        error: function (msg) {
            alert(msg.status + " " + msg.responseText);
        }
    });
}

$(document).ready(function () {
    $('.parentSelect').each(function(){
        var attr = $(this).attr('parent');
        if (attr !== 'undefined' && attr !== false && attr !== '') {
            var select = $(this);
            var parent = select.attr('parent');

            $('select[name="'+parent+'"]').on('change', function(){
                widgetSelectGetData(select);
            });

            widgetSelectGetData(select);
        }
    });
});