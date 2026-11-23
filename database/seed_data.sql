-- ===================================================
-- CHÉRIE E-COMMERCE SEED DATA
-- 21 Categories, 42 Products, Variants, Settings, Reviews
-- All prices in INR (₹)
-- ===================================================

USE `cherie_db`;

-- 1. Insert Default Settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('site_name', 'CHÉRIE', 'general', 'Official store name'),
('site_tagline', 'A Little Love in Every Detail.', 'general', 'Official brand slogan'),
('site_email', 'hello@cherieboutique.com', 'contact', 'Customer support email'),
('site_phone', '+91 98765 43210', 'contact', 'Customer support helpline'),
('site_address', 'Boutique Boulevard, Linking Road, Bandra West, Mumbai 400050', 'contact', 'Physical boutique headquarters'),
('currency_symbol', '₹', 'store', 'Store currency symbol'),
('currency_code', 'INR', 'store', 'ISO currency code'),
('free_shipping_threshold', '1499', 'shipping', 'Free shipping order minimum in INR'),
('flat_shipping_rate', '99', 'shipping', 'Standard shipping fee in INR'),
('enable_cod', '1', 'payment', 'Enable Cash on Delivery'),
('enable_razorpay', '1', 'payment', 'Enable Razorpay Payments'),
('instagram_url', 'https://instagram.com/cherieboutique', 'social', 'Instagram profile handle'),
('pinterest_url', 'https://pinterest.com/cherieboutique', 'social', 'Pinterest boutique board')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 2. Insert 21 Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_url`, `is_featured`, `sort_order`) VALUES
(1, 'Dresses', 'dresses', 'Romantic silhouettes, flowy sundresses, and evening midi elegance.', 'assets/images/categories/cat_dresses.svg', 1, 1),
(2, 'Tops', 'tops', 'Chic knit crop tops, puff-sleeve blouses, and French floral camis.', 'assets/images/categories/cat_tops.svg', 1, 2),
(3, 'Co-ord Sets', 'coord-sets', 'Effortlessly matching twinsets, brunch coordinates, and chic linen duos.', 'assets/images/categories/cat_coord_sets.svg', 1, 3),
(4, 'Jeans', 'jeans', 'Flattering high-waist vintage denim, straight leg, and wide-leg silhouettes.', 'assets/images/categories/cat_jeans.svg', 0, 4),
(5, 'Skirts', 'skirts', 'Flowy pleated midis, Parisian pleated minis, and tiered ruffle skirts.', 'assets/images/categories/cat_skirts.svg', 0, 5),
(6, 'Ethnic Wear', 'ethnic-wear', 'Delicate pastel anarkalis, mirror-work kurtas, and handcrafted chikankari.', 'assets/images/categories/cat_ethnic.svg', 1, 6),
(7, 'Makeup', 'makeup', 'Dewy cushion foundations, velvet lip tints, and blushing peach palettes.', 'assets/images/categories/cat_makeup.svg', 1, 7),
(8, 'Skincare', 'skincare', 'Rosewater mists, peptide hydration serums, and petal sleeping masks.', 'assets/images/categories/cat_skincare.svg', 1, 8),
(9, 'Jewellery', 'jewellery', '18k gold-plated huggies, dainty pearl necklaces, and celestial rings.', 'assets/images/categories/cat_jewellery.svg', 1, 9),
(10, 'Sunglasses', 'sunglasses', 'Retro cat-eye frames, tortoiseshell oval glasses, and golden wire sunnies.', 'assets/images/categories/cat_sunglasses.svg', 0, 10),
(11, 'Hair Accessories', 'hair-accessories', 'Silk scrunchies, French bow barrettes, and pearlescent claw clips.', 'assets/images/categories/cat_hair.svg', 0, 11),
(12, 'Watches', 'watches', 'Delicate mesh-strap rose gold timepieces and square vintage watches.', 'assets/images/categories/cat_watches.svg', 0, 12),
(13, 'Handbags', 'handbags', 'Structured baguette bags, pastel crescent clutches, and quilted shoulder bags.', 'assets/images/categories/cat_handbags.svg', 1, 13),
(14, 'Totes', 'totes', 'Embroidered canvas market totes, crochet shoulder totes, and shopper bags.', 'assets/images/categories/cat_totes.svg', 0, 14),
(15, 'Backpacks', 'backpacks', 'Mini vegan leather daypacks and travel-ready pastel rucksacks.', 'assets/images/categories/cat_backpacks.svg', 0, 15),
(16, 'Flats', 'flats', 'Pointed-toe French ballerinas, embellished mules, and soft leather loafers.', 'assets/images/categories/cat_flats.svg', 0, 16),
(17, 'Heels', 'heels', 'Strappy kitten heels, block heel slingbacks, and romantic satin pumps.', 'assets/images/categories/cat_heels.svg', 1, 17),
(18, 'Sneakers', 'sneakers', 'Chic pastel-accented retro tennis trainers and chunky platform sneakers.', 'assets/images/categories/cat_sneakers.svg', 0, 18),
(19, 'Sandals', 'sandals', 'Braided slide sandals, lace-up gladiator flats, and espadrille wedges.', 'assets/images/categories/cat_sandals.svg', 0, 19),
(20, 'Stationery', 'stationery', 'Linen-bound journals, gold foil gratitude planners, and pastel gel pens.', 'assets/images/categories/cat_stationery.svg', 0, 20),
(21, 'Room Decor', 'room-decor', 'Soy wax bubble candles, ceramic wave vases, and scallop picture frames.', 'assets/images/categories/cat_decor.svg', 1, 21)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. Insert 42 Realistic Sample Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_description`, `price`, `compare_at_price`, `stock_quantity`, `is_featured`, `is_new`, `rating_avg`, `reviews_count`) VALUES
-- Dresses (Cat 1)
(1, 1, 'Blush Petal Chiffon Midi Dress', 'blush-petal-chiffon-midi-dress', 'DRS-001', '<p>Float through sunlit days in our signature Blush Petal Chiffon Midi Dress. Featuring delicate tiering, sheer balloon sleeves, a smocked bodice for the ultimate comfort fit, and subtle gold threading that catches the light.</p><ul><li>100% Breathable Georgette Chiffon</li><li>Hand-embroidered neckline</li><li>Full cotton inner lining</li><li>Concealed side zipper</li></ul>', 'Dreamy floral chiffon midi with smocked bodice and romantic ruffled hem.', 2899.00, 3699.00, 35, 1, 1, 4.90, 28),
(2, 1, 'Lavender Meadows Tiered Sundress', 'lavender-meadows-tiered-sundress', 'DRS-002', '<p>An ode to Provence in bloom. Cut from certified organic linen-cotton blend with adjustable tie-up shoulder straps, side seam pockets, and an airy silhouette tailored for picnics and coastal getaways.</p>', 'Organic cotton linen tiered sundress with sweetheart neckline and tie straps.', 2499.00, 2999.00, 24, 1, 1, 4.80, 19),

