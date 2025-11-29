/**
 * Simple Parallax Effect for Fajracct LMS
 * Lightweight implementation without external dependencies
 */

(function() {
    'use strict';
    
    // Initialize parallax on page load
    window.addEventListener('DOMContentLoaded', function() {
        initParallax();
    });
    
    function initParallax() {
        const parallaxElements = document.querySelectorAll('.parallax-layer');
        
        if (parallaxElements.length === 0) return;
        
        // Parallax scroll effect
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            
            parallaxElements.forEach(function(element) {
                const speed = element.dataset.speed || -0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = 'translate3d(0, ' + yPos + 'px, 0)';
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip if it's just "#"
                if (href === '#') return;
                
                const target = document.querySelector(href);
                
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Navbar background on scroll
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }
    }
    
    // Fade-in animation on scroll
    function fadeInOnScroll() {
        const elements = document.querySelectorAll('.fade-in');
        
        elements.forEach(function(element) {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('visible');
            }
        });
    }
    
    window.addEventListener('scroll', fadeInOnScroll);
    window.addEventListener('DOMContentLoaded', fadeInOnScroll);
    
})();
