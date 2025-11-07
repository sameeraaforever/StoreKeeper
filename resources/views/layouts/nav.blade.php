                <div class="top-navbar">
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">

                        <!-- Left: Sidebar button -->
                        <button type="button" id="sidebarCollapse" class="btn d-xl-block d-lg-block d-md-none d-none">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>

                        <!-- Mobile toggle -->
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <!-- Right: Menu items -->
                        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto">
                                
                                <li class="nav-item d-flex align-items-center">
                                    @php
                                        $auth = Auth::user();
                                        $profileImg = $auth->profile_picture
                                            ? asset('storage/' . $auth->profile_picture)
                                            : asset('storage/images/default.jpg');
                                    @endphp

                                    <a class="nav-link d-flex align-items-center gap-2" href="#">
                                        <img src="{{ $profileImg }}" width="40" height="40" class="rounded-circle" alt="Profile">
                                        Profile
                                    </a>
                                </li>

                                <li class="nav-item d-flex align-items-center">
                                    <a class="nav-link d-flex align-items-center gap-2"
                                    href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                    </a>
                                </li>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </ul>
                        </div>


                        </div>
                    </nav>
                </div>