-- Tops (Cat 2)
(3, 2, 'Charlotte Broderie Anglaise Blouse', 'charlotte-broderie-anglaise-blouse', 'TOP-001', '<p>The ultimate French-girl staple. Tailored with scallop eyelet broderie lace, pearlised front buttons, and romantic puff sleeves that can be worn on or off the shoulder.</p>', 'Romantic white broderie eyelet blouse with puff sleeves and scallop trim.', 1799.00, 2299.00, 42, 1, 1, 4.95, 34),
(4, 2, 'Sweetheart Ribbed Knit Crop Top', 'sweetheart-ribbed-knit-crop-top', 'TOP-002', '<p>A wardrobe foundational piece crafted from ultra-soft viscose ribbed knit. The sweetheart neckline flatters every collarbone, while the gentle stretch retains its shape wash after wash.</p>', 'Sculpting ribbed knit top in dusty rose with a flattering sweetheart neckline.', 1199.00, 1599.00, 50, 0, 1, 4.75, 16),

-- Co-ord Sets (Cat 3)
(5, 3, 'Rosé Linen Vest & Pleated Shorts Set', 'rose-linen-vest-and-pleated-shorts-set', 'CRD-001', '<p>Effortlessly sophisticated. This two-piece ensemble includes a tailored single-breasted waistcoat and matching high-waisted pleated shorts with an elasticated back waistband.</p>', 'Chic two-piece tailored linen waistcoat and matching pleated shorts.', 3499.00, 4299.00, 20, 1, 1, 4.90, 22),
(6, 3, 'Pastel Gingham Knit Cardigan Set', 'pastel-gingham-knit-cardigan-set', 'CRD-002', '<p>A vintage-inspired duo featuring a soft pastel lilac gingham knit camisole paired with a cropped boxy cardigan adorned with daisy buttons.</p>', 'Charming lilac gingham knit cami and cropped cardigan twinset.', 2999.00, 3799.00, 18, 0, 1, 4.85, 14),

