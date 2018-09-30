$(document).ready(function(){
    var MAX_SHOWS_PER_CATEGORY = 6;

    // Get data from explore page
    kurozora.api.explore(function(success, data) {
        // Successful request
        if(success) {
            // Get the banner/carousel template
            var carouselTemplate = $('.show-reel-carousel #carouselTemplate').html();

            // Remove template
            $('.show-reel-carousel #carouselTemplate').remove();

            // Loop through banners
            $.each(data.banners, function(i, banner) {
                // Create and append banners to carousel
                var newElement = $.parseHTML(carouselTemplate);

                $(newElement).attr('data-anime-id', banner.id);
                $(newElement).css('background-image', 'url(' + banner.background +')');
                $(newElement).find('.show-title').text(banner.title);

                $('.show-reel-carousel').append(newElement);
            });

            // Initialize the carousel
            initFrontpageCarousel();

            // Get the show section template
            var showSectionTemplate = $('.show-sections #showSectionTemplate').html();

            // Loop through categories
            $.each(data.categories, function(i, cat) {
                // Create show section
                var newSection = $.parseHTML(showSectionTemplate);
                var showsForThisSection = 0;

                $(newSection).find('.show-reel-title').text(cat.title);

                // Get the show template
                var showTemplate = $(newSection).find('.show-reel #showTemplate').html();

                // Loop through shows
                $.each(cat.shows, function(i, show) {
                    if(showsForThisSection === MAX_SHOWS_PER_CATEGORY)
                        return false;

                    var newShow = $.parseHTML(showTemplate);

                    $(newShow).attr('data-anime-id', show.id);
                    $(newShow).find('.show-title').text(show.title);
                    $(newShow).find('.show-poster').attr('src', show.poster_thumbnail);
                    $(newShow).find('.show-rating').html('<i class="material-icons left">star</i>' + show.average_rating);

                    // Append show
                    $(newSection).find('.show-reel').append(newShow);
                    showsForThisSection++;
                });

                // No shows in the category
                if(!cat.shows.length) {
                    var noShowsTemplate = $(newSection).find('.show-reel #noShowsFoundTemplate').html();

                    $(newSection).find('.show-reel').append($.parseHTML(noShowsTemplate));
                }

                // Append section
                $('.show-sections').append(newSection);
            });

        }
        // Error
        else console.log(data);
    });
});

// Initializes the frontpage carousel
function initFrontpageCarousel() {
    $('.show-reel-carousel').slick({
        centerMode: true,
        centerPadding: '60px',
        slidesToShow: 3,
        autoplay: true,
        autoplaySpeed: 3000,
        infinite: true,
        pauseOnFocus: false,
        pauseOnHover: false,
        arrows: false,
        dots: true,
        responsive: [
            {
                breakpoint: 1000,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 1
                }
            }
        ]
    });
}