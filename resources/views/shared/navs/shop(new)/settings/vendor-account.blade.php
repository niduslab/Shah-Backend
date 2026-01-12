<section class="content-body p-xl-4">
    <form id="profileForm">
        <div class="row">
            <h2>Profile setting | Vendor Account</h2>
            <div class="col-lg-8">
                <div class="row gx-3">
                    <div class="col-6 mb-3">
                        <label class="form-label">First name</label>
                        <input id="fname" class="form-control" type="text" name="fname" placeholder="Type here" />
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Last name</label>
                        <input id="lname" class="form-control" type="text" name="lname" placeholder="Type here" />
                    </div>
                    <div class="col-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" class="form-control" name="gender">
                            <option value="" disabled>Select your gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="others">Others</option>
                        </select>
                    </div>


                    <div class="col-lg-6 mb-3">
                        <label class="form-label">DOB</label>
                        <input id="dob" class="form-control" type="date" name="date_of_birth" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Email</label>
                        <input id="email" class="form-control" type="email" name="email" placeholder="example@mail.com" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input id="phone_no" class="form-control" type="tel" name="phone_no" placeholder="+1234567890" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Website</label>
                        <input id="website" class="form-control" type="text" name="website" placeholder="https://example.com" />
                    </div>
                </div>
            </div>
            <aside class="col-lg-4">
                <figure class="text-lg-center">
                    <img id="avatar" class="img-lg mb-3 img-avatar" src="assets/imgs/people/avatar-1.png" alt="User Photo" />
                    <figcaption>
                        <label class="btn btn-light rounded font-md" for="upload-avatar">
                            <i class="icons material-icons md-backup font-md"></i> Upload
                        </label>
                        <input type="file" id="upload-avatar" name="image" style="display: none;" accept="image/*" />
                    </figcaption>
                </figure>
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

@push('vendor_page_script2')
<script>
    document.getElementById('upload-avatar').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
              document.getElementById('avatar').src = e.target.result;
          };
          reader.readAsDataURL(file);
      }
  });

 document.addEventListener('DOMContentLoaded', function () {
      const user = @json($user);
      // console.log(user);
      const userImageUrl = `{{ Storage::url('profile_images/${user.user_profile.image.image_name}') }}`;
      document.getElementById('avatar').src = userImageUrl;

      document.getElementById('fname').value = user.fname;
      document.getElementById('lname').value = user.lname;
      document.getElementById('gender').value = user.user_profile.gender;
      let dateOfBirth = new Date(user.user_profile.date_of_birth).toISOString().split('T')[0];
      document.getElementById('dob').value = dateOfBirth;
      document.getElementById('email').value = user.email;
      document.getElementById('phone_no').value = user.user_profile.phone_no;
      document.getElementById('website').value = user.user_profile.website;
      document.getElementById('avatar').src = userImageUrl;
  });

document.getElementById('profileForm').addEventListener('submit', function (e) {
  e.preventDefault();

  // Validation function
  if (!validateForm()) {
      return;
  }

  let formData = new FormData(this);

  // Debugging: Log all form data
  for (let [key, value] of formData.entries()) {
      // console.log(key, value);
  }

  fetch('{{ route('update.profile') }}', {
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
  const requiredFields = ['fname', 'lname', 'gender', 'dob', 'email', 'phone_no'];
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

      // Additional validation for the phone number field
      if (field === 'phone_no' && input.value.trim() !== '') {
          const phonePattern = /^[+0-9]+$/;
          if (!phonePattern.test(input.value.trim())) {
              input.classList.add('is-invalid');
              notyf.error('PHONE NO must be a valid number.');
              isValid = false;
          } else {
              input.classList.remove('is-invalid');
          }
      }

  });

  // Additional validation for the website field if it is not empty
  const websiteInput = document.getElementById('website');
  if (websiteInput.value.trim() !== '') {
      const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/;
      if (!urlPattern.test(websiteInput.value.trim())) {
          websiteInput.classList.add('is-invalid');
          notyf.error('WEBSITE must be a valid URL.');
          isValid = false;
      } else {
          websiteInput.classList.remove('is-invalid');
      }
  }

  return isValid;
}
</script>
@endpush


