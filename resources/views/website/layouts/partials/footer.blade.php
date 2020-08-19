<footer id="globalfooter" class="bg-grayBlue-500  {{ (Route::currentRouteName() == 'api.legal.privacy') ? '' : 'bg-opacity-25'}} w-full">
    <div class="container mx-auto px-4 flex">
        <section class="gf-footer">
            <div class="gf-footer-legal">
                <div class="gf-legal-copyright">Copyright © 2020 Kurozora B.V. All rights reserved</div>
                <div class="gf-legal-links">
                    <a class="gf-legal-link" href="{{ route('legal.privacy') }}">Privacy Policy</a>
                </div>
            </div>
        </section>
    </div>
</footer>

{{-- JavaScript --}}
<script src="{{ asset('js/app.js') }}"></script>
