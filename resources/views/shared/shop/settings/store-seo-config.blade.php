<section class="content-body p-xl-4">
    <form id="seoConfigForm">
        <div class="row">
            <h2>Profile setting | Store Seo</h2>
            <div class="col-lg-12">
                <!-- Existing Profile Fields -->
                <!-- SEO Fields -->
                <div class="row gx-3">
                    <input type="hidden" name="id" id="id">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Title</label>
                        <input id="meta_title" class="form-control" type="text" name="meta_title" placeholder="Type here" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea id="meta_description" class="form-control" name="meta_description" placeholder="Type here"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug</label>
                        <input id="slug" class="form-control" type="text" name="slug" placeholder="Type here" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input id="canonical_url" class="form-control" type="text" name="canonical_url" placeholder="https://example.com" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fb Profile URL</label>
                        <input id="fb_profile_link" class="form-control" type="text" name="fb_profile_link" placeholder="https://fb.com/example" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">X Profile URL</label>
                        <input id="x_profile_link" class="form-control" type="text" name="x_profile_link" placeholder="https://x.com/example" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Instra Profile URL</label>
                        <input id="instra_profile_link" class="form-control" type="text" name="instra_profile_link" placeholder="https://linkedin.com/example" />
                    </div>
                </div>
            </div>
        </div>
        <br />
        <button class="btn btn-primary" type="submit">Save changes</button>
    </form>

    <!-- Existing Password and Deactivate Account Sections -->

</section>


@push('store_seo_script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Ensure user object is available
    const user = @json($user);
    if (!user || !user.vendor_shop) return;

    let seoConfig = user.vendor_shop.shop_seo_config;
    //  console.log('seo data: ',seoConfig);

    document.getElementById('id').value = seoConfig.id || '';
    document.getElementById('meta_title').value = seoConfig.meta_title || '';
    document.getElementById('meta_description').value = seoConfig.meta_description || '';
    document.getElementById('slug').value = seoConfig.slug || '';
    document.getElementById('canonical_url').value = seoConfig.canonical_url || '';
    document.getElementById('fb_profile_link').value = seoConfig.fb_profile_link || '';
    document.getElementById('x_profile_link').value = seoConfig.x_profile_link || '';
    document.getElementById('instra_profile_link').value = seoConfig.instra_profile_link || '';
});

document.getElementById('seoConfigForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Validation function
    if (!validateForm()) {
        return;
    }

    let formData = new FormData(this);

    // Debugging: Log all form data
    // for (let [key, value] of formData.entries()) {
    //     console.log(key, value);
    // }

    fetch('{{ route('update.StoreSeoInfo') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        notyf.success(data.message);
    })
    .catch(error => console.error('Error:', error));
});

function validateForm() {
    const requiredFields = ['meta_title', 'slug'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (input.value.trim() === '') {
            input.classList.add('is-invalid');
            notyf.error(`${field.replace('_', ' ').toUpperCase()} is required.`);
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }

        // Additional validation for specific fields

    //    function urlCheck(urlType) {
    //     if (field === urlType && input.value.trim() !== '') {
    //         const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/;
    //         if (!urlPattern.test(input.value.trim())) {
    //             input.classList.add('is-invalid');
    //             notyf.error('URL must be a valid URL.');
    //             isValid = false;
    //         } else {
    //             input.classList.remove('is-invalid');
    //         }
    //     }
    //    }

    //    urlCheck('canonical_url');
    //    urlCheck('fb_profile_link');
    //    urlCheck('x_profile_link');
    //    urlCheck('instra_profile_link');
    });

    return isValid;
}

</script>
@endpush


