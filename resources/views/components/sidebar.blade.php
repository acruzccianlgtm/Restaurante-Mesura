@php
    $activo = [
        'panel' => request()->routeIs('panel.*'),
        'catalogo' => request()->routeIs('platos.*', 'categorias.*'),
        'inventario' => request()->routeIs('inventario.*'),
        'mesas' => request()->routeIs('mesas.*', 'reservas.*'),
        'usuarios' => request()->routeIs('usuarios.*'),
        'encargados' => request()->routeIs('encargados.*'),
        'carrito' => request()->routeIs('carrito.*'),
    ];

    $carrito = session('carrito_'.auth()->id(), []);
    $totalCarrito = is_array($carrito) ? array_sum(array_map('intval', $carrito)) : 0;
@endphp

<aside class="sidebar-messura">
    <div class="sidebar-brand">
        <div class="brand-logo" aria-hidden="true">M</div>
        <div class="brand-copy">
            <span class="brand-name">MESSURA</span>
            <span class="brand-sub">Restaurante</span>
        </div>
    </div>

    <p class="sidebar-label">Espacio de trabajo</p>

    <nav class="sidebar-nav" aria-label="Navegación principal">
        <a href="{{ route('panel.index') }}" class="sidebar-link {{ $activo['panel'] ? 'active' : '' }}" aria-current="{{ $activo['panel'] ? 'page' : 'false' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            </svg>
            <span>Panel de control</span>
        </a>

        @can('platos.ver')
            <a href="{{ route('platos.index') }}" class="sidebar-link {{ $activo['catalogo'] ? 'active' : '' }}" aria-current="{{ $activo['catalogo'] ? 'page' : 'false' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 3v7a3 3 0 0 0 6 0V3"></path>
                    <path d="M9 3v18"></path>
                    <path d="M17 3c-1.4 1.4-2 3.3-2 6h4V3z"></path>
                    <path d="M17 9v12"></path>
                </svg>
                <span>Platos y categorías</span>
            </a>
        @endcan

        @if (auth()->user()->esCliente())
            <a href="{{ route('carrito.index') }}" class="sidebar-link {{ $activo['carrito'] ? 'active' : '' }}" aria-current="{{ $activo['carrito'] ? 'page' : 'false' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="20" r="1.5"></circle>
                    <circle cx="18" cy="20" r="1.5"></circle>
                    <path d="M2 3h2.2l2.3 12.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L21 7H5.4"></path>
                </svg>
                <span>Mi pedido</span>
                @if ($totalCarrito > 0)
                    <span class="sidebar-badge">{{ $totalCarrito }}</span>
                @endif
            </a>
        @endif

        @can('inventario.ver')
            <a href="{{ route('inventario.index') }}" class="sidebar-link {{ $activo['inventario'] ? 'active' : '' }}" aria-current="{{ $activo['inventario'] ? 'page' : 'false' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 7l9-4 9 4-9 4-9-4z"></path>
                    <path d="M3 7v10l9 4 9-4V7"></path>
                    <path d="M12 11v10"></path>
                </svg>
                <span>Inventario</span>
            </a>
        @endcan

        @can('mesas.ver')
            <a href="{{ route('mesas.index') }}" class="sidebar-link {{ $activo['mesas'] ? 'active' : '' }}" aria-current="{{ $activo['mesas'] ? 'page' : 'false' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                    <path d="M9 4v16"></path>
                    <path d="M15 4v16"></path>
                </svg>
                <span>Mesas</span>
            </a>
        @endcan

        @can('usuarios.ver')
            <a href="{{ route('usuarios.index') }}" class="sidebar-link {{ $activo['usuarios'] ? 'active' : '' }}" aria-current="{{ $activo['usuarios'] ? 'page' : 'false' }}">
                @include('components.sidebar-icono-usuarios')
                <span>Usuarios</span>
            </a>
        @endcan

        @can('empleados.ver')
            <a href="{{ route('encargados.index') }}" class="sidebar-link {{ $activo['encargados'] ? 'active' : '' }}" aria-current="{{ $activo['encargados'] ? 'page' : 'false' }}">
                @include('components.sidebar-icono-usuarios')
                <span>Encargados</span>
            </a>
        @endcan
    </nav>

    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->nombre_completo, 0, 1)) }}</div>
            <div class="user-copy">
                <span class="user-name" title="{{ auth()->user()->nombre_completo }}">{{ auth()->user()->nombre_completo }}</span>
                <span class="user-role">{{ ucfirst(auth()->user()->rol) }}</span>
            </div>
        </div>
        <form action="{{ route('acceso.salir') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <path d="M16 17l5-5-5-5"></path>
                    <path d="M21 12H9"></path>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>
