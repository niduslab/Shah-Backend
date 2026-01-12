@extends('admin.dashboard')
@section('title', 'NidusCart - Manage Products')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Products List</h2>
            <p>Lorem ipsum dolor sit amet.</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
            <a href="{{ route('admin.add.product') }}" class="btn btn-primary btn-sm rounded">Create new</a>
        </div>
    </div>
    <div class="card mb-4">
        <header class="card-header">
            <div class="row align-items-center">
                <div class="col col-check flex-grow-0">
                    <div class="form-check ms-2">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                </div>
                <div class="col-md-3 col-12 me-auto mb-md-0 mb-3">
                    <select class="form-select">
                        <option selected>All category</option>
                        <option>Electronics</option>
                        <option>Clothes</option>
                        <option>Automobile</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <input type="date" value="02.05.2021" class="form-control" />
                </div>
                <div class="col-md-2 col-6">
                    <select class="form-select">
                        <option selected>Status</option>
                        <option>Active</option>
                        <option>Disabled</option>
                        <option>Show all</option>
                    </select>
                </div>
            </div>
        </header>
        <!-- card-header end// -->
        <div class="card-body">




            <!-- itemlist  .// -->
            <div class="col-md-12">

                <div class="table-responsive">

                    <table class="table table-striped table-hover data-table">
                        <thead>
                            <tr>
                                <th width="10%">Image</th>
                                <th width="32%">Name</th>
                                <th width="10%">Price</th>
                                <th width="11%">Status</th>
                                <th width="12%">Date</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>




                        </tbody>
                    </table>

                </div>

            </div>

            <!-- itemlist  .// -->




        </div>
        <!-- card-body end// -->
    </div>
    <!-- card end// -->



</section>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>


<script type="text/javascript">

    $(function () {
      var table = $('.data-table').DataTable({

          processing: true,
          serverSide: true,
          ajax: "{{ route('admin.show.product') }}",

          columns: [
              {data: 'image', name: 'image'},
              {data: 'product_name', name: 'product_name'},
              {data: 'price', name: 'price'},
              {data: 'status', name: 'status'},
              {data: 'created_date', name: 'created_date'},
              {data: 'action', name: 'action', orderable: false, searchable: false},
          ]

      });



    });

  </script>

@endsection
