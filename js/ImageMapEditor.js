let shape_click = function (e) {
    let shape = $(this);

    if (shape.hasClass('multiple_choice')) {
        shape.toggleClass('selected');
        
        let max = $("#max_answers").val();
        let current = shape.parent().find(".selected").length;
        
        if (max > 0 && current > max) {
            shape.removeClass('selected');
        }
        
    } else {
        shape.parents('.imagemap_editor').find('svg .selected').removeClass(
                'selected');
        shape.addClass('selected');
    }

    let selected = [];

    shape.parents('.imagemap_editor').find('svg .selected').each(
            function (index, item) {
                selected.push($(item).attr('data-value'));
            });

    $('#answer').val(selected.join(','));
};

let resize_svg_to_img = function() {
    let img = $('.imagemap_editor > img');
    let svg = $('.imagemap_editor > svg');

    svg.width(img.width());
    svg.height(img.height());    
}

$(window).load(resize_svg_to_img);
$(window).resize(resize_svg_to_img);

$(document).on("click",
               ".imagemap_editor rect, .imagemap_editor ellipse, .imagemap_editor polygon",
               shape_click);