<div>

    <div class="clnbd c8tys c58e1 c9gkl cqikb">
        <!-- Page wrapper -->
        <div class="c4ihh cm95r ckoci cbp69 csqne">

            <!-- Site header -->
            <header class="c48fs cv0zi c2f91">
                <div class="c9zbf cfacu c0spu cnm0k">
                    <div class="c1ls3 cqho4 clp4z csqne cqs3o">

                        <!-- Site branding -->
                        <div class="cqfuo cg571 rounded-full shrink-0 grow-0 h-8 w-8">
                            <!-- Logo -->
                            <a class="cdfr0 cq3a6" href="#" aria-label="{{ filament()->getBrandName() }}">
                                @if ($favicon = filament()->getFavicon())
                                    <img src="{{ $favicon }}" alt="{{ filament()->getBrandName() }}"/>
                                @endif
                            </a>
                        </div>

                        <!-- Desktop navigation -->
                        <nav class="csqne c8c54">

                            <!-- Desktop sign in links -->
                            <ul class="cqho4 c392o cmh34 csqne c8c54">
                                <li>
                                    <a class="cxymg cvqf0 cqho4 crqt4 c38bd ckc7d csqne cr309 ciyzd" href="{{filament()->getPanel('recruitiq-candidate')->getLoginUrl()}}">Sign in</a>
                                </li>
                                <li>
                                    <a class="crp1m czlxp chrwa cxa4q c9csv ckncn c0ndj c91mf chlg0" href="{{filament()->getPanel('recruitiq-candidate')->getRegistrationUrl()}}">Sign up</a>
                                </li>
                            </ul>

                        </nav>

                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="c8c54">
                <section class="cpsdf c78an" style="background-image:url('{{ asset('images/BG-HIGHWAY.png') }}'); background-size:cover; background-position:center; min-height: 320px;">
                    <div class="bg-black/50">
                        <div class="c9zbf cfacu c0spu cnm0k py-10">
                            <div class="flex items-center gap-4 text-white">
                                <img src="{{ asset('images/RECRUITIQ-LOGO.png') }}" alt="RecruitIQ" class="h-10">
                            </div>
                            <h1 class="c5zpx c9gkl c8hbn cn95v text-white mt-4">Apply to {{$jobDetail?->postingTitle}}</h1>
                        </div>
                    </div>
                </section>

                <!-- Page content -->
                <section>
                    <div class="c9zbf cfacu c0spu cnm0k">
                        <div class="cijys c73bz crfxz ctz8u">
                            <div class="cysna cqlk9">
                                <!-- Main content -->
                                <div class="cmgbb">
                                    <!-- Job description -->
                                    <div class="ctz8u">
                                        <div class="cnog5">
                                            <a class="cvqf0 crqt4" href="{{route('career.landing_page')}}"><span class="c8b8n">&lt;-</span> All Jobs</a>
                                        </div>
                                        <h5 class="c5zpx c9gkl cn95v">Applying for {{$jobDetail?->postingTitle}}</h5>
                                        <!-- Job description -->
                                        <div class="c5rk9 coxki">
                                            <form wire:submit.prevent="create">
                                                {{ $this->form }}
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

</div>
