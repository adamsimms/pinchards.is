<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';
require_once __DIR__ . '/lib/partials/citation.php';

$copyrightYear = (int) date('Y');

pinchard_layout_head("Pinchard's Island — About Cloudberry", [
    'description' => 'Cloudberry is a solar-powered, off-the-grid photography project documenting Pinchard\'s Island, Newfoundland — one photograph per hour.',
    'body_class' => 'info-page',
    'json_ld' => [
        [
            '@type' => 'AboutPage',
            'name' => 'About Cloudberry',
            'description' => 'Cloudberry is a solar-powered, off-the-grid photography project documenting Pinchard\'s Island, Newfoundland — one photograph per hour.',
            'url' => pinchard_absolute_url('/info.php'),
            'mainEntity' => [
                '@type' => 'CreativeWork',
                'name' => 'Cloudberry',
                'description' => 'An off-the-grid, solar-powered, long-term photography project documenting Pinchard\'s Island, Newfoundland.',
                'creator' => [
                    ['@type' => 'Person', 'name' => 'Adam Simms'],
                    ['@type' => 'Person', 'name' => 'Angela Gabereaux'],
                ],
            ],
        ],
    ],
]);

pinchard_layout_nav(['active' => 'info']);
?>
    <h1 class="visually-hidden">About Cloudberry</h1>
    <div class="info-hero">
        <img src="images/info/pano.jpg" class="img-fluid info_img" alt="View from Precious Memories cabin on Pinchard's Island">
    </div>

    <div class="info-layout">
        <aside class="info-toc" aria-label="Table of contents">
            <nav>
                <a href="#about" class="is-active" aria-current="true">About</a>
                <a href="#why">Why</a>
                <a href="#where">Where</a>
                <a href="#how">How</a>
                <a href="#hardware">Hardware</a>
                <a href="#installation">Installation</a>
                <a href="#who">Who</a>
                <a href="#citation">Citation</a>
                <a href="#contact">Contact</a>
            </nav>
        </aside>

        <main class="info-main">
    <div class="how_section" id="about">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <p><strong>Cloudberry</strong> is an off the grid, solar powered, long term photography project. Using a GoPro, a Raspberry Pi and a USB cellular modem, the system we designed takes one photograph per hour between 8 AM and 8 PM each day and uploads the images via a cellular network to this website.</p>

                <p>The photographs depict a view of Pinchard's Island from a small, family owned cabin named "Precious Memories." The island, only accessible by boat for a few weeks of the year, is home to a few cabins that resettled residents use while picking bake apples (the local term for cloudberries) during the summer months.</p>

                <p>The view is static–in the sense that camera is always capturing the same frame; however, the lighting of the frame can vary drastically from one image to another. These photographs are a continuation of the moments the locals spend in the cabin glancing out the window at the surrounding landscape.</p>
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <img src="images/info/precious-moments.jpg" class="img-fluid info_img" alt="Precious Memories cabin">
            </div>
        </div>
    </div>

    <div class="how_section" id="why">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>Okay, but why Pinchard's Island?</h3>

                <p><a href="http://adamsim.ms/" target="_blank" rel="noopener noreferrer">Adam</a> has been photographing <a href="http://adamsim.ms/pinchards-island/" target="_blank" rel="noopener noreferrer">Pinchard's Island</a> and its previous residents for several years. The harsh weather conditions and the extreme remoteness of the island made it difficult access the island year round and take images over long periods of time. Cloudberry grew from the desire to be able to photograph the island throughout the year from anywhere via the internet.</p>

                <img src="images/info/pinchards-island-sisters.jpg" class="img-fluid info_img" alt="Pinchard's Island Sisters">

                <p>Shortly after Newfoundland joined Canada as it's 10th province, Pinchard's Island was <a href="http://adamsim.ms/resettlement/" target="_blank" rel="noopener noreferrer">resettled</a> in an attempt to modernize the province. Adam has been documenting the return of his grandmother, along with her brothers and sisters, to this island each summer in an attempt to write the future of resettlement by reviving traditions and create new ones.</p>
            </div></div>
        </div>
    </div>

    <div class="how_section" id="where">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>Where is Pinchard's Island?</h3>
                <p>Pinchard's Island is situated at the northern edge of Bonavista Bay, Newfoundland, Canada. It was one of the first settled sites in Bonavista Bay but is no longer inhabited.</p>
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="info-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d20855.569930035457!2d-53.48462861918841!3d49.20157937004537!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4b75e272b66fd9bf%3A0xe011372b0d414175!2sPinchards+Island%2C+New-Wes-Valley%2C+NL+A0G+3L0%2C+Canada!5e0!3m2!1sen!2sgr!4v1503767433902" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map of Pinchard's Island"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="how_section" id="how">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>How?</h3>

                <p>Adam and Angela met early May 2017 to briefly discuss the possibility of collaborating together. The idea was loose, but the goal was to take photos of the island remotely, upload the images via the cellular network and access them from anywhere. We both shared connections to Newfoundland, and a passion for art and technology, so we set out to see what was possible.</p>

                <p>Our initial research showed that there were a lot of possibilities as to how we could approach the project, but it was clear from the beginning that every decision would result in many constraints that would affect every decision we made. Power, temperature, weather, sunlight, data limits, storage, remoteness, were all components that constantly determined decision making and the methods in which we worked.</p>

                <p>We used <a href="http://www.trello.com/" target="_blank" rel="noopener noreferrer">Trello</a> to plan every aspect of the project, communicate, and document ongoing research:</p>
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <a href="https://trello.com/b/eYzSO4qQ/shutter-island" target="_blank" rel="noopener noreferrer"><img src="images/info/trello.jpg" class="img-fluid info_img" alt="Trello project board"></a>
            </div>
        </div>
    </div>

    <div class="how_section">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <p>Designing a system that worked was only the start of the project. Every slight adjustment that we made to the system, such as moving from electricity to solar power, putting the USB cellular modem in a case or using different USB cables introduced new problems that we had to constantly monitor and resolve. Once we felt confident in our system, we had to be realistic that once the system was installed on the island, we would not be able to physically be there to troubleshoot any problem that might arise. This forced us to evaluate the entire solution and implement different components to help reduce the risk factor of the project.</p>

                <img src="images/info/notebook.jpg" class="img-fluid info_img" alt="Project notebook">

                <p>The entire system took us approximately 3 months to build. This includes the initial idea, research, system design, installation, and final production code. Below you'll find a system diagram and an outline of all the hardware and software used to create Cloudberry.</p>

                <h3 id="hardware">The Cloudberry System</h3>
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <a href="https://www.figma.com/file/GvUAbr6vcpJ2Ruk1T1q4e20Z/Shutter-Island?node-id=35%3A116" target="_blank" rel="noopener noreferrer"><img src="images/info/cloudberry-system.jpg" class="img-fluid info_img" alt="Cloudberry system diagram"></a>
            </div>
        </div>
    </div>

    <div class="how_section">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>What we used:</h3>

                <div class="accordion hardware-accordion" id="hardwareAccordion">
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-gopro">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-gopro-body" aria-expanded="false" aria-controls="hw-gopro-body">GoPro HERO4 Black with 16gb micro SD Card</button>
                        </h4>
                        <div id="hw-gopro-body" class="accordion-collapse collapse" aria-labelledby="hw-gopro" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>We initially wanted to use the GoPro HERO5, but the Cam Do enclosure did not support the GoPro HERO5 at the time. Creating a DIY weatherproof enclosure didn't add any benefit since the image quality between GoPro HERO4 and GoPro HERO5 is the same; therefore, we made the decision to go with the GoPro HERO4.</p>
                                <p><a href="https://www.amazon.ca/GoPro-MAIN-91068-HERO4-BLACK/dp/B00NIYNUF2" target="_blank" rel="noopener noreferrer">GoPro HERO4 Black on Amazon</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-blink">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-blink-body" aria-expanded="false" aria-controls="hw-blink-body">Cam Do Blink Interval Timer</button>
                        </h4>
                        <div id="hw-blink-body" class="accordion-collapse collapse" aria-labelledby="hw-blink" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>The interval timer turns on the GoPro every hour between 8 AM and 8 PM. The GoPro is modified with the <a href="https://cam-do.com/products/csi-pro-firmware" target="_blank" rel="noopener noreferrer">Pro-csiController</a> firmware from Cam Do and runs a custom <code>autoexec</code> script to take a photo, turn on the GoPro WiFi and then put the camera in standby mode to conserve power.</p>
                                <p><a href="https://cam-do.com/collections/schedulers/products/blink-gopro-time-lapse-controller" target="_blank" rel="noopener noreferrer">Cam Do Blink Interval Timer</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-pi">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-pi-body" aria-expanded="false" aria-controls="hw-pi-body">Raspberry Pi 3 Model B</button>
                        </h4>
                        <div id="hw-pi-body" class="accordion-collapse collapse" aria-labelledby="hw-pi" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>Standard Raspberry Pi with a <a href="https://www.adafruit.com/product/1583" target="_blank" rel="noopener noreferrer">16GB Noobs SD Card</a> running <a href="https://www.raspberrypi.org/downloads/raspbian/" target="_blank" rel="noopener noreferrer">Raspbian OS</a>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-witty">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-witty-body" aria-expanded="false" aria-controls="hw-witty-body">WittyPi 2</button>
                        </h4>
                        <div id="hw-witty-body" class="accordion-collapse collapse" aria-labelledby="hw-witty" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>A real time clock (RTC) that turns on the Raspberry Pi at night for 30 minutes. The Raspberry Pi runs a Python script that:</p>
                                <ol>
                                    <li>Wakes up the GoPro via WiFi.</li>
                                    <li>Downloads and deletes the latest images from the GoPro.</li>
                                    <li>Uploads the images to an Amazon Web Services S3 Bucket and deletes the images from the Raspberry Pi.</li>
                                    <li>Puts the GoPro into standby mode and shuts down the Raspberry Pi to conserve power.</li>
                                </ol>
                                <p><a href="http://www.uugear.com/product/wittypi2/" target="_blank" rel="noopener noreferrer">WittyPi 2</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-arduino">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-arduino-body" aria-expanded="false" aria-controls="hw-arduino-body">Arduino Pro Mini + Current Sensor</button>
                        </h4>
                        <div id="hw-arduino-body" class="accordion-collapse collapse" aria-labelledby="hw-arduino" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>We added a power-monitoring meter to avoid power issues that might corrupt the SD Card if the Raspberry Pi shuts down unexpectedly. In the event that the power supply is low, the Arduino triggers the WittyPi 2 to shut down the Raspberry Pi until there is sufficient battery to power the device.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-watchdog">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-watchdog-body" aria-expanded="false" aria-controls="hw-watchdog-body">WatchDog from Switch Doc</button>
                        </h4>
                        <div id="hw-watchdog-body" class="accordion-collapse collapse" aria-labelledby="hw-watchdog" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>Since the Raspberry Pi is managed remotely the WatchDog monitors the health of the Raspberry Pi and automatically restarts the system in the event of a malfunction.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-hotspot">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-hotspot-body" aria-expanded="false" aria-controls="hw-hotspot-body">Huawei LTE E8372 USB Cellular Modem</button>
                        </h4>
                        <div id="hw-hotspot-body" class="accordion-collapse collapse" aria-labelledby="hw-hotspot" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>The USB cellular modem is connected to the Raspberry Pi USB port with a SIM card and mobile internet plan from Bell Canada. At ±6mb per photo, 13 photos per day, we're using ±2.3gb per month.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-enclosure">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-enclosure-body" aria-expanded="false" aria-controls="hw-enclosure-body">GoPro Weatherproof Enclosure</button>
                        </h4>
                        <div id="hw-enclosure-body" class="accordion-collapse collapse" aria-labelledby="hw-enclosure" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>Home of the GoPro. We made several modifications to the enclosure including silicone sealing, a drilled power port, and removing the air pressure filter to reduce condensation on the lens filter.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-solar">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-solar-body" aria-expanded="false" aria-controls="hw-solar-body">130 Watt Solar Power System</button>
                        </h4>
                        <div id="hw-solar-body" class="accordion-collapse collapse" aria-labelledby="hw-solar" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p>We worked with Gerry of <a href="http://www.nfenergies.com/" target="_blank" rel="noopener noreferrer">Newfound Energies</a> in St. John's Newfoundland to design a solar panel system for intermittent power in harsh winter conditions.</p>
                                <ul>
                                    <li>130-watt solar panel pointed at ±20 degrees towards the path of the sun during the winter.</li>
                                    <li>40 amp solar charge controller</li>
                                    <li>1500-watt inverter</li>
                                    <li>Four 6 volt, 385 amp hour deep cycle batteries</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="hw-cloud">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hw-cloud-body" aria-expanded="false" aria-controls="hw-cloud-body">Remote access &amp; cloud storage</button>
                        </h4>
                        <div id="hw-cloud-body" class="accordion-collapse collapse" aria-labelledby="hw-cloud" data-bs-parent="#hardwareAccordion">
                            <div class="accordion-body">
                                <p><a href="http://www.dataplicity.com/" target="_blank" rel="noopener noreferrer">Dataplicity</a> allows remote CLI access to the Raspberry Pi.</p>
                                <p><a href="https://aws.amazon.com/" target="_blank" rel="noopener noreferrer">Amazon Web Services</a> S3 stores photographs; CloudFront delivers images to this website.</p>
                                <p><a href="https://github.com/KonradIT/goprowifihack" target="_blank" rel="noopener noreferrer">GoPro WiFi Hack</a> from KonradIT enables remote camera control.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <img src="images/info/boat.jpg" class="img-fluid info_img" alt="Boat approaching Pinchard's Island">
            </div>
        </div>
    </div>

    <div class="how_section" id="installation">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>Installation</h3>
                <p>During the second week of August, we embarked on our journey to install Cloudberry. The first task was to bring all of the solar power components to the island, which was a task that required four people to load the housing unit, batteries, and solar panel. It took approximately two days for Roger and Adam to install the entire system with constant readjustments.</p>

                <img src="images/info/solar-install.jpg" class="img-fluid info_img" alt="Solar power installation">

                <p>The second step was to choose the frame of the photograph and install the Cam Do enclosure. Pointing the camera towards the north avoided sun blasting and offers views of both the landscape and the ocean.</p>

                <img src="images/info/cam-do.jpg" class="img-fluid info_img" alt="Cam Do enclosure">

                <p>Once the entire system was in place, we monitored everything for a full day cycle before locking up all of the cases and leaving the island.</p>

                <img src="images/info/pi.jpg" class="img-fluid info_img" alt="Raspberry Pi assembly">
            </div></div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <img src="images/info/yay.jpg" class="img-fluid info_img" alt="Cloudberry creators">
            </div>
        </div>
    </div>

    <div class="who_section" id="who">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>Who Are you?</h3>
                <div class="row people_row">
                    <div class="col-sm-4 people_col_1">
                        <div class="people"><img src="/images/people/adam-simms.jpg" alt="Adam Simms" /></div>
                        <div class="job"><strong>Adam Simms</strong> is a Photographer pursuing his MFA in Studio Arts, Photography at Concordia University.</div>
                        <a href="http://adamsim.ms/" target="_blank" rel="noopener noreferrer" class="link">www</a>
                    </div>
                    <div class="col-sm-4 people_col_2">
                        <div class="people"><img src="/images/people/angela-gabereaux.jpg" alt="Angela Gabereaux" /></div>
                        <div class="job"><strong>Angela Gabereaux</strong> is software developer, system architect, hacker, maker, media artist and teacher.</div>
                        <a href="http://www.angelagabereaux.com/" target="_blank" rel="noopener noreferrer" class="link">www</a>
                    </div>
                    <div class="col-sm-4 people_col_3">
                        <div class="people"><img src="/images/people/roger-knight.jpg" alt="Roger Knight" /></div>
                        <div class="job"><strong>Roger Knight</strong> is a heavy equipment operator and carpenter.</div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="how_section" id="citation">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>Citing this archive</h3>
                <p>Researchers and publications are welcome to use Cloudberry photographs with attribution.</p>
                <p>The suggested format below follows the <strong>Chicago Manual of Style, Author-Date</strong> system, adapted for a born-digital photograph archive (similar to citing a website or online collection).</p>

                <h4>Citing the archive</h4>
                <p>Use this when referring to the project or website as a whole.</p>
                <?php pinchard_citation_block([
                    'text' => pinchard_citation_archive(),
                    'label' => 'Archive citation',
                    'hint' => 'Replace the bracketed access date with the day you retrieved this material.',
                    'class' => 'citation-block--spaced-below',
                ]); ?>

                <h4>Citing a specific photograph</h4>
                <p>Open the image on the site, expand the details panel, and note the <strong>date and time</strong>, <strong>photo number</strong> (shown as the large title), and <strong>filename</strong> from the page URL (<code>?filename=…</code>). Substitute those values into the template below.</p>
                <?php pinchard_citation_block([
                    'text' => pinchard_citation_photo_template(),
                    'label' => 'Individual photograph template',
                    'hint' => 'Replace bracketed fields with values from the photograph you are citing. Update the access date to the day you retrieved the image.',
                ]); ?>
            </div></div>
        </div>
    </div>

    <div class="contact_section" id="contact">
        <div class="container">
            <div class="row justify-content-center"><div class="col-12 col-md-10 col-lg-8">
                <h3>CONTACT</h3>
                <p><a href="mailto:info@pinchards.is" class="link">info@pinchards.is</a></p>
                <div class="copyright">
                    Copyright &copy; 2017&ndash;<?= $copyrightYear ?>
                </div>
            </div></div>
        </div>
    </div>

        </main>
    </div>