-- Jeans (Cat 4)
(7, 4, 'Bespoke Vintage High-Rise Straight Jeans', 'bespoke-vintage-high-rise-straight-jeans', 'JNS-001', '<p>Crafted from authentic 100% rigid cotton denim that softens beautifully with wear. Designed with a timeless high-rise, ankle-grazing raw hem, and classic five-pocket styling.</p>', 'Authentic wash high-rise rigid denim jeans in light vintage blue.', 2799.00, 3499.00, 30, 0, 0, 4.70, 12),
(8, 4, 'Ecru Wide-Leg Sailor Pants', 'ecru-wide-leg-sailor-pants', 'JNS-002', '<p>Subtle cream tone denim designed with nautical twin-pocket front detailing, a high waist, and a fluid wide-leg silhouette that elongates the frame.</p>', 'Soft cream ecru denim wide-leg trouser jeans with twin front pockets.', 2599.00, 3199.00, 25, 0, 1, 4.80, 9),

-- Skirts (Cat 5)
(9, 5, 'Seraphina Pleated Satin Midi Skirt', 'seraphina-pleated-satin-midi-skirt', 'SKT-001', '<p>A cascading accordion-pleated midi skirt spun from lustrous champagne rose satin. Finished with a comfortable elasticated satin waistband for effortless movement.</p>', 'Fluid champagne rose accordion pleated satin midi skirt.', 2199.00, 2799.00, 28, 1, 0, 4.88, 25),
(10, 5, 'Margot Floral Ruffle Wrap Mini Skirt', 'margot-floral-ruffle-wrap-mini-skirt', 'SKT-002', '<p>Playful, feminine, and charming. Featuring a true wrap silhouette with side tie fastening, cascading double ruffles, and a soft ditsy floral print.</p>', 'Ditsy floral wrap mini skirt with cascade ruffle detailing.', 1599.00, 1999.00, 33, 0, 1, 4.65, 11),

-- Ethnic Wear (Cat 6)
(11, 6, 'Gulabi Chikankari Georgette Anarkali', 'gulabi-chikankari-georgette-anarkali', 'ETH-001', '<p>Handcrafted by master artisans in Lucknow. Exquisite tone-on-tone chikankari embroidery embellished with delicate mukaish mirror specks on lightweight blush pink georgette.</p>', 'Handcrafted blush pink chikankari georgette anarkali with organza dupatta.', 4999.00, 6499.00, 15, 1, 1, 5.00, 41),
(12, 6, 'Noor Pastel Mint Kurta & Palazzo Set', 'noor-pastel-mint-kurta-and-palazzo-set', 'ETH-002', '<p>Pure Chanderi silk blend kurta set with subtle zari borders, gota patti work along the neckline, paired with flowy wide-leg palazzos and a printed dupatta.</p>', 'Chanderi silk mint green kurta set with delicate zari and gota detailing.', 3899.00, 4899.00, 22, 0, 1, 4.90, 18),

-- Makeup (Cat 7)
(13, 7, 'Velvet Petal Lip & Cheek Mousse', 'velvet-petal-lip-and-cheek-mousse', 'MKP-001', '<p>A multi-use whipped airy cream mousse that melts onto cheeks and lips for a natural flushed glow. Infused with rosehip oil and vitamin E for deep hydration without stickiness.</p>', 'Featherlight airy mousse for a diffused, romantic natural flush.', 899.00, 1199.00, 60, 1, 1, 4.92, 57),
(14, 7, 'Rosy Glow Glass Highlighter Stick', 'rosy-glow-glass-highlighter-stick', 'MKP-002', '<p>Glides over high points of the face for a dewy, wet-look radiance free from chunky glitter. Formulated with squalane and micro-fine pearl pigments.</p>', 'Dewy balm highlighter stick for a luminous Korean glass skin finish.', 999.00, 1299.00, 45, 0, 1, 4.85, 23),

