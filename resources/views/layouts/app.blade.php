<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!--Font awesome Icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- datatable css -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @yield('header_css')
    <!-- --custom css -->
    <link rel="stylesheet" href="{{ asset('css/custom.css?1') }}">
</head>
<body>
   

<div class="wrapper">


    <div class="body-overlay"></div>
    
    
            @include('layouts.sidebar')
            
            
    
            <!-- Page Content  -->
            <div id="content">
            
                
                @include('layouts.nav')
                
                
                <div class="main-content">
                
                        @yield('main_content')
                        
                        @include('layouts.footer')
                        
                </div>
                        
                    
    
            </div>
        </div>
        
      
    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- datatable js -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>


    @yield('footer_js_links')

    <script type="text/javascript">
  $(document).ready(function () {
    // Sidebar collapse toggle
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });
    

    // Mobile overlay toggle
    
    $('.body-overlay, .navbar-toggler').on('click', function () {
        $('#sidebar, .body-overlay').toggleClass('show-nav');
    });

  });
</script>


    <script type="text/javascript">
        
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.data') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString();
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });        
      
    
    
         @yield('footer_js')
               
           
    </script>
      
    
</body>
</html>