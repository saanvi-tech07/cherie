<?php
/**
 * CHÉRIE — Our Boutique Story
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Our Story & Artisan Heritage';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 80px;">
    
    <!-- Hero Editorial Header -->
    <div style="background:linear-gradient(135deg, var(--blush), var(--lavender)); border-radius:var(--radius-lg); padding:70px 40px; text-align:center; border:1px solid var(--border-color); margin-bottom:60px;">
        <span class="section-tagline">Artisan Boutique Philosophy</span>
        <h1 style="font-size:44px; margin-bottom:16px;">The CHÉRIE Story</h1>
        <p style="font-size:16px; color:#5c474c; max-width:700px; margin:0 auto; line-height:1.7;">
            We believe that elegance is never loud. It lives in the delicate scallop of a collar, the whisper of pure georgette, and the lingering fragrance of wild damask roses.
        </p>
    </div>

    <!-- Editorial Columns -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center; margin-bottom:80px;">
        <div>
            <span class="section-tagline">How It Began</span>
            <h2 style="font-size:32px; margin-bottom:18px;">A Love Letter to Romantic Fashion</h2>
            <p style="font-size:15px; color:#634f54; line-height:1.8; margin-bottom:16px;">
                Founded on the belief that everyday clothing should feel like wearable poetry, CHÉRIE began as an intimate atelier in Mumbai blending classic French tailoring with master artisan techniques from across India.
            </p>
            <p style="font-size:15px; color:#634f54; line-height:1.8;">
                From delicate hand-done Chikankari embroidery to fluid pleated midi skirts and hypoallergenic 18K vermeil baroque pearls, each design is curated with obsessive care. When you wear CHÉRIE, you carry a little piece of art made with love.
            </p>
        </div>
        <div style="background:var(--white); padding:30px; border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:var(--shadow-medium); text-align:center;">
            <div style="font-size:60px; margin-bottom:16px;">🌸</div>
            <h3 style="font-size:22px; margin-bottom:10px;">A Little Love in Every Detail</h3>
            <p style="font-size:14px; color:#78666a; line-height:1.7;">
                Every parcel we dispatch is sealed with fragrant dried rose petals, wrapped in soft blush tissue paper, and tied with a satin ribbon. Because unboxing your dreams should always feel extraordinary.
            </p>
        </div>
    </div>

    <!-- 3 Pillars Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:30px; margin-bottom:60px;">
        <div class="card-box" style="margin-bottom:0; text-align:center; padding:35px 25px;">
            <div style="font-size:36px; margin-bottom:12px;">🌿</div>
            <h3 style="font-size:18px; margin-bottom:8px;">Conscious Textiles</h3>
            <p style="font-size:13.5px; color:#705d61; line-height:1.6;">
                We prioritize certified natural cottons, organic linens, and breathable georgettes that respect your delicate skin and the planet.
            </p>
        </div>
        <div class="card-box" style="margin-bottom:0; text-align:center; padding:35px 25px;">
            <div style="font-size:36px; margin-bottom:12px;">🪡</div>
            <h3 style="font-size:18px; margin-bottom:8px;">Artisan Partnerships</h3>
            <p style="font-size:13.5px; color:#705d61; line-height:1.6;">
                Working directly with generational heritage embroiderers and jewelers to provide fair wages, safe ateliers, and sustainable livelihoods.
            </p>
        </div>
        <div class="card-box" style="margin-bottom:0; text-align:center; padding:35px 25px;">
            <div style="font-size:36px; margin-bottom:12px;">✨</div>
            <h3 style="font-size:18px; margin-bottom:8px;">Cruelty-Free Beauty</h3>
            <p style="font-size:13.5px; color:#705d61; line-height:1.6;">
                Our lip tints, overnight balms, and room fragrances are 100% cruelty-free, alcohol-free, paraben-free, and dermatologically tested.
            </p>
        </div>
    </div>

    <div style="text-align:center; margin-top:40px;">
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary" style="padding:15px 40px;">Explore Our Newest Creations &rarr;</a>
    </div>

</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
