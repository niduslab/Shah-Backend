@extends('layouts.app')

@section('title', 'NidusCart - Manage Categories')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Categories</h2>
            <p>Add a category</p>
        </div>

        <div>
            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>


        <div>
            <input type="text" placeholder="Search Categories" class="form-control bg-white" />
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="category_name" class="form-label" >Name</label>
                            <input type="text" placeholder="Type here" class="form-control" name="category_name" value="{{ old('category_name') }}" id="category_name" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Parent Category</label>
                            <select class="form-select" name="parent_category_id" id="parent_category_id">
                                <option value="">Select Category</option>
                                @foreach ( $categories as $category)
                                    <option value="{{ $category->id}}"> {{  $category->category_name}}</option>
                                    @if ( count( $category->childrenRecursive) > 0 )
                                    @include('admin.categories.subcategories', ['subcategories'=> $category->childrenRecursive, 'parent'=>$category->category_name] );
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary">Create category</button>
                        </div>


                    </form>
                </div>
                <div class="col-md-9 card p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-responsive data-table">
                            <thead>
                                <tr>
                                    <th width="10%">Id</th>
                                    <th width="30%">Name</th>
                                    <th width="30%">Parent Id</th>
                                    <th width="20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>


                              {{-- @foreach ( $categoryList as $category )



                                <tr>
                                    <td class="text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" />
                                        </div>
                                    </td>


                                    <td> {{ $category->id }}</td>

                                    <td><b>  {{ $category->category_name }} </b></td>

                                    <td>

                                            @foreach ($categoryList as $item)
                                                @if ($item->id == $category->parent_category_id)
                                                    <b>{{ $item->category_name }}</b>
                                                @endif
                                            @endforeach
                                            {{ $category->parent_category_id ? null: "None" }}

                                    </td>



                                    <td class="text-end">
                                        <div class="dropdown">
                                            <a href="#" data-bs-toggle="dropdown" class="btn btn-light rounded btn-sm font-sm"> <i class="material-icons md-more_horiz"></i> </a>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">View detail</a>
                                                <a class="dropdown-item" href="#">Edit info</a>
                                                <a class="dropdown-item text-danger" href="#">Delete</a>
                                            </div>
                                        </div>

                                    </td>


                                </tr>

                                @endforeach --}}


                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- .col// -->
            </div>
            <!-- .row // -->
        </div>
        <!-- card body .// -->
    </div>
    <!-- card .// -->
</section>


<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script type="text/javascript">

    $(function () {
      var table = $('.data-table').DataTable({

          processing: true,
          serverSide: true,
          ajax: "{{ route('admin.show.category') }}",

          columns: [
              {data: 'id', name: 'id'},
              {data: 'category_name', name: 'category_name'},
              {data: 'parent_category_id', name: 'parent_category_id'},
              {data: 'action', name: 'action', orderable: false, searchable: false},
          ]

      });



    });

  </script>

@endsection
