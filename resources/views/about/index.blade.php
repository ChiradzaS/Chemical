@extends('layouts.app')

@section('content')
<div class="about-us-container">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1>About Sailing Packaging</h1>
            <p class="tagline">Transforming waste into sustainable packaging solutions</p>
        </div>
    </div>

    <!-- Company Overview -->
    <div class="section company-overview">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>Our Story</h2>
                    <p>Sailing Packaging is a pioneering manufacturing company dedicated to creating sustainable plastic and paper products from recycled materials. Founded with a vision to reduce environmental impact, we've grown to become a leading provider of eco-friendly packaging solutions in the region.</p>
                    <p>Our innovative processes transform refuse into high-quality, durable bags of all sizes and shapes, serving various industries while contributing to a cleaner planet.</p>
                </div>
                <div class="col-md-6">
                    <div class="image-placeholder">
                        <!-- Replace with your company image -->
                        <img src="{{ asset('images/factory.jpg') }}" alt="Sailing Packaging Factory" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Mission -->
    <div class="section mission-section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2>Our Mission</h2>
                    <p class="mission-statement">To provide sustainable packaging solutions by transforming waste materials into high-quality products, reducing environmental impact while meeting the diverse needs of our customers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Process -->
    <div class="section process-section">
        <div class="container">
            <h2 class="text-center mb-5">Our Manufacturing Process</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-recycle"></i>
                        </div>
                        <h4>Collection</h4>
                        <p>We source and collect plastic waste materials from various channels.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                        <h4>Sorting</h4>
                        <p>Materials are sorted and cleaned to prepare for processing.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <h4>Production</h4>
                        <p>Using advanced technology, waste is transformed into packaging materials.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>Finishing</h4>
                        <p>Products are quality checked, customized, and prepared for delivery.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="section products-section bg-light">
        <div class="container">
            <h2 class="text-center">Our Products</h2>
            <p class="text-center mb-5">We manufacture a wide range of plastic bags and packaging solutions including:</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="product-category">
                        <h4>Retail Bags</h4>
                        <p>Shopping bags, boutique bags, and custom branded options.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-category">
                        <h4>Industrial Packaging</h4>
                        <p>Heavy-duty bags, liners, and specialized containers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-category">
                        <h4>Custom Solutions</h4>
                        <p>Bespoke packaging designed to your specifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="section contact-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>Get In Touch</h2>
                    <p>We're here to answer any questions you might have about our products and services.</p>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email: <a href="mailto:sales@sailingpackaging.com">sales@sailingpackaging.com</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>Gilbert: <a href="tel:+260617316406">061 7316406</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>Paul: <a href="tel:+260614381578">061 4381578</a></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="contact-form-container">
                        <h4>Send us a message</h4>
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="message" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .about-us-container {
        font-family: 'Open Sans', sans-serif;
    }
    
    .hero-section {
        background-color: #0d6efd;
        color: white;
        padding: 80px 0;
        text-align: center;
        margin-bottom: 50px;
    }
    
    .hero-section h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .hero-section .tagline {
        font-size: 1.3rem;
        opacity: 0.9;
    }
    
    .section {
        padding: 60px 0;
    }
    
    .section h2 {
        color: #333;
        margin-bottom: 30px;
        position: relative;
        font-weight: 600;
    }
    
    .bg-light {
        background-color: #f8f9fa;
    }
    
    .mission-statement {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.8;
    }
    
    .process-step {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .process-icon {
        font-size: 2.5rem;
        color: #0d6efd;
        margin-bottom: 15px;
    }
    
    .process-step h4 {
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .product-category {
        background: white;
        padding: 25px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: transform 0.3s ease;
    }
    
    .product-category:hover {
        transform: translateY(-5px);
    }
    
    .contact-section {
        background-color: white;
    }
    
    .contact-item {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .contact-item i {
        color: #0d6efd;
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .contact-form-container {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 5px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .btn-primary {
        background-color: #0d6efd;
        border: none;
        padding: 10px 25px;
    }
</style>
@endsection