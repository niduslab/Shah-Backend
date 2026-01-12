@foreach ( $subcategories as $sub)

<option value="{{ $sub->id }}"> {{ $parent  . "->". $sub->category_name}}</option>

    @if ( count( $sub->childrenRecursive) > 0 )
        @php
            $parents =  $parent.'->'.$sub->category_name;

        @endphp

        @include('admin.categories.subcategories', ['subcategories'=> $sub->childrenRecursive, 'parent'=>$parents] );

    @endif


@endforeach


{{-- @foreach ( $subcategories as $sub)

<option value="{{ $sub->id }}"> {{ $parent . "->". $sub->category_name}}</option>

    @if ( count( $sub->childrenRecursive) > 0 )
        @php
            $parents =$sub->category_name. "->".$sub->category_name;

        @endphp
        <option > {{  $parents}}</option>


        @include('admin.components.subcategories', ['subcategories'=> $sub->childrenRecursive, 'parent'=>$parents] );

    @endif


@endforeach --}}
