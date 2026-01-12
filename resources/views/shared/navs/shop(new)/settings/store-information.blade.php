<section class="content-body p-xl-4">
    <form id="vendorInfoForm">
        <div class="row">
            <h2>Profile setting | Store General Information</h2>
            <div class="col-lg-8">
                <div class="row gx-3">
                    <input type="hidden" name="shop_id" id="shop_id">
                    <div class="col-6 mb-3">
                        <label class="form-label">Shopname</label>
                        <input class="form-control" type="text" name="shopname" id="shopname" placeholder="Type here" />
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Shop Email</label>
                        <input class="form-control" type="text" name="shop_email" id="shop_email" placeholder="Type here" />
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Shop Contact</label>
                        <input class="form-control" type="text" name="contact" id="contact" placeholder="+10000000000" />
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Sell Commission Percentage % (Set From Admin)</label>
                        <input class="form-control" type="text" name="sell_commission_percentage" id="sell_commission_percentage" placeholder="5 %" readonly />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Product Tax Percentage %</label>
                        <input class="form-control" name="product_tax_percent" id="product_tax_percent" type="text" placeholder="2 %" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Product Vat Percentage %</label>
                        <input class="form-control" name="product_vat_percent" id="product_vat_percent" type="text" placeholder="2 %" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="location" id="location" cols="30" rows="10"></textarea>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <label class="form-label">Description *</label>
                        <textarea placeholder="Type here" class="form-control" rows="4" id="shop_description" name="shop_description"></textarea>
                    </div>
                </div>
            </div>
            <aside class="col-lg-4">
                <figure class="text-lg-center">
                    <img class="img-lg mb-3 img-logo" src="assets/imgs/people/avatar-1.png" alt="logo" />
                    <figcaption>
                        <a class="btn btn-light rounded font-md" href="#"> <i class="icons material-icons md-backup font-md"></i> Upload logo </a>
                    </figcaption>
                </figure>
                <input type="file" id="upload-logo" name="logo" style="display: none;" accept="image/*" />
                <figure class="text-lg-center mt-5">
                    <img class="img-lg mb-3 img-banner" src="assets/imgs/people/avatar-1.png" alt="banner" />
                    <figcaption>
                        <a class="btn btn-light rounded font-md" href="#"> <i class="icons material-icons md-backup font-md"></i> Upload banner </a>
                    </figcaption>
                </figure>
                <input type="file" id="upload-banner" name="banner" style="display: none;" accept="image/*" />
            </aside>
        </div>

        <br />
        <button class="btn btn-primary" type="submit">Save changes</button>
    </form>
    <hr class="my-5" />
    <div class="row" style="max-width: 920px">
        <div class="col-md">
            <article class="box mb-3 bg-light">
                <a class="btn float-end btn-light btn-sm rounded font-md" href="#">Change</a>
                <h6>Password</h6>
                <small class="text-muted d-block" style="width: 70%">You can reset or change your password by clicking here</small>
            </article>
        </div>
        <!-- col.// -->
        <div class="col-md">
            <article class="box mb-3 bg-light">
                <a class="btn float-end btn-light rounded btn-sm font-md" href="#">Deactivate</a>
                <h6>Remove account</h6>
                <small class="text-muted d-block" style="width: 70%">Once you delete your account, there is no going back.</small>
            </article>
        </div>
    </div>
</section>

@push('vendor_page_script1')

<script>

document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#shop_description',
        setup: function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });

    // Ensure user object is available
    const user = @json($user);
    if (!user || !user.vendor_shop) return;

    let vendorShop = user.vendor_shop;
    // console.log(vendorShop);

    // Pre-fill form fields with existing vendor shop data
    const shopnameElement = document.getElementById('shopname');
    const shopId = document.getElementById('shop_id');
    const contact = document.getElementById('contact');
    const shopEmailElement = document.getElementById('shop_email');
    const sellCommissionPercentageElement = document.getElementById('sell_commission_percentage');
    const productTaxPercentElement = document.getElementById('product_tax_percent');
    const productVatPercentElement = document.getElementById('product_vat_percent');
    const locationElement = document.getElementById('location');
    const shopDescriptionElement = document.getElementById('shop_description');

    if (shopId) shopId.value = vendorShop.id || '';
    if (contact) contact.value = vendorShop.contact || '';
    if (shopnameElement) shopnameElement.value = vendorShop.shopname || '';
    if (shopEmailElement) shopEmailElement.value = vendorShop.shop_email || '';
    if (sellCommissionPercentageElement) sellCommissionPercentageElement.value = vendorShop.sell_commission_percentage || '';
    if (productTaxPercentElement) productTaxPercentElement.value = vendorShop.product_tax_percent || '';
    if (productVatPercentElement) productVatPercentElement.value = vendorShop.product_vat_percent || '';
    if (locationElement) locationElement.value = vendorShop.location || '';
    if (shopDescriptionElement) shopDescriptionElement.value = vendorShop.description || '';

    if (tinymce.get('shop_description')) {
    tinymce.get('shop_description').setContent(JSON.stringify(vendorShop.description) || '');

}


    const logoUrl = `{{ Storage::url('logos/${vendorShop.logo_image.image_name}') }}`;
    const bannerUrl = `{{ Storage::url('banners/${vendorShop.banner_image.image_name}') }}`;

    document.querySelector('.img-logo').src = logoUrl;
    document.querySelector('.img-banner').src = bannerUrl;
});

document.getElementById('vendorInfoForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Validation function
    if (!vendorValidateForm()) {
        return;
    }

    let formData = new FormData(this);

    // Debugging: Log all form data
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    const shopId = document.getElementById('shop_id');
    if (shopId) formData.append('shop_id', shopId.value);

    fetch('/update-vendor-info', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => {
        // Check if the response is OK
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        notyf.success(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        // Display a user-friendly message if needed
        notyf.error('An error occurred. Please try again.');
    });
});


function vendorValidateForm() {
    const requiredFields = ['shopname', 'shop_email', 'phone', 'sell_commission_percentage', 'product_tax_percent', 'product_vat_percent', 'location', 'contact', 'shop_description'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            if (input.value.trim() === '') {
                input.classList.add('is-invalid');
                notyf.error(`${field.replace('_', ' ').toUpperCase()} is required.`);
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }

            // Additional validation for the phone field
            if (field === 'contact' && input.value.trim() !== '') {
                const phonePattern = /^[+0-9]+$/;
                if (!phonePattern.test(input.value.trim())) {
                    input.classList.add('is-invalid');
                    notyf.error('Contact must be a valid number.');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        }
    });

    return isValid;
}


</script>
@endpush

