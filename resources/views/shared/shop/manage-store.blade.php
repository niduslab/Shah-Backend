@extends('layouts.app')
@section('title', 'Store settings')

@section('content')
<section class="content-main">
    <div class="card">
        <div class="card-body">
            <div class="content-header">
                <h2 class="content-title">Store settings</h2>
            </div>
            <div class="row gx-5">
                <aside class="col-lg-2 border-end">
                    <nav class="nav nav-pills flex-lg-column mb-2" style="font-size: 12px">
                        <a class="nav-link border p-2 mb-2 mr-2 active d-flex align-items-start" aria-current="page" href="#" data-target="vendor-account">
                            <i class="material-icons md-home" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Vendor Account</span>
                        </a>
                        <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="#" data-target="store-information">
                            <i class="material-icons md-store" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Store Info</span>
                        </a>
                        {{-- <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="#" data-target="store-products">
                            <i class="material-icons md-shopping_cart" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Store Products</span>
                        </a> --}}
                        <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="#" data-target="commission-withdrawal">
                            <i class="material-icons md-account_balance_wallet" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Commission & Withdrawal</span>
                        </a>
                        <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="#" data-target="store-seo-config">
                            <i class="material-icons md-trending_up" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Store Seo & Social</span>
                        </a>
                        <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="" data-target="store-policies">
                            <i class="material-icons md-policy" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Store Policies</span>
                        </a>
                        <a class="nav-link border p-2 mb-2 mr-2 d-flex align-items-start" href="#" data-target="store-settings">
                            <i class="material-icons md-settings" style="font-size: 14px; margin-right:3px"></i>
                            <span class="ml-2">Store Settings</span>
                        </a>
                    </nav>
                </aside>
                <div class="col-lg-10">
                    <div id="store-seo-config" class="content-section d-none">
                        @include('shared.shop.settings.store-seo-config')
                    </div>
                    <div id="commission-withdrawal" class="content-section d-none">
                        @include('shared.shop.settings.commission-withdrawal')
                    </div>
                    <div id="vendor-account" class="content-section">
                        @include('shared.shop.settings.vendor-account')
                    </div>
                    <div id="store-information" class="content-section d-none">
                        @include('shared.shop.settings.store-information')
                    </div>
                    <div id="store-policies" class="content-section d-none">
                        {{-- @include('shared.shop.settings.policies.store_policies') --}}
                    </div>
                    <div id="store-policies" class="content-section d-none">
                        {{-- @include('shared.shop.settings.policies.store_policies') --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @include('shared.shop.settings.products.product-model'); --}}
    @include('admin.products.product-model');

</section>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/k9rv9gl56bd9lhc7lqqb6mfhcxbpacbcsrenu0hfbxt1fvc6/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const links = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('.content-section');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                if (targetId === 'store-policies') {
                    window.location.replace('{{ route('store_policies.index') }}');
                }
                if (targetId === 'store-settings') {
                    window.location.replace('{{ route('ecommerce-settings.index') }}');
                }
                links.forEach(link => link.classList.remove('active'));
                sections.forEach(section => section.classList.add('d-none'));
                document.getElementById(targetId).classList.remove('d-none');
                this.classList.add('active');
                console.log(targetId);
            });
        });
        document.querySelector('.nav-link.active').click();
    });

    console.log('seo data: ',seoConfig);
</script>

{{-- @stack('vendor_product_script') --}}
@stack('vendor_page_script1')
@stack('vendor_page_script2')
@stack('store_seo_script')
@stack('store_commision_withdrawal')



@endsection
