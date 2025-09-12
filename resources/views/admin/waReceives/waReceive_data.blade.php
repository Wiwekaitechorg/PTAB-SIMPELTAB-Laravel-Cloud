<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th scope="col">NO</th>
            <th scope="col">No Telp</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
            <?php $no=1; ?>
            @foreach($data as $v)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $v->no_telp }}</td>
                <td><button class="btn btn-danger" onclick="hapus({{ $v->id }})">Hapus</button></td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" class="text-center">Data tidak ada...</td>
            </tr>
        @endif
    </tbody>
</table>