-- Skincare (Cat 8)
(15, 8, 'Damask Rose Water Hydrating Elixir', 'damask-rose-water-hydrating-elixir', 'SKN-001', '<p>Distilled from pure wild Kannauj roses. Calms redness, locks in hydration, and restores skin barrier vitality. Free from alcohol, synthetic fragrance, and parabens.</p>', '100% steam-distilled pure Damask rosewater face mist and toner.', 799.00, 999.00, 80, 1, 1, 4.96, 64),
(16, 8, 'Peptide & Squalane Overnight Cloud Cream', 'peptide-and-squalane-overnight-cloud-cream', 'SKN-002', '<p>Wake up to pillow-soft, plumper skin. Formulated with multi-molecular peptides, 5% squalane, and soothing centella asiatica.</p>', 'Whipped night moisturiser that repairs, firms and deeply hydrates.', 1499.00, 1899.00, 40, 0, 1, 4.88, 31),

-- Jewellery (Cat 9)
(17, 9, 'Aurelia Freshwater Pearl Choker', 'aurelia-freshwater-pearl-choker', 'JWL-001', '<p>Hand-knotted with genuine cultured baroque freshwater pearls and finished with an 18K gold vermeil toggle clasp. Timeless elegance reimagined for modern layering.</p>', 'Natural baroque freshwater pearl necklace with 18k gold vermeil clasp.', 1899.00, 2499.00, 25, 1, 1, 4.94, 38),
(18, 9, 'Celestial Starburst Huggie Earrings', 'celestial-starburst-huggie-earrings', 'JWL-002', '<p>Miniature 18K gold-plated sterling silver hoops adorned with sparkling pavé cubic zirconia stars. Hypoallergenic, nickel-free, and waterproof.</p>', '18k gold-plated huggies with twinkling pavé star drops.', 1299.00, 1699.00, 35, 0, 1, 4.82, 29),

-- Sunglasses (Cat 10)
(19, 10, 'Palais Cat-Eye Tortoiseshell Sunglasses', 'palais-cat-eye-tortoiseshell-sunglasses', 'SNG-001', '<p>Italian acetate sunglasses with premium UV400 polarized lenses that shield eyes while making an unforgettable high-fashion statement. Includes a soft leather pouch.</p>', 'Chic vintage-inspired tortoiseshell cat-eye sunglasses with UV400 protection.', 1599.00, 2199.00, 28, 0, 1, 4.78, 17),
(20, 10, 'Saint-Tropez Golden Oval Frames', 'saint-tropez-golden-oval-frames', 'SNG-002', '<p>Ultra-slim gold wire frames fitted with gradient sepia lenses. Featherlight silhouette with adjustable silicone nose pads.</p>', '90s minimalist oval metal wire sunglasses with gradient tint.', 1499.00, 1999.00, 30, 1, 0, 4.70, 14),

-- Hair Accessories (Cat 11)
(21, 11, 'Duchess Mulberry Silk Oversized Scrunchie', 'duchess-mulberry-silk-oversized-scrunchie', 'HAR-001', '<p>Spun from Grade 6A 22-momme pure mulberry silk. Protects delicate hair fibers from breakage, prevents ponytail creases, and adds instant luxury to any updo.</p>', 'Pure 22-momme mulberry silk oversized cloud scrunchie in blush.', 699.00, 899.00, 90, 1, 1, 4.98, 85),
(22, 11, 'Ophelia French Pearl Hair Bow Clip', 'ophelia-french-pearl-hair-bow-clip', 'HAR-002', '<p>Handcrafted organza ribbon bow studded with tiny faux pearls, anchored onto an alligator clip that grips securely without pulling.</p>', 'Delicate sheer organza hair bow with miniature pearl accents.', 599.00, 799.00, 50, 0, 1, 4.85, 27),

