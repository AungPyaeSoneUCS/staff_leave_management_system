@props([
    'name' => 'signature',
    'targets' => '',
    'required' => false,
    'label' => __('common.signature'),
    'hint' => __('common.sign_here'),
    'clearLabel' => __('common.clear_signature'),
])

@php
    $padId = 'signature-pad-' . \Illuminate\Support\Str::random(8);
    $dataId = $padId . '-data';
    $clearId = $padId . '-clear';
    $targetForms = $targets ? array_filter(array_map('trim', explode(',', $targets))) : [];
@endphp

<div class="mb-6">
    <label class="cu-label mb-2 text-center" for="{{ $padId }}">{{ $label }} @if($required)*@endif</label>
    <canvas id="{{ $padId }}"
            class="block mx-auto w-72 h-28 rounded-xl border-2 border-dashed border-slate-300 bg-white cursor-crosshair touch-none"
            aria-label="{{ $hint }}"></canvas>
    <div class="mt-2 flex items-center justify-center gap-4">
        <button type="button" id="{{ $clearId }}" class="cu-btn-secondary !py-2 !px-3 text-sm">{{ $clearLabel }}</button>
        <p class="cu-muted">{{ $hint }}</p>
    </div>
    <input type="hidden" id="{{ $dataId }}" value="">
    @error($name)
        <p class="cu-form-error mt-1">{{ $message }}</p>
    @enderror
</div>

@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('{{ $padId }}');
    const hiddenInput = document.getElementById('{{ $dataId }}');
    const clearBtn = document.getElementById('{{ $clearId }}');
    if (!canvas || !hiddenInput) return;

    const targetForms = @json($targetForms);
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let lastX = 0;
    let lastY = 0;
    let cssWidth = 0;
    let cssHeight = 0;

    function resize() {
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        cssWidth = rect.width;
        cssHeight = rect.height;
        canvas.width = cssWidth * dpr;
        canvas.height = cssHeight * dpr;
        ctx.scale(dpr, dpr);
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, cssWidth, cssHeight);
        if (hiddenInput.value) {
            const img = new Image();
            img.onload = function () {
                ctx.clearRect(0, 0, cssWidth, cssHeight);
                ctx.drawImage(img, 0, 0, cssWidth, cssHeight);
            };
            img.src = hiddenInput.value;
        }
    }

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches && e.touches[0];
        return {
            x: (touch ? touch.clientX : e.clientX) - rect.left,
            y: (touch ? touch.clientY : e.clientY) - rect.top
        };
    }

    function start(e) {
        e.preventDefault();
        drawing = true;
        const pos = getPos(e);
        lastX = pos.x;
        lastY = pos.y;
    }

    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        lastX = pos.x;
        lastY = pos.y;
        hiddenInput.value = canvas.toDataURL('image/png');
    }

    function end() {
        if (!drawing) return;
        drawing = false;
        hiddenInput.value = canvas.toDataURL('image/png');
    }

    function clear() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, cssWidth, cssHeight);
        hiddenInput.value = '';
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    if (clearBtn) {
        clearBtn.addEventListener('click', clear);
    }

    targetForms.forEach(function (formId) {
        const form = document.getElementById(formId);
        if (!form) return;
        let hidden = form.querySelector('input[name="{{ $name }}"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = '{{ $name }}';
            form.appendChild(hidden);
        }
        form.addEventListener('submit', function () {
            hidden.value = hiddenInput.value;
        });
    });

    window.addEventListener('resize', resize);
    resize();
})();
</script>
@endpush