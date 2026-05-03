<x-admin-layout title="Dashboard">
    <style>
        .page-head {
            min-height: 170px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding: 50px 31px 64px;
            background: var(--blue-dark);
            color: #fff;
        }

        .page-head h1 {
            margin: 0 0 17px;
            font-size: 24px;
            line-height: 1.15;
            font-weight: 700;
        }

        .page-head p {
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: 14px;
            font-weight: 300;
        }

        .head-actions {
            display: flex;
            gap: 10px;
            padding-top: 13px;
        }

        .pill {
            min-width: 98px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .9);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .pill.fill {
            min-width: 137px;
            border-color: var(--purple);
            background: var(--purple);
        }

        .page-content {
            position: relative;
            padding: 0 31px 60px;
            margin-top: -32px;
        }

        .reference-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(300px, 330px));
            gap: 29px;
            align-items: stretch;
        }

        .card {
            min-height: 382px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
            border: 1px solid rgba(235, 236, 236, .8);
        }

        .card-body {
            padding: 25px 20px 26px;
        }

        .card h2 {
            margin: 0;
            color: #44495a;
            font-size: 21px;
            line-height: 1.25;
            font-weight: 400;
        }

        .card-subtitle {
            margin: 13px 0 0;
            color: #8d9498;
            font-size: 14px;
            line-height: 1.4;
        }

        .ring-area {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            justify-items: center;
            row-gap: 0;
            margin-top: 25px;
        }

        .ring-stat {
            display: grid;
            justify-items: center;
            text-align: center;
        }

        .ring-stat.center {
            grid-column: 1 / -1;
            margin-top: 2px;
        }

        .ring {
            width: 90px;
            height: 90px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background:
                radial-gradient(circle, #fff 0 59%, transparent 60%),
                conic-gradient(var(--ring-color) var(--ring-size), #f0f0f0 0);
            color: #555b6b;
            font-size: 31px;
            line-height: 1;
            font-weight: 400;
        }

        .ring-label {
            margin-top: 18px;
            color: #44495a;
            font-size: 14px;
            font-weight: 700;
        }

        .income-layout {
            display: grid;
            grid-template-columns: 105px 1fr;
            gap: 8px;
            min-height: 252px;
            align-items: end;
            padding-top: 21px;
        }

        .totals {
            align-self: center;
            padding-bottom: 11px;
        }

        .total-block + .total-block {
            margin-top: 26px;
        }

        .total-label {
            display: block;
            font-size: 14px;
            line-height: 1.22;
            font-weight: 700;
            text-transform: uppercase;
        }

        .total-label.green {
            color: var(--green);
        }

        .total-label.red {
            color: var(--red);
        }

        .total-value {
            display: block;
            margin-top: 11px;
            color: #575962;
            font-size: 20px;
            font-weight: 700;
        }

        .bars {
            height: 190px;
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: 7px;
            align-items: end;
            padding: 0 4px 23px 0;
        }

        .bar-column {
            display: grid;
            justify-items: center;
            gap: 4px;
            min-width: 0;
        }

        .bar {
            width: 12px;
            min-height: 16px;
            background: var(--orange);
        }

        .bar-day {
            color: #686e76;
            font-size: 11px;
            line-height: 1;
        }

        .setting-float {
            position: fixed;
            right: 0;
            top: 56.3%;
            z-index: 15;
            width: 61px;
            height: 45px;
            display: grid;
            place-items: center;
            border-radius: 5px 0 0 5px;
            background: #6861ce;
            color: #fff;
            box-shadow: 0 5px 12px rgba(104, 97, 206, .3);
        }

        .toast {
            position: fixed;
            right: 20px;
            bottom: 21px;
            z-index: 16;
            width: 335px;
            min-height: 79px;
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) 18px;
            gap: 12px;
            align-items: center;
            padding: 14px 14px 14px 17px;
            border-left: 4px solid #48abf7;
            background: #fff;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .12);
        }

        .toast-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #48abf7;
            color: #fff;
        }

        .toast-title {
            margin: 0 0 8px;
            color: #575962;
            font-size: 14px;
            font-weight: 700;
        }

        .toast-text {
            margin: 0;
            color: #8d9498;
            font-size: 13px;
        }

        .toast-close {
            color: #8d9498;
            font-size: 30px;
            line-height: 1;
            font-weight: 300;
        }

        @media (max-width: 980px) {
            .reference-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .page-head {
                padding: 30px 18px 58px;
                flex-direction: column;
            }

            .head-actions {
                padding-top: 0;
                flex-wrap: wrap;
            }

            .page-content {
                padding: 0 18px 40px;
            }

            .reference-grid {
                grid-template-columns: 1fr;
            }

            .card {
                min-height: 0;
            }

            .income-layout {
                grid-template-columns: 1fr;
            }

            .toast {
                width: calc(100vw - 28px);
                right: 14px;
                bottom: 14px;
            }
        }
    </style>

    <section class="page-head">
        <div>
            <h1>Dashboard</h1>
            <p>Free Bootstrap 4 Admin Dashboard</p>
        </div>
        <div class="head-actions">
            <a class="pill" href="{{ route('registrations.index') }}">Manage</a>
            <a class="pill fill" href="{{ route('admin.users.index') }}">Add Customer</a>
        </div>
    </section>

    <section class="page-content">
        <div class="reference-grid">
            <article class="card">
                <div class="card-body">
                    <h2>Overall statistics</h2>
                    <p class="card-subtitle">Daily information about statistics in system</p>

                    <div class="ring-area">
                        <div class="ring-stat">
                            <div class="ring" style="--ring-color: var(--orange); --ring-size: 72%;">5</div>
                            <div class="ring-label">New Users</div>
                        </div>
                        <div class="ring-stat">
                            <div class="ring" style="--ring-color: #2bbf45; --ring-size: 76%;">36</div>
                            <div class="ring-label">Sales</div>
                        </div>
                        <div class="ring-stat center">
                            <div class="ring" style="--ring-color: var(--red); --ring-size: 42%;">12</div>
                            <div class="ring-label">Subscribers</div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-body">
                    <h2>Total income &amp; spend statistics</h2>

                    <div class="income-layout">
                        <div class="totals">
                            <div class="total-block">
                                <span class="total-label green">Total<br>Income</span>
                                <span class="total-value">$9.782</span>
                            </div>
                            <div class="total-block">
                                <span class="total-label red">Total<br>Spend</span>
                                <span class="total-value">$1,248</span>
                            </div>
                        </div>

                        <div class="bars" aria-label="Total income bar chart">
                            @foreach ([61, 31, 106, 46, 32, 61, 30, 16, 120] as $index => $height)
                                <div class="bar-column">
                                    <span class="bar" style="height: {{ $height }}px;"></span>
                                    <span class="bar-day">{{ ['S', 'M', 'T', 'W', 'T', 'F', 'S', 'S', 'M'][$index] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <div class="setting-float" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="2"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.9 2.9l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.9-2.9l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.9-2.9l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.9 2.9l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1h.1a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
    </div>

    <aside class="toast" aria-label="Atlantis notification">
        <span class="toast-icon">
            <svg viewBox="0 0 24 24" width="23" height="23" fill="none" aria-hidden="true">
                <path d="M7 17V9m5 8V5m5 12v-6M5 19h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </span>
        <div>
            <p class="toast-title">Atlantis Lite</p>
            <p class="toast-text">Free Bootstrap 4 Admin Dashboard</p>
        </div>
        <span class="toast-close">×</span>
    </aside>
</x-admin-layout>
