<nav id="app-sidebar" class="sidebar d-flex flex-column" aria-label="Navegación principal">
    <div class="sidebar-brand">
        <svg class="sidebar-shield" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L4 5V11C4 16.55 7.16 21.74 12 23C16.84 21.74 20 16.55 20 11V5L12 2Z" fill="#A7E36D"/>
            <path d="M9.5 12L11 13.5L14.5 10" stroke="#06452F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="sidebar-brand-text">Sello ITSa<small>Autenticidad académica</small></span>
        <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Contraer panel lateral" aria-expanded="true" aria-controls="app-sidebar" title="Contraer panel">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i><span class="sidebar-link-text">Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('proyectos.*') ? 'active' : '' }}" href="{{ route('proyectos.index') }}">
                <i class="bi bi-folder"></i><span class="sidebar-link-text">Proyectos</span>
            </a>
        </li>
        @if(Auth::user()->esAdministrador() || Auth::user()->esGestor())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('analisis.*') ? 'active' : '' }}" href="{{ route('analisis.index') }}">
                    <i class="bi bi-intersect"></i><span class="sidebar-link-text">Comparar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('crossref.*') ? 'active' : '' }}" href="{{ route('crossref.index') }}">
                    <i class="bi bi-search"></i><span class="sidebar-link-text">Búsqueda</span>
                </a>
            </li>
            <li class="sidebar-section-label"><span>Registro académico</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('estudiantes.*') ? 'active' : '' }}" href="{{ route('estudiantes.index') }}">
                    <i class="bi bi-mortarboard"></i><span class="sidebar-link-text">Estudiantes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('docentes.*') ? 'active' : '' }}" href="{{ route('docentes.index') }}">
                    <i class="bi bi-person-video3"></i><span class="sidebar-link-text">Docentes tutores</span>
                </a>
            </li>
        @endif
        @if(Auth::user()->esAdministrador())
            <li class="sidebar-section-label"><span>Administración</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                    <i class="bi bi-people"></i><span class="sidebar-link-text">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('carreras.*') ? 'active' : '' }}" href="{{ route('carreras.index') }}">
                    <i class="bi bi-mortarboard"></i><span class="sidebar-link-text">Carreras</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('respaldos.*') ? 'active' : '' }}" href="{{ route('respaldos.index') }}">
                    <i class="bi bi-cloud-arrow-down"></i><span class="sidebar-link-text">Respaldos</span>
                </a>
            </li>
        @endif
        <li class="nav-item sidebar-reports-link">
            <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                <i class="bi bi-file-earmark-bar-graph"></i><span class="sidebar-link-text">Reportes</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer dropdown">
        <a href="#" class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cuenta: {{ Auth::user()->name }}">
            <i class="bi bi-person-circle"></i><span class="sidebar-user-name">{{ Auth::user()->name }}</span>
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Mi perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Cerrar sesión</button>
                </form>
            </li>
        </ul>
    </div>

    <svg class="sidebar-watermark" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2L4 5V11C4 16.55 7.16 21.74 12 23C16.84 21.74 20 16.55 20 11V5L12 2Z" fill="#fff"/>
    </svg>
</nav>
