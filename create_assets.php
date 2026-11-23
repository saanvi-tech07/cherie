<?php
/**
 * CHÉRIE Asset Generator
 * Creates beautiful, Canva/Pinterest-grade vector artworks for categories, products, and banners.
 */

$baseDir = __DIR__;
$categoriesDir = $baseDir . '/assets/images/categories';
$productsDir = $baseDir . '/assets/images/products';
$imagesDir = $baseDir . '/assets/images';

foreach ([$categoriesDir, $productsDir, $imagesDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 1. Logo SVG
$logoSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 80" width="320" height="80">
  <defs>
    <linearGradient id="roseGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#49383C" />
      <stop offset="50%" stop-color="#C58A9C" />
      <stop offset="100%" stop-color="#49383C" />
    </linearGradient>
  </defs>
  <g transform="translate(160, 36)" text-anchor="middle">
    <!-- Monogram / Icon Heart Accent -->
    <path d="M 0 -22 C -6 -28 -14 -22 -14 -14 C -14 -6 0 2 0 6 C 0 2 14 -6 14 -14 C 14 -22 6 -28 0 -22 Z" fill="#C58A9C" opacity="0.85"/>
    <text y="14" font-family="Playfair Display, Georgia, serif" font-size="28" font-weight="600" letter-spacing="6" fill="#49383C">CHÉRIE</text>
    <text y="30" font-family="Poppins, sans-serif" font-size="7.5" font-weight="300" letter-spacing="2.5" fill="#C58A9C" text-transform="uppercase">A Little Love in Every Detail</text>
  </g>
</svg>';
file_put_contents($imagesDir . '/logo.svg', $logoSvg);

// 2. Hero Banner SVG
$heroSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 650" width="1600" height="650">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFF9F5" />
      <stop offset="50%" stop-color="#F7DCE5" />
      <stop offset="100%" stop-color="#EAE3F5" />
    </linearGradient>
    <radialGradient id="sunGlow" cx="80%" cy="30%" r="50%">
      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#F7DCE5" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#bgGrad)" />
  <circle cx="1250" cy="220" r="320" fill="url(#sunGlow)"/>
  
  <!-- Architectural Parisian Arches -->
  <g stroke="#C58A9C" stroke-width="1.2" fill="none" opacity="0.35">
    <path d="M 1150 650 L 1150 250 A 180 180 0 0 1 1510 250 L 1510 650" />
    <path d="M 1180 650 L 1180 270 A 150 150 0 0 1 1480 270 L 1480 650" />
    <circle cx="1330" cy="180" r="40" stroke="#C58A9C" stroke-dasharray="3,4"/>
  </g>

  <!-- Editorial Text Content -->
  <g transform="translate(140, 220)">
    <rect x="0" y="0" width="160" height="28" rx="14" fill="#C58A9C" opacity="0.15" />
    <text x="80" y="18" text-anchor="middle" font-family="Poppins, sans-serif" font-size="11" font-weight="600" letter-spacing="2" fill="#C58A9C">NEW COLLECTION 2026</text>
    <text x="0" y="80" font-family="Playfair Display, Georgia, serif" font-size="56" font-weight="600" fill="#49383C" letter-spacing="1">Romance in</text>
    <text x="0" y="145" font-family="Playfair Display, Georgia, serif" font-size="56" font-style="italic" font-weight="400" fill="#C58A9C" letter-spacing="1">Every Silhouette.</text>
    <text x="0" y="195" font-family="Poppins, sans-serif" font-size="16" font-weight="300" fill="#665257" letter-spacing="0.5">Handcrafted fabrics, dreamy pastel silhouettes, and timeless Parisian charm.</text>
    
    <!-- CTA Button Aesthetic Vector -->
    <g transform="translate(0, 240)">
      <rect width="200" height="52" rx="26" fill="#49383C" />
      <text x="100" y="32" text-anchor="middle" font-family="Poppins, sans-serif" font-size="13" font-weight="500" letter-spacing="1" fill="#FFFFFF">EXPLORE SHOP &rarr;</text>
    </g>
  </g>
</svg>';
file_put_contents($imagesDir . '/hero-banner.svg', $heroSvg);

// 3. Category Image Generator
$categories = [
    'cat_dresses' => ['name' => 'Dresses', 'color' => '#F7DCE5', 'icon' => '👗', 'desc' => 'Midis & Sundresses'],
    'cat_tops' => ['name' => 'Tops & Blouses', 'color' => '#FFF9F5', 'icon' => '👚', 'desc' => 'Broderie & Knits'],
    'cat_coord_sets' => ['name' => 'Co-ord Sets', 'color' => '#EAE3F5', 'icon' => '✨', 'desc' => 'Twinsets & Linen'],
    'cat_jeans' => ['name' => 'Denim & Jeans', 'color' => '#F2E8EB', 'icon' => '👖', 'desc' => 'High-Rise Vintage'],
    'cat_skirts' => ['name' => 'Skirts', 'color' => '#F7DCE5', 'icon' => '🩰', 'desc' => 'Pleated & Satin'],
    'cat_ethnic' => ['name' => 'Ethnic Wear', 'color' => '#FFF3F6', 'icon' => '🌸', 'desc' => 'Chikankari & Silk'],
    'cat_makeup' => ['name' => 'Boutique Makeup', 'color' => '#FCEBED', 'icon' => '💄', 'desc' => 'Dewy Glow & Tints'],
    'cat_skincare' => ['name' => 'Clean Skincare', 'color' => '#F5EEF9', 'icon' => '🧴', 'desc' => 'Rosewater & Peptides'],
    'cat_jewellery' => ['name' => 'Dainty Jewellery', 'color' => '#FBF7F0', 'icon' => '💍', 'desc' => '18K Vermeil & Pearls'],
    'cat_sunglasses' => ['name' => 'Sunglasses', 'color' => '#F7F0EA', 'icon' => '🕶️', 'desc' => 'Cat-Eye & Tortoise'],
    'cat_hair' => ['name' => 'Hair Accessories', 'color' => '#F7DCE5', 'icon' => '🎀', 'desc' => 'Silk Bows & Barrettes'],
    'cat_watches' => ['name' => 'Timepieces', 'color' => '#EFE8F5', 'icon' => '⌚', 'desc' => 'Rose Gold Mesh'],
    'cat_handbags' => ['name' => 'Handbags', 'color' => '#FDF0F4', 'icon' => '👜', 'desc' => 'Quilted & Baguettes'],
    'cat_totes' => ['name' => 'Canvas Totes', 'color' => '#FFF9F5', 'icon' => '🛍️', 'desc' => 'Embroidered & Straw'],
    'cat_backpacks' => ['name' => 'Backpacks', 'color' => '#F3EBF7', 'icon' => '🎒', 'desc' => 'Pastel Vegan Leather'],
    'cat_flats' => ['name' => 'Ballet Flats', 'color' => '#F9EFF2', 'icon' => '🥿', 'desc' => 'Cushioned Parisienne'],
    'cat_heels' => ['name' => 'Kitten Heels', 'color' => '#FFF5F8', 'icon' => '👠', 'desc' => 'Slingback & Satin'],
    'cat_sneakers' => ['name' => 'Sneakers', 'color' => '#F4EEF7', 'icon' => '👟', 'desc' => 'Pastel Court Tennis'],
    'cat_sandals' => ['name' => 'Sandals', 'color' => '#FBF5EE', 'icon' => '👡', 'desc' => 'Braided & Espadrille'],
    'cat_stationery' => ['name' => 'Stationery', 'color' => '#F5EEF9', 'icon' => '📖', 'desc' => 'Linen Gratitude Books'],
    'cat_decor' => ['name' => 'Room Decor', 'color' => '#FFF3F6', 'icon' => '🕯️', 'desc' => 'Soy Candles & Vases']
];

foreach ($categories as $filename => $info) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 600" width="500" height="600">
      <defs>
        <linearGradient id="catGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="' . $info['color'] . '" />
          <stop offset="100%" stop-color="#FFFFFF" />
        </linearGradient>
      </defs>
      <rect width="100%" height="100%" fill="url(#catGrad)" rx="24"/>
      
      <!-- Architectural Arch -->
      <path d="M 70 560 L 70 240 A 180 180 0 0 1 430 240 L 430 560" fill="none" stroke="#C58A9C" stroke-width="1.5" opacity="0.3"/>
      
      <!-- Icon Emblem -->
      <circle cx="250" cy="260" r="85" fill="#FFFFFF" filter="drop-shadow(0 8px 24px rgba(73, 56, 60, 0.08))"/>
      <text x="250" y="290" text-anchor="middle" font-size="64">' . $info['icon'] . '</text>
      
      <!-- Label -->
      <text x="250" y="420" text-anchor="middle" font-family="Playfair Display, Georgia, serif" font-size="30" font-weight="600" fill="#49383C">' . $info['name'] . '</text>
      <text x="250" y="455" text-anchor="middle" font-family="Poppins, sans-serif" font-size="13" font-weight="400" letter-spacing="1" fill="#C58A9C">' . $info['desc'] . '</text>
      <line x1="210" y1="480" x2="290" y2="480" stroke="#C58A9C" stroke-width="1.5" opacity="0.4"/>
    </svg>';
    file_put_contents($categoriesDir . '/' . $filename . '.svg', $svg);
}

// 4. Product Images Generator (42 Products)
$productThemes = [
    1 => ['name' => 'Blush Petal Chiffon Dress', 'cat' => 'DRESSES', 'color' => '#F7DCE5', 'icon' => '🌸', 'tag' => 'BESTSELLER'],
    2 => ['name' => 'Lavender Meadows Sundress', 'cat' => 'DRESSES', 'color' => '#EAE3F5', 'icon' => '💜', 'tag' => 'NEW'],
    3 => ['name' => 'Broderie Anglaise Blouse', 'cat' => 'TOPS', 'color' => '#FFF9F5', 'icon' => '✨', 'tag' => 'PARISIENNE'],
    4 => ['name' => 'Sweetheart Ribbed Knit Top', 'cat' => 'TOPS', 'color' => '#F2E4DF', 'icon' => '💕', 'tag' => 'ESSENTIAL'],
    5 => ['name' => 'Linen Vest & Shorts Set', 'cat' => 'CO-ORDS', 'color' => '#F7ECE1', 'icon' => '🌿', 'tag' => 'TRENDING'],
    6 => ['name' => 'Gingham Knit Twinset', 'cat' => 'CO-ORDS', 'color' => '#EFE4F7', 'icon' => '🧺', 'tag' => 'COZY CHIC'],
    7 => ['name' => 'Vintage High-Rise Straight Jeans', 'cat' => 'DENIM', 'color' => '#E3ECF5', 'icon' => '👖', 'tag' => 'CLASSIC'],
    8 => ['name' => 'Ecru Wide-Leg Sailor Pants', 'cat' => 'DENIM', 'color' => '#FAF5EE', 'icon' => '🐚', 'tag' => 'FLATTERING'],
    9 => ['name' => 'Pleated Satin Midi Skirt', 'cat' => 'SKIRTS', 'color' => '#F7E6DC', 'icon' => '🩰', 'tag' => 'LUXURY'],
    10 => ['name' => 'Floral Ruffle Wrap Mini Skirt', 'cat' => 'SKIRTS', 'color' => '#F9E2E8', 'icon' => '🌺', 'tag' => 'ROMANTIC'],
    11 => ['name' => 'Gulabi Chikankari Georgette Anarkali', 'cat' => 'ETHNIC', 'color' => '#FADAE3', 'icon' => '🪡', 'tag' => 'HANDCRAFTED'],
    12 => ['name' => 'Chanderi Silk Kurta Set', 'cat' => 'ETHNIC', 'color' => '#E5F4EE', 'icon' => '🍃', 'tag' => 'FESTIVE'],
    13 => ['name' => 'Velvet Petal Lip & Cheek Mousse', 'cat' => 'BEAUTY', 'color' => '#FAD2D8', 'icon' => '💄', 'tag' => 'CULT FAVORITE'],
    14 => ['name' => 'Rosy Glow Glass Highlighter Stick', 'cat' => 'BEAUTY', 'color' => '#FFF0F3', 'icon' => '✨', 'tag' => 'DEWY'],
    15 => ['name' => 'Damask Rose Hydrating Elixir', 'cat' => 'SKINCARE', 'color' => '#FCEBF0', 'icon' => '🌹', 'tag' => '100% PURE'],
    16 => ['name' => 'Peptide Overnight Cloud Cream', 'cat' => 'SKINCARE', 'color' => '#EAF1F8', 'icon' => '☁️', 'tag' => 'BARRIER CARE'],
    17 => ['name' => 'Aurelia Freshwater Pearl Choker', 'cat' => 'JEWELLERY', 'color' => '#FFFBF2', 'icon' => '🦪', 'tag' => '18K GOLD VERMEIL'],
    18 => ['name' => 'Celestial Starburst Huggie Hoops', 'cat' => 'JEWELLERY', 'color' => '#FFF7E6', 'icon' => '⭐', 'tag' => 'WATERPROOF'],
    19 => ['name' => 'Palais Cat-Eye Sunglasses', 'cat' => 'ACCESSORIES', 'color' => '#F4E7DA', 'icon' => '🕶️', 'tag' => 'UV400'],
    20 => ['name' => 'Golden Oval Wire Sunnies', 'cat' => 'ACCESSORIES', 'color' => '#F8F1E2', 'icon' => '👓', 'tag' => 'MINIMALIST'],
    21 => ['name' => 'Duchess Mulberry Silk Scrunchie', 'cat' => 'HAIR', 'color' => '#FADBE4', 'icon' => '🎀', 'tag' => '22-MOMME SILK'],
    22 => ['name' => 'French Pearl Organza Bow Clip', 'cat' => 'HAIR', 'color' => '#FDF4F8', 'icon' => '🤍', 'tag' => 'HANDMADE'],
    23 => ['name' => 'Petite Rose Gold Mesh Watch', 'cat' => 'WATCHES', 'color' => '#F5E4E8', 'icon' => '⌚', 'tag' => 'MOP DIAL'],
    24 => ['name' => 'Vintage Roman Square Watch', 'cat' => 'WATCHES', 'color' => '#ECE0D6', 'icon' => '🕰️', 'tag' => 'PARIS CHIC'],
    25 => ['name' => 'Signature Quilted Baguette Crossbody', 'cat' => 'BAGS', 'color' => '#FCE9EF', 'icon' => '👜', 'tag' => 'ICONIC'],
    26 => ['name' => 'Amélie Pastel Crescent Shoulder Bag', 'cat' => 'BAGS', 'color' => '#F9E5ED', 'icon' => '🌙', 'tag' => 'VEGAN LEATHER'],
    27 => ['name' => 'Café de Flore Embroidered Tote', 'cat' => 'TOTES', 'color' => '#FAF2EB', 'icon' => '🥐', 'tag' => '16OZ COTTON'],
    28 => ['name' => 'Riviera Woven Straw Market Tote', 'cat' => 'TOTES', 'color' => '#F7EBDC', 'icon' => '🧺', 'tag' => 'ARTISAN WOVEN'],
    29 => ['name' => 'Marseille Mini Backpack', 'cat' => 'BACKPACKS', 'color' => '#EAE0F5', 'icon' => '🎒', 'tag' => 'COMPACT'],
    30 => ['name' => 'Blossom Pastel Travel Daypack', 'cat' => 'BACKPACKS', 'color' => '#E8EFF9', 'icon' => '🌷', 'tag' => 'WATER REPELLENT'],
    31 => ['name' => 'Ballerine Square-Toe Ballet Flats', 'cat' => 'SHOES', 'color' => '#FCEFF2', 'icon' => '🩰', 'tag' => 'CLOUD CUSHION'],
    32 => ['name' => 'Geneva Embellished Pointed Mules', 'cat' => 'SHOES', 'color' => '#F7E5EE', 'icon' => '💎', 'tag' => 'EVENING GLAM'],
    33 => ['name' => 'Fleur Delicate Kitten Heels', 'cat' => 'SHOES', 'color' => '#FCEBED', 'icon' => '👠', 'tag' => 'COMFORT HEIGHT'],
    34 => ['name' => 'Clara Satin Bow Slingbacks', 'cat' => 'SHOES', 'color' => '#FFF1E8', 'icon' => '🎀', 'tag' => 'BRIDAL / BRUNCH'],
    35 => ['name' => 'Pastel Court Tennis Trainers', 'cat' => 'SHOES', 'color' => '#EFF2F8', 'icon' => '👟', 'tag' => 'RETRO TENNIS'],
    36 => ['name' => 'Luna Chunky Platform Sneakers', 'cat' => 'SHOES', 'color' => '#FAF6EE', 'icon' => '☁️', 'tag' => 'LIGHTWEIGHT'],
    37 => ['name' => 'Riviera Braided Leather Slides', 'cat' => 'SHOES', 'color' => '#F4EAE1', 'icon' => '👡', 'tag' => 'CONTOURED FOOTBED'],
    38 => ['name' => 'Isla Lace-Up Ribbon Flatforms', 'cat' => 'SHOES', 'color' => '#FBF3EB', 'icon' => '🌾', 'tag' => 'JUTE ROPE'],
    39 => ['name' => 'L’Amour Hardcover Linen Journal', 'cat' => 'STATIONERY', 'color' => '#F9E2E8', 'icon' => '📖', 'tag' => 'GOLD FOIL'],
    40 => ['name' => 'Pastel Dream Gel Pen Set', 'cat' => 'STATIONERY', 'color' => '#E8E3F5', 'icon' => '🖊️', 'tag' => '0.5MM QUICK DRY'],
    41 => ['name' => 'Bubble Cube Scented Soy Candle', 'cat' => 'ROOM DECOR', 'color' => '#FFF7F0', 'icon' => '🕯️', 'tag' => 'SOY WAX'],
    42 => ['name' => 'Aura Ceramic Scallop Wave Vase', 'cat' => 'ROOM DECOR', 'color' => '#F4EAE6', 'icon' => '🏺', 'tag' => 'CERAMIC ART']
];

for ($i = 1; $i <= 42; $i++) {
    $info = $productThemes[$i];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 750" width="600" height="750">
      <defs>
        <linearGradient id="pGrad' . $i . '" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="' . $info['color'] . '" />
          <stop offset="60%" stop-color="#FFFFFF" />
          <stop offset="100%" stop-color="' . $info['color'] . '" stop-opacity="0.5"/>
        </linearGradient>
      </defs>
      <rect width="100%" height="100%" fill="url(#pGrad' . $i . ')" rx="28"/>
      
      <!-- Architectural Framing -->
      <rect x="25" y="25" width="550" height="700" rx="20" fill="none" stroke="#C58A9C" stroke-width="1.2" opacity="0.3"/>
      
      <!-- Badge Tag -->
      <g transform="translate(45, 50)">
        <rect width="130" height="26" rx="13" fill="#49383C" />
        <text x="65" y="17" text-anchor="middle" font-family="Poppins, sans-serif" font-size="9" font-weight="600" letter-spacing="1" fill="#FFFFFF">' . $info['tag'] . '</text>
      </g>

      <!-- Center Aesthetic Vignette -->
      <g transform="translate(300, 360)">
        <!-- Subtle Halo Circles -->
        <circle r="160" fill="#FFFFFF" opacity="0.6"/>
        <circle r="130" fill="none" stroke="#C58A9C" stroke-width="1" stroke-dasharray="4,4" opacity="0.4"/>
        <circle r="100" fill="#FFFFFF" filter="drop-shadow(0 12px 30px rgba(73, 56, 60, 0.09))"/>
        
        <!-- Large Icon Showcase -->
        <text y="32" text-anchor="middle" font-size="80">' . $info['icon'] . '</text>
      </g>

      <!-- Bottom Card Metadata -->
      <g transform="translate(300, 610)">
        <text text-anchor="middle" font-family="Poppins, sans-serif" font-size="11" font-weight="600" letter-spacing="2" fill="#C58A9C">' . $info['cat'] . '</text>
        <text y="32" text-anchor="middle" font-family="Playfair Display, Georgia, serif" font-size="24" font-weight="600" fill="#49383C">' . $info['name'] . '</text>
        <text y="58" text-anchor="middle" font-family="Poppins, sans-serif" font-size="12" font-weight="300" letter-spacing="0.5" fill="#756266">Handcrafted Boutique Elegance • CHÉRIE</text>
      </g>
    </svg>';
    file_put_contents($productsDir . '/prod_' . $i . '_1.svg', $svg);
    if ($i === 1) {
        file_put_contents($productsDir . '/prod_1_2.svg', $svg);
    }
}

echo "All 42 product artworks, 21 categories, logo, and banners created successfully.\n";
