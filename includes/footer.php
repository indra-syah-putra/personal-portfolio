    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <p>&copy; <span id="year"></span> Dibuat dengan <strong>Indra Syah Putra</strong>.</p>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // --- HELPERS ---
        const body = document.body;
        const isDetail = body.classList.contains('page-detail_artikel');

        // 1. Set Copyright Year
        document.getElementById('year').textContent = new Date().getFullYear();

        // 2. Mobile Menu
        function toggleMenu() {
            document.getElementById('navLinks').classList.toggle('active');
            document.getElementById('mobileOverlay').classList.toggle('active');
        }
        function closeMobileMenu() {
            document.getElementById('navLinks').classList.remove('active');
            document.getElementById('mobileOverlay').classList.remove('active');
        }

        // 3. Dark Mode
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') { body.setAttribute('data-theme', 'dark'); updateIcon(true); }

        themeToggle.addEventListener('click', () => {
            const isDark = body.getAttribute('data-theme') === 'dark';
            if (isDark) { body.removeAttribute('data-theme'); localStorage.setItem('theme', 'light'); updateIcon(false); }
            else { body.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark'); updateIcon(true); }
        });
        function updateIcon(isDark) {
            const p = themeToggle.querySelector('path');
            p.setAttribute('d', isDark ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' : 'M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z');
        }

        // 4. Reading Progress Bar (only on detail artikel)
        if (isDetail) {
            const progBar = document.getElementById('readingProgress');
            if (progBar) {
                window.addEventListener('scroll', () => {
                    const scrollTop = body.scrollTop || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    progBar.style.width = (scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0) + '%';
                });
            }
        }

        // 5. Scroll Reveal (IntersectionObserver)
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        // 6. Lightbox
        function openLightbox(src, caption) {
            const lb = document.getElementById('lightbox');
            if (!lb) return;
            document.getElementById('lightboxImg').src = src;
            const cap = document.getElementById('lightboxCaption');
            if (cap) cap.textContent = caption || '';
            lb.classList.add('active');
            body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            if (!lb) return;
            lb.classList.remove('active');
            body.style.overflow = '';
        }
        // Bind click on all .img-zoom
        document.querySelectorAll('.img-zoom').forEach(el => {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                openLightbox(this.src, this.alt);
            });
        });

        // 7. Back to Top
        const backBtn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            backBtn.classList.toggle('visible', window.scrollY > 400);
        });
        backBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    </script>

    </script>

    <button id="backToTop" class="back-to-top" aria-label="Back to top">
        <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
    </button>
</body>
</html>