-- Watches (Cat 12)
(23, 12, 'Petite Étoile Rose Gold Mesh Watch', 'petite-etoile-rose-gold-mesh-watch', 'WAT-001', '<p>Crafted with a minimalist 28mm mother-of-pearl dial, rose gold stainless steel mesh strap, and precision Japanese quartz movement. Water-resistant to 3 ATM.</p>', 'Dainty 28mm mother-of-pearl watch with rose gold milanese mesh band.', 3299.00, 4299.00, 18, 1, 1, 4.91, 26),
(24, 12, 'Vintage Roman Square Leather Watch', 'vintage-roman-square-leather-watch', 'WAT-002', '<p>Evoking 1950s French glamour. Features a rectangular gold case with classic black Roman numerals and a genuine chocolate-brown croc-embossed calfskin strap.</p>', 'Rectangular vintage timepiece with chocolate croc-embossed leather band.', 2899.00, 3699.00, 20, 0, 1, 4.89, 21),

-- Handbags (Cat 13)
(25, 13, 'Chérie Signature Quilted Crossbody', 'cherie-signature-quilted-crossbody', 'BAG-001', '<p>Our crowning accessory. Handcrafted from butter-soft cruelty-free vegan leather with diamond quilting, bespoke golden hardware, and an adjustable chain strap.</p>', 'Luxury quilted vegan leather crossbody bag with polished gold turnlock.', 3699.00, 4799.00, 22, 1, 1, 4.97, 52),
(26, 13, 'Amélie Pastel Crescent Shoulder Bag', 'amelie-pastel-crescent-shoulder-bag', 'BAG-002', '<p>A sculptural silhouette that sits comfortably tucked beneath your arm. Magnetic flap closure with suede-finish interior lining and inner zip pouch.</p>', 'Minimalist crescent shoulder bag in soft blush pink vegan leather.', 2699.00, 3399.00, 27, 0, 1, 4.80, 19),

-- Totes (Cat 14)
(27, 14, 'Café de Flore Embroidered Canvas Tote', 'cafe-de-flore-embroidered-canvas-tote', 'TOT-001', '<p>Heavyweight 16oz cotton canvas tote featuring delicate French typographic embroidery, reinforced shoulder straps, a full zipper closure, and an internal laptop sleeve (up to 15").</p>', 'Structured heavy canvas tote with French cursive embroidery and laptop sleeve.', 1499.00, 1899.00, 45, 1, 1, 4.89, 43),
(28, 14, 'Riviera Woven Straw Market Tote', 'riviera-woven-straw-market-tote', 'TOT-002', '<p>Handwoven from natural palm leaves by fair-trade artisans, detailed with soft tan leather handles and a fabric drawstring lining to keep belongings safe.</p>', 'Handwoven natural straw basket tote with genuine leather handles.', 2199.00, 2799.00, 20, 0, 1, 4.75, 15),

-- Backpacks (Cat 15)
(29, 15, 'Marseille Mini Vegan Leather Backpack', 'marseille-mini-vegan-leather-backpack', 'BPK-001', '<p>Compact yet surprisingly roomy. Holds an iPad, cosmetics pouch, water bottle, and books. Features dual gold zippers, anti-theft back pocket, and convertible straps.</p>', 'Chic compact vegan leather backpack with gold hardware and multiple compartments.', 2799.00, 3499.00, 24, 0, 1, 4.82, 18),
(30, 15, 'Blossom Pastel Daypack', 'blossom-pastel-daypack', 'BPK-002', '<p>Water-resistant nylon backpack trimmed in soft lavender with padded ergonomic shoulder straps and insulated side pockets.</p>', 'Lightweight water-repellent nylon backpack in lavender mist.', 2299.00, 2899.00, 30, 0, 0, 4.70, 10),

-- Flats (Cat 16)
(31, 16, 'Ballerine Square-Toe Soft Ballet Flats', 'ballerine-square-toe-soft-ballet-flats', 'FLT-001', '<p>Buttery soft lambskin-feel faux leather with memory foam insole cushioning that guarantees blister-free walking all day long. Finished with a delicate satin cord bow.</p>', 'Classic Parisian square-toe ballet flats with cushioned memory foam.', 1999.00, 2599.00, 32, 1, 1, 4.91, 33),
(32, 16, 'Geneva Embellished Pointed Mules', 'geneva-embellished-pointed-mules', 'FLT-002', '<p>Slip into instant glamour. Pointed-toe silhouette adorned with an architectural crystal buckle, cushioned footbed, and non-slip rubber sole.</p>', 'Pointed-toe satin mules with a sparkling crystal brooch.', 2499.00, 3199.00, 20, 0, 1, 4.84, 16),

