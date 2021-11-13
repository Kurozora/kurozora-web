<main>
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <img class="w-full h-32 object-cover object-center rounded-lg shadow-lg sm:h-40" src="{{ asset('images/static/promotional/made_with_love_banner.webp') }}"  alt="Made with Love by 2 Students." />

        <div class="flex flex-col mt-8 sm:flex-row">
            @foreach($users as $user)
                @php
                    switch ($user->id) {
                    case 1:
                        $backgroundColor = 'bg-[#F9F9F9]';
                        $textColor = 'text-gray-800';
                        break;
                    case 2:
                        $backgroundColor = 'bg-gray-800';
                        $textColor = 'text-[#F9F9F9]';
                        break;
                    default:
                        $backgroundColor = 'bg-white';
                        $textColor = 'text-gray-700';
                    }
                @endphp

                <a class="p-2 sm:w-2/4" href="{{ route('profile.details', $user) }}">
                    <div class="{{ $backgroundColor }} px-6 py-8 rounded-lg shadow-lg text-center">
                        <div class="flex justify-center mb-3">
                            <picture class="relative w-40 h-40 rounded-full shadow-lg overflow-hidden">
                                <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $user->username }} Profile Picture" title="{{ $user->username }}">

                                <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-full"></div>
                            </picture>
                        </div>

                        <h2 class="text-xl font-medium {{ $textColor }}">{{ $user->username }}</h2>

                        <span class="text-orange-500 block mb-5">Co-Developer</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</main>
