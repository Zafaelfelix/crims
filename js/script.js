// ========== Page Loader ==========
window.addEventListener('load', function() {
    const pageLoader = document.getElementById('pageLoader');
    const header = document.querySelector('.header');
    
    // Show header immediately
    if (header) {
        header.classList.add('visible');
    }
    
    if (pageLoader) {
        setTimeout(() => {
            pageLoader.classList.add('hidden');
            // Initialize animations after page loads
            initScrollAnimations();
        }, 600);
    } else {
        initScrollAnimations();
    }
});

// ========== Scroll Animations ==========
function initScrollAnimations() {
    // Add animation classes to elements
    const animatedElements = document.querySelectorAll(
        '.project-card, .news-card, .achievement-card, .commercialization-card, .feature, .partner-logo, .about-text, .about-image'
    );
    
    animatedElements.forEach(el => {
        el.classList.add('fade-in-up');
    });
    
    // Intersection Observer for scroll animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Optional: Unobserve after animation to improve performance
                // observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all animated elements
    animatedElements.forEach(el => {
        observer.observe(el);
    });
    
    // Animate section headers
    const sectionHeaders = document.querySelectorAll('.section-title, .section-header');
    sectionHeaders.forEach(header => {
        header.classList.add('fade-in');
        observer.observe(header);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // ========== Navigation Elements ==========
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    const navLinksItems = document.querySelectorAll('.nav-links li a');
    const dropdownToggles = document.querySelectorAll('.dropdown > a');
    const header = document.querySelector('.header');
    
    // Toggle dropdown on mobile
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const parent = this.parentElement;
                const isActive = parent.classList.contains('active');
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    if (dropdown !== parent) {
                        dropdown.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                if (!isActive) {
                    parent.classList.add('active');
                } else {
                    parent.classList.remove('active');
                }
            }
        });
    });
    
    // ========== Mobile Menu Toggle ==========
    hamburger.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        hamburger.classList.toggle('active');
        
        // Prevent body scroll when menu is open
        if (navLinks.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
    
    // ========== Close Mobile Menu on Link Click ==========
    navLinksItems.forEach(link => {
        // Only close menu if it's not a dropdown toggle on mobile
        if (!link.parentElement.classList.contains('dropdown') || window.innerWidth > 768) {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
                
                // Close all dropdowns when navigating
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            });
        }
    });
    
    // ========== Close Menu When Clicking Outside ==========
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.navbar') && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            hamburger.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // ========== Sticky Header on Scroll ==========
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        // Add scrolled class for styling
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
    
    // ========== Smooth Scrolling for Anchor Links ==========
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Ignore empty hash
            if (targetId === '#' || targetId === '') return;
            
            e.preventDefault();
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerHeight = header.offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ========== Active Navigation on Scroll ==========
    const sections = document.querySelectorAll('section[id]');
    
    function highlightNavigation() {
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 150;
            const sectionId = section.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                navLinksItems.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', highlightNavigation);
    
    // Scroll animations are now handled by initScrollAnimations() function above
    
    // ========== Projects Slider ==========
    const projectsSlider = document.getElementById('projectsSlider');
    const projectsSliderPrev = document.getElementById('projectsSliderPrev');
    const projectsSliderNext = document.getElementById('projectsSliderNext');
    
    if (projectsSlider && projectsSliderPrev && projectsSliderNext) {
        const projectCards = projectsSlider.querySelectorAll('.project-card');
        let currentIndex = 0;
        const totalProjects = projectCards.length;
        
        function updateSlider() {
            // Add smooth animation by first removing active class from all
            projectCards.forEach((card, index) => {
                if (index === currentIndex) {
                    // Active card - will be centered and clear
                    setTimeout(() => {
                        card.classList.add('active');
                    }, 50);
                } else {
                    // Non-active cards - blurred and scaled down
                    card.classList.remove('active');
                }
            });
            
            // Perfect centering calculation - only show active card
            const wrapper = projectsSlider.parentElement;
            const wrapperWidth = wrapper.offsetWidth;
            const cardWidth = 500; // Fixed card width in pixels
            
            // Calculate the exact position to center the active card
            // Formula: center = (wrapperWidth - cardWidth) / 2
            const centerPosition = (wrapperWidth - cardWidth) / 2;
            
            // Calculate translateX in pixels to center the active card
            const cardPosition = currentIndex * cardWidth;
            const translateXPixels = centerPosition - cardPosition;
            
            // Convert to percentage for smooth CSS transition
            const sliderWidth = projectsSlider.offsetWidth;
            const translateXPercent = (translateXPixels / sliderWidth) * 100;
            
            projectsSlider.style.transform = `translateX(${translateXPercent}%)`;
            
            // Disable/enable prev button
            if (currentIndex <= 0) {
                projectsSliderPrev.style.opacity = '0.4';
                projectsSliderPrev.style.cursor = 'not-allowed';
                projectsSliderPrev.disabled = true;
            } else {
                projectsSliderPrev.style.opacity = '0.9';
                projectsSliderPrev.style.cursor = 'pointer';
                projectsSliderPrev.disabled = false;
            }
            
            // Disable/enable next button
            if (currentIndex >= totalProjects - 1) {
                projectsSliderNext.style.opacity = '0.4';
                projectsSliderNext.style.cursor = 'not-allowed';
                projectsSliderNext.disabled = true;
            } else {
                projectsSliderNext.style.opacity = '0.9';
                projectsSliderNext.style.cursor = 'pointer';
                projectsSliderNext.disabled = false;
            }
        }
        
        projectsSliderPrev.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
        
        projectsSliderNext.addEventListener('click', function() {
            if (currentIndex < totalProjects - 1) {
                currentIndex++;
                updateSlider();
            }
        });
        
        // Initialize slider
        updateSlider();
    }
    
    // ========== Partners Slider Auto Scroll ==========
    // Disabled cloning to prevent duplication - slider will scroll normally without infinite loop
    // If you want smooth infinite scroll later, uncomment and adjust the code below
    /*
    const partnersSlider = document.querySelector('.partners-slider');
    
    if (partnersSlider && window.innerWidth > 768) {
        const logos = Array.from(document.querySelectorAll('.partner-logo'));
        const logoCount = logos.length;
        
        // Only enable auto-scroll if we have many items (>= 8)
        if (logoCount >= 8) {
            // Clone logos for seamless loop
            logos.forEach(logo => {
                const clone = logo.cloneNode(true);
                partnersSlider.appendChild(clone);
            });
            
            let scrollPosition = 0;
            const scrollSpeed = 0.5;
            let isScrolling = true;
            
            function autoScroll() {
                if (isScrolling && partnersSlider) {
                    scrollPosition += scrollSpeed;
                    partnersSlider.scrollLeft = scrollPosition;
                    
                    // Reset when reaching halfway (where clones start)
                    if (scrollPosition >= partnersSlider.scrollWidth / 2) {
                        scrollPosition = 0;
                        partnersSlider.scrollLeft = 0;
                    }
                }
                requestAnimationFrame(autoScroll);
            }
            
            // Pause on hover
            partnersSlider.addEventListener('mouseenter', () => {
                isScrolling = false;
            });
            
            partnersSlider.addEventListener('mouseleave', () => {
                isScrolling = true;
            });
            
            // Start auto-scroll
            setTimeout(() => {
                requestAnimationFrame(autoScroll);
            }, 1000);
        }
    }
    */
    
    // ========== Counter Animation for Stats ==========
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start);
            }
        }, 16);
    }
    
    // Initialize counters if they exist
    const counters = document.querySelectorAll('[data-count]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.getAttribute('data-count'));
                animateCounter(entry.target, target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => counterObserver.observe(counter));
    
    // ========== Back to Top Button (Optional) ==========
    const createBackToTop = () => {
        const button = document.createElement('button');
        button.innerHTML = '<i class="fas fa-arrow-up"></i>';
        button.className = 'back-to-top';
        button.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e5ba8, #2980b9);
            color: white;
            border: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            box-shadow: 0 4px 15px rgba(30, 91, 168, 0.4);
        `;
        
        button.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                button.style.opacity = '1';
                button.style.visibility = 'visible';
            } else {
                button.style.opacity = '0';
                button.style.visibility = 'hidden';
            }
        });
        
        document.body.appendChild(button);
    };
    
    createBackToTop();
    
    // ========== Form Validation (if forms exist) ==========
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form validation logic here
            console.log('Form submitted');
        });
    });
    
    // ========== Lazy Loading Images ==========
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // ========== Initialize all animations on page load ==========
    window.addEventListener('load', () => {
        highlightNavigation();
    });
    
    // ========== Performance: Debounce scroll events ==========
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Apply debounce to scroll-heavy functions if needed
    const debouncedScroll = debounce(highlightNavigation, 50);
    window.addEventListener('scroll', debouncedScroll);
    
    console.log('CRiMS Website Initialized Successfully! 🚀');
});