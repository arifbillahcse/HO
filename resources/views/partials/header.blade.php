<div class="top-info-bar">
    <div class="container-wrapper">
        <div class="top-bar-content">
            <div class="top-left-links">
                <a href="{{ hostorio_business()->clientAreaUrl() }}submitticket.php" class="info-link">
                    <i class="fas fa-question-circle"></i>
                    <span data-i18n="nav.helpCenter">Help Center</span>
                </a>
                <a href="{{ hostorio_business()->phoneHref() }}" class="info-link">
                    <i class="fas fa-phone-alt"></i>
                    <span>{{ hostorio_business()->phone() }}</span>
                </a>
                <div class="language-selector">
                    <img src="{{ asset('assets/images/flags/us.png') }}" alt="English" class="flag-icon">
                    <span>English</span>
                    <i class="fas fa-chevron-down"></i>

                    <div class="language-dropdown-menu">
                        <button class="language-option active" data-language="en" data-flag="{{ asset('assets/images/flags/us.png') }}">
                            <img src="{{ asset('assets/images/flags/us.png') }}" alt="English" class="flag-icon">
                            <span>English</span>
                        </button>
                        <button class="language-option" data-language="bn" data-flag="{{ asset('assets/images/flags/bd.png') }}">
                            <img src="{{ asset('assets/images/flags/bd.png') }}" alt="Bangla" class="flag-icon">
                            <span>বাংলা</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="top-right-links">
                <div class="currency-switcher desktop-currency">
                    <button class="currency-btn active" data-currency="BDT">
                        <i class="fas fa-bangladeshi-taka-sign"></i>
                        <span>BDT</span>
                    </button>
                    <span class="currency-divider">|</span>
                    <button class="currency-btn" data-currency="USD">
                        <i class="fas fa-dollar-sign"></i>
                        <span>USD</span>
                    </button>
                </div>

                <div class="currency-dropdown mobile-currency">
                    <button class="currency-dropdown-btn" id="currencyDropdownBtn">
                        <i class="fas fa-bangladeshi-taka-sign"></i>
                        <span class="current-currency">BDT</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="currency-dropdown-menu" id="currencyDropdownMenu">
                        <button class="currency-option active" data-currency="BDT">
                            <i class="fas fa-bangladeshi-taka-sign"></i>
                            <span>BDT</span>
                        </button>
                        <button class="currency-option" data-currency="USD">
                            <i class="fas fa-dollar-sign"></i>
                            <span>USD</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="container-wrapper">
        <div class="header-content">
            <div class="brand-logo">
                <a href="{{ url('/') }}">
                    @php
                        $logo = hostorio('logo_url', '');
                        if (empty($logo)) {
                            $logo = asset('assets/images/logo/hostorio-logo.png');
                        }
                    @endphp
                    <img src="{{ $logo }}" alt="{{ hostorio_business()->name() }} – web hosting company logo" class="logo-image">
                </a>
            </div>

            <nav class="primary-nav desktop-nav">
                <ul class="nav-menu">
                    <li><a href="{{ url('/') }}" class="nav-item" data-i18n="nav.home">Home</a></li>
                    <li class="nav-item-dropdown">
                        <a href="#" class="nav-item">
                            <span data-i18n="nav.webHosting">Web Hosting</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ hostorio_url('web-hosting') }}" class="dropdown-item">
                                <i class="fas fa-server"></i>
                                <span data-i18n="nav.wordpressHosting">Web Hosting</span>
                            </a></li>
                            <li><a href="{{ hostorio_url('turbo-hosting') }}" class="dropdown-item">
                                <i class="fas fa-bolt"></i>
                                <span data-i18n="nav.turboHosting">Turbo Hosting</span>
                            </a></li>
                            <li><a href="{{ hostorio_url('reseller-hosting') }}" class="dropdown-item">
                                <i class="fas fa-store"></i>
                                <span data-i18n="nav.resellerHosting">Reseller Hosting</span>
                            </a></li>
                            <li><a href="{{ hostorio_url('student-hosting') }}" class="dropdown-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span data-i18n="nav.studentHosting">Student Hosting</span>
                            </a></li>
                        </ul>
                    </li>
                    <li><a href="{{ hostorio_url('business-email') }}" class="nav-item" data-i18n="nav.businessEmail">Business Email</a></li>
                    <li><a href="{{ hostorio_url('vps') }}" class="nav-item" data-i18n="nav.vps">VPS</a></li>
                    <li class="nav-item-dropdown">
                        <a href="#" class="nav-item">
                            <span data-i18n="nav.domain">Domain</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ hostorio_url('domain-registration') }}" class="dropdown-item">
                                <i class="fas fa-globe"></i>
                                <span data-i18n="nav.domainRegistration">Domain Registration</span>
                            </a></li>
                            <li><a href="{{ hostorio_url('domain-registration') }}#domain-prices" class="dropdown-item">
                                <i class="fas fa-tags"></i>
                                <span data-i18n="nav.domainPricing">Domain Pricing</span>
                            </a></li>
                            <li><a href="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&amp;domain=transfer" class="dropdown-item">
                                <i class="fas fa-exchange-alt"></i>
                                <span data-i18n="nav.domainTransfer">Domain Transfer</span>
                            </a></li>
                        </ul>
                    </li>
                    <li><a href="{{ hostorio_url('offer') }}" class="nav-item" data-i18n="nav.offer">Offer</a></li>
                </ul>
            </nav>

            <div class="mobile-account-center">
                <a href="{{ hostorio_business()->clientAreaUrl() }}" class="account-btn" data-i18n="nav.myAccount">My Account</a>
            </div>

            <div class="header-right">
                <a href="{{ hostorio_business()->clientAreaUrl() }}" class="account-btn desktop-account" data-i18n="nav.myAccount">My Account</a>

                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>
        </div>
    </div>
</header>

<nav class="primary-nav mobile-nav" id="mobileNav">
    <ul class="nav-menu">
        <li><a href="{{ url('/') }}" class="nav-item" data-i18n="nav.home">Home</a></li>
        <li class="nav-item-dropdown">
            <a href="#" class="nav-item mobile-dropdown-trigger">
                <span data-i18n="nav.webHosting">Web Hosting</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="{{ hostorio_url('web-hosting') }}" class="dropdown-item">
                    <i class="fas fa-server"></i>
                    <span data-i18n="nav.wordpressHosting">Web Hosting</span>
                </a></li>
                <li><a href="{{ hostorio_url('turbo-hosting') }}" class="dropdown-item">
                    <i class="fas fa-bolt"></i>
                    <span data-i18n="nav.turboHosting">Turbo Hosting</span>
                </a></li>
                <li><a href="{{ hostorio_url('reseller-hosting') }}" class="dropdown-item">
                    <i class="fas fa-store"></i>
                    <span data-i18n="nav.resellerHosting">Reseller Hosting</span>
                </a></li>
                <li><a href="{{ hostorio_url('student-hosting') }}" class="dropdown-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span data-i18n="nav.studentHosting">Student Hosting</span>
                </a></li>
            </ul>
        </li>
        <li><a href="{{ hostorio_url('business-email') }}" class="nav-item" data-i18n="nav.businessEmail">Business Email</a></li>
        <li><a href="{{ hostorio_url('vps') }}" class="nav-item" data-i18n="nav.vps">VPS</a></li>
        <li class="nav-item-dropdown">
            <a href="#" class="nav-item mobile-dropdown-trigger">
                <span data-i18n="nav.domain">Domain</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="{{ hostorio_url('domain-registration') }}" class="dropdown-item">
                    <i class="fas fa-globe"></i>
                    <span data-i18n="nav.domainRegistration">Domain Registration</span>
                </a></li>
                <li><a href="{{ hostorio_url('domain-registration') }}#domain-prices" class="dropdown-item">
                    <i class="fas fa-tags"></i>
                    <span data-i18n="nav.domainPricing">Domain Pricing</span>
                </a></li>
                <li><a href="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&amp;domain=transfer" class="dropdown-item">
                    <i class="fas fa-exchange-alt"></i>
                    <span data-i18n="nav.domainTransfer">Domain Transfer</span>
                </a></li>
            </ul>
        </li>
        <li><a href="{{ hostorio_url('offer') }}" class="nav-item" data-i18n="nav.offer">Offer</a></li>
    </ul>
</nav>