<?php pinchard_layout_footer([
    'extra_scripts' => <<<'JS'
    <script>
        (function() {
            var toc = document.querySelector('.info-toc');
            var nav = toc ? toc.querySelector('nav') : null;
            var sectionIds = ['about', 'why', 'where', 'how', 'hardware', 'installation', 'who', 'citation', 'contact'];
            var links = document.querySelectorAll('.info-toc nav a');
            var sections = sectionIds.map(function(id) {
                return document.getElementById(id);
            }).filter(Boolean);

            function updateScrollFades() {
                if (!toc || !nav) {
                    return;
                }
                var maxScroll = nav.scrollWidth - nav.clientWidth;
                var scrollLeft = nav.scrollLeft;
                toc.classList.toggle('is-fade-right', maxScroll > 2 && scrollLeft < maxScroll - 2);
                toc.classList.toggle('is-fade-left', scrollLeft > 2);
            }

            function scrollActiveLinkIntoView() {
                if (!nav) {
                    return;
                }
                var active = nav.querySelector('a.is-active');
                if (!active) {
                    return;
                }
                var navRect = nav.getBoundingClientRect();
                var linkRect = active.getBoundingClientRect();
                if (linkRect.left < navRect.left) {
                    nav.scrollLeft -= (navRect.left - linkRect.left) + 16;
                } else if (linkRect.right > navRect.right) {
                    nav.scrollLeft += (linkRect.right - navRect.right) + 16;
                }
                updateScrollFades();
            }

            if (nav) {
                nav.addEventListener('scroll', updateScrollFades, { passive: true });
                window.addEventListener('resize', updateScrollFades);
                if ('ResizeObserver' in window) {
                    new ResizeObserver(updateScrollFades).observe(nav);
                }
                updateScrollFades();
            }

            if (!sections.length || !('IntersectionObserver' in window)) {
                return;
            }

            function setActive(id) {
                links.forEach(function(link) {
                    var href = link.getAttribute('href') || '';
                    var match = href === '#' + id;
                    link.classList.toggle('is-active', match);
                    if (match) {
                        link.setAttribute('aria-current', 'true');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                });
                scrollActiveLinkIntoView();
            }

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        setActive(entry.target.id);
                    }
                });
            }, {
                root: null,
                rootMargin: '-40% 0px -50% 0px',
                threshold: 0
            });

            sections.forEach(function(section) {
                observer.observe(section);
            });
        })();
    </script>
JS,
]); ?>
