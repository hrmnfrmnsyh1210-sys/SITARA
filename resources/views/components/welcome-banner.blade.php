@props(['mascot' => 'maskot1', 'title' => 'Selamat datang', 'subtitle' => ''])

<div class="card border-0 mb-4 overflow-hidden" data-aos="fade-up"
     style="background:linear-gradient(120deg,#1763c9 0%,#1e3a8a 55%,#0d9488 100%)">
    <div class="card-body text-white d-flex align-items-center justify-content-between p-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <p class="mb-0 opacity-75">{{ $subtitle }}</p>
        </div>
        <img src="{{ asset('assets/' . $mascot . '.png') }}" alt="" class="animate-float d-none d-sm-block"
             style="height:120px;width:auto;margin:-1.5rem 0 -2.5rem">
    </div>
</div>
