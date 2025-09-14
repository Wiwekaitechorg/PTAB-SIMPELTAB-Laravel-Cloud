@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Enter OTP</h3>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <div class="form-group">
            <label for="otp">OTP</label>
            <input type="text" name="otp" id="otp" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Verify OTP</button>
    </form>
</div>
@endsection
