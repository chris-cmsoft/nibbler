<style>
body {
    margin: 0;
    min-height: 100vh;
    display: grid;
    place-items: center;
    font-family: system-ui, sans-serif;
    background: linear-gradient(#fff7fb, #eef7ff);
    color: #333;
}

.card {
    width: min(520px, 92vw);
    padding: 32px;
    border-radius: 28px;
    background: white;
    box-shadow: 0 20px 60px rgba(0,0,0,.12);
    text-align: center;
}

h1 {
    margin: 0 0 6px;
    font-size: 28px;
}

.status {
    margin: 0 0 24px;
    color: #666;
    min-height: 24px;
}

.stage {
    height: 300px;
    display: grid;
    place-items: center;
    position: relative;
}

.mouse {
    width: 280px;
    overflow: visible;
    animation: idle 2.5s ease-in-out infinite;
    transform-origin: center bottom;
}

.tail {
    animation: tailWag 2s ease-in-out infinite;
    transform-origin: 72px 178px;
}

.ear {
    transform-origin: center;
    animation: earTwitch 4s ease-in-out infinite;
}

.whisker {
    stroke-dasharray: 60;
    stroke-dashoffset: 0;
}

.food {
    opacity: 0;
    transform-origin: center;
}

.heart {
    opacity: 0;
    transform-origin: center;
}

.feed .mouse {
    animation: munch .8s ease-in-out 3;
}

.feed .food {
    animation: foodPop 2.2s ease forwards;
}

.feed .mouth {
    animation: chew .25s ease-in-out infinite alternate;
}

.pet .mouse {
    animation: happyBounce .55s ease-in-out 4;
}

.pet .heart {
    animation: heartFloat 1.8s ease forwards;
}

.pet .eyes {
    transform-origin: center;
    animation: blinkHappy 1.8s ease forwards;
}

.buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 8px;
}

button {
    border: 0;
    border-radius: 999px;
    padding: 13px 20px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    background: #ffe1ec;
    color: #7a2944;
    transition: transform .15s ease, box-shadow .15s ease;
}

button:nth-child(2) {
    background: #dff4ff;
    color: #1d5570;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,.12);
}

@keyframes idle {
    0%, 100% { transform: translateY(0) rotate(-1deg); }
    50% { transform: translateY(-8px) rotate(1deg); }
}

@keyframes tailWag {
    0%, 100% { transform: rotate(-6deg); }
    50% { transform: rotate(8deg); }
}

@keyframes earTwitch {
    0%, 88%, 100% { transform: rotate(0); }
    90% { transform: rotate(-8deg); }
    92% { transform: rotate(6deg); }
}

@keyframes munch {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(10px) scale(1.04, .96); }
}

@keyframes chew {
    from { transform: scaleY(1); }
    to { transform: scaleY(.35); }
}

@keyframes foodPop {
    0% { opacity: 0; transform: translate(0, 20px) scale(.4); }
    20% { opacity: 1; transform: translate(0, 0) scale(1); }
    80% { opacity: 1; transform: translate(80px, -8px) scale(.9); }
    100% { opacity: 0; transform: translate(115px, -16px) scale(.2); }
}

@keyframes happyBounce {
    0%, 100% { transform: translateY(0) scale(1); }
    45% { transform: translateY(-18px) scale(1.04); }
}

@keyframes heartFloat {
    0% { opacity: 0; transform: translateY(20px) scale(.5); }
    20% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-90px) scale(1.2); }
}

@keyframes blinkHappy {
    0%, 100% { transform: scaleY(1); }
    30%, 65% { transform: scaleY(.22); }
}
</style>
<template>
  <main class="card">
    <h1>Nibble the Mouse</h1>
    <p class="status" id="status">Nibble is waiting for attention.</p>

    <section class="stage" id="stage">
      <svg class="mouse" viewBox="0 0 320 260" aria-label="Animated cute mouse">
        <g class="tail">
          <path d="M82 177 C18 178 10 120 46 107 C73 97 84 123 62 133"
                fill="none" stroke="#d79aa8" stroke-width="14" stroke-linecap="round"/>
        </g>

        <ellipse cx="170" cy="162" rx="88" ry="62" fill="#d8d8df"/>
        <ellipse cx="224" cy="143" rx="62" ry="54" fill="#dedee6"/>

        <g class="ear">
          <circle cx="198" cy="91" r="33" fill="#d8d8df"/>
          <circle cx="198" cy="91" r="21" fill="#f3b7c4"/>
        </g>
        <g class="ear">
          <circle cx="252" cy="98" r="29" fill="#d8d8df"/>
          <circle cx="252" cy="98" r="18" fill="#f3b7c4"/>
        </g>

        <ellipse cx="247" cy="153" rx="28" ry="24" fill="#f5f5f7"/>
        <circle cx="267" cy="154" r="8" fill="#ef8ca4"/>

        <g class="eyes">
          <circle cx="224" cy="132" r="6" fill="#333"/>
          <circle cx="260" cy="135" r="6" fill="#333"/>
        </g>

        <g stroke="#555" stroke-width="3" stroke-linecap="round">
          <path class="whisker" d="M265 160 L306 148"/>
          <path class="whisker" d="M266 166 L309 166"/>
          <path class="whisker" d="M265 172 L304 185"/>
        </g>

        <path class="mouth" d="M246 169 Q254 177 263 169"
              fill="none" stroke="#555" stroke-width="4" stroke-linecap="round"/>

        <ellipse cx="126" cy="212" rx="22" ry="10" fill="#c5c5cc"/>
        <ellipse cx="215" cy="210" rx="24" ry="10" fill="#c5c5cc"/>

        <g class="food">
          <circle cx="70" cy="208" r="18" fill="#f4b35e"/>
          <circle cx="64" cy="202" r="3" fill="#9c6b2f"/>
          <circle cx="76" cy="213" r="3" fill="#9c6b2f"/>
          <circle cx="80" cy="200" r="2.5" fill="#9c6b2f"/>
        </g>

        <g class="heart">
          <path d="M154 78
                   C142 60 111 68 116 96
                   C119 116 154 136 154 136
                   C154 136 189 116 192 96
                   C197 68 166 60 154 78Z"
                fill="#ff7aa8"/>
        </g>
      </svg>
    </section>

    <div class="buttons">
      <button id="feedBtn">Feed</button>
      <button id="petBtn">Pet</button>
    </div>
  </main>

  <script>
    const stage = document.getElementById('stage');
    const status = document.getElementById('status');

    function react(type, message) {
      stage.classList.remove('feed', 'pet');
      void stage.offsetWidth;
      stage.classList.add(type);
      status.textContent = message;

      window.clearTimeout(stage.resetTimer);
      stage.resetTimer = window.setTimeout(() => {
        stage.classList.remove(type);
        status.textContent = 'Nibble is content and curious.';
      }, 2200);
    }

    document.getElementById('feedBtn').addEventListener('click', () => {
      react('feed', 'Nibble munches happily.');
    });

    document.getElementById('petBtn').addEventListener('click', () => {
      react('pet', 'Nibble loves the attention.');
    });
  </script>
</template>
