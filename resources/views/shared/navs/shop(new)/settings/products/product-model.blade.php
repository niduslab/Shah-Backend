  <!-- Promotion Modal -->
  <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true"
  data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header">
              {{-- <h5 class="modal-title" id="promotionModalLabel">Add New Product</h5> --}}
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form method="POST" id="productForm" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" id="product_id" name="product_id">
                  <div class="row">
                      <div class="col-12">
                          <div class="content-header">
                              <h2 class="content-title">Add New Product <br>
                                  <h6 id="show-error"></h6>
                              </h2>
                              <div>
                                  <button class="btn btn-md rounded font-sm hover-up" type="submit">Save</button>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-8">
                          <div class="card mb-4">
                              <div class="card-body">

                                  <div class="row py-5">
                                      <div class="col-lg-6">
                                          <div class="row">
                                              <div class="col-lg-12 mb-4">
                                                  <label for="product_name" class="form-label">Product Name
                                                      *</label>
                                                  <input type="text" placeholder="Type here"
                                                      class="form-control" id="product_name" name="product_name"
                                                      value="{{ old('product_name') }}"
                                                      data-parsley-required="true" />
                                                  <div class="parsley-errors-list"></div>
                                                  @error('product_name')
                                                      <div class="text-danger mt-2">{{ $message }}</div>
                                                  @enderror
                                              </div>


                                              <div class="col-lg-6">
                                                  <div class="mb-4">
                                                      <label class="form-label">Price *</label>
                                                      <input type="text" class="form-control" id="price"
                                                          name="price" value="{{ old('price') }}"
                                                          data-parsley-required="true" />
                                                      @error('price')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-6">
                                                  <div class="mb-4">
                                                      <label class="form-label">Stock</label>
                                                      <input type="text" class="form-control" id="stock"
                                                          name="stock" value="{{ old('stock') }}"
                                                          data-parsley-required="true" />
                                                      @error('stock')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>

                                              <div class="col-lg-6">
                                                  <div class="mb-4">
                                                      <label class="form-label">Weight Type *</label>
                                                      <select class="form-select" id="weight_type"
                                                          name="weight_type" data-parsley-required="true">
                                                          {{-- <option value="gm" {{ old('weight_type') == 'gm' ? 'selected' : '' }}>gm</option> --}}
                                                          <option value="kg"
                                                              {{ old('weight_type') == 'kg' ? 'selected' : '' }}>
                                                              kg</option>
                                                          {{-- <option value="lb" {{ old('weight_type') == 'lb' ? 'selected' : '' }}>lb</option> --}}
                                                      </select>
                                                      @error('weight_type')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-6">
                                                  <div class="mb-4">
                                                      <label class="form-label">Weight Value *</label>
                                                      <input type="text" class="form-control"
                                                          id="weight_value" name="weight_value"
                                                          value="{{ old('weight_value') }}"
                                                          data-parsley-required="true" />
                                                      @error('weight_value')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label class="form-label">Height</label>
                                                      <input type="text" class="form-control"
                                                          id="height" name="height"
                                                          value="{{ old('height') }}"
                                                          data-parsley-required="true" />
                                                      @error('height')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label class="form-label">Width</label>
                                                      <input type="text" class="form-control"
                                                          id="width" name="width"
                                                          value="{{ old('width') }}"
                                                          data-parsley-required="true" />
                                                      @error('width')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label class="form-label">Length</label>
                                                      <input type="text" class="form-control"
                                                          id="length" name="length"
                                                          value="{{ old('length') }}"
                                                          data-parsley-required="true" />
                                                      @error('length')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>

                                              <div class="col-lg-12 d-flex justify-content-between">
                                                  <div class="mb-4">
                                                      <label class="form-check-label">Virtual Product</label>
                                                      <div class="form-check">
                                                          <input class="form-check-input" type="checkbox"
                                                              id="is_virtual" name="is_virtual"
                                                              {{ old('is_virtual') ? 'checked' : '' }}>
                                                          <label class="form-check-label" for="is_virtual">Mark as
                                                              Virtual</label>
                                                      </div>
                                                  </div>
                                                  <div class="mb-4">
                                                      <label class="form-check-label">Trending</label>
                                                      <div class="form-check">
                                                          <input class="form-check-input" type="checkbox"
                                                              id="trending" name="trending"
                                                              {{ old('trending') ? 'checked' : '' }}>
                                                          <label class="form-check-label" for="trending">Mark as
                                                              Trending</label>
                                                      </div>
                                                  </div>
                                                  <div class="mb-4">
                                                      <label class="form-check-label">Featured</label>
                                                      <div class="form-check">
                                                          <input class="form-check-input" type="checkbox"
                                                              id="featured" name="featured"
                                                              {{ old('featured') ? 'checked' : '' }}>
                                                          <label class="form-check-label" for="featured">Mark as
                                                              Featured</label>
                                                      </div>
                                                  </div>
                                              </div>

                                              <div class="col-md-8">
                                                  <div id="variationContainer">
                                                  </div>

                                                  <!-- HTML Structure for the Popup -->
                                                  <div id="variationOptionPopup" style="display: none;">
                                                      <div class="popup-content">
                                                          <h3>Enter Variation Option Details</h3>
                                                          <form id="variationOptionForm">
                                                              <input type="hidden" id="optionId">
                                                              <input type="hidden" id="variationOptionId">
                                                              <div>
                                                                  <label for="price">Price:</label>
                                                                  <input class="form-control" type="text"
                                                                      id="variation_price" name="variation_price">
                                                              </div>
                                                              <div>
                                                                  <label for="imageIds">Image IDs:</label>
                                                                  {{-- <input class="form-control" type="file" id="files"
                                                                  data-parsley-required="true" name="variation_option_images[]" multiple id="variation_option_images" name="variation_option_images"> --}}

                                                                  <input class="form-control" type="file" id="variation_option_images" name="variation_option_images[]" multiple>

                                                              </div>
                                                              <button class="btn btn-sm btn-light"
                                                                  id="saveOptionDetails">Save</button>
                                                          </form>
                                                          <button class="btn btn-sm btn-outline-danger"
                                                              id="closePopup">Close</button>
                                                      </div>
                                                  </div>
                                              </div>




                                          </div>


                                      </div>
                                      <div class="col-lg-6">
                                        <label class="form-label">Description *</label>
                                        <textarea placeholder="Type here" class="form-control" rows="4" id="description" name="description"
                                            data-parsley-required="true">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                      <!-- Trending and Featured Checkboxes -->
                                  </div>
                                  <!-- SEO start -->
                                  <div class="card mb-4">
                                      <div class="card-header">
                                          <h4>SEO</h4>
                                      </div>
                                      <div class="card-body">
                                          <div class="row">
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label for="meta_title" class="form-label">Meta Title
                                                          *</label>
                                                      <input type="text" placeholder="Type here"
                                                          class="form-control" id="meta_title"
                                                          name="meta_title" data-parsley-required="true"
                                                          value="{{ old('meta_title') }}" />
                                                      @error('meta_title')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label for="slug" class="form-label">Slug *</label>
                                                      <input type="text" placeholder="Type here"
                                                          class="form-control" data-parsley-required="true"
                                                          id="slug" name="slug"
                                                          value="{{ old('slug') }}" />
                                                      @error('slug')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label for="meta_description" class="form-label">Meta
                                                          description *</label>
                                                      <textarea placeholder="Type here" class="form-control" rows="4" data-parsley-required="true"
                                                          id="meta_description" name="meta_description">{{ old('meta_description') }}</textarea>
                                                      @error('meta_description')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                              <div class="col-lg-4">
                                                  <div class="mb-4">
                                                      <label for="canonical_url" class="form-label">Canonical
                                                          URL</label>
                                                      <input type="text" placeholder="Type here"
                                                          class="form-control" id="canonical_url"
                                                          data-parsley-required="true" name="canonical_url"
                                                          value="{{ old('canonical_url') }}" />
                                                      @error('canonical_url')
                                                          <div class="text-danger">{{ $message }}</div>
                                                      @enderror
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                  <!-- SEO end -->
                              </div>
                          </div>
                      </div>

                      <div class="col-lg-4">
                          <div class="card">
                              <div class="card-header">
                                  <h4>Upload Images</h4>
                              </div>
                              <div class="card-body">
                                  <div class="input-upload">
                                      {{-- <img src="assets/imgs/theme/upload.svg" alt="Upload Icon" id="upload-icon" /> --}}
                                      <input class="form-control" type="file" id="files" data-parsley-required="true" name="images[]" accept="image/*" multiple>
                                      <br><br>
                                      <div id="image-preview-container"></div>
                                      @error('images.*')
                                          <div class="text-danger">{{ $message }}</div>
                                      @enderror
                                  </div>
                              </div>
                          </div>

                          <!-- card end// -->
                          <div class="card mb-4">
                              <div class="card-header">
                                  <h4>Organization</h4>
                              </div>
                              <div class="card-body">
                                  <div class="row">
                                      <div class="col-md-6 gx-2">
                                          <div class="mb-3">
                                              <label class="form-label">Category</label>
                                              <select class="form-select" name="category_id" id="category_id"
                                                  data-parsley-required="true">
                                                  <option value="">Select Category</option>
                                              </select>
                                              @error('category_id')
                                                  <div class="text-danger">{{ $message }}</div>
                                              @enderror
                                          </div>
                                      </div>
                                      <!-- row.// -->

                                      <div class="col-md-6 gx-2">
                                          <div class="mb-3">
                                              <label class="form-label">Brand</label>
                                              <select class="form-select" id="brand" name="brand"
                                                  data-parsley-required="true">
                                                  <option value="">Select Brand</option>
                                              </select>
                                              @error('brand')
                                                  <div class="text-danger">{{ $message }}</div>
                                              @enderror
                                          </div>
                                      </div>
                                      <!-- row.// -->

                                      <div class="col-md-6 gx-2">
                                          <label class="form-label">Model</label>
                                          <select class="form-select" id="product_model" name="product_model"
                                              data-parsley-required="true">
                                              <option value="">Select Model</option>
                                          </select>
                                          @error('product_model')
                                              <div class="text-danger">{{ $message }}</div>
                                          @enderror
                                      </div>

                                      <div class="col-md-6 gx-2">
                                          <div class="mb-4">
                                              <label class="form-label">SKU * </label>
                                              <input type="text" class="form-control mb-2" id="sku" name="sku" value="{{ old('sku') }}" data-parsley-required="true" />
                                              <button type="button" class="btn btn-sm btn-light" onclick="generateSKU()">Generate SKU</button>
                                              @error('sku')
                                                  <div class="text-danger">{{ $message }}</div>
                                              @enderror
                                          </div>
                                      </div>
                                  </div>
                                  <!-- row.// -->
                              </div>
                          </div>
                          <!-- card end// -->
                          <!-- card end// -->
                          <div class="card mb-4">
                              <div class="card-header">
                                  <h4>Shipping</h4>
                              </div>
                              <div class="card-body">
                                  <div class="col-sm-12 mb-3">
                                      <label class="form-label">Select Shipping</label>
                                      <select class="form-select" id="shipping_id" name="shipping_id"
                                          data-parsley-required="true">
                                          <option value="">Select Model</option>
                                      </select>
                                      @error('shipping_id')
                                          <div class="text-danger">{{ $message }}</div>
                                      @enderror
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </form>
              <div id="message" class="mt-3"></div>
          </div>
      </div>
  </div>
</div>