-- Heels (Cat 17)
(33, 17, 'Fleur Delicate Ankle-Strap Kitten Heels', 'fleur-delicate-ankle-strap-kitten-heels', 'HLS-001', '<p>The sweet spot between comfort and high fashion. A 2.2-inch sculpted kitten heel with an adjustable micro ankle buckle and pointed closed toe.</p>', '2.2-inch pointed kitten heels in blush rose with slender ankle strap.', 2899.00, 3699.00, 26, 1, 1, 4.93, 29),
(34, 17, 'Clara Satin Bow Block Slingbacks', 'clara-satin-bow-block-slingbacks', 'HLS-002', '<p>Crafted in champagne satin with an oversized bow across the vamp and a stable 2.5-inch block heel designed for weddings, dances, and celebrations.</p>', 'Champagne satin slingback block heels with romantic bow statement.', 3199.00, 3999.00, 18, 0, 1, 4.88, 22),

-- Sneakers (Cat 18)
(35, 18, 'Pastel Court Retro Tennis Sneakers', 'pastel-court-retro-tennis-sneakers', 'SNK-001', '<p>Fresh white vegan leather sneakers upgraded with soft blush and lavender suede overlays. Features perforated breathability and a plush cloud cushion insole.</p>', 'Clean white sneakers with soft pastel suede detailing and bouncy sole.', 2699.00, 3399.00, 35, 1, 1, 4.87, 36),
(36, 18, 'Luna Chunky Platform Low-Tops', 'luna-chunky-platform-low-tops', 'SNK-002', '<p>Elevate every casual outfit with a 1.5-inch lightweight platform sole, tonal laces, and gold eyelet details.</p>', 'Lightweight chunky platform sneakers in soft ecru cream.', 2899.00, 3599.00, 25, 0, 1, 4.79, 14),

-- Sandals (Cat 19)
(37, 19, 'Riviera Braided Leather Slide Sandals', 'riviera-braided-leather-slide-sandals', 'SND-001', '<p>Padded woven braids across the foot create a cozy hug. Features an ergonomic contoured footbed and genuine rubber grip sole.</p>', 'Buttery soft braided slides with arch support and contoured footbed.', 1699.00, 2199.00, 40, 1, 1, 4.86, 25),
(38, 19, 'Isla Lace-Up Espadrille Flatforms', 'isla-lace-up-espadrille-flatforms', 'SND-002', '<p>Traditional Spanish jute rope wrapping along a 2-inch flatform with long blush cotton ribbon straps that wind gracefully around the calves.</p>', 'Jute rope flatform sandals with wrap-around ribbon laces.', 2399.00, 2999.00, 20, 0, 1, 4.72, 11),

-- Stationery (Cat 20)
(39, 20, 'L’Amour Hardcover Linen Gratitude Journal', 'lamour-hardcover-linen-gratitude-journal', 'STN-001', '<p>Bound in premium blush bookcloth linen with embossed gold foil typography. Contains 180 pages of 120gsm bleed-proof paper, daily prompts, ribbon bookmark, and back envelope.</p>', 'Gold-foiled linen hardcover daily mindfulness and gratitude journal.', 999.00, 1399.00, 50, 1, 1, 4.96, 48),
(40, 20, 'Pastel Dream Gel Pen Set of 5', 'pastel-dream-gel-pen-set-of-5', 'STN-002', '<p>Smooth-flowing 0.5mm quick-dry black gel ink encased in soft-touch matte pastel barrel bodies with polished rose gold clips.</p>', '0.5mm soft-touch matte pastel aesthetic gel pen collection.', 499.00, 699.00, 75, 0, 1, 4.90, 37),

