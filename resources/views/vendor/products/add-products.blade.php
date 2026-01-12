@extends('app')


@section('content')

<section class="content-main">
 <form action={{ route('store.product') }} method="POST" >
    @csrf
    <div class="row">
        <div class="col-9">
            <div class="content-header">
                <h2 class="content-title">Add New Product</h2>
                <div>
                    <button class="btn btn-md rounded font-sm hover-up" type="submit">Save</button>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">


                <div class="card-body">

                        <div class="mb-4">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" placeholder="Type here" class="form-control" id="product_name" name="product_name" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea placeholder="Type here" class="form-control" rows="4" id="description" name="description" ></textarea>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-4">
                                    <label class="form-label">Price</label>
                                    <div class="row gx-2">
                                        <input  type="text" class="form-control" id="price" name="price" />
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-lg-4">
                                <label class="form-label">Weight Type</label>
                                <select class="form-select" id="weight_type" name="weight_type">
                                    <option value="gm">gm</option>
                                    <option value="kg">kg</option>
                                    <option value="lb">lb</option>

                                </select>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-4">
                                    <label class="form-label">Weight Value</label>
                                    <input  type="text" class="form-control" id="weight_value" name="weight_value" />
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-4">
                                    <label class="form-label">Stock</label>
                                    <input  type="text" class="form-control" id="stock" name="stock" />
                                </div>
                            </div>


                        </div>






                    <!-- SEO start -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>SEO</h4>
                        </div>
                        <div class="card-body">
                            <form>



                                <div class="mb-4">
                                    <label for="product_name" class="form-label">Page Title</label>
                                    <input type="text" placeholder="Type here" class="form-control" id="product_title" name="product_title"/>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Meta description</label>
                                    <textarea placeholder="Type here" class="form-control" rows="4" id="meta_description" name="meta_description" ></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="product_name" class="form-label">Canonical URL</label>
                                    <input type="text" placeholder="Type here" class="form-control" id="canonical_url" name="canonical_url"/>
                                </div>

                                <div class="mb-4">
                                    <label for="product_name" class="form-label">Slug </label>
                                    <input type="text" placeholder="Type here" class="form-control" id="slug " name="slug "/>
                                </div>



                            </form>
                        </div>
                    </div>
                    <!-- SEO end// -->



                    </form>
                </div>
            </div>



        </div>
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Media</h4>
                </div>
                <div class="card-body">
                    <div class="input-upload">
                        <img src="assets/imgs/theme/upload.svg" alt="" />
                        {{-- <input class="form-control" type="file" name="images[]" id="images" multiple required> --}}
                        <input type="file" id="files" name="images[]" multiple><br><br>
                    </div>
                </div>
            </div>
            <!-- card end// -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Organization</h4>
                </div>
                <div class="card-body">

                    <div class="row gx-2">
                        <div class="col-sm-12 mb-3">
                            <label class="form-label">Category</label>

                            <select class="form-select" name="category_id" id="category_id">
                                <option value="">Select Category</option>
                                @foreach ( $categories as $category)
                                    <option value="{{ $category->id}}"> {{  $category->category_name}}</option>
                                    @if ( count( $category->childrenRecursive) > 0 )
                                    @include('admin.components.subcategories', ['subcategories'=> $category->childrenRecursive, 'parent'=>$category->category_name] );
                                    @endif
                                @endforeach
                            </select>

                        </div>

                    </div>
                    <!-- row.// -->


                    <div class="row gx-2">
                        <div class="col-sm-12 mb-3">
                            <label class="form-label">Brand</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">Select Brand</option>
                                @foreach ( $brandList as $brand)
                                <option value="{{ $brand->id}}"> {{  $brand->brand_name}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <!-- row.// -->


                    <div class="row gx-2">
                        <div class="col-sm-12 mb-3">
                            <label class="form-label">Model</label>

                                <select class="form-select" id="product_model" name="product_model">
                                 <option value="">Select Model</option>
                                    @foreach ( $modelList as $model)
                                    <option value="{{ $model->id}}"> {{  $model->model_name}} </option>
                                    @endforeach
                                </select>

                        </div>

                    </div>
                    <!-- row.// -->




                </div>
            </div>
            <!-- card end// -->
        </div>


    </div>
 </form>
</section>

@endsection
