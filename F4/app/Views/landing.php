<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Solar consultation, design, installation, and maintenance from Sun Son Solar in Pasig City.">
    <title>Sun Son Solar | Power your future</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/portal.css') ?>">
</head>
<body class="landing-page">
    <header class="site-header">
        <a class="brand" href="<?= site_url('/') ?>" aria-label="Sun Son Solar home">
            <img src="<?= base_url('assets/logo-sss.png') ?>" alt="">
            <span>Sun Son <b>Solar</b></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle><span></span><span></span></button>
        <nav class="site-nav" data-site-nav>
            <a href="#services">Services</a>
            <a href="#products">Products</a>
            <a href="#why-solar">Why solar</a>
            <a href="#about">Our story</a>
            <a class="nav-login" href="<?= site_url('login') ?>">Log in</a>
            <a class="button button-dark" href="<?= site_url('register') ?>">Get started <span aria-hidden="true">↗</span></a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <img class="hero-image" src="https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=2200&q=85" alt="Solar panels collecting sunlight across a wide rooftop">
            <div class="hero-shade"></div>
            <div class="hero-content">
                <p class="eyebrow light-eyebrow"><span></span> Solar made personal · Pasig City</p>
                <h1>Power your future<br>with solar.</h1>
                <p class="hero-description">Thoughtful solar systems, from the first conversation to the years of clean energy that follow.</p>
                <div class="hero-actions">
                    <a class="button button-sun" href="<?= site_url('register') ?>">Request a free quote <span aria-hidden="true">↗</span></a>
                    <a class="text-link light-link" href="#services">Explore our services <span aria-hidden="true">↓</span></a>
                </div>
                <div class="hero-note"><span class="note-mark">SS</span><span>Family-founded.<br>Built for the long run.</span></div>
            </div>
            <div class="hero-index">01 <span></span> 03</div>
        </section>

        <section class="benefit-band" id="why-solar">
            <div class="benefit-intro"><p class="eyebrow">A brighter kind of power</p><h2>Good energy<br>changes everything.</h2></div>
            <article class="benefit-item"><span class="benefit-number">01</span><h3>More control</h3><p>Generate your own electricity and rely less on changing grid prices.</p></article>
            <article class="benefit-item"><span class="benefit-number">02</span><h3>A lighter footprint</h3><p>Use a renewable source of energy and make everyday power cleaner.</p></article>
            <article class="benefit-item"><span class="benefit-number">03</span><h3>Made for your place</h3><p>Get a system planned around your roof, energy use, and priorities.</p></article>
        </section>

        <section class="section-wrap services-section" id="services">
            <div class="section-heading">
                <div><p class="eyebrow">From idea to uptime</p><h2>Here for every<br>step in the sun.</h2></div>
                <p class="section-summary">One local team to help you plan, install, and care for your solar system.</p>
            </div>
            <div class="service-grid">
                <?php foreach ($services as $index => $service): ?>
                    <article class="service-tile">
                        <div class="tile-top"><span>0<?= $index + 1 ?></span><span class="tile-arrow" aria-hidden="true">↗</span></div>
                        <p class="tile-category"><?= esc($service['category']) ?></p>
                        <h3><?= esc($service['name']) ?></h3>
                        <p><?= esc($service['description']) ?></p>
                        <a href="<?= site_url('register') ?>">Plan a visit <span aria-hidden="true">→</span></a>
                    </article>
                <?php endforeach ?>
            </div>
        </section>

        <section class="story-section" id="about">
            <div class="story-image-wrap"><img src="https://images.unsplash.com/photo-1497440001374-f26997328c1b?auto=format&fit=crop&w=1200&q=80" alt="Solar panels installed on a home rooftop"></div>
            <div class="story-copy"><p class="eyebrow">Rooted in Pasig</p><h2>Good work.<br>Good people.<br>Good energy.</h2><p>Sun Son Solar was founded by Katherine Sinagaraw and her father, Santino “Sonny” Sinagaraw. We bring practical guidance and dependable service to every solar project.</p><a class="text-link" href="#contact">Talk with our team <span aria-hidden="true">↗</span></a></div>
        </section>

        <section class="stories-section">
            <div class="stories-heading"><p class="eyebrow">Good things, shared</p><h2>Real work.<br>Real results.</h2></div>
            <?php if ($stories): ?><div class="story-quotes"><?php foreach ($stories as $story): ?><article class="story-quote"><span class="quote-mark" aria-hidden="true">“</span><blockquote><?= esc($story['quote']) ?></blockquote><p><?= esc($story['customer_display']) ?> <span>· <?= esc($story['service_name']) ?></span></p></article><?php endforeach ?></div><?php else: ?><p class="stories-empty">Customer stories will appear here as clients choose to share their solar experience.</p><?php endif ?>
        </section>

        <section class="section-wrap product-section" id="products">
            <div class="section-heading compact-heading">
                <div><p class="eyebrow">The right pieces, together</p><h2>Solar essentials.</h2></div>
                <p class="section-summary">Explore the equipment that helps turn sunlight into dependable power.</p>
            </div>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
                    <article class="product-row"><span class="product-mark" aria-hidden="true">☼</span><div><h3><?= esc($product['name']) ?></h3><p><?= esc($product['description']) ?></p></div><span class="product-category"><?= esc($product['category']) ?></span><a href="<?= site_url('register') ?>" aria-label="Ask about <?= esc($product['name']) ?>">↗</a></article>
                <?php endforeach ?>
            </div>
        </section>

        <?php if ($news): ?>
            <section class="news-section">
                <div class="news-heading"><p class="eyebrow">From our team</p><h2>Sun Son updates.</h2></div>
                <div class="news-list"><?php foreach ($news as $item): ?><article class="news-item"><span><?= esc(date('M j, Y', strtotime($item['created_at']))) ?></span><div><h3><?= esc($item['title']) ?></h3><p><?= esc($item['message']) ?></p></div><span aria-hidden="true">↗</span></article><?php endforeach ?></div>
            </section>
        <?php endif ?>

        <section class="faq-section">
            <div class="faq-heading"><p class="eyebrow">A few good questions</p><h2>Before the first panel.</h2></div>
            <div class="faq-list">
                <details><summary>How do I know if solar is right for my property?<span>+</span></summary><p>Start with a consultation. Our team can discuss your electricity use, roof, and goals before recommending next steps.</p></details>
                <details><summary>Can you help with design and permits?<span>+</span></summary><p>Yes. We offer system design and permitting support as part of the project journey.</p></details>
                <details><summary>What happens after installation?<span>+</span></summary><p>We also provide maintenance, repair, and monitoring services to help care for your system over time.</p></details>
            </div>
        </section>

        <section class="contact-band" id="contact"><div><p class="eyebrow">Your next step starts here</p><h2>Let’s make room<br>for more sunshine.</h2></div><a class="button button-dark" href="<?= site_url('register') ?>">Request a free quote <span aria-hidden="true">↗</span></a></section>
    </main>

    <footer class="site-footer">
        <a class="brand footer-brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/logo-sss.png') ?>" alt=""><span>Sun Son <b>Solar</b></span></a>
        <p>Family-founded solar for homes and businesses.<br>Pasig City, Philippines</p>
        <div class="footer-contact"><a href="tel:+639291230983">+63 929 123 0983</a><a href="<?= site_url('login') ?>">Customer & team login</a></div>
        <small>© <?= date('Y') ?> Sun Son Solar. All rights reserved.</small>
    </footer>
    <script src="<?= base_url('assets/js/portal.js') ?>" defer></script>
</body>
</html>