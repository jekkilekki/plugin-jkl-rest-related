/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
( function( $ ) {
    $( '.get-related-posts' ).on( 'click', function( e ) {
        e.preventDefault();
        
        $( 'a.get-related-posts' ).remove();
        $( '.ajax-loader' ).show();
        
        // Get Postdata from PHP
        var post_id = Postdata.post_id;
        var json_url = Postdata.json_url;

        // The AJAX
        $.ajax({
            dataType: 'json',
            url: json_url,
            success: function (result) {
            //        alert("SUCCESS!!!");
            },
            error: function (xhr, ajaxOptions, thrownError) {
            //        alert(xhr.statusText);
            //        console.log(xhr.responseText);
            //        alert(xhr.status);
            //        alert(thrownError);
            }
        })
        .done( function( response ) {
            console.log( response );
            $( '#related-posts' ).append( '<h1 class="related-header">' + Postdata.header + '</h1>' );
    
            // Loop through each of the related posts
            $.each( response, function( index, object ) {
                
                if( object.id == post_id ) {
                    return;
                }
                
                var feat_img = '';
                if( object.featured_image !== 0 ) {
                    feat_img =      '<figure class="related-featured">' +
                                    '<img src="' + object.featured_image_src + '" alt="">' +
                                    '</figure>';
                }
                // Set up the HTML to be added
                var related_loop =  '<aside class="related-post clear">' +
                                    '<a href="' + object.link + '">' +
                                    '<h1 class="related-post-title">' + object.title.rendered + '</h1>' +
                                    '<div class="related-author">by <em>' + object.author_name + '</em></div>' +
                                    '<div class="related-excerpt">' +
                                    feat_img +
                                    object.excerpt.rendered +
                                    '</div>' +
                                    '</a>' +
                                    '</aside><!-- .related-post -->';
                
                $( '.ajax-loader' ).remove();
                // Append HTML to existing content
                $( '#related-posts' ).append( related_loop );
            });
        })
        .fail( function( jqXHR, textStatus ) {
            console.log( "Fail" );
            console.log( json_url );
            console.log( textStatus );
        })
        .always( function() {
            console.log( "Complete" );
        });

    });  
}) ( jQuery );

