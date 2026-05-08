/* =========================================
   Portfolio – Main JS
   - Mobile-Nav Toggle
   - Scroll-Effekte (Header, Scroll-Top, Reveal)
   - Aktiver Nav-Link
   - Contact-Form Validation
   ========================================= */

(() => {
    'use strict';

    const doc = document;
    const win = window;

    /* ---------- Aktuelles Jahr im Footer ---------- */
    const yearEl = doc.getElementById('currentYear');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear();
    }

    /* ---------- Mobile-Navigation ---------- */
    const navToggle = doc.getElementById('navToggle');
    const navMenu = doc.getElementById('navMenu');
    const navLinks = navMenu ? navMenu.querySelectorAll('.nav__link') : [];

    const closeMenu = () => {
        navToggle?.classList.remove('is-active');
        navMenu?.classList.remove('is-open');
        navToggle?.setAttribute('aria-expanded', 'false');
        doc.body.classList.remove('no-scroll');
    };

    const openMenu = () => {
        navToggle?.classList.add('is-active');
        navMenu?.classList.add('is-open');
        navToggle?.setAttribute('aria-expanded', 'true');
        doc.body.classList.add('no-scroll');
    };

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            if (navMenu.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        navLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        doc.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('is-open')) {
                closeMenu();
                navToggle.focus();
            }
        });
    }

    /* ---------- Header: Scroll-Verhalten ---------- */
    const header = doc.getElementById('header');
    const scrollTopBtn = doc.getElementById('scrollTopBtn');
    let lastScrollY = win.scrollY;
    let ticking = false;

    const updateOnScroll = () => {
        const currentY = win.scrollY;

        if (header) {
            header.classList.toggle('is-scrolled', currentY > 20);

            // Header beim Runter-Scrollen ausblenden, beim Hochscrollen wieder zeigen
            if (currentY > 200 && currentY > lastScrollY) {
                header.classList.add('is-hidden');
            } else {
                header.classList.remove('is-hidden');
            }
        }

        if (scrollTopBtn) {
            if (currentY > 600) {
                scrollTopBtn.hidden = false;
            } else {
                scrollTopBtn.hidden = true;
            }
        }

        lastScrollY = currentY;
        ticking = false;
    };

    win.addEventListener('scroll', () => {
        if (!ticking) {
            win.requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    }, { passive: true });

    /* ---------- Scroll-To-Top ---------- */
    scrollTopBtn?.addEventListener('click', () => {
        win.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ---------- Aktiver Nav-Link via IntersectionObserver ---------- */
    const sections = doc.querySelectorAll('main section[id]');

    if ('IntersectionObserver' in win && sections.length) {
        const setActiveLink = (id) => {
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                link.classList.toggle('is-active', href === `#${id}`);
            });
        };

        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setActiveLink(entry.target.id);
                }
            });
        }, {
            rootMargin: '-40% 0px -55% 0px',
            threshold: 0
        });

        sections.forEach(section => sectionObserver.observe(section));
    }

    /* ---------- Reveal-on-Scroll ---------- */
    const revealCandidates = doc.querySelectorAll(
        '.section__title, .about__text, .about__image, .skill-card, .project-card, .timeline__item, .contact__container > *'
    );

    revealCandidates.forEach(el => el.classList.add('reveal'));

    if ('IntersectionObserver' in win) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Kleine Staffelung für Karten
                    const target = entry.target;
                    const delay = (index % 4) * 80;
                    setTimeout(() => target.classList.add('is-visible'), delay);
                    observer.unobserve(target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -60px 0px'
        });

        revealCandidates.forEach(el => revealObserver.observe(el));
    } else {
        revealCandidates.forEach(el => el.classList.add('is-visible'));
    }

    /* ---------- Contact-Form Validation ---------- */
    const form = doc.getElementById('contactForm');
    const statusEl = doc.getElementById('formStatus');

    const showError = (field, message) => {
        const wrapper = field.closest('.contact-form__field');
        const errorEl = wrapper?.querySelector('.contact-form__error');
        wrapper?.classList.add('has-error');
        if (errorEl) errorEl.textContent = message;
    };

    const clearError = (field) => {
        const wrapper = field.closest('.contact-form__field');
        const errorEl = wrapper?.querySelector('.contact-form__error');
        wrapper?.classList.remove('has-error');
        if (errorEl) errorEl.textContent = '';
    };

    const isValidEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

    const validateField = (field) => {
        const value = field.value.trim();

        if (field.required && !value) {
            showError(field, 'Dieses Feld ist erforderlich.');
            return false;
        }

        if (field.type === 'email' && value && !isValidEmail(value)) {
            showError(field, 'Bitte gib eine gültige E-Mail ein.');
            return false;
        }

        if (field.tagName === 'TEXTAREA' && value.length < 10) {
            showError(field, 'Deine Nachricht sollte mindestens 10 Zeichen lang sein.');
            return false;
        }

        clearError(field);
        return true;
    };

    if (form) {
        const fields = form.querySelectorAll('input[required], textarea[required]');

        fields.forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.closest('.contact-form__field')?.classList.contains('has-error')) {
                    validateField(field);
                }
            });
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            let isValid = true;
            fields.forEach(field => {
                if (!validateField(field)) isValid = false;
            });

            if (!isValid) {
                if (statusEl) {
                    statusEl.textContent = 'Bitte überprüfe die markierten Felder.';
                    statusEl.className = 'contact-form__status is-error';
                }
                return;
            }

            // Demo: Da kein Backend angeschlossen ist, simulieren wir den Versand.
            if (statusEl) {
                statusEl.textContent = 'Nachricht wird gesendet ...';
                statusEl.className = 'contact-form__status';
            }

            setTimeout(() => {
                form.reset();
                if (statusEl) {
                    statusEl.textContent = 'Danke! Deine Nachricht wurde (simuliert) gesendet.';
                    statusEl.className = 'contact-form__status is-success';
                }
            }, 800);
        });
    }
})();
