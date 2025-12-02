<div class="space-y-2">
    <div class="text-sm text-gray-600">Health Declaration Signature: sign inside the box using your mouse or touch. Click "Save Signature" to attach it.</div>
    <div class="border rounded-md p-2 bg-white">
        <canvas id="sigCanvasHealth" width="600" height="200" style="touch-action: none;" class="w-full"></canvas>
    </div>
    <div class="flex gap-2">
        <button type="button" class="fi-btn fi-color-gray" onclick="window.__sigPadHealthClear()">Clear</button>
        <button type="button" class="fi-btn fi-color-primary" onclick="window.__sigPadHealthSave()">Save Signature</button>
    </div>
    <div id="sigHealthPreviewCtn" class="mt-2 hidden">
        <div class="text-sm text-gray-600">Saved preview:</div>
        <img id="sigHealthPreview" src="" alt="Signature Preview" class="border rounded-md" />
    </div>
</div>
<script>
    (function(){
        const canvas = document.getElementById('sigCanvasHealth');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let last = null;

        ctx.strokeStyle = '#111827';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';

        function pos(e){
            const rect = canvas.getBoundingClientRect();
            return {
                x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
                y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
            };
        }

        function start(e){ drawing = true; last = pos(e); e.preventDefault(); }
        function move(e){ if(!drawing) return; const p = pos(e); ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(p.x, p.y); ctx.stroke(); last = p; e.preventDefault(); }
        function end(e){ drawing = false; e.preventDefault(); }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseleave', end);

        canvas.addEventListener('touchstart', start, {passive:false});
        canvas.addEventListener('touchmove', move, {passive:false});
        canvas.addEventListener('touchend', end, {passive:false});

        window.__sigPadHealthClear = function(){ ctx.clearRect(0,0,canvas.width, canvas.height); document.getElementById('sigHealthPreviewCtn').classList.add('hidden'); };
        window.__sigPadHealthSave = function(){
            const dataURL = canvas.toDataURL('image/png');
            const preview = document.getElementById('sigHealthPreview');
            preview.src = dataURL;
            document.getElementById('sigHealthPreviewCtn').classList.remove('hidden');
            // bind to Livewire state
            @this.set('data.SignatureHealthDataUrl', dataURL);
        };
    })();
</script>

