<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=public-sans:300,400,500,600,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue: #1572e8;
            --blue-dark: #1158b4;
            --text: #2a2f3a;
            --muted: #8d9498;
            --line: #ebecec;
            --page: #f5f7fb;
            --green: #31ce36;
            --orange: #ff9e27;
            --red: #f25961;
            --purple: #716aca;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--page);
            color: var(--text);
            font-family: "Public Sans", Arial, sans-serif;
            letter-spacing: 0;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .atlantis {
            min-height: 100vh;
            padding-left: 258px;
            background: var(--page);
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 30;
            width: 258px;
            background: #fff;
            box-shadow: 0 0 20px rgba(69, 87, 105, .13);
        }

        .brand {
            height: 64px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 31px;
            background: var(--blue);
            color: #fff;
            font-size: 17px;
            font-weight: 700;
        }

        .brand-logo {
            width: 31px;
            height: 36px;
            display: grid;
            place-items: center;
            position: relative;
        }

        .brand-logo::before,
        .brand-logo::after {
            content: "";
            position: absolute;
            border: 6px solid #fff;
            border-radius: 6px;
            transform: rotate(30deg);
        }

        .brand-logo::before {
            width: 22px;
            height: 22px;
        }

        .brand-logo::after {
            width: 8px;
            height: 8px;
            background: var(--blue);
        }

        .sidebar-toggle {
            margin-left: auto;
            width: 34px;
            height: 34px;
            border: 0;
            display: grid;
            place-items: center;
            border-radius: 4px;
            color: #fff;
            background: transparent;
            cursor: pointer;
        }

        .sidebar-body {
            height: calc(100vh - 64px);
            overflow-y: auto;
            padding: 22px 10px 24px;
        }

        .profile {
            min-height: 56px;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) 16px;
            align-items: center;
            gap: 14px;
            padding: 0 10px 18px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 19px;
        }

        .avatar,
        .top-avatar {
            display: inline-grid;
            place-items: center;
            border-radius: 50%;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 38%, #f4c7a1 0 15%, transparent 16%),
                radial-gradient(circle at 50% 78%, #2f3747 0 27%, transparent 28%),
                linear-gradient(#dfe8f2, #f6f7f9);
        }

        .avatar {
            width: 44px;
            height: 44px;
        }

        .profile-name {
            margin: 0 0 5px;
            font-size: 13px;
            color: #575962;
            font-weight: 400;
        }

        .profile-role {
            margin: 0;
            font-size: 12px;
            color: #30333a;
            font-weight: 600;
        }

        .sidebar-title {
            margin: 20px 28px 13px;
            color: #83848a;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-link {
            min-height: 45px;
            display: grid;
            grid-template-columns: 27px minmax(0, 1fr) 16px;
            align-items: center;
            gap: 12px;
            margin: 0 13px 7px;
            padding: 0 13px;
            border-radius: 5px;
            color: #8d9498;
            font-size: 14px;
            font-weight: 400;
        }

        .nav-link.active {
            color: #fff;
            background: var(--blue);
            box-shadow: 0 8px 15px rgba(21, 114, 232, .28);
        }

        .nav-link svg {
            width: 21px;
            height: 21px;
        }

        .nav-link .arrow {
            justify-self: end;
            font-size: 18px;
            line-height: 1;
        }

        .badge {
            justify-self: end;
            min-width: 25px;
            height: 25px;
            display: inline-grid;
            place-items: center;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .main {
            min-height: 100vh;
            position: relative;
        }

        .topbar {
            height: 64px;
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 0 31px;
            background: var(--blue);
            color: #fff;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .12);
        }

        .search {
            width: 384px;
            height: 42px;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 0 16px;
            border-radius: 5px;
            background: rgba(0, 0, 0, .12);
        }

        .search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            color: #fff;
            background: transparent;
            font-size: 14px;
        }

        .search input::placeholder {
            color: rgba(255, 255, 255, .95);
        }

        .top-spacer {
            flex: 1;
        }

        .top-icon {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            display: grid;
            place-items: center;
            position: relative;
            background: transparent;
            color: #fff;
            cursor: pointer;
        }

        .notif-dot {
            position: absolute;
            right: 2px;
            top: 0;
            min-width: 17px;
            height: 17px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
        }

        .top-avatar {
            width: 39px;
            height: 39px;
            border: 2px solid rgba(255, 255, 255, .65);
        }

        .mobile-toggle {
            display: none;
        }

        .overlay {
            display: none;
        }

        @media (max-width: 980px) {
            .atlantis {
                padding-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform .2s ease;
            }

            .atlantis.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .mobile-toggle {
                display: grid;
            }

            .search {
                width: min(384px, 50vw);
            }

            .overlay {
                position: fixed;
                inset: 0;
                z-index: 25;
                background: rgba(33, 37, 41, .45);
            }

            .atlantis.sidebar-open .overlay {
                display: block;
            }
        }

        @media (max-width: 720px) {
            .topbar {
                padding: 0 14px;
                gap: 8px;
            }

            .search {
                display: none;
            }

            .top-icon {
                width: 32px;
            }
        }
    </style>
</head>
<body>
    @php
        $displayName = 'Hizrian';
        $displayRole = 'Administrator';
    @endphp

    <div class="atlantis" id="atlantisShell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('admin.dashboard') }}">
                <span class="brand-logo" aria-hidden="true"></span>
                <span>Atlantis</span>
                <span class="sidebar-toggle" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </span>
            </a>

            <div class="sidebar-body">
                <div class="profile">
                    <span class="avatar" aria-hidden="true"></span>
                    <div>
                        <p class="profile-name">{{ $displayName }}</p>
                        <p class="profile-role">{{ $displayRole }}</p>
                    </div>
                    <span style="color:#8d9498;">⌄</span>
                </div>

                <ul class="nav">
                    <li>
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 11.3 12 4l9 7.3V21h-6v-6H9v6H3v-9.7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            <span>Dashboard</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-title">Components</div>
                <ul class="nav">
                    <li>
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Zm8 8.5-8 4.5-8-4.5m16 4-8 4.5-8-4.5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            <span>Base</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 5h16M4 12h16M4 19h16M8 5v14M16 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Sidebar Layouts</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('registrations.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 4h14v16H5V4Zm4 5h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Forms</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('documents.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 5h16v14H4V5Zm0 5h16M9 5v14M15 5v14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            <span>Tables</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('biodata.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 21s7-4.7 7-11a7 7 0 1 0-14 0c0 6.3 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" fill="currentColor"/>
                            </svg>
                            <span>Maps</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 19h16M6 16V8m5 8V4m5 12v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Charts</span>
                            <span class="arrow">⌄</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Widgets</span>
                            <span class="badge">4</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="overlay" data-sidebar-close></div>

        <main class="main">
            <header class="topbar">
                <button class="top-icon mobile-toggle" type="button" aria-label="Open sidebar" data-sidebar-toggle>
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </button>

                <form class="search">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true">
                        <path d="m21 21-4.2-4.2M10.7 18.3a7.6 7.6 0 1 1 0-15.2 7.6 7.6 0 0 1 0 15.2Z" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>
                    <input type="search" placeholder="Search ...">
                </form>

                <div class="top-spacer"></div>

                <button class="top-icon" type="button" aria-label="Mail">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true">
                        <path d="M4 6h16v12H4V6Zm0 0 8 7 8-7" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="top-icon" type="button" aria-label="Notifications">
                    <span class="notif-dot">4</span>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-4.3 13a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="top-icon" type="button" aria-label="Layers">
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" aria-hidden="true">
                        <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </button>
                <span class="top-avatar" aria-hidden="true"></span>
            </header>

            {{ $slot }}
        </main>
    </div>

    <script>
        const shell = document.getElementById('atlantisShell');
        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                shell.classList.toggle('sidebar-open');
            });
        });
        document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => {
            shell.classList.remove('sidebar-open');
        });
    </script>
</body>
</html>
