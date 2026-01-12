@extends('layouts.app')

@section('title', 'NidusCart - Manage Models')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Models</h2>
            <p>Add a Models</p>
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
                    <form action="{{ route('admin.brand.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="brand_name" class="form-label" >Model Name</label>
                            <input type="text" placeholder="Type here" class="form-control" name="model_name" value="{{ old('model_name') }}" id="model_name" required />
                        </div>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary text-center">Create new brand</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-9">
                    <div class="table-responsive">


                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th width="10%">Id</th>
                                    <th width="30%">Model Name</th>
                                </tr>
                            </thead>
                            <tbody>
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
          ajax: "{{ route('admin.manage.models') }}",

          columns: [
              {data: 'id', name: 'id'},
              {data: 'model_name', name: 'model_name'},
              {data: 'action', name: 'action', orderable: false, searchable: false},
          ]

      });



    });

  </script>

@endsection
