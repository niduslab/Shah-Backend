<div class="screen-overlay"></div>
    <aside class="navbar-aside" id="offcanvas_aside">
        <div class="aside-top">
            <a href="{{route('admin.dashboard')}}" class="brand-wrap">
                <img src="{{asset('assets/imgs/logos/niduscart_logo_1.png')}}" class="logo" alt="NidusCart Dashboard" />
            </a>
            <div>
                <button class="btn btn-icon btn-aside-minimize"><i class="text-muted material-icons md-menu_open"></i></button>
            </div>
        </div>

        @if ((auth()->user()->user_type === 'admin') || (auth()->user()->user_type === 'vendor'))
        {{-- admin / super_admin sidebar --}}
        <nav>
            <ul class="menu-aside">
                <li class="menu-item {{Route::is('admin.dashboard') ? "active" : ""}}">
                    <a class="menu-link" href="{{route('admin.dashboard')}}">
                        <i class="icon fas fa-dashboard text-center" style="font-size: 20px"></i>
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li class="menu-item has-submenu {{(Route::is('admin.show.product') || Route::is('variations.index') || Route::is('shipping.index') || Route::is('admin.show.category') || Route::is('admin.manage.brands') || Route::is('admin.product_models.index')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('admin.show.product')}}">
                        <i class="icon fas fa-shopping-bag text-center" style="font-size: 20px"></i>
                        <span class="text">Products</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.show.product')}}" class="{{Route::is('admin.show.product') ? "text-primary": ""}}">Manage Products</a>
                        <a href="{{route('variations.index')}}" class="{{Route::is('variations.index') ? "text-primary": ""}}">Manage Variations</a>
                        {{-- <a href="{{route('shipping.index')}}" class="{{Route::is('shipping.index') ? "text-primary": ""}}">Manage Shipping</a> --}}
                        <a href="{{route('admin.show.category')}}" class="{{Route::is('admin.show.category') ? "text-primary" : ""}}">Manage Categories</a>
                        <a href="{{route('admin.manage.brands')}}" class="{{Route::is('admin.manage.brands') ? "text-primary" : ""}}">Manage Brands</a>
                        <a href="{{route('admin.product_models.index')}}" class="{{Route::is('admin.product_models.index') ? "text-primary" : ""}}">Manage Models</a>
                    </div>
                </li>

                <li class="menu-item has-submenu {{(Route::is('shipping-rates.index') || Route::is('weight-cost-rules.index') || Route::is('weight-cost-rule-items.index')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('admin.show.product')}}">
                        <i class="icon fas fa-shipping-fast text-center" style="font-size: 16px; text-align: center"></i>
                        <span class="text">Shipping</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('shipping-rates.index')}}" class="{{Route::is('shipping-rates.index') ? "text-primary": ""}}">Manage Shipping Rates</a>
                        <a href="{{route('weight-cost-rules.index')}}" class="{{Route::is('weight-cost-rules.index') ? "text-primary": ""}}">Weight Cost Rules</a>
                        <a href="{{route('weight-cost-rule-items.index')}}" class="{{Route::is('weight-cost-rule-items.index') ? "text-primary": ""}}">Weight Cost Rule Items</a>
                    </div>
                </li>
                {{-- <li class="menu-item has-submenu {{(Route::is('variations.index')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('variations.index')}}">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Variations</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('variations.index')}}" class="{{Route::is('variations.index') ? "text-primary": ""}}">Manage Variations</a>
                    </div>
                </li> --}}

                <li class="menu-item has-submenu {{ Route::is('manage.store') ? 'active' : '' }}">
                    <a class="menu-link" href="{{ route('manage.store', ['encryptedUserId' => Crypt::encrypt(Auth::id())]) }}">
                        <i class="icon material-icons md-store"></i>

                        <span class="text">Store</span>
                    </a>
                    <div class="submenu">
                        <a href="{{ route('manage.store', ['encryptedUserId' => Crypt::encrypt(Auth::id())]) }}" class="{{ Route::is('manage.store') ? 'text-primary' : '' }}">
                            Manage Store
                        </a>
                    </div>
                </li>



                {{-- <li class="menu-item has-submenu {{(Route::is('shipping.index')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('shipping.index')}}">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Shipping</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('shipping.index')}}" class="{{Route::is('shipping.index') ? "text-primary": ""}}">Manage Shipping</a>
                    </div>
                </li> --}}

                <li class="menu-item has-submenu {{Route::is('admin.manage.orders') ? "active" : ""}}">
                    <a class="menu-link" href="{{route('admin.manage.orders')}}">
                        <i class="icon fas fa-copy ml-5" style="font-size: 18px"></i>
                        <span class="text">Orders</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.orders')}}" class="{{Route::is('admin.manage.orders') ? "text-primary" : ""}}">Manage Orders</a>
                        {{-- <a href="page-orders-2.html">Order list 2</a>
                        <a href="page-orders-detail.html">Order detail</a> --}}
                    </div>
                </li>
                @if (auth()->user()->user_type === 'admin')
                <li class="menu-item has-submenu {{Route::is('admin.manage.vendors') ? "active" : ""}}">
                    <a class="menu-link" href="">
                        <i class="icon fas fa-shapes fs-6"></i>
                        <span class="text">Vendors</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.vendors')}}" class="{{Route::is('admin.manage.vendors') ? "text-primary" : ""}}">Manage Vendors</a>
                        {{-- <a href="page-orders-2.html">Order list 2</a>
                        <a href="page-orders-detail.html">Order detail</a> --}}
                    </div>
                </li>
                <li class="menu-item has-submenu {{Route::is('admin.manage.users') ? "active" : ""}}">
                    <a class="menu-link" href="">
                        <i class="icon fas fa-users fs-6"></i>
                        <span class="text">Users</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.users')}}" class="{{Route::is('admin.manage.users') ? "text-primary" : ""}}">Manage Users</a>
                        {{-- <a href="page-orders-2.html">Order list 2</a>
                        <a href="page-orders-detail.html">Order detail</a> --}}
                    </div>
                </li>
                @endif

                <li class="menu-item has-submenu {{Route::is('admin.manage.promotions') ? "active" : ""}}">
                    <a class="menu-link" href="{{Route::is('admin.manage.promotions')}}">
                        <i class="icon fab fa-discourse fs-6"></i>
                        <span class="text">Promotions</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.promotions')}}" class="{{Route::is('admin.manage.promotions') ? "text-primary" : ""}}">Manage Promotions</a>
                        {{-- <a href="">Product grid</a> --}}
                    </div>
                </li>

                <li class="menu-item has-submenu {{(Route::is('manage.manage.productReturns') || Route::is('manage.manage.productRefunds')) ? "active" : ""}}">
                    <a class="menu-link" href="#">
                        <i class="fas fa-undo-alt icon fs-6"></i>
                        <span class="text">Returns & Refunds</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('manage.productReturns')}}" class="{{Route::is('manage.productReturns') ? "text-primary" : ""}}">Manage Returns</a>
                        <a href="{{route('manage.productRefunds')}}" class="{{Route::is('manage.productRefunds') ? "text-primary" : ""}}">Manage Refunds</a>
                        {{-- <a href="">Product grid</a> --}}
                    </div>
                </li>
                {{-- <li class="menu-item has-submenu {{Route::is('admin.show.category') ? "active" : ""}}">
                    <a class="menu-link" href="#">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Category</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.show.category')}}" class="{{Route::is('admin.show.category') ? "text-primary" : ""}}">Manage Categories</a>
                    </div>
                </li> --}}
                <li class="menu-item has-submenu {{Route::is('admin.manage.reviews') ? "active" : ""}}">
                    <a class="menu-link" href="#">
                        <i class="fas fa-search-location icon fs-6"></i>
                        <span class="text">Reviews</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.reviews')}}" class="{{Route::is('admin.manage.reviews') ? "text-primary" : ""}}">Manage Reviews</a>
                    </div>
                </li>
                {{-- <li class="menu-item has-submenu {{Route::is('admin.manage.brands') ? "active" : ""}}">
                    <a class="menu-link" href="#">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Brands</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.manage.brands')}}" class="{{Route::is('admin.manage.brands') ? "text-primary" : ""}}">Manage Brands</a>
                    </div>
                </li>
                <li class="menu-item has-submenu {{Route::is('admin.product_models.index') ? "active" : ""}}">
                    <a class="menu-link" href="{{Route('admin.product_models.index')}}">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Models</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('admin.product_models.index')}}" class="{{Route::is('admin.product_models.index') ? "text-primary" : ""}}">Manage Models</a>
                    </div>
                </li> --}}
                <li class="menu-item has-submenu {{Route::is('store_policies.index') ? "active" : ""}}">
                    <a class="menu-link" href="#">
                        <i class="fas fa-vote-yea icon fs-6"></i>
                        <span class="text">Policy</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('store_policies.index')}}" class="{{Route::is('store_policies.index') ? "text-primary" : ""}}">Manage Policies</a>
                    </div>
                </li>

                @if (auth()->user()->user_type === 'admin')
                <li class="menu-item" {{Route::is('chat.agent') ? "active" : ""}}>
                    <a class="menu-link {{Route::is('chat.agent') ? "text-primary" : ""}}" href="{{route('chat.agent')}}">
                        <i class="far fa-credit-card icon fs-6"></i>
                        <span class="text">Customer Agent</span>
                    </a>
                </li>

                <li class="menu-item" {{Route::is('admin.payouts.manage') ? "active" : ""}}>
                    <a class="menu-link {{Route::is('admin.payouts.manage') ? "text-primary" : ""}}" href="{{route('admin.payouts.manage')}}">
                        <i class="far fa-credit-card icon fs-6"></i>
                        <span class="text">Admin Payouts</span>
                    </a>
                </li>
                <li class="menu-item" {{Route::is('admin.banners.index') ? "active" : ""}}>
                    <a class="menu-link {{Route::is('admin.banners.index') ? "text-primary" : ""}}" href="{{route('admin.banners.index')}}">
                        <i class="fas fa-band-aid icon fs-6" style="font-size: width: 16px"></i>
                        <span class="text">Manage Banners</span>
                    </a>
                </li>

                @endif

                {{-- <li class="menu-item has-submenu">
                    <a class="menu-link" href="page-transactions-1.html">
                        <i class="icon material-icons md-monetization_on"></i>
                        <span class="text">Transactions</span>
                    </a>
                    <div class="submenu">
                        <a href="page-transactions-1.html">Transaction 1</a>
                        <a href="page-transactions-2.html">Transaction 2</a>
                    </div>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="page-reviews.html">
                        <i class="icon material-icons md-comment"></i>
                        <span class="text">Reviews</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="page-brands.html"> <i class="icon material-icons md-stars"></i> <span class="text">Brands</span> </a>
                </li> --}}
                <li class="menu-item" {{Route::is('ecommerce-statistics.index') ? "active" : ""}}>
                    <a class="menu-link {{Route::is('ecommerce-statistics.index') ? "text-primary" : ""}}" href="{{route('ecommerce-statistics.index')}}">
                        <i class="icon material-icons md-pie_chart"></i>
                        <span class="text">Statistics</span>
                    </a>
                </li>
            </ul>
            <hr />

            @if (auth()->user()->user_type === 'admin')
            <ul class="menu-aside">
                <li class="menu-item" {{Route::is('ecommerce-settings.index') ? "active" : ""}}>
                    <a class="menu-link {{Route::is('ecommerce-settings.index') ? "text-primary" : ""}}" href="{{route('ecommerce-settings.index')}}">
                        <i class="icon material-icons md-settings"></i>
                        <span class="text">Settings</span>
                    </a>
                    {{-- <div class="submenu">
                        <a href="page-settings-1.html">Setting sample 1</a>
                        <a href="page-settings-2.html">Setting sample 2</a>
                    </div> --}}
                </li>
                @endif
            </ul>
            <br />
            <br />
        </nav>
        {{-- admin / vendor sidebar --}}
        {{-- @elseif ((auth()->user()->user_type === 'vendor'))
           <nav>
            <ul class="menu-aside">
                <li class="menu-item has-submenu {{(Route::is('manage.store')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('manage.store')}}">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Store</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('manage.store')}}" class="{{Route::is('manage.store') ? "text-primary": ""}}">Manage Store</a>
                    </div>
                </li>
                <li class="menu-item has-submenu {{(Route::is('shipping.index')) ? "active" : ""}}">
                    <a class="menu-link" href="{{route('shipping.index')}}">
                        <i class="icon material-icons md-shopping_bag"></i>
                        <span class="text">Shipping</span>
                    </a>
                    <div class="submenu">
                        <a href="{{route('shipping.index')}}" class="{{Route::is('shipping.index') ? "text-primary": ""}}">Manage Shipping</a>
                    </div>
                </li>
            </ul>
           </nav> --}}
        @endif
    </aside>
</div>