-- Room Decor (Cat 21)
(41, 21, 'Bubble Cube Soy Wax Scented Candle', 'bubble-cube-soy-wax-scented-candle', 'DCR-001', '<p>Hand-poured 100% natural soy wax candle infused with notes of French vanilla, warm amber, and English pear. The iconic Pinterest sculptural aesthetic for your bedside table.</p>', 'Aesthetic sculptural bubble cube candle with English pear & vanilla fragrance.', 699.00, 899.00, 60, 1, 1, 4.95, 62),
(42, 21, 'Aura Ceramic Scallop Wave Vase', 'aura-ceramic-scallop-wave-vase', 'DCR-002', '<p>Architectural organic ceramic vase with a matte speckled glaze and playful scalloped arches. Perfect for dried florals, fresh blooms, or as a standalone art piece.</p>', 'Sculptural wavy ceramic art vase in matte soft cream glaze.', 1799.00, 2299.00, 30, 1, 1, 4.93, 31)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 4. Product Images Mapping (Default Aesthetic Visuals)
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(1, 'assets/images/products/prod_1_1.svg', 1, 1),
(1, 'assets/images/products/prod_1_2.svg', 0, 2),
(2, 'assets/images/products/prod_2_1.svg', 1, 1),
(3, 'assets/images/products/prod_3_1.svg', 1, 1),
(4, 'assets/images/products/prod_4_1.svg', 1, 1),
(5, 'assets/images/products/prod_5_1.svg', 1, 1),
(6, 'assets/images/products/prod_6_1.svg', 1, 1),
(7, 'assets/images/products/prod_7_1.svg', 1, 1),
(8, 'assets/images/products/prod_8_1.svg', 1, 1),
(9, 'assets/images/products/prod_9_1.svg', 1, 1),
(10, 'assets/images/products/prod_10_1.svg', 1, 1),
(11, 'assets/images/products/prod_11_1.svg', 1, 1),
(12, 'assets/images/products/prod_12_1.svg', 1, 1),
(13, 'assets/images/products/prod_13_1.svg', 1, 1),
(14, 'assets/images/products/prod_14_1.svg', 1, 1),
(15, 'assets/images/products/prod_15_1.svg', 1, 1),
(16, 'assets/images/products/prod_16_1.svg', 1, 1),
(17, 'assets/images/products/prod_17_1.svg', 1, 1),
(18, 'assets/images/products/prod_18_1.svg', 1, 1),
(19, 'assets/images/products/prod_19_1.svg', 1, 1),
(20, 'assets/images/products/prod_20_1.svg', 1, 1),
(21, 'assets/images/products/prod_21_1.svg', 1, 1),
(22, 'assets/images/products/prod_22_1.svg', 1, 1),
(23, 'assets/images/products/prod_23_1.svg', 1, 1),
(24, 'assets/images/products/prod_24_1.svg', 1, 1),
(25, 'assets/images/products/prod_25_1.svg', 1, 1),
(26, 'assets/images/products/prod_26_1.svg', 1, 1),
(27, 'assets/images/products/prod_27_1.svg', 1, 1),
(28, 'assets/images/products/prod_28_1.svg', 1, 1),
(29, 'assets/images/products/prod_29_1.svg', 1, 1),
(30, 'assets/images/products/prod_30_1.svg', 1, 1),
(31, 'assets/images/products/prod_31_1.svg', 1, 1),
(32, 'assets/images/products/prod_32_1.svg', 1, 1),
(33, 'assets/images/products/prod_33_1.svg', 1, 1),
(34, 'assets/images/products/prod_34_1.svg', 1, 1),
(35, 'assets/images/products/prod_35_1.svg', 1, 1),
(36, 'assets/images/products/prod_36_1.svg', 1, 1),
(37, 'assets/images/products/prod_37_1.svg', 1, 1),
(38, 'assets/images/products/prod_38_1.svg', 1, 1),
(39, 'assets/images/products/prod_39_1.svg', 1, 1),
(40, 'assets/images/products/prod_40_1.svg', 1, 1),
(41, 'assets/images/products/prod_41_1.svg', 1, 1),
(42, 'assets/images/products/prod_42_1.svg', 1, 1);

