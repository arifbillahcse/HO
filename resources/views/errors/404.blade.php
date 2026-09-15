@extends('layouts.app')

@section('title', 'Page Not Found – Hostorio')

@push('meta')
<meta name="robots" content="noindex, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="Page Not Found – Hostorio">
<meta property="og:description" content="The page you're looking for doesn't exist.">
@endpush

@push('head')
<style>
.error-container {
    min-height: calc(100vh - 300px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #0a1628 0%, #1a2d4f 100%);
}

.error-content {
    text-align: center;
    max-width: 600px;
}

.error-code {
    font-size: 120px;
    font-weight: 900;
    color: #007bff;
    margin: 0;
    line-height: 1;
    text-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
}

.error-title {
    font-size: 32px;
    font-weight: 700;
    color: #ffffff;
    margin: 20px 0 10px;
}

.error-description {
    font-size: 18px;
    color: #a8c0d8;
    margin: 0 0 30px;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.error-btn {
    display: inline-block;
    padding: 12px 30px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.error-btn-primary {
    background: #007bff;
    color: white;
}

.error-btn-primary:hover {
    background: #0056cc;
    transform: translateY(-2px);
}

.error-btn-secondary {
    background: transparent;
    color: #007bff;
    border: 2px solid #007bff;
}

.error-btn-secondary:hover {
    background: #007bff;
    color: white;
    transform: translateY(-2px);
}

.error-icon {
    font-size: 80px;
    color: #4da8ff;
    margin-bottom: 20px;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 80px;
    }

    .error-title {
        font-size: 24px;
    }

    .error-description {
        font-size: 16px;
    }

    .error-actions {
        flex-direction: column;
    }

    .error-btn {
        width: 100%;
    }
}
</style>
@endpush

@section('content')

<div class="error-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-description">
            Sorry, the page you're looking for doesn't exist. It might have been moved or deleted.
        </p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="error-btn error-btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="javascript:history.back()" class="error-btn error-btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
    </div>
</div>

@endsection
