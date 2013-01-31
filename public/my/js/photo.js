$(function() {

    var $container = $('#similars');
    // rating bindings
    $container.on('click','.vote', function(e){
        e.preventDefault();
        var vote = 0;
        if ($(this).hasClass('love'))
            vote = 10;
        else if ($(this).hasClass('hate'))
            vote = 1;
        
        var photo_id = $(this).closest('.photo').attr('data-photo');
        
        jQuery.post(SITE_ROOT + '/rate',{
            photo: photo_id,
            vote: vote
        }, function(response){
            if (response.success){
                //
            }
        })
    })
});