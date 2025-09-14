@extends('layouts.admin')
@section('content')
<div class="container">
    <div class="row">
        <!-- <div class="col-md-10 offset-md-1 "> -->
            <!-- <div class="row"> -->
            @php
            {{ $per_page=10; }}
            @endphp
            @foreach($lock_groups as $key => $lock_group) 
            @php
            {{ $first_group=$key*$per_page; $last_group=($key*$per_page)+$per_page; }}
            @endphp    
            <div class="col-md-3 mt-5 mr-4 ml-4 text-center" style="background-color: #333;">
                    <a href="{{ route('admin.spp.sppprintall',['locks' => $lock_group['data']]) }}" class="nav-link ">
                        <div style="height:95px">
                            <div class="mt-3 mb-3">
                                <i class="nav-icon fas fa-print fa-3x" ></i>
                                <hr>
                                <p>Print SPP Area: {{$lock_group['title']}}<p>
                            </div> 
                        </div>
                    </a>
                </div>
                @endforeach
            <!-- </div>  -->
        <!-- </div> -->
    </div>
</div>
@section('scripts')
@endsection
@endsection