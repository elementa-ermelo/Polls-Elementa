{{-- Beloning direct na het versturen: bedankje met de einddatum, en confetti in het groen en geel van Centrum Ermelo. --}}
@php
    $maanden = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    $einde = $poll->closes_at ? $poll->closes_at->day . ' ' . $maanden[$poll->closes_at->month - 1] : null;
@endphp
<div class="card beloning" id="beloning">
    <p class="beloning-titel">Dank u wel voor het invullen</p>
    <p class="beloning-tekst">@if($einde)Wij verzamelen tot {{ $einde }} alle antwoorden.@else Wij verzamelen nog even alle antwoorden.@endif Daarna volgt uiteraard de uitslag.</p>
</div>

<style>
    .beloning { margin-bottom: 16px; padding: 28px 24px; text-align: center; background: #f0f9e8; border: 1px solid #c6e3a8; }
    .beloning-titel { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #2f6f2f; }
    .beloning-tekst { margin: 0; font-size: 16px; line-height: 1.6; color: #334155; }
</style>

<script nonce="{{ $cspNonce ?? '' }}">
// Confetti over het scherm en een kloppend logo (niet bij "minder beweging"); daarna in beeld met het bedankje.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () { var b = document.getElementById('beloning'); if (b) b.scrollIntoView({ block: 'center' }); }, 60);
    });
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var kleuren = ['#2f8f3a', '#8cc63f', '#c8d400', '#e6e000', '#ffffff'];
    var laag = document.createElement('div'); laag.className = 'confetti-laag'; document.body.appendChild(laag);
    for (var i = 0; i < 90; i++) {
        var c = document.createElement('i');
        c.style.left = (Math.random() * 100) + 'vw';
        c.style.background = kleuren[i % kleuren.length];
        c.style.animationDelay = (Math.random() * 0.8) + 's';
        c.style.animationDuration = (2.4 + Math.random() * 1.6) + 's';
        c.style.setProperty('--draai', (Math.random() * 720 - 360) + 'deg');
        c.style.setProperty('--zij', (Math.random() * 80 - 40) + 'px');
        laag.appendChild(c);
    }
    var logo = document.querySelector('.topnav-logo img');
    if (logo) logo.classList.add('klop');
    setTimeout(function () { laag.remove(); }, 5500);
})();
</script>
