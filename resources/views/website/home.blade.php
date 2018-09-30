@extends('website.main_template')

@section('content')
        <div class="show-reel-carousel">
            <template id="carouselTemplate">
                <div class="show">
                    <span class="show-title">[title]</span>
                </div>
            </template>
        </div>

    <div class="container">
        {{-- Start display show sections --}}
        <div class="show-sections">
            <template id="showSectionTemplate">
                <div class="show-section">
                    <h4 class="show-reel-title">[title]</h4>

                    <div class="show-reel row disable-select">
                        <template id="showTemplate">
                            <div class="show-holder col s6 m3 l2">
                                <div class="show">
                                    <img src="https://www.thetvdb.com/banners/_cache/posters/85249-7.jpg" alt="One piece poster" class="show-poster">
                                    <span class="show-title">[title]</span>
                                    <span class="show-rating"><i class="material-icons left">star</i>4.5</span>
                                </div>
                            </div>
                        </template>

                        <template id="noShowsFoundTemplate">
                            <i class="material-icons left grey-text">error_outline</i> <span class="grey-text">Category is empty</span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection