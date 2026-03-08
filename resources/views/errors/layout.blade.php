<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ App\Models\Setting::get('system_name', 'ARTIKA POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(App\Models\Setting::get('site_logo', 'img/logo2.png')) }}">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Include Bootstrap CSS from assets (if Vite is working) or CDN fallback -->
    @vite(['resources/css/app.scss'])
    
    <!-- Custom CSS Variables -->
    {!! \App\Helpers\ThemeHelper::getCssVariables(\App\Models\Setting::get('site_color_theme', 'brown')) !!}
    
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--color-bg, #f8f9fa);
            color: var(--color-text, #333);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem;
            text-align: center;
        }

        .error-container {
            max-width: 600px;
            width: 100%;
            background: var(--card-bg, #ffffff);
            padding: 3rem 2rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--brown-100, #eee);
            animation: fadeIn 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Background Blob */
        .error-container::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: var(--brown-50, #fcfaf8);
            border-radius: 50%;
            z-index: 0;
            opacity: 0.6;
        }
        .error-container::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: var(--brown-50, #fcfaf8);
            border-radius: 50%;
            z-index: 0;
            opacity: 0.6;
        }

        .error-content {
            position: relative;
            z-index: 1;
        }

        .error-icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--brown-50, #fcfaf8);
            color: var(--color-primary-dark, #5c4335);
            font-size: 3.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(133, 105, 90, 0.15);
            animation: float 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--color-primary, #85695a);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--color-primary-dark, #4a3429);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .error-message {
            font-size: 1.05rem;
            color: var(--gray-600, #6c757d);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-custom-primary {
            background: var(--color-primary, #85695a);
            color: white;
            border: none;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(133, 105, 90, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-custom-primary:hover {
            background: var(--color-primary-dark, #5c4335);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(133, 105, 90, 0.4);
            color: white;
        }

        .btn-custom-outline {
            background: transparent;
            color: var(--color-primary-dark, #5c4335);
            border: 2px solid var(--brown-200, #e2d8d3);
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-custom-outline:hover {
            background: var(--brown-50, #fcfaf8);
            border-color: var(--color-primary, #85695a);
            transform: translateY(-2px);
            color: var(--color-primary-dark, #5c4335);
        }

        .system-logo {
            max-height: 40px;
            margin-top: 3rem;
            opacity: 0.7;
            filter: grayscale(100%);
            transition: all 0.3s ease;
        }
        
        .system-logo:hover {
            opacity: 1;
            filter: grayscale(0%);
        }

        .custom-info {
            background: var(--brown-50);
            border-left: 4px solid var(--color-primary);
            padding: 1rem;
            border-radius: 8px;
            text-align: left;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--color-primary-dark);
            display: none;
        }

        @media (max-width: 576px) {
            .error-container {
                padding: 2.5rem 1.5rem;
                border-radius: 20px;
            }
            .error-icon-wrapper {
                width: 100px;
                height: 100px;
                font-size: 3rem;
            }
            .error-title {
                font-size: 1.8rem;
            }
            .error-message {
                font-size: 0.95rem;
            }
            .error-actions {
                flex-direction: column;
                width: 100%;
            }
            .error-actions a {
                width: 100%;
                justify-content: center;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon-wrapper">
                @yield('icon')
            </div>
            
            <div class="error-code">@yield('code')</div>
            <h1 class="error-title">@yield('title')</h1>
            <p class="error-message">@yield('message')</p>
            
            @hasSection('custom_info')
                <div class="custom-info" style="display: block;">
                    @yield('custom_info')
                </div>
                <br>
            @endif

            <div class="error-actions">
                @yield('actions')
            </div>

            <img src="{{ asset(App\Models\Setting::get('site_logo', 'img/logo2.png')) }}" alt="Logo" class="system-logo">
        </div>
    </div>

    @stack('scripts')
</body>
</html>
