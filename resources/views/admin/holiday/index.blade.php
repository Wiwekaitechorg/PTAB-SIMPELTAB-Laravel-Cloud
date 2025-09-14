lolll
{{-- <!DOCTYPE html>
<html>
<head>
    <title>How to Use Fullcalendar in Laravel 8</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

</head>
<body> --}}
  
    @extends('layouts.admin3')
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
            <h1 class="text-center" >Hari Libur</h1>
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
    if(confirm("Are you sure you want to remove it?"))
            {
                $.ajax({
                    url:'{{ route("admin.holiday.action") }}',
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
            }
}
$(document).ready(function () {

    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
        }
    });

    var calendar = $('#calendar').fullCalendar({
        editable:false,
        eventStartEditable: false,
        disableDragging: true,
        displayEventTime: false,
        eventTextColor : '#FFFFFF',
        eventColor : '#ff0000',
        header:{
            left:'prev,next today',
            center:'title',
            right:'month'
        },
        events:'{{ route("admin.holiday.index") }}',
        selectable:true,
        
        selectHelper: true,
        select: async function (start, end, allDay) {
	  const { value: formValues } = await Swal.fire({
		title: 'Tambah Hari Libur',
		html:
        '<div class="form-group>'+
                '<label for="title">Acara*</label>'+
                '<input type="text" id="title" name="title" placeholder="Masukan Hari Libur" class="form-control" required>'+
            '</div>'+
            '<div class="form-group>'+
                '<label for="title">Deskripsi*</label>'+
                '<textarea id="description" class="form-control" placeholder="Masukan Deskripsi"></textarea>'+
            '</div>'
		  ,
		focusConfirm: false,
		preConfirm: () => {
		  return [
			document.getElementById('title').value,
			document.getElementById('description').value,
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
    console.log(""+document.getElementById('title').value)
            if(formValues)
            {
                var start = $.fullCalendar.formatDate(start, 'Y-MM-DD HH:mm:ss');

                var end = $.fullCalendar.formatDate(end, 'Y-MM-DD HH:mm:ss');

                $.ajax({
                    url:'{{ route("admin.holiday.action") }}',
                    type:"POST",
                    data:{
                        title : document.getElementById('title').value,
                        description : document.getElementById('description').value,
                        start: start,
                        end: end,
                        type: 'add'
                    },
                    success:function(data)
                    {
                        console.log(data)
                        if(data == "fail" ){
                        alert("Data sudah ada");
                    }
                    else{
                        calendar.fullCalendar('refetchEvents');
                        alert("Event Created Successfully");
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
                url:'{{ route("admin.holiday.action") }}',
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
            var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss');
            var end = $.fullCalendar.formatDate(event.end, 'Y-MM-DD HH:mm:ss');
            var title = event.title;
            var id = event.id;
            $.ajax({
                url:'{{ route("admin.holiday.action") }}',
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

        eventClick:function(details)
        {
            var id = details.id;
            var t = 3;
            console.log(details)
          
            $.ajax({
                    url:'{{ route("admin.holiday.edit") }}',
                    type:"GET",
                    data:{
                        id: id,
                    },
                    success:function(data)
                    {
                        t = String(data.id);
                        console.log(data)
                        Swal.fire({
		title: 'Add Event',
		html:
          '<div class="form-group>'+
                '<label for="title">Acara*</label>'+
                '<input type="text" id="title" name="title" placeholder="Masukan Hari Libur" value ="'+
                data.title+
                '" class="form-control" required>'+
            '</div>'+
            '<div class="form-group>'+
                '<label for="title">Deskripsi*</label>'+
                '<textarea id="description" class="form-control" placeholder="Masukan Deskripsi">'+
                    data.description+
                    '</textarea>'+
            '</div>'+
            '<button onclick="myFunction('+id+')">Delete</button>'
            ,
		focusConfirm: false,
		preConfirm: () => {
            alert(t)
            $.ajax({
                    url:'{{ route("admin.holiday.action") }}',
                    type:"POST",
                    data:{
                        id : id,
                        title : document.getElementById('title').value,
                        description : document.getElementById('description').value,
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
                        alert("Event Created Successfully");
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
            //         url:'{{ route("admin.holiday.action") }}',
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

  
{{-- </body>
</html> --}}
