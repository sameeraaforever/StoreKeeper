<!-- Sidebar  -->
            <nav id="sidebar">
                <div class="sidebar-header">
                    <h3><img src="{{asset('images/logo.png')}}" class="img-fluid"/><span>Sam design</span></h3>
                </div>
                <ul class="list-unstyled components">
                <li  class="active">
                        <a href="{{ url('/home') }}" class="dashboard"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
                    </li>
            
                    <div class="small-screen navbar-display">
                    
                        <li  class="d-lg-none d-md-block d-xl-none d-sm-block ">
                            <a href="{{ url('/companies') }}"><i class="fa-solid fa-calendar-check"></i><span>Companies</span></a>
                        </li>
                        
                        <li  class="d-lg-none d-md-block d-xl-none d-sm-block">
                            <a href="{{ url('/locations') }}"><i class="fa-regular fa-user"></i><span>Locations</span></a>
                        </li>
                        
                        <li  class="d-lg-none d-md-block d-xl-none d-sm-block">
                            <a href="#"><i class="fa-solid fa-pen-to-square"></i><span>setting</span></a>
                        </li>
                    </div>
                

                    <li class="{{ request()->is('companies*') ? 'active' : '' }}">
                        <a href="{{ url('/companies') }}"><i class="fa-solid fa-calendar-check"></i><span>Companies</span></a>
                    </li>

                    <li class="{{ request()->is('locations*') ? 'active' : '' }}">
                        <a href="{{ url('/locations') }}"><i class="fa-solid fa-calendar-check"></i><span>Locations</span></a>
                    </li>


                    
                    
                    <li class="dropdown {{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'active' : '' }}">
                        <a href="#pageSubmenu2" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'true' : 'false' }}"
                        class="dropdown-toggle">
                            <i class="fa-solid fa-calendar-check"></i><span>Product Management</span>
                        </a>

                        <ul class="collapse list-unstyled menu {{ request()->is('categories*') || request()->is('products*') || request()->is('product-prices*') ? 'show' : '' }}"
                            id="pageSubmenu2">
                            
                            <li class="{{ request()->is('categories*') ? 'active' : '' }}">
                                <a href="{{ route('categories.index') }}">Category</a>
                            </li>

                            <li class="{{ request()->is('products*') ? 'active' : '' }}">
                                <a href="{{ url('/products') }}">Product</a>
                            </li>

                            <li class="{{ request()->is('product-prices*') ? 'active' : '' }}">
                                <a href="{{ url('/product-prices') }}">Product Price</a>
                            </li>
                        </ul>
                    </li>


                   <li class="{{ request()->is('supply-records*') ? 'active' : '' }}">
                        <a href="{{ url('/supply-records') }}"><i class="fa-solid fa-calendar-check"></i><span>Supply Record</span></a>
                    </li>
                   
                </ul>
    
               
            </nav>