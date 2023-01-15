<main>
    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <x-picture>
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/made_with_love.webp') }}"  alt="Made with Love by 2 Students." />
        </x-picture>

        <div class="flex flex-col flex-wrap mt-8 sm:flex-row">
            @foreach($this->users as $user)
                @php
                    switch ($user->id) {
                    case 1:
                        $backgroundColor = '#F9F9F9';
                        $usernameColor = '#1F2937';
                        $text = '(ex) Co-Developer';
                        break;
                    case 2:
                        $backgroundColor = '#1F2937';
                        $usernameColor = '#F9F9F9';
                        $text = 'Owner/Developer';
                        break;
                    case 380:
                        $backgroundColor = '#F1C9BF';
                        $usernameColor = '#1F2937';
                        $text = 'Event Manager';
                        break;
                    case 668:
                        $backgroundColor = '#ECECEC';
                        $usernameColor = '#1F2937';
                        $text = 'Queen of Kurozora (VTuber)';
                        break;
                    default:
                        $backgroundColor = 'bg-white';
                        $usernameColor = '#1F2937';
                        $text = '';
                    }
                @endphp

                <a class="pt-2 pr-2 pb-2 pl-2 sm:w-2/4" href="{{ route('profile.details', $user) }}">
                    <div class="px-6 py-6 rounded-lg shadow-lg text-center" style="background: {{ $backgroundColor }};">
                        <div class="flex justify-center mb-3">
                            <picture class="relative w-40 h-40 rounded-full shadow-lg overflow-hidden">
                                <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" title="{{ $user->username }}">

                                <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-full"></div>
                            </picture>
                        </div>

                        <h2 class="text-xl font-medium" style="color: {{ $usernameColor }};">{{ $user->username }}</h2>

                        <span class="text-orange-500 block mb-5">{{ $text }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</main>
