<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') | MESSURA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --restaurante: #7b2d26;
            --restaurante-oscuro: #5f221d;
            --restaurante-claro: #ffd8a8;
            --sidebar-fondo: #241c19;
            --sidebar-texto: rgba(255, 255, 255, .72);
        }

        body {
            background-color: #f7f4ef;
            color: #2c2a28;
        }

        .navbar-restaurante {
            background-color: var(--restaurante);
        }

        .navbar-restaurante .navbar-brand,
        .navbar-restaurante .nav-link {
            color: #fff;
        }

        .navbar-restaurante .nav-link:hover,
        .navbar-restaurante .nav-link.active {
            color: var(--restaurante-claro);
        }

        .nav-logo {
            width: 32px;
            height: 32px;
            border: 2px solid var(--restaurante-claro);
            border-radius: 50%;
            background-color: var(--restaurante);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: .85rem;
        }

        .layout-admin {
            display: flex;
            min-height: 100vh;
        }

        .sidebar-messura {
            width: 264px;
            flex: 0 0 264px;
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            padding: 28px 18px 22px;
            background: linear-gradient(180deg, #2f2420 0%, var(--sidebar-fondo) 100%);
            color: #fff;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 26px;
            border-bottom: 1px solid rgba(255, 255, 255, .09);
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            border: 2px solid var(--restaurante-claro);
            border-radius: 50%;
            background-color: var(--restaurante);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1.3rem;
            font-weight: 800;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25);
        }

        .brand-copy {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-name {
            font-size: .98rem;
            font-weight: 800;
            letter-spacing: .16em;
        }

        .brand-sub {
            margin-top: 3px;
            color: rgba(255, 255, 255, .48);
            font-size: .66rem;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .sidebar-label {
            margin: 26px 10px 12px;
            color: rgba(255, 255, 255, .42);
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 11px;
            color: var(--sidebar-texto);
            font-size: .89rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .18s ease, color .18s ease, transform .18s ease;
        }

        .sidebar-link svg {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, .07);
            color: #fff;
            transform: translateX(2px);
        }

        .sidebar-link.active {
            background-color: var(--restaurante);
            color: #fff;
            box-shadow: 0 6px 18px rgba(123, 45, 38, .35);
        }

        .sidebar-badge {
            min-width: 22px;
            margin-left: auto;
            padding: 2px 6px;
            border-radius: 999px;
            background-color: var(--restaurante);
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            text-align: center;
        }

        .sidebar-link.active .sidebar-badge {
            background-color: rgba(255, 255, 255, .24);
        }

        .sidebar-user {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, .09);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 50%;
            background-color: var(--restaurante-claro);
            color: #3d2119;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .user-copy {
            display: flex;
            min-width: 0;
            flex-direction: column;
            line-height: 1.3;
        }

        .user-name {
            overflow: hidden;
            color: #fff;
            font-size: .83rem;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-role {
            color: rgba(255, 255, 255, .46);
            font-size: .72rem;
            text-transform: capitalize;
        }

        .sidebar-logout {
            width: 100%;
            margin-top: 16px;
            padding: 9px 12px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 10px;
            background-color: transparent;
            color: var(--sidebar-texto);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: .82rem;
            font-weight: 500;
            transition: background-color .18s ease, color .18s ease;
        }

        .sidebar-logout svg {
            width: 16px;
            height: 16px;
        }

        .sidebar-logout:hover {
            background-color: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .admin-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .admin-content {
            width: 100%;
            max-width: 1240px;
            flex: 1;
            margin: 0 auto;
            padding: 36px 40px;
        }

        .admin-footer {
            border-top: 1px solid #e9e2d8;
            padding: 14px 40px;
            color: #8a8178;
            font-size: .78rem;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .page-title {
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .card {
            border: 0;
            box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .06);
        }

        .table thead th {
            color: #6c757d;
            font-size: .78rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .btn-restaurante {
            background-color: var(--restaurante);
            border-color: var(--restaurante);
            color: #fff;
        }

        .btn-restaurante:hover,
        .btn-restaurante:focus {
            background-color: var(--restaurante-oscuro);
            border-color: var(--restaurante-oscuro);
            color: #fff;
        }

        .btn-outline-restaurante {
            border-color: var(--restaurante);
            color: var(--restaurante);
        }

        .btn-outline-restaurante:hover {
            background-color: var(--restaurante);
            border-color: var(--restaurante);
            color: #fff;
        }

        .foto-miniatura {
            width: 56px;
            height: 56px;
            border-radius: .5rem;
            object-fit: cover;
        }

        .foto-detalle {
            width: 100%;
            max-height: 360px;
            border-radius: .75rem;
            object-fit: cover;
        }

        .sin-foto {
            display: grid;
            min-height: 56px;
            place-items: center;
            border-radius: .5rem;
            background: #eee9e2;
            color: #8a8178;
            font-size: .72rem;
        }

        @media (max-width: 767.98px) {
            .layout-admin {
                flex-direction: column;
            }

            .sidebar-messura {
                width: 100%;
                height: auto;
                position: static;
                flex: none;
                padding: 18px;
            }

            .sidebar-brand {
                padding-bottom: 18px;
            }

            .sidebar-label {
                margin-top: 20px;
            }

            .sidebar-nav {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .sidebar-user {
                margin-top: 20px;
            }

            .admin-content {
                padding: 26px 18px;
            }

            .admin-footer {
                padding: 14px 18px;
            }
        }
    </style>
</head>
<body class="{{ auth()->check() ? '' : 'd-flex min-vh-100 flex-column' }}">
    @auth
        <div class="layout-admin">
            <x-sidebar />
            <div class="admin-main">
                <main class="admin-content">
                    @include('layouts.alerts')
                    @yield('contenido')
                </main>
                <footer class="admin-footer">
                    <span>MESSURA · Espacio de trabajo</span>
                    <span>Gestión del restaurante</span>
                </footer>
            </div>
        </div>
    @else
        <nav class="navbar navbar-restaurante shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <span class="nav-logo">M</span>
                    MESSURA
                </a>
            </div>
        </nav>

        <main class="container flex-grow-1 py-4 py-lg-5">
            @include('layouts.alerts')
            @yield('contenido')
        </main>

        <footer class="border-top py-3">
            <div class="container d-flex flex-wrap justify-content-between gap-2 small text-muted">
                <span>MESSURA</span>
                <span>Gestión de carta</span>
            </div>
        </footer>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
