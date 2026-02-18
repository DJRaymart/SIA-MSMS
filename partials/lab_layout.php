<?php $lab_base = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : ''; ?>
<div class="fixed inset-0 -z-30 pointer-events-none overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat blur-md" style="background-image: url('<?php echo htmlspecialchars($lab_base); ?>assets/images/bg_school.png'); transform: scale(1.1);"></div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        33% { transform: translate(5px, -35px) rotate(2deg); }
        66% { transform: translate(-5px, 20px) rotate(-2deg); }
    }

    .animate-scanline {
        position: absolute;
        animation: scanline 12s linear infinite;
        opacity: 0.6;
    }

    @keyframes scanline {
        0% { top: -10%; }
        100% { top: 110%; }
    }

    .invert {
        filter: invert(1);
    }
    
    img, .animate-pulse {
        will-change: transform, opacity;
        backface-visibility: hidden;
    }
</style>
