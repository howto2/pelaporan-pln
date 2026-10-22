$(document).ready(function(){
    // Efek hover pada kartu wisata
    $(".card").hover(
        function() {
            $(this).addClass('shadow-lg').css('cursor', 'pointer'); 
        }, function() {
            $(this).removeClass('shadow-lg');
        }
    );

    // Smooth scroll ke bagian paket wisata saat link di-klik
    $("a[href*='#']").on('click', function(event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top
        }, 500);
    });
});
