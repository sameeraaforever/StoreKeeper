<!-- Sidebar  -->
            <nav id="sidebar">
                <div class="sidebar-header">
                    <h3><img src="{{asset('images/logo.png')}}" class="img-fluid"/><span>StoreKeeper</span></h3>
                </div>
                <ul class="list-unstyled components">
                <li  class="{{ request()->is('locations*') ? 'active' : '' }}">
                        <a href="{{ url('/home') }}" class="dashboard "><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
                    </li>

                    <div class="small-screen navbar-display">
                    
                        <li  class="d-lg-none d-md-block d-xl-none d-sm-block">
                            <a href="#"><i class="fa-solid fa-pen-to-square"></i><span>Profile</span></a>
                        </li>

                    </div>

                    <li class="{{ request()->is('companies*') ? 'active' : '' }}">
                        <a href="{{ url('/companies') }}"><i class="fa-solid fa-building"></i><span>Companies</span></a>
                    </li>

                    <li class="{{ request()->is('locations*') ? 'active' : '' }}">
                        <a href="{{ url('/locations') }}"><i class="fa-solid fa-map"></i><span>Locations</span></a>
                    </li>

                    
                    <li class="dropdown {{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'active' : '' }}">
                        <a href="#pageSubmenu2" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'true' : 'false' }}"
                        class="dropdown-toggle">
                            <i class="fa-solid fa-broom"></i><span>Product Management</span>
                        </a>

                        <ul class="collapse list-unstyled menu {{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'show' : '' }}"
                            id="pageSubmenu2">
                            
                            <li class="{{ request()->is('categories*') ? 'active' : '' }}">
                                <a href="{{ route('categories.index') }}"><i class="fa-solid fa-layer-group"></i><span>Category</span></a>
                            </li>

                            <li class="{{ request()->is('products*') ? 'active' : '' }}">
                                <a href="{{ url('/products') }}"><i class="fa-solid fa-boxes-packing"></i><span>Product</span></a>
                            </li>

                            <li class="{{ request()->is('product-prices*') ? 'active' : '' }}">
                                <a href="{{ url('/product-prices') }}"><i class="fa-solid fa-dollar"></i><span>Price History</span></a>
                            </li>
                        </ul>
                    </li>


                   <li class="{{ request()->is('supply-records*') ? 'active' : '' }}">
                        <a href="{{ url('/supply-records') }}"><i class="fa-solid fa-boxes"></i><span>Supply Record</span></a>
                    </li>
                   
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <li class="{{ request()->is('admin*') ? 'active' : '' }}">
                                <a href="{{ url('/admin/users') }}">
                                    <i class="fa-solid fa-users"></i><span>Users</span>
                                </a>
                            </li>
                        @endif
                    @endauth
                    
                    <div class="small-screen navbar-display d-block">
                    
                        <form id="logout-form2" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                            <li class="nav-item"><a class="nav-link " href="#" onclick="event.preventDefault(); document.getElementById('logout-form2').submit();"><i class="fa-solid fa-pen-to-square"></i> Logout</a></li>

                    </div>

                </ul>
    
               
            </nav>