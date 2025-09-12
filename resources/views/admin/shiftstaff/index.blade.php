<@extends('layouts.admin3')
@section('content')
{{-- @can('holiday_create') --}}
    {{-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.holiday.create') }}">
                {{ trans('global.add') }} {{ trans('global.holiday.title_singular') }}
            </a>
        </div>
    </div> --}}
    
{{-- @endcan --}}
<div class="card">

    <div class="card-header">
        {{ trans('global.holiday.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
        <div class="container">
            <br />
            <h1 class="text-center">{{ $shift_parent->title }}</h1>
            <br />
        
            <div id="calendar"></div>
        
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent
   
<script>
function myFunction(data) {
    swal.close()

    Swal.fire({
  title: 'Apa anda ingin menghapus data ini?',
  showDenyButton: true,
  confirmButtonText: 'ya',
  denyButtonText: `tidak`,
}).then((result) => {
  /* Read more about isConfirmed, isDenied below */
  if (result.isConfirmed) {
    $.ajax({
                    url:'{{ route("admin.shift_planner_staff.action") }}',
                    type:"POST",
                    data:{
                        id:data,
                        type:"delete"
                    },
                    success:function(response)
                    {
                        location.reload();
                        // calendar.fullCalendar('refetchEvents');
                        // alert("Event Deleted Successfully");
                    }
                })
  } else if (result.isDenied) {
    // Swal.fire('Changes are not saved', '', 'info')
  }
})
    // if(confirm("Are you sure you want to remove it?"))
    //         {
             
    //         }
}
$(document).ready(function () {

    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
        }
    });

    var calendar = $('#calendar').fullCalendar({
        editable:false,
        // eventStartEditable: false,
        // disableDragging: true,
        eventTextColor : '#FFFFFF',
        displayEventTime: false,
        header:{
            left:'prev,next today',
            center:'title',
            right:'month'
        },
        events:'{{ route("admin.shift_planner_staff.index", ["id"=>$_REQUEST["id"]]) }}',
        selectable:true,
        
        selectHelper: true,
        select: async function (start, end, allDay) {
	  const { value: formValues } = await Swal.fire({
		title: 'Tambah Staff',
		html:
        //   '<select id="staff_id" class="swal2-input" placeholder="Enter URL">'+
        //     '@foreach ($pgw as $data)'+
        //     '<option value="{{$data->id}}"> {{$data->name}} </option>'+  
        //    '@endforeach'+

           '<div class="form-group">'+
                    '<label for="staff_id">Staff*</label>'+
                    '<select id="staff_id" name="staff_id" class="form-control">'+
                        '@foreach ($pgw as $data)'+
            '<option value="{{$data->id}}"> {{$data->name}} </option>'+  
           '@endforeach'+
                    '</select>'+
                   
                '</div>'+

                '<div class="form-group">'+
                    '<label for="shift_group_id">Shift*</label>'+
                    '<select id="shift_group_id" name="shift_group_id" class="form-control">'+
                        '@foreach ($sg as $data)'+
            '<option value="{{$data->id}}"> {{$data->title}} </option>'+  
           '@endforeach'+
                    '</select>'+
                '</div>'
            
        //     '</select>'+ '<select id="shift_group_id" class="swal2-input" placeholder="Enter URL">'+
        //     '@foreach ($sg as $data)'+
        //     '<option value="{{$data->id}}"> {{$data->title}} </option>'+  
        //    '@endforeach'+
            
        //     '</select>'
            ,

		focusConfirm: false,
		preConfirm: () => {
		  return [
            document.getElementById('staff_id').value,
            document.getElementById('shift_group_id').value,
		  ]
		}
	  });

            // var title = prompt('Event Title:');

    //   	const { value: formValues } = await Swal.fire({
	// 	title: 'Add Event',
	// 	html:
	// 	  '<input id="swalEvtTitle" class="swal2-input" placeholder="Enter title">' +
	// 	  '<textarea id="swalEvtDesc" class="swal2-input" placeholder="Enter description"></textarea>' +
	// 	  '<input id="swalEvtURL" class="swal2-input" placeholder="Enter URL">',
	// 	focusConfirm: false,
	// 	preConfirm: () => {
	// 	  return [
	// 		document.getElementById('swalEvtTitle').value,
	// 		document.getElementById('swalEvtDesc').value,
	// 		document.getElementById('swalEvtURL').value
	// 	  ]
	// 	}
	//   });
    // console.log(""+document.getElementById('staff').value)
            if(formValues)
            {
             
                var start = $.fullCalendar.formatDate(start, 'Y-MM-DD HH:mm:ss');

                // var end = $.fullCalendar.formatDate(end, 'Y-MM-DD HH:mm:ss');

                $.ajax({
                    url:'{{ route("admin.shift_planner_staff.action") }}',
                    type:"POST",
                    data:{
                        staff_id : document.getElementById('staff_id').value,
                        shift_group_id : document.getElementById('shift_group_id').value,
                        start: start,
                        end: start,
                        type: 'add'
                    },
                    success:function(data)
                    {
                        console.log(data)
                        if(data == "fail" ){
                            Swal.fire(
  'Failed',
  'Periksa kembali data sudah ada data sama, cek juga tanggal yang dipilih jika tanggal sekarang tidak bisa dirubah',
  'error'
)
calendar.fullCalendar('refetchEvents');
                    
                           
                        }
                    else{
                        calendar.fullCalendar('refetchEvents');
                        Swal.fire(
  'Success',
  'Data Shift Berhasil Ditambahkan',
  'success'
)
                    }
                    }
                })
            }
        },
        editable:true,
        eventResize: function(event, delta)
        {
            var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss');
            var end = $.fullCalendar.formatDate(event.end, 'Y-MM-DD HH:mm:ss');
            var title = event.title;
            var id = event.id;
            $.ajax({
                url:'{{ route("admin.shift_planner_staff.action") }}',
                type:"POST",
                data:{
                    title: title,
                    start: start,
                    end: end,
                    id: id,
                    type: 'update'
                },
                success:function(response)
                {
                    calendar.fullCalendar('refetchEvents');
                    alert("Event Updated Successfully");
                }
            })
        },
        eventDrop: function(event, delta)
        {
            // alert("2")
            var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss')
            var end = "";
            var id = event.id;
            // alert(id,end)
            $.ajax({
                url:'{{ route("admin.shift_planner_staff.action") }}',
                type:"POST",
                data:{
                    start: start,
                    end: start,
                    id: id,
                    type: 'updateD'
                },
                success:function(data)
                    {
                        console.log(data)
                        if(data == "fail" ){
                            Swal.fire(
  'Failed',
  'Periksa kembali data sudah ada data sama, cek juga tanggal yang dipilih jika tanggal sekarang tidak bisa dirubah',
  'error'
)
                        calendar.fullCalendar('refetchEvents');
                    }
                    else{
                        calendar.fullCalendar('refetchEvents');
                        Swal.fire(
  'Success',
  'Data Shift Berhasil Ditambahkan',
  'success'
)
                    }
                    }
            })
        },

        eventClick:function(details)
        {
            var id = details.id;
            var t = 3;
            console.log(details)
            $.ajax({
                    url:'{{ route("admin.shift_planner_staff.edit") }}',
                    type:"GET",
                    data:{
                        id: id,
                    },
                    success:function(data)
                    {
                        console.log(data)
                        Swal.fire({
		title: 'Edit/Hapus',
		html:
		//   data[1].title +data[1].user_id+t+

            '<div class="form-group">'+
                    '<label for="staff_id">Staff*</label>'+
                    '<select id="staff_id" name="staff_id" class="form-control">'+
               
            data[0]+
            '</select>'+
            '</div>'+
            '<button onclick="myFunction('+id+')" type="button" class="btn btn-danger">Hapus</button>'
            ,
		focusConfirm: false,
		preConfirm: () => {
            // alert(id)
            $.ajax({
                    url:'{{ route("admin.shift_planner_staff.action") }}',
                    type:"POST",
                    data:{
                        id : id,
                        staff_id :  document.getElementById('staff_id').value,
                        type: 'update'
                    },
                    success:function(data)
                    {
                        console.log(data)
                        if(data == "fail" ){
                        alert("Data sudah ada");
                    }
                    else{
                        calendar.fullCalendar('refetchEvents');
                        Swal.fire(
  'Success',
  'Data Shift Berhasil Dirubah',
  'success'
)
                    }
                    }
                })
           
		}
	  });
                    }
                })
                
   
            // if(confirm("Are you sure you want to remove it?"))
            // {
            //     var id = event.id;
            //     $.ajax({
            //         url:'{{ route("admin.shift_planner_staff.action") }}',
            //         type:"POST",
            //         data:{
            //             id:id,
            //             type:"delete"
            //         },
            //         success:function(response)
            //         {
            //             calendar.fullCalendar('refetchEvents');
            //             alert("Event Deleted Successfully");
            //         }
            //     })
            // }
        }
    });

});
  
</script>
  
@endsection