-- 5. Product Variants (Sizes & Colors)
INSERT INTO `product_variants` (`product_id`, `variant_type`, `variant_value`, `additional_price`, `stock_quantity`, `sku`) VALUES
(1, 'Size', 'XS', 0.00, 8, 'DRS-001-XS'),
(1, 'Size', 'S', 0.00, 12, 'DRS-001-S'),
(1, 'Size', 'M', 0.00, 10, 'DRS-001-M'),
(1, 'Size', 'L', 0.00, 5, 'DRS-001-L'),
(2, 'Size', 'S', 0.00, 10, 'DRS-002-S'),
(2, 'Size', 'M', 0.00, 8, 'DRS-002-M'),
(2, 'Size', 'L', 0.00, 6, 'DRS-002-L'),
(3, 'Size', 'XS', 0.00, 10, 'TOP-001-XS'),
(3, 'Size', 'S', 0.00, 15, 'TOP-001-S'),
(3, 'Size', 'M', 0.00, 12, 'TOP-001-M'),
(3, 'Size', 'L', 0.00, 5, 'TOP-001-L'),
(4, 'Size', 'S', 0.00, 20, 'TOP-002-S'),
(4, 'Size', 'M', 0.00, 20, 'TOP-002-M'),
(4, 'Size', 'L', 0.00, 10, 'TOP-002-L'),
(5, 'Size', 'S', 0.00, 8, 'CRD-001-S'),
(5, 'Size', 'M', 0.00, 8, 'CRD-001-M'),
(5, 'Size', 'L', 0.00, 4, 'CRD-001-L'),
(7, 'Size', '26', 0.00, 6, 'JNS-001-26'),
(7, 'Size', '28', 0.00, 10, 'JNS-001-28'),
(7, 'Size', '30', 0.00, 8, 'JNS-001-30'),
(7, 'Size', '32', 0.00, 6, 'JNS-001-32'),
(13, 'Shade', 'Petal Pink', 0.00, 25, 'MKP-001-PP'),
(13, 'Shade', 'Nude Rose', 0.00, 20, 'MKP-001-NR'),
(13, 'Shade', 'Berry Kiss', 0.00, 15, 'MKP-001-BK'),
(31, 'Size', 'EU 36', 0.00, 8, 'FLT-001-36'),
(31, 'Size', 'EU 37', 0.00, 10, 'FLT-001-37'),
(31, 'Size', 'EU 38', 0.00, 8, 'FLT-001-38'),
(31, 'Size', 'EU 39', 0.00, 6, 'FLT-001-39'),
(33, 'Size', 'EU 36', 0.00, 6, 'HLS-001-36'),
(33, 'Size', 'EU 37', 0.00, 10, 'HLS-001-37'),
(33, 'Size', 'EU 38', 0.00, 6, 'HLS-001-38'),
(33, 'Size', 'EU 39', 0.00, 4, 'HLS-001-39'),
(35, 'Size', 'EU 36', 0.00, 10, 'SNK-001-36'),
(35, 'Size', 'EU 37', 0.00, 12, 'SNK-001-37'),
(35, 'Size', 'EU 38', 0.00, 8, 'SNK-001-38'),
(35, 'Size', 'EU 39', 0.00, 5, 'SNK-001-39');

-- 6. Insert Realistic Customer Reviews
INSERT INTO `reviews` (`product_id`, `customer_name`, `customer_email`, `rating`, `title`, `comment`, `is_approved`) VALUES
(1, 'Aanya Sharma', 'aanya.sharma@example.com', 5, 'The prettiest dress in my entire wardrobe!', 'I wore this for my birthday high tea and received endless compliments! The fabric is so airy and the lining is 100% soft cotton so it does not prick at all. CHÉRIE packaging felt like unboxing a Parisian gift.', 1),
(1, 'Rhea Sen', 'rhea.sen@example.com', 5, 'Pure poetry in clothing', 'The smocked bodice hugs like a dream and gives such a flattering waistline. 10/10 recommend!', 1),
(3, 'Mira Kapoor', 'mira.k@example.com', 5, 'Authentic French aesthetic', 'The eyelet embroidery is so delicate and high quality. Paired it with high waist jeans and pointed flats. Must buy!', 1),
(11, 'Tara Mehra', 'tara.mehra@example.com', 5, 'Heirloom quality chikankari', 'The blush color is pure elegance. Masterful artisan craftsmanship.', 1),
(13, 'Ishita Roy', 'ishita.roy@example.com', 5, 'Holy grail lip & cheek tint', 'It gives the softest diffused petal blur. Completely non-sticky and stays on for hours.', 1),
(25, 'Kavya Singhania', 'kavya.s@example.com', 5, 'Canva/Pinterest perfection', 'The quilted leather feels so plush and the gold chain has a lovely weight. Looks like a luxury designer piece.', 1);
