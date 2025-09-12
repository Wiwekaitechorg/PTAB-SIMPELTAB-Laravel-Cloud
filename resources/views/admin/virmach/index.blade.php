@if ($message = Session::get('success'))
    <div class="success">
        <strong>{{ $message }}</strong>
    </div>
@endif
 
<form action="{{ route("admin.virmach.store") }}" method="POST" enctype="multipart/form-data">
    @csrf
    <p>
        <input type="file" name="profile_image" />
    </p>
    <button type="submit" name="submit">Submit</button>
</form>