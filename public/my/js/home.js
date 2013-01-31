jQuery(function() {
    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        url: SITE_ROOT + 'upload'
    });

    // Load existing files:
    $.ajax({
        url: SITE_ROOT + 'view',
        dataType: 'json',
        context: $('#fileupload')[0]
    }).done(function(result) {
        $(this).fileupload('option', 'done')
                .call(this, null, {result: result});
    });

    $('body').on('click','.tag_submit',function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var val = form.find('.add_tag').val()
        $.getJSON($(this).attr('href'),{
            tags: form.find('.add_tag').val()
        }, function(response){
            form.find('.tags').append(' ' + response + ' ');
            form.find('.add_tag').val('').focus();
        })
    });
})