$(function() {

    var $container = $('#photo-browser');

    $container.imagesLoaded(function() {
        $container.masonry({
            itemSelector: '.photo',
            columnWidth: 235,
            isAnimated: true,
            isFitWidth: true
        });
    });

    $container.infinitescroll({
        navSelector: '#next-photo-page',
        nextSelector: '#next-photo-page a',
        itemSelector: '.photo',
        bufferPx: 400
    },
    // trigger Masonry as a callback
    function(newElements) {
        // hide new items while they are loading
        var $newElems = $(newElements).css({opacity: 0});
        // ensure that images load before adding to masonry layout
        $newElems.imagesLoaded(function() {
            // show elems now they're ready
            $newElems.animate({opacity: 1});
            $container.masonry('appended', $newElems, true);
        });
    }
    );
    
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